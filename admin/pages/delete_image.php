<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
  exit;
}

include('../includes/db_connect.php');

// Check if request method is POST and image_id is provided
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_image_id']) || !is_numeric($_POST['product_image_id'])) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid request']);
  exit;
}

$image_id = intval($_POST['product_image_id']);

try {
  // Get image details before deletion
  $imageQuery = "SELECT * FROM product_images WHERE product_image_id = $image_id";
  $imageResult = mysqli_query($conn, $imageQuery);

  if (!$imageResult || mysqli_num_rows($imageResult) === 0) {
    throw new Exception('Image not found');
  }

  $image = mysqli_fetch_assoc($imageResult);
  $product_id = $image['product_id'];
  $image_path = $image['image_path'];
  $is_main = $image['is_main'];

  // Delete image record from database
  $deleteQuery = "DELETE FROM product_images WHERE product_image_id = $image_id";

  if (!mysqli_query($conn, $deleteQuery)) {
    throw new Exception('Failed to delete image from database: ' . mysqli_error($conn));
  }

  // Delete physical file
  $file_path = '../uploads/' . $image_path;
  if (file_exists($file_path)) {
    if (!unlink($file_path)) {
      // Log warning but don't fail the operation
      error_log("Warning: Could not delete image file: $file_path");
    }
  }

  // If this was the main image, set another image as main (if any exist)
  if ($is_main) {
    $updateMainQuery = "UPDATE product_images 
                        SET is_main = 1 
                        WHERE product_id = $product_id 
                        AND product_image_id = (
                          SELECT min_id FROM (
                            SELECT MIN(product_image_id) as min_id 
                            FROM product_images 
                            WHERE product_id = $product_id
                          ) as temp
                        )";
    mysqli_query($conn, $updateMainQuery);
  }

  echo json_encode([
    'success' => true,
    'message' => 'Image deleted successfully',
    'was_main' => $is_main
  ]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ]);
}
