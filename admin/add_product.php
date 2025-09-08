<?php
session_start();

require_once '../includes/functions.php';
$conn = getConnection();

$errors = [];
$success = false;

// Simple slug generation function
function createSlug($name, $conn)
{
  $slug = strtolower(trim($name));
  $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
  $slug = preg_replace('/\s+/', '-', $slug);
  $slug = preg_replace('/-+/', '-', $slug);
  $slug = trim($slug, '-');

  // Check if slug exists and make it unique
  $originalSlug = $slug;
  $counter = 1;

  while (true) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM products WHERE slug = ?");
    mysqli_stmt_bind_param($stmt, "s", $slug);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (($data['count'] ?? 0) == 0) break;

    $slug = $originalSlug . '-' . $counter;
    $counter++;
  }

  return $slug;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get form data
  $name = trim($_POST['name'] ?? '');
  $sku = trim($_POST['sku'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;
  $discount = isset($_POST['discount']) ? floatval($_POST['discount']) : 0.0;
  $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
  $weight = isset($_POST['weight']) ? floatval($_POST['weight']) : 0;
  $category_id = isset($_POST['category']) ? intval($_POST['category']) : 0;
  $brand_id = isset($_POST['brand']) ? intval($_POST['brand']) : 0;
  $is_featured = isset($_POST['is_featured']) ? 1 : 0;
  $is_active = isset($_POST['is_active']) ? 1 : 0;

  // Validation
  if (empty($name) || empty($sku) || empty($description) || $price <= 0 || $stock < 0 || $category_id <= 0 || $brand_id <= 0) {
    $errors[] = "Please fill all required fields with valid values.";
  }

  // Check SKU uniqueness (prepared)
  if (!empty($sku)) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM products WHERE sku = ?");
    mysqli_stmt_bind_param($stmt, "s", $sku);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (($data['count'] ?? 0) > 0) {
      $errors[] = "SKU already exists. Please use a unique SKU.";
    }
  }

  // Process uploaded images first (so we can include filenames in products insert)
  $uploadDir = __DIR__ . '/../uploads/';
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

  $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

  // We'll map uploaded file fields to product columns
  $imageCols = ['main_image' => '', 'image_1' => '', 'image_2' => '', 'image_3' => ''];

  // image1 is main image and required in the form
  for ($i = 1; $i <= 4; $i++) {
    $field = 'image' . $i;
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
      $file = $_FILES[$field];
      $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      if (in_array($extension, $allowedExt)) {
        $filename = uniqid('img_', true) . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
          if ($i === 1) $imageCols['main_image'] = $filename;
          elseif ($i === 2) $imageCols['image_1'] = $filename;
          elseif ($i === 3) $imageCols['image_2'] = $filename;
          elseif ($i === 4) $imageCols['image_3'] = $filename;
        } else {
          $errors[] = "Failed to move uploaded file for $field.";
        }
      } else {
        // ignore unsupported type but if main image missing then error below
      }
    }
  }

  // Ensure main image provided (form had required). If not present from files, error.
  if (empty($imageCols['main_image'])) {
    $errors[] = "Main image is required.";
  }

  // Generate slug if no errors so far
  if (empty($errors)) {
    $slug = createSlug($name, $conn);
  }

  // Insert product if still no errors
  if (empty($errors)) {
    // Use prepared statement to insert product including image columns
    $sql = "INSERT INTO products (product_name, sku, slug, description, price, discount, stock, weight, brand_id, category_id, main_image, image_1, image_2, image_3, is_featured, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
      $errors[] = "DB prepare failed: " . mysqli_error($conn);
    } else {
      mysqli_stmt_bind_param(
        $stmt,
        "ssssddiiiissssii",
        $name,
        $sku,
        $slug,
        $description,
        $price,
        $discount,
        $stock,
        $weight,
        $brand_id,
        $category_id,
        $imageCols['main_image'],
        $imageCols['image_1'],
        $imageCols['image_2'],
        $imageCols['image_3'],
        $is_featured,
        $is_active
      );

      $exec = mysqli_stmt_execute($stmt);
      if (!$exec) {
        $errors[] = "Failed to add product: " . mysqli_stmt_error($stmt);
      } else {
        $product_id = (int) mysqli_insert_id($conn);

        // Save product specs (delete existing then insert)
        if (!empty($_POST['spec_group_name']) && is_array($_POST['spec_group_name'])) {
          $delStmt = mysqli_prepare($conn, "DELETE FROM product_specs WHERE product_id = ?");
          mysqli_stmt_bind_param($delStmt, 'i', $product_id);
          mysqli_stmt_execute($delStmt);
          mysqli_stmt_close($delStmt);

          $insStmt = mysqli_prepare(
            $conn,
            "INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES (?, ?, ?, ?, ?)"
          );

          foreach ($_POST['spec_group_name'] as $gIdx => $groupRaw) {
            $groupName = trim($groupRaw) ?: 'General';
            $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $groupName);

            $names  = $_POST['spec_name_' . $safeKey]  ?? [];
            $values = $_POST['spec_value_' . $safeKey] ?? [];
            $orders = $_POST['spec_order_' . $safeKey] ?? [];

            $rows = max(count($names), count($values));
            for ($j = 0; $j < $rows; $j++) {
              $sname  = trim($names[$j]  ?? '');
              $svalue = trim($values[$j] ?? '');
              if ($sname === '' && $svalue === '') continue;
              $sorder = isset($orders[$j]) ? intval($orders[$j]) : ($j * 10);
              mysqli_stmt_bind_param($insStmt, 'isssi', $product_id, $sname, $svalue, $groupName, $sorder);
              mysqli_stmt_execute($insStmt);
              if (mysqli_stmt_errno($insStmt)) {
                $errors[] = "Spec insert failed: " . mysqli_stmt_error($insStmt);
              }
            }
          }
          if ($insStmt) mysqli_stmt_close($insStmt);
        }

        // If no errors now, redirect to products page
        if (empty($errors)) {
          header("Location: products.php?insert=success");
          exit;
        }
      }
      mysqli_stmt_close($stmt);
    }
  }
}

