<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // $id = $_POST['id'];
  $category_name = trim($_POST['name']);
  $parent_id = $_POST['parent_id'];

  if ($category_name) {
    // Generate slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $category_name), '-'));

    // Check for duplicate
    $check = mysqli_query($conn, "SELECT category_id FROM categories WHERE slug = '$slug'");
    if (mysqli_num_rows($check) > 0) {
      header("Location: categories.php?error=duplicate");
      exit;
    }

    if ($parent_id) {
      $stmt = mysqli_prepare($conn, "INSERT INTO categories ( category_name, parent_id, level, slug) VALUES (?, ?, 1, ?)");
      mysqli_stmt_bind_param($stmt, "sis", $category_name, $parent_id, $slug);
    } else {
      $stmt = mysqli_prepare($conn, "INSERT INTO categories ( category_name, level, slug) VALUES (?, 0, ?)");
      mysqli_stmt_bind_param($stmt, "ss", $category_name, $slug);
    }


    if (mysqli_stmt_execute($stmt)) {
      header("Location: categories.php?success=added");
    } else {
      header("Location: categories.php?error=insert_failed");
    }
  } else {
    header("Location: categories.php?error=missing_fields");
  }
}
