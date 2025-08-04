<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: index.php");
  exit;
}

include('../includes/db_connect.php');

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: products.php?delete=invalid");
  exit;
}

$product_id = intval($_GET['id']);

// Get product images before deletion
$imagesQuery = "SELECT image_path FROM product_images WHERE product_id = $product_id";
$imagesResult = mysqli_query($conn, $imagesQuery);
$imagesToDelete = [];

if ($imagesResult) {
  while ($image = mysqli_fetch_assoc($imagesResult)) {
    $imagesToDelete[] = $image['image_path'];
  }
}

// Start transaction
mysqli_begin_transaction($conn);

try {
  // Delete product images from database
  $deleteImagesQuery = "DELETE FROM product_images WHERE product_id = $product_id";
  if (!mysqli_query($conn, $deleteImagesQuery)) {
    throw new Exception("Failed to delete product images from database");
  }

  // Delete product from database
  $deleteProductQuery = "DELETE FROM products WHERE id = $product_id";
  if (!mysqli_query($conn, $deleteProductQuery)) {
    throw new Exception("Failed to delete product from database");
  }

  // Check if product was actually deleted
  if (mysqli_affected_rows($conn) === 0) {
    throw new Exception("Product not found or already deleted");
  }

  // Commit transaction
  mysqli_commit($conn);

  // Delete image files from filesystem
  foreach ($imagesToDelete as $imagePath) {
    $fullPath = '../uploads/' . $imagePath;
    if (file_exists($fullPath)) {
      unlink($fullPath);
    }
  }

  // Redirect with success message
  header("Location: products.php?delete=success");
  exit;
} catch (Exception $e) {
  // Rollback transaction
  mysqli_rollback($conn);

  // Redirect with error message
  header("Location: products.php?delete=failed");
  exit;
}
