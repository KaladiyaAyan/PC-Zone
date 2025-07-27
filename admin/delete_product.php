<?php
session_start();

// Redirect if user is not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: ./../index.php");
  exit;
}

require_once './includes/db_connect.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $product_id = intval($_GET['id']);

  // Optional: Fetch product to delete associated images later if needed
  $stmt = $conn->prepare("SELECT image1, image2, image3, image4 FROM products WHERE id = ?");
  $stmt->bind_param("i", $product_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $product = $result->fetch_assoc();

  // Delete the product
  $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
  $stmt->bind_param("i", $product_id);

  if ($stmt->execute()) {
    // Optional: Delete associated images from server (if stored locally)
    // foreach (['image1', 'image2', 'image3', 'image4'] as $imgKey) {
    //     if (!empty($product[$imgKey])) {
    //         $imagePath = './../assets/images/' . $product[$imgKey];
    //         if (file_exists($imagePath)) {
    //             unlink($imagePath); // delete image
    //         }
    //     }
    // }

    header("Location: products.php?delete=success");
    exit;
  } else {
    header("Location: products.php?delete=failed");
    exit;
  }
} else {
  header("Location: products.php?delete=invalid");
  exit;
}
