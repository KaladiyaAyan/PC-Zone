<?php
session_start();

require_once '../includes/functions.php';
$conn = getConnection();

$errors = [];
$success = false;

// Handle AJAX image delete (posts to same page)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_image') {
  // expected: product_id, col (one of main_image,image_1,image_2,image_3)
  $pid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
  $col = $_POST['col'] ?? '';
  $allowed = ['main_image', 'image_1', 'image_2', 'image_3'];
  header('Content-Type: application/json');
  if ($pid <= 0 || !in_array($col, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
  }

  // fetch current filename
  $stmt = mysqli_prepare($conn, "SELECT $col FROM products WHERE product_id = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, 'i', $pid);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);

  $filename = $row[$col] ?? '';
  if ($filename) {
    // try remove file from uploads and assets
    $uploads = __DIR__ . '/../uploads/' . $filename;
    $assets  = __DIR__ . '/../assets/images/' . $filename;
    if (file_exists($uploads)) @unlink($uploads);
    if (file_exists($assets)) @unlink($assets);
  }

  // update DB to empty string (schema main_image is NOT NULL, so use '')
  $uStmt = mysqli_prepare($conn, "UPDATE products SET $col = ? WHERE product_id = ?");
  $empty = '';
  mysqli_stmt_bind_param($uStmt, 'si', $empty, $pid);
  $ok = mysqli_stmt_execute($uStmt);
  mysqli_stmt_close($uStmt);

  if ($ok) echo json_encode(['success' => true]);
  else echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
  exit;
}

// Validate product_id in GET
if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
  header("Location: products.php");
  exit;
}
$product_id = intval($_GET['product_id']);

