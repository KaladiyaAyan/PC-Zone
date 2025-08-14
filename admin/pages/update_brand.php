<?php
include '../includes/db_connect.php';
include '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $brand_id = $_POST['id'];
  $brand_name = trim($_POST['name']);
  $category_id = $_POST['category_id'];

  if ($brand_id && $brand_name && $category_id) {
    // $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    $slug = slugify($brand_name);

    // Check for slug conflict with other brands
    $check = mysqli_query($conn, "SELECT brand_id FROM brands WHERE slug = '$slug' AND brand_id != $brand_id");
    if (mysqli_num_rows($check) > 0) {
      header("Location: brands.php?error=duplicate");
      exit;
    }

    $stmt = mysqli_prepare($conn, "UPDATE brands SET brand_name=?, category_id=?, slug=? WHERE brand_id=?");
    mysqli_stmt_bind_param($stmt, "sisi", $brand_name, $category_id, $slug, $brand_id);

    if (mysqli_stmt_execute($stmt)) {
      header("Location: brands.php?success=updated");
    } else {
      header("Location: brands.php?error=update_failed");
    }
  } else {
    header("Location: brands.php?error=missing_fields");
  }
}
