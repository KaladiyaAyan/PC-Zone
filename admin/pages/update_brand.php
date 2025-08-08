<?php
include '../includes/db_connect.php';
include '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];
  $name = trim($_POST['name']);
  $category_id = $_POST['category_id'];

  if ($id && $name && $category_id) {
    // $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    $slug = slugify($name);

    // Check for slug conflict with other brands
    $check = mysqli_query($conn, "SELECT id FROM brands WHERE slug = '$slug' AND id != $id");
    if (mysqli_num_rows($check) > 0) {
      header("Location: brands.php?error=duplicate");
      exit;
    }

    $stmt = mysqli_prepare($conn, "UPDATE brands SET name=?, category_id=?, slug=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sisi", $name, $category_id, $slug, $id);

    if (mysqli_stmt_execute($stmt)) {
      header("Location: brands.php?success=updated");
    } else {
      header("Location: brands.php?error=update_failed");
    }
  } else {
    header("Location: brands.php?error=missing_fields");
  }
}
