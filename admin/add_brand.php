<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $category_name = trim($_POST['name']);
  $category_id = $_POST['category_id'];

  if ($category_name && $category_id) {
    // Generate slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $category_name), '-'));

    // Check for duplicate
    $check = mysqli_query($conn, "SELECT brand_id FROM brands WHERE slug = '$slug'");
    if (mysqli_num_rows($check) > 0) {
      header("Location: brands.php?error=duplicate");
      exit;
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO brands (brand_name, category_id, slug) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sis", $category_name, $category_id, $slug);

    if (mysqli_stmt_execute($stmt)) {
      header("Location: brands.php?success=added");
    } else {
      header("Location: brands.php?error=insert_failed");
    }
  } else {
    header("Location: brands.php?error=missing_fields");
  }
}