// Fetch product
$stmt = mysqli_prepare($conn, "
  SELECT p.*, c.category_name AS category_name, b.brand_name AS brand_name
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.category_id
  LEFT JOIN brands b ON p.brand_id = b.brand_id
  WHERE p.product_id = ? LIMIT 1
");
mysqli_stmt_bind_param($stmt, 'i', $product_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (!$res || mysqli_num_rows($res) === 0) {
  mysqli_stmt_close($stmt);
  header("Location: products.php");
  exit;
}
$product = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

// Build existing images array from product columns (preserve ordering)
$existingImages = [];
foreach (['main_image', 'image_1', 'image_2', 'image_3'] as $col) {
  if (!empty($product[$col])) {
    $existingImages[] = [
      'col' => $col,
      'filename' => $product[$col],
      'is_main' => ($col === 'main_image')
    ];
  }
}

// Fetch specs grouped
$specGroups = [];
$sStmt = mysqli_prepare($conn, "SELECT spec_name, spec_value, spec_group, display_order FROM product_specs WHERE product_id = ? ORDER BY spec_group, display_order");
mysqli_stmt_bind_param($sStmt, 'i', $product_id);
mysqli_stmt_execute($sStmt);
$sres = mysqli_stmt_get_result($sStmt);
while ($s = mysqli_fetch_assoc($sres)) {
  $group = $s['spec_group'] ?: 'General';
  $specGroups[$group][] = [
    'name' => $s['spec_name'],
    'value' => $s['spec_value'],
    'order' => $s['display_order']
  ];
}
mysqli_stmt_close($sStmt);

// Handle main form submit (update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
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

  // SKU uniqueness excluding current
  if (!empty($sku)) {
    $sStmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM products WHERE sku = ? AND product_id != ?");
    mysqli_stmt_bind_param($sStmt, 'si', $sku, $product_id);
    mysqli_stmt_execute($sStmt);
    $sres = mysqli_stmt_get_result($sStmt);
    $srow = mysqli_fetch_assoc($sres);
    mysqli_stmt_close($sStmt);
    if (($srow['count'] ?? 0) > 0) $errors[] = "SKU already exists. Please use a unique SKU.";
  }

  // Validate brand belongs to category
  if ($category_id > 0 && $brand_id > 0) {
    $bStmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM brands WHERE brand_id = ? AND category_id = ?");
    mysqli_stmt_bind_param($bStmt, 'ii', $brand_id, $category_id);
    mysqli_stmt_execute($bStmt);
    $bres = mysqli_stmt_get_result($bStmt);
    $brow = mysqli_fetch_assoc($bres);
    mysqli_stmt_close($bStmt);
    if (($brow['count'] ?? 0) == 0) $errors[] = "Selected brand does not belong to the selected category.";
  }

  // Process images first (uploads)
  $uploadDir = __DIR__ . '/../uploads/';
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
  $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

  // Map input fields to product columns
  $uploadCols = [
    'image1' => 'main_image',
    'image2' => 'image_1',
    'image3' => 'image_2',
    'image4' => 'image_3'
  ];
  $newFiles = []; // column => filename

  foreach ($uploadCols as $field => $col) {
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
      $file = $_FILES[$field];
      $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      if (in_array($ext, $allowedTypes)) {
        $filename = uniqid('img_', true) . '.' . $ext;
        $target = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $target)) {
          $newFiles[$col] = $filename;
        } else {
          $errors[] = "Failed to move uploaded file for {$field}.";
        }
      } // unsupported types ignored
    }
  }

  // If no errors, update DB
  if (empty($errors)) {
    // Escape text fields
    $name_e = mysqli_real_escape_string($conn, $name);
    $sku_e = mysqli_real_escape_string($conn, $sku);
    $desc_e = mysqli_real_escape_string($conn, $description);

    // Build update statement (include image columns if new files uploaded)
    $setParts = [];
    $params = [];
    $types = '';

    $setParts[] = "product_name = ?";
    $types .= 's';
    $params[] = &$name_e;
    $setParts[] = "sku = ?";
    $types .= 's';
    $params[] = &$sku_e;
    $setParts[] = "description = ?";
    $types .= 's';
    $params[] = &$desc_e;
    $setParts[] = "price = ?";
    $types .= 'd';
    $params[] = &$price;
    $setParts[] = "discount = ?";
    $types .= 'd';
    $params[] = &$discount;
    $setParts[] = "stock = ?";
    $types .= 'i';
    $params[] = &$stock;
    $setParts[] = "weight = ?";
    $types .= 'd';
    $params[] = &$weight;
    $setParts[] = "brand_id = ?";
    $types .= 'i';
    $params[] = &$brand_id;
    $setParts[] = "category_id = ?";
    $types .= 'i';
    $params[] = &$category_id;
    $setParts[] = "is_featured = ?";
    $types .= 'i';
    $params[] = &$is_featured;
    $setParts[] = "is_active = ?";
    $types .= 'i';
    $params[] = &$is_active;

    // images
    foreach ($newFiles as $col => $filename) {
      $setParts[] = "$col = ?";
      $types .= 's';
      $params[] = &$filename;
    }

    $setParts[] = "updated_at = CURRENT_TIMESTAMP";

    $sql = "UPDATE products SET " . implode(', ', $setParts) . " WHERE product_id = ?";
    $types .= 'i';
    $params[] = &$product_id;

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
      $errors[] = "DB prepare failed: " . mysqli_error($conn);
    } else {
      // bind dynamically
      mysqli_stmt_bind_param($stmt, $types, ...$params);
      $ok = mysqli_stmt_execute($stmt);
      if (!$ok) {
        $errors[] = "Failed to update product: " . mysqli_stmt_error($stmt);
      } else {
        // Update local $product to reflect filenames (useful if we fall through to display page)
        foreach ($newFiles as $col => $filename) {
          $product[$col] = $filename;
        }

        // Save product specs: delete existing then insert new
        if (!empty($_POST['spec_group_name']) && is_array($_POST['spec_group_name'])) {
          $delStmt = mysqli_prepare($conn, "DELETE FROM product_specs WHERE product_id = ?");
          mysqli_stmt_bind_param($delStmt, 'i', $product_id);
          mysqli_stmt_execute($delStmt);
          mysqli_stmt_close($delStmt);

          $insStmt = mysqli_prepare($conn, "INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES (?, ?, ?, ?, ?)");
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
            }
          }
          if ($insStmt) mysqli_stmt_close($insStmt);
        }

        // Redirect on success
        header("Location: products.php?update=success");
        exit;
      }
      mysqli_stmt_close($stmt);
    }
  }
}

