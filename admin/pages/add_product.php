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
    $checkQuery = "SELECT COUNT(*) as count FROM products WHERE slug = '$slug'";
    $result = mysqli_query($conn, $checkQuery);
    $data = mysqli_fetch_assoc($result);

    if ($data['count'] == 0) {
      break;
    }

    $slug = $originalSlug . '-' . $counter;
    $counter++;
  }

  return $slug;
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

  // Check SKU uniqueness
  if (!empty($sku)) {
    $checkQuery = "SELECT COUNT(*) as count FROM products WHERE sku = '$sku'";
    $result = mysqli_query($conn, $checkQuery);
    $data = mysqli_fetch_assoc($result);

    if ($data['count'] > 0) {
      $errors[] = "SKU already exists. Please use a unique SKU.";
    }
  }

  // Generate slug if no errors
  if (empty($errors)) {
    $slug = createSlug($name, $conn);
  }

  // Process if no errors
  if (empty($errors)) {
    // Escape strings for SQL
    $name = mysqli_real_escape_string($conn, $name);
    $sku = mysqli_real_escape_string($conn, $sku);
    $slug = mysqli_real_escape_string($conn, $slug);
    $description = mysqli_real_escape_string($conn, $description);

    // Insert product
    $insertQuery = "INSERT INTO products (name, sku, slug, description, price, discount, stock, weight, brand_id, category_id, is_featured, is_active) 
                       VALUES ('$name', '$sku', '$slug', '$description', $price, $discount, $stock, $weight, $brand_id, $category_id, $is_featured, $is_active)";

    if (mysqli_query($conn, $insertQuery)) {
      $product_id = mysqli_insert_id($conn);

      // Handle image uploads
      $uploadedImages = 0;
      $uploadDir = '../uploads/';

      // Create upload directory if it doesn't exist
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

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
              // Set first image as main image
              $isMain = ($uploadedImages == 0) ? 1 : 0;

              $imageQuery = "INSERT INTO product_images (product_id, image_path, is_main) VALUES ($product_id, '$filename', $isMain)";
              mysqli_query($conn, $imageQuery);

              $uploadedImages++;
            }
          }
        }
      }

      if ($uploadedImages > 0) {
        header("Location: products.php?insert=success");
        exit;
      } else {
        $errors[] = "Product added but no images were uploaded. At least one image is recommended.";
      }
    } else {
      $errors[] = "Failed to add product: " . mysqli_error($conn);
    }
  }
}

// Get categories and brands for dropdowns
$categoriesQuery = "SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name ASC";
$categoriesResult = mysqli_query($conn, $categoriesQuery);

$brandsQuery = "SELECT id, name, category_id FROM brands ORDER BY name ASC";
$brandsResult = mysqli_query($conn, $brandsQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Add Product - PC ZONE Admin</title>
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

    .preview-images {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 10px;
    }

    .preview-images img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 5px;
      border: 2px solid #ddd;
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
                    <?php while ($category = mysqli_fetch_assoc($categoriesResult)): ?>
                      <option value="<?= $category['id'] ?>"
                        <?= (isset($_POST['category']) && $_POST['category'] == $category['id']) ? 'selected' : '' ?>>
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
                    <?php while ($brand = mysqli_fetch_assoc($brandsResult)): ?>
                      <option value="<?= $brand['id'] ?>" data-category="<?= $brand['category_id'] ?>"
                        <?= (isset($_POST['brand']) && $_POST['brand'] == $brand['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($brand['name']) ?>
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

      // Reset brand selection
      brandSelect.value = '';

      // Show/hide brand options based on category
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

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      // Set up category change listener
      document.getElementById('categorySelect').addEventListener('change', filterBrands);

      // Initial filter
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