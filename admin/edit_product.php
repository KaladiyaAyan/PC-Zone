<?php
session_start();
require('../includes/db_connect.php');
require('../includes/functions.php');

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ../login.php');
  exit;
}

$product_id = (int)$_GET['product_id'];

// Handle image deletion
if (isset($_GET['delete_image'])) {
  $col = $_GET['delete_image'];
  $allowed = ['main_image', 'image_1', 'image_2', 'image_3'];

  if (in_array($col, $allowed)) {
    // Get current filename and delete
    $result = mysqli_query($conn, "SELECT $col FROM products WHERE product_id = $product_id");
    $row = mysqli_fetch_assoc($result);
    if ($row[$col]) {
      $file_path = '../uploads/' . $row[$col];
      if (file_exists($file_path)) @unlink($file_path);
    }

    // Update database
    mysqli_query($conn, "UPDATE products SET $col = '' WHERE product_id = $product_id");
    header("Location: edit_product.php?product_id=$product_id");
    exit;
  }
}

// Fetch product data
$result = mysqli_query($conn, "
    SELECT p.*, c.category_name, b.brand_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    WHERE p.product_id = $product_id
");
if (!$result || mysqli_num_rows($result) === 0) {
  header("Location: product.php");
  exit;
}
$product = mysqli_fetch_assoc($result);

// Get existing images
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

// Get specifications
$specGroups = [];
$specs_result = mysqli_query($conn, "
    SELECT spec_name, spec_value, spec_group, display_order 
    FROM product_specs 
    WHERE product_id = $product_id 
    ORDER BY spec_group, display_order
");
while ($spec = mysqli_fetch_assoc($specs_result)) {
  $group = $spec['spec_group'] ?: 'General';
  $specGroups[$group][] = $spec;
}

// Get categories and brands
$categories = getRootCategories();
$brands_result = mysqli_query($conn, "SELECT brand_id, brand_name, category_id FROM brands ORDER BY brand_name");
$brandsData = mysqli_fetch_all($brands_result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Edit Product - PC ZONE Admin</title>
  <?php include './includes/header-link.php'; ?>
</head>

<body>
  <?php require('./includes/alert.php'); ?>
  <?php include './includes/header.php'; ?>
  <?php $current_page = 'product';
  include './includes/sidebar.php'; ?>

  <main class="main-content my-4">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h1>Edit Product</h1>
              <a href="product.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Products
              </a>
            </div>

            <div class="current-selection">
              <h6 class="mb-2"><i class="fas fa-info-circle"></i> Current Product Details</h6>
              <div class="row">
                <div class="col-md-6"><strong>Name:</strong> <?= e($product['product_name']) ?></div>
                <div class="col-md-3"><strong>Category:</strong> <?= e($product['category_name']) ?></div>
                <div class="col-md-3"><strong>Brand:</strong> <?= e($product['brand_name']) ?></div>
              </div>
            </div>

            <form action="edit.php" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="product_id" value="<?= $product_id ?>">
              <input type="hidden" name="edit-product" value="1">

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Product Name <span class="required">*</span></label>
                  <input type="text" name="name" class="form-control" value="<?= e($product['product_name']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">SKU <span class="required">*</span></label>
                  <input type="text" name="sku" class="form-control" value="<?= e($product['sku']) ?>" required>
                </div>
                <div class="col-12">
                  <label class="form-label">Description <span class="required">*</span></label>
                  <textarea name="description" class="form-control" rows="4" required><?= e($product['description']) ?></textarea>
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
                      <option value="<?= $cat['category_id'] ?>" <?= $product['category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                        <?= e($cat['category_name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Brand <span class="required">*</span></label>
                  <select name="brand" class="form-select" id="brandSelect" required>
                    <option value="">-- Select Brand --</option>
                    <?php foreach ($brandsData as $brand): ?>
                      <option value="<?= $brand['brand_id'] ?>" data-category="<?= $brand['category_id'] ?>"
                        <?= $product['brand_id'] == $brand['brand_id'] ? 'selected' : '' ?>>
                        <?= e($brand['brand_name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="brand-info" id="brandInfo">Brands will be filtered based on selected category</div>
                </div>

                <!-- Platform (new) -->
                <div class="col-md-6">
                  <label class="form-label">Platform</label>
                  <select name="platform" class="form-select" id="platformSelect">
                    <option value="none" <?= ($product['platform'] ?? 'none') === 'none' ? 'selected' : '' ?>>None</option>
                    <option value="intel" <?= ($product['platform'] ?? 'none') === 'intel' ? 'selected' : '' ?>>Intel</option>
                    <option value="amd" <?= ($product['platform'] ?? 'none') === 'amd' ? 'selected' : '' ?>>AMD</option>
                    <option value="both" <?= ($product['platform'] ?? 'none') === 'both' ? 'selected' : '' ?>>Both</option>
                  </select>
                  <small class="text-muted">Choose platform compatibility. Defaults to "None".</small>
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

                <!-- Specifications -->
                <div class="col-12">
                  <h5 class="mt-4 mb-3">Product Specifications</h5>
                  <div id="specGroupsContainer">
                    <?php if (!empty($specGroups)): ?>
                      <?php foreach ($specGroups as $groupName => $specs): ?>
                        <?php $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $groupName); ?>
                        <div class="spec-group mb-3 border rounded p-2">
                          <div class="d-flex mb-2">
                            <input name="spec_group_name[]" class="form-control me-2 spec-group-name" value="<?= e($groupName) ?>" placeholder="Group name">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-group-btn">Remove Group</button>
                          </div>
                          <div class="spec-rows">
                            <?php foreach ($specs as $spec): ?>
                              <div class="input-group mb-2 spec-row">
                                <input name="spec_name_<?= $safeName ?>[]" class="form-control" placeholder="Spec name" value="<?= e($spec['spec_name']) ?>">
                                <input name="spec_value_<?= $safeName ?>[]" class="form-control" placeholder="Spec value" value="<?= e($spec['spec_value']) ?>">
                                <input type="number" name="spec_order_<?= $safeName ?>[]" class="form-control w-25" placeholder="Order" value="<?= (int)$spec['display_order'] ?>">
                                <button type="button" class="btn btn-outline-secondary remove-row-btn">−</button>
                              </div>
                            <?php endforeach; ?>
                          </div>
                          <div>
                            <button type="button" class="btn btn-sm btn-primary add-row-btn">+ Add spec</button>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <div class="spec-group mb-3 border rounded p-2">
                        <div class="d-flex mb-2">
                          <input name="spec_group_name[]" class="form-control me-2 spec-group-name" value="General" placeholder="Group name">
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
                    <?php endif; ?>
                  </div>
                  <div class="mt-2">
                    <button id="addGroupBtn" type="button" class="btn btn-sm btn-success">+ Add Group</button>
                    <small class="text-muted d-block mt-2">Create groups like "Processor", "Performance" and add related specs.</small>
                  </div>
                </div>

                <!-- Existing Images -->
                <?php if (!empty($existingImages)): ?>
                  <div class="col-12">
                    <h5 class="mt-3 mb-2">Existing Images</h5>
                    <div class="existing-images d-flex flex-wrap gap-2">
                      <?php foreach ($existingImages as $img): ?>
                        <?php
                        $imagePath = "../uploads/" . $img['filename'];
                        if (!file_exists($imagePath)) $imagePath = "../assets/images/" . $img['filename'];
                        ?>
                        <div class="image-container position-relative" style="width:140px;">
                          <img src="<?= e($imagePath) ?>" alt="<?= e($product['product_name']) ?>" style="max-width:100%;border:1px solid #ddd;border-radius:6px;">
                          <?php if ($img['is_main']): ?>
                            <span class="main-image-badge badge bg-danger position-absolute top-0 start-0">Main</span>
                          <?php endif; ?>
                          <a href="edit_product.php?product_id=<?= $product_id ?>&delete_image=<?= $img['col'] ?>"
                            class="btn btn-sm btn-outline-danger position-absolute top-0 end-0"
                            onclick="return confirm('Delete this image?')">×</a>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>

                <!-- New Images -->
                <div class="col-12">
                  <h5 class="mt-4 mb-3">Add New Images</h5>
                  <div class="row">
                    <?php foreach (['Image 1 (main)', 'Image 2', 'Image 3', 'Image 4'] as $index => $label): ?>
                      <div class="col-md-3">
                        <label class="form-label"><?= $label ?></label>
                        <input type="file" name="image<?= $index + 1 ?>" class="form-control" accept="image/*">
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>

                <!-- Submit -->
                <div class="col-12 mt-4">
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Update Product</button>
                    <a href="product.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php require('./includes/footer-link.php') ?>

  <script>
    // Filter brands by category
    document.getElementById('categorySelect').addEventListener('change', function() {
      const categoryId = this.value;
      const brandSelect = document.getElementById('brandSelect');

      Array.from(brandSelect.options).forEach(option => {
        if (!option.value) return;
        const optionCategory = option.getAttribute('data-category');
        option.style.display = !categoryId || optionCategory === categoryId ? 'block' : 'none';
      });

      if (!brandSelect.value) brandSelect.selectedIndex = 0;
    });

    // Initialize brand filter
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('categorySelect').dispatchEvent(new Event('change'));
    });

    // Specifications management
    document.addEventListener('DOMContentLoaded', function() {
      const container = document.getElementById('specGroupsContainer');
      const addGroupBtn = document.getElementById('addGroupBtn');

      function createSpecRow(groupName, name = '', value = '', order = 10) {
        const safeName = groupName.replace(/[^a-zA-Z0-9]/g, '_');
        const div = document.createElement('div');
        div.className = 'input-group mb-2 spec-row';
        div.innerHTML = `
          <input name="spec_name_${safeName}[]" class="form-control" placeholder="Spec name" value="${name}">
          <input name="spec_value_${safeName}[]" class="form-control" placeholder="Spec value" value="${value}">
          <input type="number" name="spec_order_${safeName}[]" class="form-control w-25" placeholder="Order" value="${order}">
          <button type="button" class="btn btn-outline-secondary remove-row-btn">−</button>
        `;
        return div;
      }

      function createGroup(groupName = 'New Group') {
        const safeName = groupName.replace(/[^a-zA-Z0-9]/g, '_');
        const groupDiv = document.createElement('div');
        groupDiv.className = 'spec-group mb-3 border rounded p-2';
        groupDiv.innerHTML = `
          <div class="d-flex mb-2">
            <input name="spec_group_name[]" class="form-control me-2 spec-group-name" value="${groupName}" placeholder="Group name">
            <button type="button" class="btn btn-outline-danger btn-sm remove-group-btn">Remove Group</button>
          </div>
          <div class="spec-rows"></div>
          <div><button type="button" class="btn btn-sm btn-primary add-row-btn">+ Add spec</button></div>
        `;

        // Add initial row
        groupDiv.querySelector('.spec-rows').appendChild(createSpecRow(groupName));

        // Update input names when group name changes
        groupDiv.querySelector('.spec-group-name').addEventListener('input', function() {
          const newSafeName = this.value.replace(/[^a-zA-Z0-9]/g, '_');
          groupDiv.querySelectorAll('.spec-row').forEach(row => {
            const inputs = row.querySelectorAll('input');
            inputs[0].name = `spec_name_${newSafeName}[]`;
            inputs[1].name = `spec_value_${newSafeName}[]`;
            inputs[2].name = `spec_order_${newSafeName}[]`;
          });
        });

        return groupDiv;
      }

      // Event delegation for buttons
      container.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-group-btn')) {
          e.target.closest('.spec-group').remove();
        } else if (e.target.classList.contains('add-row-btn')) {
          const group = e.target.closest('.spec-group');
          const groupName = group.querySelector('.spec-group-name').value;
          group.querySelector('.spec-rows').appendChild(createSpecRow(groupName));
        } else if (e.target.classList.contains('remove-row-btn')) {
          e.target.closest('.spec-row').remove();
        }
      });

      addGroupBtn.addEventListener('click', function() {
        container.appendChild(createGroup());
      });
    });
  </script>
</body>

</html>