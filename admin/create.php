<?php
session_start();

include('../includes/db_connect.php');
include('../functions/message.php');

// Check admin authentication
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ./login.php');
  exit;
}

// Add product functionality
if (isset($_POST['add-product'])) {
  // Get form data
  $name = mysqli_real_escape_string($conn, $_POST['name']);
  $sku = mysqli_real_escape_string($conn, $_POST['sku']);
  $description = mysqli_real_escape_string($conn, $_POST['description']);
  $price = mysqli_real_escape_string($conn, $_POST['price']);
  $discount = mysqli_real_escape_string($conn, $_POST['discount']);
  $stock = mysqli_real_escape_string($conn, $_POST['stock']);
  $weight = mysqli_real_escape_string($conn, $_POST['weight']);
  $category = mysqli_real_escape_string($conn, $_POST['category']);
  $brand = mysqli_real_escape_string($conn, $_POST['brand']);
  $is_featured = isset($_POST['is_featured']) ? 1 : 0;
  $is_active = isset($_POST['is_active']) ? 1 : 0;

  // Validate required fields
  if (empty($name) || empty($sku) || empty($description) || empty($price) || empty($category) || empty($brand)) {
    message('popup-error', '<i class="ri-close-line"></i>', 'Please fill all required fields');
    header('Location: add_product.php');
    exit;
  }

  // Check SKU uniqueness
  $check_sku = "SELECT * FROM products WHERE sku='$sku'";
  $check_sku_run = mysqli_query($conn, $check_sku);

  if (mysqli_num_rows($check_sku_run) > 0) {
    message('popup-error', '<i class="ri-close-line"></i>', 'SKU already exists');
    header('Location: add_product.php');
    exit;
  }

  // Handle main image (required)
  if (empty($_FILES['image1']['name'])) {
    message('popup-error', '<i class="ri-close-line"></i>', 'Main image is required');
    header('Location: add_product.php');
    exit;
  }

  // Process images
  $main_image = $_FILES['image1']['name'];
  $image_1 = $_FILES['image2']['name'] ?? '';
  $image_2 = $_FILES['image3']['name'] ?? '';
  $image_3 = $_FILES['image4']['name'] ?? '';

  // Rename main image
  $main_image_ext = pathinfo($main_image, PATHINFO_EXTENSION);
  $main_image = time() . "." . $main_image_ext;

  // Rename additional images if they exist
  if (!empty($image_1)) {
    $image_1_ext = pathinfo($image_1, PATHINFO_EXTENSION);
    $image_1 = time() . "1" . "." . $image_1_ext;
  }

  if (!empty($image_2)) {
    $image_2_ext = pathinfo($image_2, PATHINFO_EXTENSION);
    $image_2 = time() . "2" . "." . $image_2_ext;
  }

  if (!empty($image_3)) {
    $image_3_ext = pathinfo($image_3, PATHINFO_EXTENSION);
    $image_3 = time() . "3" . "." . $image_3_ext;
  }

  // Create slug from product name
  $slug = strtolower(trim($name));
  $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
  $slug = preg_replace('/\s+/', '-', $slug);
  $slug = trim($slug, '-');

  // Check SLUG uniqueness
  $check_slug = "SELECT * FROM products WHERE slug='$slug'";
  $check_slug_run = mysqli_query($conn, $check_slug);

  if (mysqli_num_rows($check_slug_run) > 0) {
    message('popup-error', '<i class="ri-close-line"></i>', 'Slug already exists');
    header('Location: add_product.php');
    exit;
  }

  // Insert product into database
  $insert_product = "INSERT INTO `products`(`product_name`, `sku`, `slug`, `description`, `price`, `discount`, `stock`, `weight`, `brand_id`, `category_id`, `main_image`, `image_1`, `image_2`, `image_3`, `is_featured`, `is_active`) 
                      VALUES ('$name', '$sku', '$slug', '$description', '$price', '$discount', '$stock', '$weight', '$brand', '$category', '$main_image', '$image_1', '$image_2', '$image_3', '$is_featured', '$is_active')";

  $insert_product_run = mysqli_query($conn, $insert_product);

  if ($insert_product_run) {
    // Move uploaded files
    move_uploaded_file($_FILES['image1']['tmp_name'], '../uploads/' . $main_image);

    if (!empty($_FILES['image2']['name'])) {
      move_uploaded_file($_FILES['image2']['tmp_name'], '../uploads/' . $image_1);
    }

    if (!empty($_FILES['image3']['name'])) {
      move_uploaded_file($_FILES['image3']['tmp_name'], '../uploads/' . $image_2);
    }

    if (!empty($_FILES['image4']['name'])) {
      move_uploaded_file($_FILES['image4']['tmp_name'], '../uploads/' . $image_3);
    }

    message('popup-success', '<i class="ri-check-line"></i>', 'Product added successfully');
    header('Location: products.php');
    exit;
  } else {
    message('popup-error', '<i class="ri-close-line"></i>', 'Failed to add product');
    header('Location: add_product.php');
    exit;
  }
}

