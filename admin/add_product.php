<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: ./../index.php");
  exit;
}

require_once './includes/db_connect.php';

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name        = trim($_POST['name']);
  $description = trim($_POST['description']);
  $price       = floatval($_POST['price']);
  $stock       = intval($_POST['stock']);
  $category_id = intval($_POST['category']);
  $brand_id    = intval($_POST['brand']);

  if ($name === '' || $description === '' || $price <= 0 || $stock < 0 || !$category_id || !$brand_id) {
    $errors[] = "All fields except images are required.";
  }

  $imagePaths = [];
  for ($i = 1; $i <= 4; $i++) {
    $imgField = 'image' . $i;
    if (isset($_FILES[$imgField]) && $_FILES[$imgField]['error'] === 0) {
      $ext = pathinfo($_FILES[$imgField]['name'], PATHINFO_EXTENSION);
      $filename = uniqid("img_", true) . '.' . $ext;
      $target = '../uploads/' . $filename;
      move_uploaded_file($_FILES[$imgField]['tmp_name'], $target);
      $imagePaths[] = $filename;
    } else {
      $imagePaths[] = null;
    }
  }

  // Require at least one image
  if (count(array_filter($imagePaths)) < 1) {
    $errors[] = "At least one product image is required.";
  }

  if (empty($errors)) {
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, brand_id, category_id, image1, image2, image3, image4) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
      "ssdiisssss",
      $name,
      $description,
      $price,
      $stock,
      $brand_id,
      $category_id,
      $imagePaths[0],
      $imagePaths[1],
      $imagePaths[2],
      $imagePaths[3]
    );

    if ($stmt->execute()) {
      $success = true;
      header("Location: products.php?insert=success");
      exit;
    } else {
      $errors[] = "Error inserting product: " . $stmt->error;
    }
  }
}

// Fetch categories
$categoryStmt = $conn->query("SELECT id, name FROM categories ORDER BY name");
$categories = $categoryStmt->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Add Product - PC ZONE Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="./assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
  <?php include './includes/header.php'; ?>
  <?php $page = 'products';
  include './includes/sidebar.php'; ?>

  <main class="main-content ">
    <h1 class="mb-4">Add New Product</h1>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><?php echo implode("<br>", $errors); ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Product Name</label>
        <input type="text" name="name" class="form-control" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Price (₹)</label>
        <input type="number" name="price" class="form-control" step="0.01" required>
      </div>

      <div class="col-md-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="6" required></textarea>
      </div>

      <div class="col-md-4">
        <label class="form-label">Stock</label>
        <input type="number" name="stock" class="form-control" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">Category</label>
        <select name="category" id="category" class="form-select" required>
          <option value="">-- Select Category --</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Brand</label>
        <select name="brand" id="brand" class="form-select" required>
          <option value="">-- Select Brand --</option>
        </select>
      </div>

      <?php for ($i = 1; $i <= 4; $i++): ?>
        <div class="col-md-3">
          <label class="form-label">Image <?= $i ?></label>
          <input type="file" name="image<?= $i ?>" accept="image/*" class="form-control">
        </div>
      <?php endfor; ?>

      <div class="col-12">
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Add Product</button>
        <a href="products.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </main>

  <script src="./assets/vendor/jquery/jquery-3.7.1.min.js"></script>
  <script>
    $('#category').on('change', function() {
      const categoryId = $(this).val();
      $('#brand').html('<option>Loading...</option>');

      $.get('get_brands.php?category_id=' + categoryId, function(data) {
        $('#brand').html('<option value="">-- Select Brand --</option>');
        const brands = JSON.parse(data);
        brands.forEach(brand => {
          $('#brand').append(`<option value="${brand.id}">${brand.name}</option>`);
        });
      });
    });
  </script>
</body>

</html>