// categories & brands for dropdowns
$categories = getRootCategories();
$brandsQuery = "SELECT brand_id, brand_name, category_id FROM brands ORDER BY brand_name ASC";
$brandsResult = mysqli_query($conn, $brandsQuery);
$brandsData = [];
while ($b = mysqli_fetch_assoc($brandsResult)) $brandsData[] = $b;

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Edit Product - PC ZONE Admin</title>
  <?php include './includes/header-link.php'; ?>
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
              <h1>Edit Product</h1>
              <a href="products.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Products
              </a>
            </div>

            <div class="current-selection">
              <h6 class="mb-2"><i class="fas fa-info-circle"></i> Current Product Details</h6>
              <div class="row">
                <div class="col-md-6"><strong>Name:</strong> <?= htmlspecialchars($product['product_name']) ?></div>
                <div class="col-md-3"><strong>Category:</strong> <?= htmlspecialchars($product['category_name']) ?></div>
                <div class="col-md-3"><strong>Brand:</strong> <?= htmlspecialchars($product['brand_name']) ?></div>
              </div>
            </div>

            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?></ul>
              </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
              <div class="row g-3">
                <!-- fields (unchanged markup) -->
                <div class="col-md-6">
                  <label class="form-label">Product Name <span class="required">*</span></label>
                  <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['product_name']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">SKU <span class="required">*</span></label>
                  <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku']) ?>" required>
                </div>
                <div class="col-12">
                  <label class="form-label">Description <span class="required">*</span></label>
                  <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Price (₹) <span class="required">*</span></label>
                  <input type="number" step="0.01" min="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Discount (%)</label>
                  <input type="number" step="0.01" min="0" max="100" name="discount" class="form-control" value="<?= $product['discount'] ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Stock Quantity <span class="required">*</span></label>
                  <input type="number" name="stock" class="form-control" min="0" value="<?= $product['stock'] ?>" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Category <span class="required">*</span></label>
                  <select name="category" class="form-select" id="categorySelect" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?= $cat['category_id'] ?>" <?= ($product['category_id'] == $cat['category_id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Brand <span class="required">*</span></label>
                  <select name="brand" class="form-select" id="brandSelect" required>
                    <option value="">-- Select Brand --</option>
                    <?php foreach ($brandsData as $brand): ?>
                      <option value="<?= $brand['brand_id'] ?>" data-category="<?= $brand['category_id'] ?>" <?= ($product['brand_id'] == $brand['brand_id']) ? 'selected' : '' ?>><?= htmlspecialchars($brand['brand_name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <div class="brand-info" id="brandInfo">Brands will be filtered based on selected category</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Weight (kg)</label>
                  <input type="number" step="0.01" min="0" name="weight" class="form-control" value="<?= $product['weight'] ?>">
                </div>

                <div class="col-md-6">
                  <div class="row">
                    <div class="col-6">
                      <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" <?= $product['is_featured'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Featured Product</label>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= $product['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Specifications (unchanged markup, using $specGroups) -->
                <div class="col-12">
                  <h5 class="mt-4 mb-3">Product Specifications</h5>
                  <div id="specGroupsContainer">
                    <?php
                    if (!empty($specGroups)) {
                      foreach ($specGroups as $gName => $rows) {
                        $safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $gName);
                    ?>
                        <div class="spec-group mb-3 border rounded p-2">
                          <div class="d-flex mb-2">
                            <input name="spec_group_name[]" class="form-control me-2 spec-group-name" value="<?= htmlspecialchars($gName) ?>" placeholder="Group name (e.g. Processor)">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-group-btn">Remove Group</button>
                          </div>
                          <div class="spec-rows">
                            <?php foreach ($rows as $r): ?>
                              <div class="input-group mb-2 spec-row">
                                <input name="spec_name_<?= $safe ?>[]" class="form-control" placeholder="Spec name" value="<?= htmlspecialchars($r['name']) ?>">
                                <input name="spec_value_<?= $safe ?>[]" class="form-control" placeholder="Spec value" value="<?= htmlspecialchars($r['value']) ?>">
                                <input type="number" name="spec_order_<?= $safe ?>[]" class="form-control w-25" placeholder="Order" value="<?= (int)$r['order'] ?>">
                                <button type="button" class="btn btn-outline-secondary remove-row-btn">−</button>
                              </div>
                            <?php endforeach; ?>
                          </div>
                          <div>
                            <button type="button" class="btn btn-sm btn-primary add-row-btn">+ Add spec</button>
                          </div>
                        </div>
                      <?php
                      }
                    } else {
                      // default
                      ?>
                      <div class="spec-group mb-3 border rounded p-2">
                        <div class="d-flex mb-2">
                          <input name="spec_group_name[]" class="form-control me-2 spec-group-name" value="General" placeholder="Group name (e.g. Processor)">
                          <button type="button" class="btn btn-outline-danger btn-sm remove-group-btn">Remove Group</button>
                        </div>
                        <div class="spec-rows">
                          <div class="input-group mb-2 spec-row">
                            <input name="spec_name_General[]" class="form-control" placeholder="Spec name">
                            <input name="spec_value_General[]" class="form-control" placeholder="Spec value">
                            <input type="number" name="spec_order_General[]" class="form-control w-25" placeholder="Order" value="10">
                            <button type="button" class="btn btn-outline-secondary remove-row-btn">−</button>
                          </div>
                        </div>
                        <div>
                          <button type="button" class="btn btn-sm btn-primary add-row-btn">+ Add spec</button>
                        </div>
                      </div>
                    <?php } ?>
                  </div>
                  <div class="mt-2">
                    <button id="addGroupBtn" type="button" class="btn btn-sm btn-success">+ Add Group</button>
                    <small class="text-muted d-block mt-2">Create groups like "Processor", "Performance" and add related specs.</small>
                  </div>
                </div>

                <!-- Existing images (from product columns) -->
                <?php if (!empty($existingImages)): ?>
                  <div class="col-12">
                    <h5 class="mt-3 mb-2">Existing Images</h5>
                    <div class="existing-images d-flex flex-wrap gap-2">
                      <?php foreach ($existingImages as $img):
                        $imagePath = "../uploads/" . $img['filename'];
                        if (!file_exists($imagePath)) $imagePath = "../assets/images/" . $img['filename'];
                      ?>
                        <div class="image-container position-relative" style="width:140px;">
                          <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" style="max-width:100%;border:1px solid #ddd;border-radius:6px;">
                          <?php if ($img['is_main']): ?><span class="main-image-badge badge bg-danger position-absolute top-0 start-0">Main</span><?php endif; ?>
                          <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0" onclick="deleteImage('<?= $img['col'] ?>')" title="Delete Image">×</button>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>

                <!-- Add New Images -->
                <div class="col-12">
                  <h5 class="mt-4 mb-3">Add New Images</h5>
                  <div class="row">
                    <div class="col-md-3">
                      <label class="form-label">Image 1 (main)</label>
                      <input type="file" name="image1" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Image 2</label>
                      <input type="file" name="image2" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Image 3</label>
                      <input type="file" name="image3" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Image 4</label>
                      <input type="file" name="image4" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>

                <!-- Submit -->
                <div class="col-12 mt-4">
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Update Product</button>
                    <a href="products.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
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
    // Delete image (posts action to same page)
    function deleteImage(col) {
      if (!confirm('Are you sure you want to delete this image?')) return;
      const data = new URLSearchParams();
      data.append('action', 'delete_image');
      data.append('product_id', <?= $product_id ?>);
      data.append('col', col);

      fetch('', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: data.toString()
        })
        .then(r => r.json())
        .then(json => {
          if (json.success) location.reload();
          else alert('Failed to delete image: ' + (json.message || 'unknown'));
        })
        .catch(err => {
          console.error(err);
          alert('Error deleting image');
        });
    }

    // Filter brands by category (same logic as add_product)
    document.addEventListener('DOMContentLoaded', function() {
      const categorySelect = document.getElementById('categorySelect');
      const brandSelect = document.getElementById('brandSelect');

      function filterBrands() {
        const sel = categorySelect.value;
        Array.from(brandSelect.options).forEach(opt => {
          if (!opt.value) return;
          if (!sel || opt.getAttribute('data-category') === sel) opt.style.display = 'block';
          else {
            opt.style.display = 'none';
            opt.selected = false;
          }
        });
        if (!brandSelect.value) brandSelect.selectedIndex = 0;
      }

      categorySelect.addEventListener('change', filterBrands);
      filterBrands();

      // Spec groups manager (delegation)
      (function() {
        function safeKey(name) {
          return String(name || 'Group').replace(/[^a-zA-Z0-9]/g, '_');
        }

        function escapeHtml(s) {
          return String(s || '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;');
        }

        const container = document.getElementById('specGroupsContainer');
        const addGroupBtn = document.getElementById('addGroupBtn');

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

        function buildGroupNode(group) {
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
          wrapper.querySelector('.spec-rows').appendChild(createRowEl(safe));
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
          });
          return wrapper;
        }

        container.addEventListener('click', function(e) {
          if (e.target.matches('.remove-group-btn')) {
            e.target.closest('.spec-group')?.remove();
            return;
          }
          if (e.target.matches('.add-row-btn')) {
            const g = e.target.closest('.spec-group');
            if (!g) return;
            const key = safeKey(g.querySelector('.spec-group-name')?.value || 'General');
            g.querySelector('.spec-rows').appendChild(createRowEl(key));
            return;
          }
          if (e.target.matches('.remove-row-btn')) {
            e.target.closest('.spec-row')?.remove();
            return;
          }
        });

        addGroupBtn?.addEventListener('click', function() {
          container.appendChild(buildGroupNode('New Group'));
        });
      })();

      // Sidebar collapsed state
      const hamburger = document.getElementById("hamburger");
      const sidebar = document.getElementById("sidebar");
      if (hamburger && sidebar) {
        const isCollapsed = localStorage.getItem("sidebarCollapsed") === "true";
        if (isCollapsed) sidebar.classList.add("collapsed");
        hamburger.addEventListener("click", () => {
          sidebar.classList.toggle("collapsed");
          localStorage.setItem("sidebarCollapsed", sidebar.classList.contains("collapsed"));
        });
      }
    });
  </script>

  <script src="./assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>