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
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: products.php");
  exit;
}

$product_id = intval($_GET['id']);

// Get existing product data
$productQuery = "SELECT p.*, c.name as category_name, b.name as brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN brands b ON p.brand_id = b.id 
                WHERE p.id = $product_id";
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
    $checkQuery = "SELECT COUNT(*) as count FROM products WHERE sku = '" . mysqli_real_escape_string($conn, $sku) . "' AND id != $product_id";
    $result = mysqli_query($conn, $checkQuery);
    $data = mysqli_fetch_assoc($result);

    if ($data['count'] > 0) {
      $errors[] = "SKU already exists. Please use a unique SKU.";
    }
  }

  // Validate brand belongs to selected category
  if ($category_id > 0 && $brand_id > 0) {
    $brandCategoryQuery = "SELECT COUNT(*) as count FROM brand_categories 
                          WHERE brand_id = $brand_id AND category_id = $category_id";
    $brandCategoryResult = mysqli_query($conn, $brandCategoryQuery);
    $brandCategoryData = mysqli_fetch_assoc($brandCategoryResult);

    if ($brandCategoryData['count'] == 0) {
      $errors[] = "Selected brand is not valid for the chosen category.";
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
                       name = '$name', 
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
                       WHERE id = $product_id";

    if (mysqli_query($conn, $updateQuery)) {
      // Handle new image uploads
      $uploadDir = '../uploads/';

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

      header("Location: products.php?update=success");
      exit;
    } else {
      $errors[] = "Failed to update product: " . mysqli_error($conn);
    }
  }
}

// Get categories for dropdown
$categoriesQuery = "SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC";
$categoriesResult = mysqli_query($conn, $categoriesQuery);

// Get all brands with their categories for JavaScript filtering
$brandsQuery = "SELECT b.id, b.name, GROUP_CONCAT(bc.category_id) as category_ids 
                FROM brands b 
                LEFT JOIN brand_categories bc ON b.id = bc.brand_id 
                WHERE b.is_active = 1 
                GROUP BY b.id, b.name 
                ORDER BY b.name ASC";
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

  <style>
    .form-container {
      background: white;
      border-radius: 8px;
      padding: 2rem;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .required {
      color: #dc3545;
    }

    .existing-images {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 10px;
    }

    .existing-images img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 5px;
      border: 2px solid #ddd;
    }

    .image-container {
      position: relative;
      display: inline-block;
    }

    .delete-image {
      position: absolute;
      top: -5px;
      right: -5px;
      background: #dc3545;
      color: white;
      border: none;
      border-radius: 50%;
      width: 25px;
      height: 25px;
      font-size: 12px;
      cursor: pointer;
    }

    .main-image-badge {
      position: absolute;
      bottom: 5px;
      left: 5px;
      background: #28a745;
      color: white;
      padding: 2px 6px;
      border-radius: 3px;
      font-size: 10px;
    }

    .brand-info {
      font-size: 0.85em;
      color: #6c757d;
      margin-top: 5px;
    }

    .current-selection {
      background-color: #e7f3ff;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 15px;
      border-left: 4px solid #0066cc;
    }
  </style>
</head>

<body>
  <?php include '../includes/header.php'; ?>
  <?php $page = 'products';
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
                  <strong>Name:</strong> <?= htmlspecialchars($product['name']) ?>
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
                    value="<?= htmlspecialchars($product['name']) ?>" required>
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
                      <option value="<?= $category['id'] ?>"
                        <?= ($product['category_id'] == $category['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <!-- Brand -->
                <div class="col-md-6">
                  <label class="form-label">Brand <span class="required">*</span></label>
                  <select name="brand" class="form-select" id="brandSelect" required>
                    <option value="">-- Select Brand --</option>
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
                          <img src="<?= $imagePath ?>" alt="<?= $product['name'] ?>">
                          <?php if ($img['is_main']): ?>
                            <span class="main-image-badge">Main</span>
                          <?php endif; ?>
                          <button type="button" class="delete-image"
                            onclick="deleteImage(<?= $img['id'] ?>)"
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
                    <button type="submit" class="btn btn-primary">
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
    const brandsData = <?= json_encode($brandsData) ?>;
    const currentBrandId = <?= $product['brand_id'] ?>;
    const currentCategoryId = <?= $product['category_id'] ?>;

    // Delete image function
    function deleteImage(imageId) {
      if (confirm('Are you sure you want to delete this image?')) {
        fetch('delete_image.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'image_id=' + imageId
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

    // Filter brands based on selected category
    function filterBrands() {
      const selectedCategory = document.getElementById('categorySelect').value;
      const brandSelect = document.getElementById('brandSelect');
      const brandInfo = document.getElementById('brandInfo');

      // Clear current options
      brandSelect.innerHTML = '<option value="">-- Select Brand --</option>';

      if (selectedCategory === '') {
        brandInfo.textContent = 'Select a category first to see available brands';
        return;
      }

      // Filter brands for selected category
      const availableBrands = brandsData.filter(brand => {
        if (!brand.category_ids) return false;
        const categoryIds = brand.category_ids.split(',');
        return categoryIds.includes(selectedCategory);
      });

      if (availableBrands.length === 0) {
        brandSelect.innerHTML = '<option value="">-- No brands available for this category --</option>';
        brandInfo.textContent = 'No brands found for selected category';
        return;
      }

      // Add filtered brands to dropdown
      availableBrands.forEach(brand => {
        const option = document.createElement('option');
        option.value = brand.id;
        option.textContent = brand.name;

        // Select current brand if it matches
        if (parseInt(brand.id) === currentBrandId) {
          option.selected = true;
        }

        brandSelect.appendChild(option);
      });

      brandInfo.textContent = `${availableBrands.length} brand(s) available for this category`;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      // Set up category change listener
      document.getElementById('categorySelect').addEventListener('change', filterBrands);

      // Initial filter to populate brands
      filterBrands();

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

  <script src="../assets/vendor/jquery/jquery-3.7.1.min.js"></script>
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>