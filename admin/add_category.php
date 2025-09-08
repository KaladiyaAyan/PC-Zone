<?php
// add_category.php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: categories.php");
  exit;
}

$conn = $conn ?? getConnection(); // prefer existing $conn, fall back to helper

$category_name = trim($_POST['name'] ?? '');
$parent_id_raw = $_POST['parent_id'] ?? '';

if ($category_name === '') {
  header("Location: categories.php?error=missing_fields");
  exit;
}

// normalize parent id
$parent_id = null;
if ($parent_id_raw !== '' && $parent_id_raw !== null) {
  $parent_id = (int)$parent_id_raw;
  if ($parent_id <= 0) $parent_id = null;
}

// generate base slug
function make_slug($str)
{
  $s = strtolower(trim($str));
  $s = preg_replace('/[^a-z0-9\s-]/', '', $s);
  $s = preg_replace('/\s+/', '-', $s);
  $s = preg_replace('/-+/', '-', $s);
  return trim($s, '-');
}

$baseSlug = make_slug($category_name);
$slug = $baseSlug;
$counter = 1;

// ensure unique slug
$stmtCheck = mysqli_prepare($conn, "SELECT COUNT(*) AS cnt FROM categories WHERE slug = ?");
if (!$stmtCheck) {
  header("Location: categories.php?error=db_prepare");
  exit;
}
while (true) {
  mysqli_stmt_bind_param($stmtCheck, 's', $slug);
  mysqli_stmt_execute($stmtCheck);
  $res = mysqli_stmt_get_result($stmtCheck);
  $row = mysqli_fetch_assoc($res);
  if (!$row || (int)$row['cnt'] === 0) break;
  $slug = $baseSlug . '-' . $counter;
  $counter++;
}
mysqli_stmt_close($stmtCheck);

// determine level: if parent provided try to fetch parent's level, otherwise 0
$level = 0;
if ($parent_id !== null) {
  $pstmt = mysqli_prepare($conn, "SELECT level FROM categories WHERE category_id = ? LIMIT 1");
  if ($pstmt) {
    mysqli_stmt_bind_param($pstmt, 'i', $parent_id);
    mysqli_stmt_execute($pstmt);
    $pres = mysqli_stmt_get_result($pstmt);
    $prow = mysqli_fetch_assoc($pres);
    if ($prow) {
      $level = (int)$prow['level'] + 1;
    } else {
      // parent doesn't exist -> treat as top-level
      $parent_id = null;
      $level = 0;
    }
    mysqli_stmt_close($pstmt);
  }
}

// insert category
if ($parent_id === null) {
  $ins = mysqli_prepare($conn, "INSERT INTO categories (category_name, parent_id, level, slug) VALUES (?, NULL, ?, ?)");
  if (!$ins) {
    header("Location: categories.php?error=db_prepare");
    exit;
  }
  mysqli_stmt_bind_param($ins, 'sis', $category_name, $level, $slug);
} else {
  $ins = mysqli_prepare($conn, "INSERT INTO categories (category_name, parent_id, level, slug) VALUES (?, ?, ?, ?)");
  if (!$ins) {
    header("Location: categories.php?error=db_prepare");
    exit;
  }
  mysqli_stmt_bind_param($ins, 'siis', $category_name, $parent_id, $level, $slug);
}

$ok = mysqli_stmt_execute($ins);
if ($ok) {
  mysqli_stmt_close($ins);
  header("Location: categories.php?success=added");
  exit;
} else {
  $err = mysqli_stmt_error($ins);
  mysqli_stmt_close($ins);
  header("Location: categories.php?error=insert_failed");
  exit;
}
