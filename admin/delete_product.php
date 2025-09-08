<?php
session_start();

require_once '../includes/functions.php';
$conn = getConnection();

// Validate product_id
if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
  header("Location: products.php?delete=invalid");
  exit;
}
$product_id = (int) $_GET['product_id'];

// Fetch product image filenames from products table
$stmt = mysqli_prepare($conn, "SELECT main_image, image_1, image_2, image_3 FROM products WHERE product_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $product_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$row) {
  // product not found
  header("Location: products.php?delete=invalid");
  exit;
}

$imagesToDelete = [];
foreach (['main_image', 'image_1', 'image_2', 'image_3'] as $col) {
  if (!empty($row[$col])) $imagesToDelete[] = $row[$col];
}

// Start transaction
mysqli_begin_transaction($conn);

try {
  // Delete product_specs (explicit cleanup)
  $delSpecs = mysqli_prepare($conn, "DELETE FROM product_specs WHERE product_id = ?");
  mysqli_stmt_bind_param($delSpecs, 'i', $product_id);
  if (!mysqli_stmt_execute($delSpecs)) {
    throw new Exception("Failed to delete product specs: " . mysqli_stmt_error($delSpecs));
  }
  mysqli_stmt_close($delSpecs);

  // Delete product row
  $delProd = mysqli_prepare($conn, "DELETE FROM products WHERE product_id = ?");
  mysqli_stmt_bind_param($delProd, 'i', $product_id);
  if (!mysqli_stmt_execute($delProd)) {
    throw new Exception("Failed to delete product: " . mysqli_stmt_error($delProd));
  }

  // Ensure a row was deleted
  if (mysqli_stmt_affected_rows($delProd) === 0) {
    mysqli_stmt_close($delProd);
    throw new Exception("Product not found or already deleted");
  }
  mysqli_stmt_close($delProd);

  // Commit DB transaction
  mysqli_commit($conn);

  // Remove image files from filesystem (uploads first, then fallback assets)
  foreach ($imagesToDelete as $fname) {
    $uploadsPath = __DIR__ . '/../uploads/' . $fname;
    $assetsPath  = __DIR__ . '/../assets/images/' . $fname;
    if (file_exists($uploadsPath)) @unlink($uploadsPath);
    elseif (file_exists($assetsPath)) @unlink($assetsPath);
  }

  header("Location: products.php?delete=success");
  exit;
} catch (Exception $e) {
  // Rollback on error
  mysqli_rollback($conn);
  // (optional) error_log($e->getMessage());
  header("Location: products.php?delete=failed");
  exit;
}
