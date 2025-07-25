<?php
// File: pcparts-admin-panel/pages/edit_product.php
session_start();
// redirect if not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid request.");
}

require_once __DIR__ . '/../includes/db_connect.php';


$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  die("Product not found.");
}

$product = $result->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Product</title>

  <link rel="stylesheet" href="./../assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="./../assets/css/edit_product.css">
</head>

<body>
  <main class="main-content">
    <h1>Edit Product</h1>
    <form action="update_product.php" method="POST" enctype="multipart/form-data" class="edit-form">
      <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">

      <label>Name</label>
      <input type="text" name="name" required value="<?= htmlspecialchars($product['name']) ?>">

      <label>Category</label>
      <select name="category" required>
        <option value="CPU" <?= $product['category'] === 'CPU' ? 'selected' : '' ?>>CPU</option>
        <option value="GPU" <?= $product['category'] === 'GPU' ? 'selected' : '' ?>>GPU</option>
        <option value="Motherboard" <?= $product['category'] === 'Motherboard' ? 'selected' : '' ?>>Motherboard</option>
        <!-- Add more -->
      </select>

      <label>Brand</label>
      <select name="brand" required>
        <option value="Intel" <?= $product['brand'] === 'Intel' ? 'selected' : '' ?>>Intel</option>
        <option value="AMD" <?= $product['brand'] === 'AMD' ? 'selected' : '' ?>>AMD</option>
        <option value="Nvidia" <?= $product['brand'] === 'Nvidia' ? 'selected' : '' ?>>Nvidia</option>
        <!-- Add more -->
      </select>

      <label>Price (₹)</label>
      <input type="number" step="0.01" name="price" required value="<?= htmlspecialchars($product['price']) ?>">

      <label>Stock</label>
      <input type="number" name="stock" required value="<?= $product['stock'] ?>">

      <label>Description</label>
      <textarea name="description" rows="8"><?= htmlspecialchars($product['description']) ?></textarea>

      <?php for ($i = 1; $i <= 4; $i++): ?>
        <label>Image <?= $i ?> <?= !empty($product["image$i"]) ? '(Current: ' . htmlspecialchars($product["image$i"]) . ')' : '' ?></label>
        <input type="file" name="image<?= $i ?>">
      <?php endfor; ?>

      <button type="submit" class="btn-save">Update Product</button>
    </form>
  </main>
</body>

</html>