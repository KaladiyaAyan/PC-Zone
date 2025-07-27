<?php
include('./includes/db_connect.php');

// Get product ID
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT p.*, c.name as category, b.name as brand FROM products p
JOIN categories c ON p.category_id = c.id
JOIN brands b ON p.brand_id = b.id
WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

include('./includes/product_form.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
  $name = $_POST['name'];
  $category = $_POST['category'];
  $brand = $_POST['brand'];
  $stock = $_POST['stock'];
  $price = $_POST['price'];
  $description = $_POST['description'];

  // Upload new images if provided
  function uploadImage($inputName, $oldValue)
  {
    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
      $targetDir = "uploads/";
      $fileName = basename($_FILES[$inputName]["name"]);
      $targetFile = $targetDir . uniqid() . '_' . $fileName;
      move_uploaded_file($_FILES[$inputName]["tmp_name"], $targetFile);
      return $targetFile;
    }
    return $oldValue;
  }

  $img1 = uploadImage('image1', $product['image1']);
  $img2 = uploadImage('image2', $product['image2']);
  $img3 = uploadImage('image3', $product['image3']);

  $cat_id = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
  $cat_id->execute([$category]);
  $category_id = $cat_id->fetchColumn();

  $brand_id_stmt = $pdo->prepare("SELECT id FROM brands WHERE name = ?");
  $brand_id_stmt->execute([$brand]);
  $brand_id = $brand_id_stmt->fetchColumn();

  $stmt = $pdo->prepare("UPDATE products SET name=?, category_id=?, brand_id=?, stock=?, price=?, description=?, image1=?, image2=?, image3=? WHERE id=?");
  $stmt->execute([$name, $category_id, $brand_id, $stock, $price, $description, $img1, $img2, $img3, $id]);

  header("Location: products.php?msg=updated");
  exit();
}