if (isset($_POST['add-category'])) {
  $name = trim(mysqli_real_escape_string($conn, $_POST['name'] ?? ''));
  $parent_raw = $_POST['parent_id'] ?? '';

  if ($name === '') {
    message('popup-error', '<i class="ri-close-line"></i>', 'Please enter category name');
    header('Location: categories.php');
    exit;
  }

  // normalize parent_id
  $parent_id = null;
  if ($parent_raw !== '' && is_numeric($parent_raw) && intval($parent_raw) > 0) {
    $parent_id = intval($parent_raw);
  }

  // create slug and ensure uniqueness (simple)
  $base = strtolower(trim($name));
  $base = preg_replace('/[^a-z0-9\s-]/', '', $base);
  $base = preg_replace('/\s+/', '-', $base);
  $base = preg_replace('/-+/', '-', $base);
  $base = trim($base, '-');
  $slug = $base;
  $i = 1;
  $stmtc = mysqli_prepare($conn, "SELECT COUNT(*) AS cnt FROM categories WHERE slug = ?");
  while (true) {
    mysqli_stmt_bind_param($stmtc, 's', $slug);
    mysqli_stmt_execute($stmtc);
    $res = mysqli_stmt_get_result($stmtc);
    $r = mysqli_fetch_assoc($res);
    if (intval($r['cnt'] ?? 0) === 0) break;
    $slug = $base . '-' . $i;
    $i++;
  }
  mysqli_stmt_close($stmtc);

  // determine level
  $level = 0;
  if ($parent_id !== null) {
    $pstmt = mysqli_prepare($conn, "SELECT level FROM categories WHERE category_id = ? LIMIT 1");
    if ($pstmt) {
      mysqli_stmt_bind_param($pstmt, 'i', $parent_id);
      mysqli_stmt_execute($pstmt);
      $pres = mysqli_stmt_get_result($pstmt);
      $prow = mysqli_fetch_assoc($pres);
      if ($prow) $level = intval($prow['level']) + 1;
      else $parent_id = null; // parent missing -> make top-level
      mysqli_stmt_close($pstmt);
    } else {
      $parent_id = null;
    }
  }

  // insert
  if ($parent_id === null) {
    $ins = mysqli_prepare($conn, "INSERT INTO categories (category_name, parent_id, level, slug) VALUES (?, NULL, ?, ?)");
    mysqli_stmt_bind_param($ins, 'sis', $name, $level, $slug);
  } else {
    $ins = mysqli_prepare($conn, "INSERT INTO categories (category_name, parent_id, level, slug) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($ins, 'siis', $name, $parent_id, $level, $slug);
  }

  $ok = mysqli_stmt_execute($ins);
  if ($ok) {
    mysqli_stmt_close($ins);
    message('popup-success', '<i class="ri-check-line"></i>', 'Category created successfully');
    header('Location: categories.php?success=added');
    exit;
  } else {
    $err = mysqli_stmt_error($ins);
    if ($ins) mysqli_stmt_close($ins);
    message('popup-error', '<i class="ri-close-line"></i>', 'Failed to create category');
    header('Location: categories.php?error=insert_failed');
    exit;
  }
}

if (isset($_POST['add-brand'])) {
  $name = trim(mysqli_real_escape_string($conn, $_POST['name'] ?? ''));
  $parent_raw = $_POST['category_id'] ?? '';

  if ($name === '') {
    message('popup-error', '<i class="ri-close-line"></i>', 'Please enter brand name');
    header('Location: brands.php');
    exit;
  }

  $category_id = null;
  if ($parent_raw !== '' && is_numeric($parent_raw) && intval($parent_raw) > 0) {
    $category_id = intval($parent_raw);
  }

  // generate slug (unique)
  $base = strtolower(trim($name));
  $base = preg_replace('/[^a-z0-9\s-]/', '', $base);
  $base = preg_replace('/\s+/', '-', $base);
  $base = preg_replace('/-+/', '-', $base);
  $base = trim($base, '-');
  $slug = $base;
  $i = 1;
  $chk = mysqli_prepare($conn, "SELECT COUNT(*) AS cnt FROM brands WHERE slug = ?");
  while (true) {
    mysqli_stmt_bind_param($chk, 's', $slug);
    mysqli_stmt_execute($chk);
    $r = mysqli_stmt_get_result($chk);
    $row = mysqli_fetch_assoc($r);
    if (intval($row['cnt'] ?? 0) === 0) break;
    $slug = $base . '-' . $i;
    $i++;
  }
  mysqli_stmt_close($chk);

  // Insert brand
  $ins = mysqli_prepare($conn, "INSERT INTO brands (brand_name, category_id, slug) VALUES (?, ?, ?)");
  // allow NULL for category_id
  if ($category_id === null) {
    $null = null;
    mysqli_stmt_bind_param($ins, 'sis', $name, $null, $slug); // PDO-like handling; mysqli requires var - use 0 instead
    // fallback since mysqli doesn't support binding null easily with string types -> use 'i' for category and pass 0 as category_id then update to NULL
    mysqli_stmt_close($ins);
    $ins2 = mysqli_prepare($conn, "INSERT INTO brands (brand_name, category_id, slug) VALUES (?, NULL, ?)");
    mysqli_stmt_bind_param($ins2, 'ss', $name, $slug);
    $ok = mysqli_stmt_execute($ins2);
    mysqli_stmt_close($ins2);
  } else {
    mysqli_stmt_bind_param($ins, 'sis', $name, $category_id, $slug);
    $ok = mysqli_stmt_execute($ins);
    mysqli_stmt_close($ins);
  }

  if (!empty($ok)) {
    message('popup-success', '<i class="ri-check-line"></i>', 'Brand added successfully');
    header('Location: brands.php?success=added');
    exit;
  } else {
    message('popup-error', '<i class="ri-close-line"></i>', 'Failed to add brand');
    header('Location: brands.php?error=failed');
    exit;
  }
}

// If no action, redirect to admin dashboard
header('Location: index.php');
exit;
