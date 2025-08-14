<?php
include '../includes/db_connect.php';

if (isset($_GET['id'])) {
  $id = intval($_GET['id']);

  // Optional: Check if any products use this brand before deleting
  $product_check = mysqli_query($conn, "SELECT product_id FROM products WHERE brand_id = $id LIMIT 1");
  if (mysqli_num_rows($product_check) > 0) {
    header("Location: brands.php?error=brand_in_use");
    exit;
  }

  $delete = mysqli_query($conn, "DELETE FROM brands WHERE brand_id = $id");
  if ($delete) {
    header("Location: brands.php?success=deleted");
  } else {
    header("Location: brands.php?error=delete_failed");
  }
}
