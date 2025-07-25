<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header("Location: ../index.php");
  exit;
}

require_once __DIR__ . '/../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  die("Invalid access");
}

$id         = intval($_POST['id']);
$name       = trim($_POST['name']);
$category   = trim($_POST['category']);
$brand      = trim($_POST['brand']);
$price      = floatval($_POST['price']);
$stock      = intval($_POST['stock']);
$description = trim($_POST['description']);

// Get existing image paths
$stmt = $conn->prepare("SELECT image1, image2, image3, image4 FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$existing = $result->fetch_assoc();

$uploadsDir = "../uploads/";
$updatedImages = [];

for ($i = 1; $i <= 4; $i++) {
  $fileKey = 'image' . $i;

  if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES[$fileKey]['tmp_name'];
    $ext = pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);
    $filename = uniqid("product_") . ".$ext";
    move_uploaded_file($tmpName, $uploadsDir . $filename);
    $updatedImages[$fileKey] = "uploads/$filename";
  } else {
    $updatedImages[$fileKey] = $existing[$fileKey]; // keep old image
  }
}

// Update query
$stmt = $conn->prepare("UPDATE products SET name=?, category=?, brand=?, price=?, stock=?, description=?, image1=?, image2=?, image3=?, image4=? WHERE id=?");
$stmt->bind_param(
  "ssssisssssi",
  $name,
  $category,
  $brand,
  $price,
  $stock,
  $description,
  $updatedImages['image1'],
  $updatedImages['image2'],
  $updatedImages['image3'],
  $updatedImages['image4'],
  $id
);

if ($stmt->execute()) {
  header("Location: products.php?update=success");
  exit;
} else {
  echo "Update failed: " . $stmt->error;
}
