<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: ./../index.php");
  exit;
}

require_once './includes/db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
  header("Location: products.php?error=invalid_id");
  exit;
}

$errors = [];

// Fetch existing product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) {
  die("Product not found.");
}

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

  // Upload images (keep old if not re-uploaded)
  $imagePaths = [];
  for ($i = 1; $i <= 4; $i++) {
    $imgField = 'image' . $i;
    if (isset($_FILES[$imgField]) && $_FILES[$imgField]['error'] === 0) {
      $ext = pathinfo($_FILES[$imgField]['name'], PATHINFO_EXTENSION);
      $filename = uniqid("img_", true) . '.' . $ext;
      $target = '../uploads/' . $filename;
      move_uploaded_file($_FILES[$imgField]['tmp_name'], $target);
      $imagePaths[$i] = $filename;
    } else {
      $imagePaths[$i] = $product["image$i"]; // use existing
    }
  }

  // ✅ New check: Require at least one image
  if (count(array_filter($imagePaths)) < 1) {
    $errors[] = "At least one product image is required.";
  }

  if (empty($errors)) {
    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, brand_id=?, category_id=?, image1=?, image2=?, image3=?, image4=? WHERE id=?");
    $stmt->bind_param(
      "ssdiisssssi",
      $name,
      $description,
      $price,
      $stock,
      $brand_id,
      $category_id,
      $imagePaths[1],
      $imagePaths[2],
      $imagePaths[3],
      $imagePaths[4],
      $id
    );

    if ($stmt->execute()) {
      header("Location: products.php?update=success");
      exit;
    } else {
      $errors[] = "Error updating product: " . $stmt->error;
    }
  }
}

// Fetch categories
$categoryStmt = $conn->query("SELECT id, name FROM categories ORDER BY name");
$categories = $categoryStmt->fetch_all(MYSQLI_ASSOC);

// Fetch brands (optional JS handles filter)
$brandStmt = $conn->query("SELECT id, name FROM brands ORDER BY name");
$brands = $brandStmt->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Edit Product - PC ZONE Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="./assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
  <?php include './includes/header.php'; ?>
  <?php $page = 'products';
  include './includes/sidebar.php'; ?>

  <main class="main-content">
    <h1 class="mb-4">Edit Product</h1>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><?php echo implode("<br>", $errors); ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Product Name</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Price (₹)</label>
        <input type="number" name="price" class="form-control" step="0.01" value="<?= htmlspecialchars($product['price']) ?>" required>
      </div>

      <div class="col-md-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="6" required><?= htmlspecialchars($product['description']) ?></textarea>
      </div>

      <div class="col-md-4">
        <label class="form-label">Stock</label>
        <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">Category</label>
        <select name="category" id="category" class="form-select" required>
          <option value="">-- Select Category --</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Brand</label>
        <select name="brand" id="brand" class="form-select" required>
          <option value="">-- Select Brand --</option>
          <?php foreach ($brands as $b): ?>
            <option value="<?= $b['id'] ?>" <?= $product['brand_id'] == $b['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($b['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <?php for ($i = 1; $i <= 4; $i++): ?>
        <div class="col-md-3">
          <label class="form-label">Image <?= $i ?></label>
          <input type="file" name="image<?= $i ?>" accept="image/*" class="form-control">
          <?php if (!empty($product["image$i"])): ?>
            <img src="../uploads/<?= $product["image$i"] ?>" alt="Image <?= $i ?>" class="img-fluid mt-1" style="max-height: 100px;">
          <?php endif; ?>
        </div>
      <?php endfor; ?>

      <div class="col-12">
        <button type="submit" class="btn btn-warning"><i class="fa fa-edit"></i> Update Product</button>
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