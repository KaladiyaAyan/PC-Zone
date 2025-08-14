<?php
include '../includes/db_connect.php';

if (isset($_GET['id'])) {
  $id = intval($_GET['id']);

  // Function to recursively delete a category and its subcategories
  function deleteCategoryRecursive($conn, $categoryId)
  {
    // First, find all subcategories
    $subCats = mysqli_query($conn, "SELECT category_id FROM categories WHERE parent_id = $categoryId");
    while ($sub = mysqli_fetch_assoc($subCats)) {
      deleteCategoryRecursive($conn, $sub['category_id']);
    }

    // Then delete the current category
    mysqli_query($conn, "DELETE FROM categories WHERE category_id = $categoryId");
  }

  // Start deletion process
  deleteCategoryRecursive($conn, $id);

  if (mysqli_affected_rows($conn) > 0) {
    header("Location: categories.php?success=deleted");
  } else {
    header("Location: categories.php?error=delete_failed");
  }
  exit;
}
