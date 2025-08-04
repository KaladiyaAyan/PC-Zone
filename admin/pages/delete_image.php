<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
  exit;
}

include('../includes/db_connect.php');

// Check if image ID is provided
if (!isset($_POST['image_id']) || !is_numeric($_POST['image_id'])) {
  echo json_encode(['success' => false, 'message' => 'Invalid image ID']);
  exit;
}

$image_id = intval($_POST['image_id']);

// Get image details before deletion
$imageQuery = "SELECT * FROM product_images WHERE id = $image_id";
$imageResult = mysqli_query($conn, $imageQuery);

if (!$imageResult || mysqli_num_rows($imageResult) === 0) {
  echo json_encode(['success' => false, 'message' => 'Image not found']);
  exit;
}

$image = mysqli_fetch_assoc($imageResult);
$product_id = $image['product_id'];
$image_path = $image['image_path'];
$is_main = $image['is_main'];

// Check if this is the only image for the product
$countQuery = "SELECT COUNT(*) as count FROM product_images WHERE product_id = $product_id";
$countResult = mysqli_query($conn, $countQuery);
$countData = mysqli_fetch_assoc($countResult);

if ($countData['count'] <= 1) {
  echo json_encode(['success' => false, 'message' => 'Cannot delete the only image. Product must have at least one image.']);
  exit;
}

// Delete image from database
$deleteQuery = "DELETE FROM product_images WHERE id = $image_id";
if (mysqli_query($conn, $deleteQuery)) {
  // If this was the main image, set another image as main
  if ($is_main) {
    $setMainQuery = "UPDATE product_images SET is_main = 1 WHERE product_id = $product_id LIMIT 1";
    mysqli_query($conn, $setMainQuery);
  }

  // Delete image file from filesystem
  $fullPath = '../uploads/' . $image_path;
  if (file_exists($fullPath)) {
    unlink($fullPath);
  }

  echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
} else {
  echo json_encode(['success' => false, 'message' => 'Failed to delete image: ' . mysqli_error($conn)]);
}
