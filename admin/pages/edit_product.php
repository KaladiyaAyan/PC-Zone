<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: index.php");
  exit;
}

include('../includes/db_connect.php');

$errors = [];
$success = false;

// Check if product ID is provided
if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
  header("Location: products.php");
  exit;
}

$product_id = intval($_GET['product_id']);

// Get existing product data
$productQuery = "SELECT p.*, c.category_name as category_name, b.brand_name as brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.category_id 
                LEFT JOIN brands b ON p.brand_id = b.brand_id 
                WHERE p.product_id = $product_id";
$productResult = mysqli_query($conn, $productQuery);

if (!$productResult || mysqli_num_rows($productResult) === 0) {
  header("Location: products.php");
  exit;
}

$product = mysqli_fetch_assoc($productResult);

// Get existing product images
$imagesQuery = "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY is_main DESC";
$imagesResult = mysqli_query($conn, $imagesQuery);
$existingImages = [];
while ($img = mysqli_fetch_assoc($imagesResult)) {
  $existingImages[] = $img;
}

// Get existing product specs grouped
$specGroups = [];
$specsQuery = "SELECT spec_name, spec_value, spec_group, display_order FROM product_specs WHERE product_id = $product_id ORDER BY spec_group, display_order";
$specsResult = mysqli_query($conn, $specsQuery);
while ($s = mysqli_fetch_assoc($specsResult)) {
  $group = $s['spec_group'] ?: 'General';
  $specGroups[$group][] = [
    'name' => $s['spec_name'],
    'value' => $s['spec_value'],
    'order' => $s['display_order']
  ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get form data
  $name = trim($_POST['name']);
  $sku = trim($_POST['sku']);
  $description = trim($_POST['description']);
  $price = floatval($_POST['price']);
  $discount = isset($_POST['discount']) ? floatval($_POST['discount']) : 0.0;
  $stock = intval($_POST['stock']);
  $weight = isset($_POST['weight']) ? floatval($_POST['weight']) : 0;
  $category_id = intval($_POST['category']);
  $brand_id = intval($_POST['brand']);
  $is_featured = isset($_POST['is_featured']) ? 1 : 0;
  $is_active = isset($_POST['is_active']) ? 1 : 0;

  // Validation
  if (empty($name) || empty($sku) || empty($description) || $price <= 0 || $stock < 0 || $category_id <= 0 || $brand_id <= 0) {
    $errors[] = "Please fill all required fields with valid values.";
  }

  // Check SKU uniqueness (excluding current product)
  if (!empty($sku)) {
    $checkQuery = "SELECT COUNT(*) as count FROM products WHERE sku = '" . mysqli_real_escape_string($conn, $sku) . "' AND product_id != $product_id";
    $result = mysqli_query($conn, $checkQuery);
    $data = mysqli_fetch_assoc($result);

    if ($data['count'] > 0) {
      $errors[] = "SKU already exists. Please use a unique SKU.";
    }
  }

  // Validate brand belongs to selected category
  if ($category_id > 0 && $brand_id > 0) {
    // $brandCategoryQuery = "SELECT COUNT(*) as count FROM brand_categories 
    //                       WHERE brand_id = $brand_id AND category_id = $category_id";
    // $brandCategoryResult = mysqli_query($conn, $brandCategoryQuery);
    // $brandCategoryData = mysqli_fetch_assoc($brandCategoryResult);

    // if ($brandCategoryData['count'] == 0) {
    //   $errors[] = "Selected brand is not valid for the chosen category.";
    // }
    $check_brand_category = mysqli_query(
      $conn,
      "SELECT COUNT(*) as count FROM brands 
       WHERE brand_id = $brand_id AND category_id = $category_id"
    );
    $row = mysqli_fetch_assoc($check_brand_category);

    if ($row['count'] == 0) {
      $errors[] = "Selected brand does not belong to the selected category.";
    }
  }

  // Process if no errors
  if (empty($errors)) {
    // Escape strings for SQL
    $name = mysqli_real_escape_string($conn, $name);
    $sku = mysqli_real_escape_string($conn, $sku);
    $description = mysqli_real_escape_string($conn, $description);

    // Update product
    $updateQuery = "UPDATE products SET 
                       product_name = '$name', 
                       sku = '$sku', 
                       description = '$description', 
                       price = $price, 
                       discount = $discount, 
                       stock = $stock, 
                       weight = $weight, 
                       brand_id = $brand_id, 
                       category_id = $category_id, 
                       is_featured = $is_featured, 
                       is_active = $is_active,
                       updated_at = CURRENT_TIMESTAMP
                       WHERE product_id = $product_id";

    if (mysqli_query($conn, $updateQuery)) {
      // Handle new image uploads
      $uploadDir = '../uploads/';
      // if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

      for ($i = 1; $i <= 4; $i++) {
        $field = 'image' . $i;

        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
          $file = $_FILES[$field];
          $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
          $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

          if (in_array($extension, $allowedTypes)) {
            // Generate unique filename
            $filename = uniqid('img_', true) . '.' . $extension;
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
              // Check if we have a main image, if not make this one main
              $mainImageQuery = "SELECT COUNT(*) as count FROM product_images WHERE product_id = $product_id AND is_main = 1";
              $mainResult = mysqli_query($conn, $mainImageQuery);
              $mainData = mysqli_fetch_assoc($mainResult);
              $isMain = ($mainData['count'] == 0) ? 1 : 0;

              $imageQuery = "INSERT INTO product_images (product_id, image_path, is_main) VALUES ($product_id, '$filename', $isMain)";
              mysqli_query($conn, $imageQuery);
            }
          }
        }
      }

      // === Save product specs ===
      // Delete existing specs and insert new ones based on posted groups and rows.
      if (!empty($_POST['spec_group_name']) && is_array($_POST['spec_group_name'])) {
        // delete existing
        $delStmt = mysqli_prepare($conn, "DELETE FROM product_specs WHERE product_id = ?");
        mysqli_stmt_bind_param($delStmt, 'i', $product_id);
        mysqli_stmt_execute($delStmt);
        mysqli_stmt_close($delStmt);

        // prepare insert
        $insStmt = mysqli_prepare(
          $conn,
          "INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES (?, ?, ?, ?, ?)"
        );

        foreach ($_POST['spec_group_name'] as $gIdx => $groupRaw) {
          $groupName = trim($groupRaw) ?: 'General';
          // safe key used in input names
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

      header("Location: products.php?update=success");
      exit;
    } else {
      $errors[] = "Failed to update product: " . mysqli_error($conn);
    }
  }
}

// Get categories for dropdown
// $categoriesQuery = "SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC";
// $categoriesQuery = "SELECT category_id, category_name FROM categories WHERE level = 1 ORDER BY category_name ASC";
// $categoriesResult = mysqli_query($conn, $categoriesQuery);

// Get all brands with their categories for JavaScript filtering
// $brandsQuery = "SELECT b.brand_id, b.brand_name, GROUP_CONCAT(bc.category_id) as category_ids 
//                 FROM brands b 
//                 LEFT JOIN brand_categories bc ON b.id = bc.brand_id 
//                 WHERE b.is_active = 1 
//                 GROUP BY b.brand_id, b.brand_name 
//                 ORDER BY b.brand_name ASC";

// $brandsResult = mysqli_query($conn, $brandsQuery);
// $brandsData = [];
// while ($brand = mysqli_fetch_assoc($brandsResult)) {
//   $brandsData[] = $brand;
// }
$categoriesQuery = "SELECT category_id, category_name 
                    FROM categories 
                    ORDER BY category_name ASC";
$categoriesResult = mysqli_query($conn, $categoriesQuery);

// Get all brands linked to categories
$brandsQuery = "SELECT brand_id, brand_name, category_id 
                FROM brands 
                ORDER BY brand_name ASC";
$brandsResult = mysqli_query($conn, $brandsQuery);
$brandsData = [];
while ($brand = mysqli_fetch_assoc($brandsResult)) {
  $brandsData[] = $brand;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Edit Product - PC ZONE Admin</title>
  <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>
  <?php include '../includes/header.php'; ?>
  <?php $current_page = 'products';
  include '../includes/sidebar.php'; ?>

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

            <!-- Current Selection Info -->
            <div class="current-selection">
              <h6 class="mb-2"><i class="fas fa-info-circle"></i> Current Product Details</h6>
              <div class="row">
                <div class="col-md-6">
                  <strong>Name:</strong> <?= htmlspecialchars($product['product_name']) ?>
                </div>
                <div class="col-md-3">
                  <strong>Category:</strong> <?= htmlspecialchars($product['category_name']) ?>
                </div>
                <div class="col-md-3">
                  <strong>Brand:</strong> <?= htmlspecialchars($product['brand_name']) ?>
                </div>
              </div>
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
                    value="<?= htmlspecialchars($product['product_name']) ?>" required>
                </div>

                <!-- SKU -->
                <div class="col-md-6">
                  <label class="form-label">SKU <span class="required">*</span></label>
                  <input type="text" name="sku" class="form-control"
                    value="<?= htmlspecialchars($product['sku']) ?>" required>
                </div>

                <!-- Description -->
                <div class="col-12">
                  <label class="form-label">Description <span class="required">*</span></label>
                  <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <!-- Price -->
                <div class="col-md-4">
                  <label class="form-label">Price (₹) <span class="required">*</span></label>
                  <input type="number" step="0.01" min="0.01" name="price" class="form-control"
                    value="<?= $product['price'] ?>" required>
                </div>

                <!-- Discount -->
                <div class="col-md-4">
                  <label class="form-label">Discount (%)</label>
                  <input type="number" step="0.01" min="0" max="100" name="discount" class="form-control"
                    value="<?= $product['discount'] ?>">
                </div>

                <!-- Stock -->
                <div class="col-md-4">
                  <label class="form-label">Stock Quantity <span class="required">*</span></label>
                  <input type="number" name="stock" class="form-control" min="0"
                    value="<?= $product['stock'] ?>" required>
                </div>

                <!-- Category -->
                <div class="col-md-6">
                  <label class="form-label">Category <span class="required">*</span></label>
                  <select name="category" class="form-select" id="categorySelect" required>
                    <option value="">-- Select Category --</option>
                    <?php while ($category = mysqli_fetch_assoc($categoriesResult)): ?>
                      <option value="<?= $category['category_id'] ?>"
                        <?= ($product['category_id'] == $category['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['category_name']) ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <!-- Brand -->
                <!-- <div class="col-md-6">
                  <label class="form-label">Brand <span class="required">*</span></label>
                  <select name="brand" class="form-select" id="brandSelect" required>
                    <option value="">-- Select Brand --</option>
                  </select>
                  <div class="brand-info" id="brandInfo">
                    Brands will be filtered based on selected category
                  </div>
                </div> -->
                <div class="col-md-6">
                  <label class="form-label">Brand <span class="required">*</span></label>
                  <select name="brand" class="form-select" id="brandSelect" required>
                    <option value="">-- Select Brand --</option>
                    <?php foreach ($brandsData as $brand): ?>
                      <option value="<?= $brand['brand_id'] ?>"
                        data-category="<?= $brand['category_id'] ?>"
                        <?= ($product['brand_id'] == $brand['brand_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($brand['brand_name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="brand-info" id="brandInfo">
                    Brands will be filtered based on selected category
                  </div>
                </div>


                <!-- Weight -->
                <div class="col-md-6">
                  <label class="form-label">Weight (kg)</label>
                  <input type="number" step="0.01" min="0" name="weight" class="form-control"
                    value="<?= $product['weight'] ?>">
                </div>

                <!-- Status Options -->
                <div class="col-md-6">
                  <div class="row">
                    <div class="col-6">
                      <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured"
                          <?= $product['is_featured'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Featured Product</label>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                          <?= $product['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Product Specifications -->
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
                      // default single General group
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
                <!-- End Product Specifications -->

                <!-- Existing Images -->
                <?php if (!empty($existingImages)): ?>
                  <div class="col-12">
                    <h5 class="mt-3 mb-2">Existing Images</h5>
                    <div class="existing-images">
                      <?php foreach ($existingImages as $img): ?>
                        <?php
                        $imagePath = "../uploads/" . $img['image_path'];
                        if (!file_exists($imagePath)) {
                          $imagePath = "../assets/images/" . $img['image_path'];
                        }
                        ?>
                        <div class="image-container">
                          <img src="<?= $imagePath ?>" alt="<?= $product['product_name'] ?>">
                          <?php if ($img['is_main']): ?>
                            <span class="main-image-badge">Main</span>
                          <?php endif; ?>
                          <button type="button" class="delete-image"
                            onclick="deleteImage(<?= $img['product_image_id'] ?>)"
                            title="Delete Image">×</button>
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
                      <label class="form-label">Image 1</label>
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

                <!-- Submit Buttons -->
                <div class="col-12 mt-4">
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                      <i class="fas fa-save"></i> Update Product
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
    // Brands data from PHP and current product data
    // const brandsData = <?= json_encode($brandsData) ?>;
    // const currentBrandId = <?= $product['brand_id'] ?>;
    // const currentCategoryId = <?= $product['category_id'] ?>;

    // Delete image function
    function deleteImage(imageId) {
      if (confirm('Are you sure you want to delete this image?')) {
        fetch('delete_image.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_image_id=' + imageId
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              location.reload();
            } else {
              alert('Failed to delete image: ' + data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the image.');
          });
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      const categorySelect = document.getElementById('categorySelect');
      const brandSelect = document.getElementById('brandSelect');
      const currentCategoryId = <?= (int)$product['category_id'] ?>;

      function filterBrands() {
        const selectedCategory = categorySelect.value;
        let hasSelectedBrand = false;

        [...brandSelect.options].forEach(option => {
          if (!option.value) return; // Skip placeholder
          if (option.getAttribute('data-category') === selectedCategory) {
            option.style.display = 'block';
          } else {
            option.style.display = 'none';
            if (option.selected) {
              option.selected = false;
            }
          }
        });

        // If no brand selected after filtering, reset to placeholder
        if (!brandSelect.value) {
          brandSelect.selectedIndex = 0;
        }
      }

      // Set category to current product's category
      categorySelect.value = currentCategoryId;

      // Run filter on page load
      filterBrands();

      // Filter again on category change
      categorySelect.addEventListener('change', filterBrands);
    });


    // document.getElementById('category_id').addEventListener('change', function() {
    //   var categoryId = this.value;
    //   var brandOptions = document.querySelectorAll('#brand_id option');

    //   brandOptions.forEach(function(option) {
    //     if (!option.value) return; // Skip placeholder
    //     if (option.getAttribute('data-category') === categoryId) {
    //       option.style.display = 'block';
    //     } else {
    //       option.style.display = 'none';
    //       option.selected = false;
    //     }
    //   });
    // });


    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('category_id').dispatchEvent(new Event('change'));
      // Set up category change listener
      // document.getElementById('categorySelect').addEventListener('change', filterBrands);

      // Initial filter to populate brands
      // filterBrands();

      // Sidebar functionality
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

  <script>
    // Simple spec groups/rows manager (add/remove, keep input names consistent)
    (function() {
      function safeKey(name) {
        return String(name || 'Group').replace(/[^a-zA-Z0-9_-]/g, '_');
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

      function buildGroupNode(groupName) {
        const safe = safeKey(groupName);
        const wrapper = document.createElement('div');
        wrapper.className = 'spec-group mb-3 border rounded p-2';
        wrapper.innerHTML = `
          <div class="d-flex mb-2">
            <input name="spec_group_name[]" class="form-control me-2 spec-group-name" value="${escapeHtml(groupName)}" placeholder="Group name (e.g. Processor)">
            <button type="button" class="btn btn-outline-danger btn-sm remove-group-btn">Remove Group</button>
          </div>
          <div class="spec-rows"></div>
          <div><button type="button" class="btn btn-sm btn-primary add-row-btn">+ Add spec</button></div>
        `;
        wrapper.querySelector('.spec-rows').appendChild(createRowEl(safe));
        // update names when group name changes
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

      // Delegated clicks
      container.addEventListener('click', function(e) {
        if (e.target.matches('.remove-group-btn')) {
          const g = e.target.closest('.spec-group');
          if (g) g.remove();
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
          const row = e.target.closest('.spec-row');
          if (row) row.remove();
          return;
        }
      });

      // Add new group
      addGroupBtn?.addEventListener('click', function() {
        container.appendChild(buildGroupNode('New Group'));
      });
    })();
  </script>

  <script src="../assets/vendor/jquery/jquery-3.7.1.min.js"></script>
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>