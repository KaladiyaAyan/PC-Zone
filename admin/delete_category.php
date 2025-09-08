<?php
// delete_category.php
// Recursively delete category + subcategories and cleanup product image files.
// Uses schema provided in database.sql (products store images in main_image, image_1..image_3)

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$conn = $conn ?? getConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: categories.php?error=invalid_id");
  exit;
}

$rootId = (int) $_GET['id'];

// collect all category ids to delete (iterative DFS/BFS)
$toDelete = [];
$stack = [$rootId];

$childStmt = mysqli_prepare($conn, "SELECT category_id FROM categories WHERE parent_id = ?");
if ($childStmt === false) {
  header("Location: categories.php?error=db_prepare");
  exit;
}

while (!empty($stack)) {
  $cid = array_pop($stack);
  $toDelete[] = $cid;

  mysqli_stmt_bind_param($childStmt, 'i', $cid);
  mysqli_stmt_execute($childStmt);
  $res = mysqli_stmt_get_result($childStmt);
  while ($r = mysqli_fetch_assoc($res)) {
    $stack[] = (int)$r['category_id'];
  }
}
mysqli_stmt_close($childStmt);

if (empty($toDelete)) {
  header("Location: categories.php?error=not_found");
  exit;
}

$inList = implode(',', array_map('intval', $toDelete));

mysqli_begin_transaction($conn);
try {
  // 1) Find products under these categories to cleanup files
  $prodSql = "SELECT product_id, main_image, image_1, image_2, image_3 FROM products WHERE category_id IN ($inList)";
  $prodRes = mysqli_query($conn, $prodSql);
  $imagesToRemove = [];
  if ($prodRes) {
    while ($p = mysqli_fetch_assoc($prodRes)) {
      foreach (['main_image', 'image_1', 'image_2', 'image_3'] as $col) {
        if (!empty($p[$col])) $imagesToRemove[] = $p[$col];
      }
    }
  }

  // 2) Delete products that belong to these categories (this will cascade to product_specs, order_items via FK)
  $delProductsSql = "DELETE FROM products WHERE category_id IN ($inList)";
  if (!mysqli_query($conn, $delProductsSql)) {
    throw new Exception("Failed to delete products: " . mysqli_error($conn));
  }

  // 3) Delete the categories themselves (we have full list)
  $delCatsSql = "DELETE FROM categories WHERE category_id IN ($inList)";
  if (!mysqli_query($conn, $delCatsSql)) {
    throw new Exception("Failed to delete categories: " . mysqli_error($conn));
  }

  // Commit DB changes
  mysqli_commit($conn);

  // 4) Remove image files from filesystem (best-effort, do not fail on unlink errors)
  $uploadDir = __DIR__ . '/../uploads/';
  $assetDir  = __DIR__ . '/../assets/images/';
  foreach ($imagesToRemove as $img) {
    $img = trim((string)$img);
    if ($img === '') continue;
    $paths = [
      $uploadDir . $img,
      $assetDir . $img
    ];
    foreach ($paths as $p) {
      if (file_exists($p) && is_file($p)) {
        @unlink($p);
      }
    }
  }

  header("Location: categories.php?success=deleted");
  exit;
} catch (Exception $e) {
  mysqli_rollback($conn);
  // optionally log $e->getMessage()
  header("Location: categories.php?error=delete_failed");
  exit;
}