// Get categories and brands for dropdowns
$categories = getRootCategories();
$brandsQuery = "SELECT brand_id, brand_name, category_id FROM brands ORDER BY brand_name ASC";
$brandsResult = mysqli_query($conn, $brandsQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Add Product - PC ZONE Admin</title>

  <?php require './includes/header-link.php'; ?>

</head>

<body>
  <?php include './includes/header.php'; ?>
  <?php $current_page = 'products';
  include './includes/sidebar.php'; ?>

  <main class="main-content my-4">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h1>Add New Product</h1>
              <a href="products.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Products
              </a>
            </div>

            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
              <div class="row g-3">
                <!-- Product Name -->
                <div class="col-md-6">
                  <label class="form-label">Product Name <span class="required">*</span></label>
                  <input type="text" name="name" class="form-control"
                    value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                </div>

                <!-- SKU -->
                <div class="col-md-6">
                  <label class="form-label">SKU <span class="required">*</span></label>
                  <input type="text" name="sku" class="form-control"
                    value="<?= isset($_POST['sku']) ? htmlspecialchars($_POST['sku']) : '' ?>" required>
                </div>

                <!-- Description -->
                <div class="col-12">
                  <label class="form-label">Description <span class="required">*</span></label>
                  <textarea name="description" class="form-control" rows="4" required><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                </div>

                <!-- Price -->
                <div class="col-md-4">
                  <label class="form-label">Price (₹) <span class="required">*</span></label>
                  <input type="number" step="0.01" min="0.01" name="price" class="form-control"
                    value="<?= isset($_POST['price']) ? $_POST['price'] : '' ?>" required>
                </div>

                <!-- Discount -->
                <div class="col-md-4">
                  <label class="form-label">Discount (%)</label>
                  <input type="number" step="0.01" min="0" max="100" name="discount" class="form-control"
                    value="<?= isset($_POST['discount']) ? $_POST['discount'] : '0' ?>">
                </div>

                <!-- Stock -->
                <div class="col-md-4">
                  <label class="form-label">Stock Quantity <span class="required">*</span></label>
                  <input type="number" name="stock" class="form-control" min="0"
                    value="<?= isset($_POST['stock']) ? $_POST['stock'] : '' ?>" required>
                </div>

                <!-- Category -->
                <div class="col-md-6">
                  <label class="form-label">Category <span class="required">*</span></label>
                  <select name="category" class="form-select" id="categorySelect" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $category): ?>
                      <option value="<?= $category['category_id'] ?>"
                        <?= (isset($_POST['category']) && $_POST['category'] == $category['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['category_name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Brand -->
                <div class="col-md-6">
                  <label class="form-label">Brand <span class="required">*</span></label>
                  <select name="brand" class="form-select" id="brandSelect" required>
                    <option value="">-- Select Brand --</option>
                    <?php while ($brand = mysqli_fetch_assoc($brandsResult)): ?>
                      <option value="<?= $brand['brand_id'] ?>" data-category="<?= $brand['category_id'] ?>"
                        <?= (isset($_POST['brand']) && $_POST['brand'] == $brand['brand_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($brand['brand_name']) ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <!-- Weight -->
                <div class="col-md-6">
                  <label class="form-label">Weight (kg)</label>
                  <input type="number" step="0.01" min="0" name="weight" class="form-control"
                    value="<?= isset($_POST['weight']) ? $_POST['weight'] : '' ?>">
                </div>

                <!-- Status Options -->
                <div class="col-md-6">
                  <div class="row">
                    <div class="col-6">
                      <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured"
                          <?= (isset($_POST['is_featured'])) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Featured Product</label>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                          <?= (!isset($_POST['is_active']) || isset($_POST['is_active'])) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Product Specifications -->
                <div class="col-12">
                  <h5 class="mt-4 mb-3">Product Specifications</h5>

                  <div id="specGroupsContainer">
                    <!-- initial group -->
                    <div class="spec-group mb-3 border rounded p-2" data-group-index="0">
                      <div class="d-flex mb-2">
                        <input name="spec_group_name[]" class="form-control me-2 spec-group-name" value="General" placeholder="Group name (e.g. Processor)">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-group-btn">Remove Group</button>
                      </div>

                      <div class="spec-rows">
                        <div class="input-group mb-2 spec-row">
                          <input name="spec_name_General[]" class="form-control" placeholder="Spec name (e.g. Cores)">
                          <input name="spec_value_General[]" class="form-control" placeholder="Spec value (e.g. 8)">
                          <input type="number" name="spec_order_General[]" class="form-control w-25" placeholder="Order" value="10">
                          <button type="button" class="btn btn-outline-secondary remove-row-btn">−</button>
                        </div>
                      </div>

                      <div>
                        <button type="button" class="btn btn-sm btn-primary add-row-btn">+ Add spec</button>
                      </div>
                    </div>
                  </div>

                  <div class="mt-2">
                    <button id="addGroupBtn" type="button" class="btn btn-sm btn-success">+ Add Group</button>
                    <small class="text-muted d-block mt-2">Create groups like "Processor", "Performance" and add related specs.</small>
                  </div>
                </div>

                <!-- Product Images -->
                <div class="col-12">
                  <h5 class="mt-4 mb-3">Product Images</h5>
                  <div class="row">
                    <div class="col-md-3">
                      <label class="form-label">Main Image <span class="required">*</span></label>
                      <input type="file" name="image1" class="form-control" accept="image/*"
                        onchange="previewImage(this, 'preview1')" required>
                      <div id="preview1" class="preview-images"></div>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Image 2</label>
                      <input type="file" name="image2" class="form-control" accept="image/*"
                        onchange="previewImage(this, 'preview2')">
                      <div id="preview2" class="preview-images"></div>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Image 3</label>
                      <input type="file" name="image3" class="form-control" accept="image/*"
                        onchange="previewImage(this, 'preview3')">
                      <div id="preview3" class="preview-images"></div>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Image 4</label>
                      <input type="file" name="image4" class="form-control" accept="image/*"
                        onchange="previewImage(this, 'preview4')">
                      <div id="preview4" class="preview-images"></div>
                    </div>
                  </div>
                </div>

                <!-- Submit Buttons -->
                <div class="col-12 mt-4">
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                      <i class="fas fa-save"></i> Add Product
                    </button>
                    <a href="products.php" class="btn btn-secondary">
                      <i class="fas fa-times"></i> Cancel
                    </a>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const KEY = "pczone_add_product_draft";
      const form = document.querySelector('form');
      const specContainer = document.getElementById('specGroupsContainer');
      const addGroupBtn = document.getElementById('addGroupBtn');
      const cancelBtn = document.querySelector('a[href="products.php"]');
      const addBtn = form.querySelector('button[type="submit"]');

      function safeKey(name) {
        return String(name || 'Group').replace(/[^a-zA-Z0-9]/g, '_');
      }

      function escapeHtml(s) {
        return String(s || '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;');
      }

      function createRowEl(safe, name = '', value = '', order = 10) {
        const div = document.createElement('div');
        div.className = 'input-group mb-2 spec-row';
        div.innerHTML = `
      <input name="spec_name_${safe}[]" class="form-control" placeholder="Spec name" value="${escapeHtml(name)}">
      <input name="spec_value_${safe}[]" class="form-control" placeholder="Spec value" value="${escapeHtml(value)}">
      <input type="number" name="spec_order_${safe}[]" class="form-control w-25" placeholder="Order" value="${escapeHtml(order)}">
      <button type="button" class="btn btn-outline-secondary remove-row-btn">−</button>
    `;
        return div;
      }

      function buildGroupNode(groupObj) {
        const group = (groupObj && groupObj.group) ? groupObj.group : 'General';
        const safe = safeKey(group);
        const wrapper = document.createElement('div');
        wrapper.className = 'spec-group mb-3 border rounded p-2';
        wrapper.innerHTML = `
      <div class="d-flex mb-2">
        <input name="spec_group_name[]" class="form-control me-2 spec-group-name" value="${escapeHtml(group)}" placeholder="Group name (e.g. Processor)">
        <button type="button" class="btn btn-outline-danger btn-sm remove-group-btn">Remove Group</button>
      </div>
      <div class="spec-rows"></div>
      <div><button type="button" class="btn btn-sm btn-primary add-row-btn">+ Add spec</button></div>
    `;
        const rowsContainer = wrapper.querySelector('.spec-rows');
        const rows = (groupObj && Array.isArray(groupObj.rows) && groupObj.rows.length) ? groupObj.rows : [{
          name: '',
          value: '',
          order: 10
        }];
        rows.forEach(r => rowsContainer.appendChild(createRowEl(safe, r.name, r.value, r.order)));

        wrapper.querySelector('.spec-group-name').addEventListener('input', function() {
          const newSafe = safeKey(this.value || 'General');
          wrapper.querySelectorAll('.spec-row').forEach(row => {
            const inputs = row.querySelectorAll('input');
            if (inputs.length >= 3) {
              inputs[0].name = `spec_name_${newSafe}[]`;
              inputs[1].name = `spec_value_${newSafe}[]`;
              inputs[2].name = `spec_order_${newSafe}[]`;
            }
          });
          saveDraft();
        });

        return wrapper;
      }

      function getDraft() {
        const data = {
          fields: {},
          specs: []
        };

        ['name', 'sku', 'description', 'price', 'discount', 'stock', 'category', 'brand', 'weight'].forEach(k => {
          const el = form.querySelector('[name="' + k + '"]');
          if (!el) return;
          if (el.type === 'checkbox') data.fields[k] = el.checked;
          else data.fields[k] = el.value;
        });
        data.fields.is_featured = !!form.querySelector('[name="is_featured"]')?.checked;
        data.fields.is_active = !!form.querySelector('[name="is_active"]')?.checked;

        specContainer.querySelectorAll('.spec-group').forEach(g => {
          const gname = g.querySelector('.spec-group-name')?.value || 'General';
          const rowsArr = [];
          g.querySelectorAll('.spec-row').forEach(row => {
            const inputs = row.querySelectorAll('input');
            const n = inputs[0]?.value || '';
            const v = inputs[1]?.value || '';
            const o = inputs[2]?.value || '';
            if (n || v) rowsArr.push({
              name: n,
              value: v,
              order: o
            });
          });
          data.specs.push({
            group: gname,
            rows: rowsArr
          });
        });

        return data;
      }

      function saveDraft() {
        try {
          localStorage.setItem(KEY, JSON.stringify(getDraft()));
        } catch (e) {
          console.warn('saveDraft error', e);
        }
      }

      function restoreDraft() {
        const raw = localStorage.getItem(KEY);
        if (!raw) return;
        try {
          const data = JSON.parse(raw);
          if (data.fields) {
            Object.keys(data.fields).forEach(k => {
              const el = form.querySelector('[name="' + k + '"]');
              if (!el) return;
              if (el.type === 'checkbox') el.checked = !!data.fields[k];
              else el.value = data.fields[k];
            });
          }
          if (Array.isArray(data.specs) && data.specs.length) {
            specContainer.innerHTML = '';
            data.specs.forEach(g => specContainer.appendChild(buildGroupNode(g)));
          }
        } catch (e) {
          console.warn('Invalid draft', e);
        }
      }

      function clearDraft() {
        localStorage.removeItem(KEY);
      }

      specContainer.addEventListener('click', function(e) {
        if (e.target.matches('.remove-group-btn')) {
          const g = e.target.closest('.spec-group');
          if (g) {
            g.remove();
            saveDraft();
          }
          return;
        }
        if (e.target.matches('.add-row-btn')) {
          const g = e.target.closest('.spec-group');
          if (!g) return;
          const key = safeKey(g.querySelector('.spec-group-name')?.value || 'General');
          g.querySelector('.spec-rows').appendChild(createRowEl(key));
          saveDraft();
          return;
        }
        if (e.target.matches('.remove-row-btn')) {
          const row = e.target.closest('.spec-row');
          if (row) {
            row.remove();
            saveDraft();
          }
        }
      });

      form.addEventListener('input', saveDraft);
      form.addEventListener('change', saveDraft);

      addGroupBtn?.addEventListener('click', function() {
        specContainer.appendChild(buildGroupNode({
          group: 'New Group',
          rows: []
        }));
        saveDraft();
      });

      addBtn?.addEventListener('click', clearDraft);
      cancelBtn?.addEventListener('click', clearDraft);

      restoreDraft();
    });
  </script>

  <script>
    // Image preview function
    function previewImage(input, previewId) {
      const preview = document.getElementById(previewId);
      preview.innerHTML = '';

      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const img = document.createElement('img');
          img.src = e.target.result;
          preview.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
      }
    }

    // Filter brands based on selected category
    function filterBrands() {
      const selectedCategory = document.getElementById('categorySelect').value;
      const brandSelect = document.getElementById('brandSelect');
      const brandOptions = brandSelect.options;

      brandSelect.value = '';

      for (let i = 0; i < brandOptions.length; i++) {
        const option = brandOptions[i];

        if (option.value === '') {
          option.style.display = 'block';
          continue;
        }

        const brandCategory = option.getAttribute('data-category');
        if (selectedCategory === '' || brandCategory === selectedCategory) {
          option.style.display = 'block';
        } else {
          option.style.display = 'none';
        }
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('categorySelect').addEventListener('change', filterBrands);
      filterBrands();

      const hamburger = document.getElementById("hamburger");
      const sidebar = document.getElementById("sidebar");

      if (hamburger && sidebar) {
        const isCollapsed = localStorage.getItem("sidebarCollapsed") === "true";
        if (isCollapsed) {
          sidebar.classList.add("collapsed");
        }

        hamburger.addEventListener("click", () => {
          sidebar.classList.toggle("collapsed");
          localStorage.setItem("sidebarCollapsed", sidebar.classList.contains("collapsed"));
        });
      }
    });
  </script>

  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>