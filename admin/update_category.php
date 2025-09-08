<?php
include '../includes/db_connect.php';
include '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $category_id = $_POST['id'];
  $category_name = trim($_POST['name']);
  $parent_id = $_POST['parent_id'] !== '' ? intval($_POST['parent_id']) : NULL;

  if ($category_id && $category_name) {
    // Generate slug
    // $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    $slug = slugify($category_name);

    $check = mysqli_query($conn, "SELECT category_id FROM categories WHERE slug = '$slug' AND category_id != $category_id");
    // $query = "UPDATE categories SET name=?, parent_id=?, slug=? WHERE id=?"; 
    if (mysqli_num_rows($check) > 0) {
      header("Location: categories.php?error=duplicate");
      exit;
    }

    if ($parent_id !== NULL) {
      $stmt = mysqli_prepare($conn, "UPDATE categories SET category_name=?, parent_id=?, level=1, slug=? WHERE category_id=?");
      mysqli_stmt_bind_param($stmt, "sisi", $category_name, $parent_id, $slug, $category_id);
    } else {
      $stmt = mysqli_prepare($conn, "UPDATE categories SET category_name=?,parent_id=NULL, level=0, slug=? WHERE category_id=?");
      mysqli_stmt_bind_param($stmt, "ssi", $category_name, $slug, $category_id);
    }

    // $stmt = mysqli_prepare($conn, "UPDATE categories SET name=?, parent_id=?, slug=? WHERE id=?");
    // mysqli_stmt_bind_param($stmt, "sssi", $name, $parent_id, $slug, $id);

    if (mysqli_stmt_execute($stmt)) {
      header("Location: categories.php?success=updated");
    } else {
      header("Location:categories.php?error=update_failed");
    }
  } else {
    header("Location: categories.php?error=missing_fields");
  }
}
