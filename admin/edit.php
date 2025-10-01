<?php
session_start();
require('../includes/db_connect.php');
require('../includes/functions.php');

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ../login.php');
  exit;
}

if (isset($_POST['edit-category'])) {

  $category_id = $_POST['id'];
  $category_name = trim($_POST['name']);
  $parent_id = $_POST['parent_id'] !== '' ? intval($_POST['parent_id']) : NULL;

  if ($category_id && $category_name) {
    $result = mysqli_query($conn, "SELECT slug FROM categories WHERE category_id = $category_id");
    $row = mysqli_fetch_assoc($result);
    $slug = $row['slug'];

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

    if (mysqli_stmt_execute($stmt)) {
      header("Location: categories.php?success=updated");
      exit;
    } else {
      header("Location:categories.php?error=update_failed");
      exit;
    }
  } else {
    header("Location: categories.php?error=missing_fields");
    exit;
  }
} else if (isset($_POST['edit-brand'])) {
  $brand_id = $_POST['id'];
  $brand_name = trim($_POST['name']);
  $category_id = $_POST['category_id'];

  if ($brand_id && $brand_name && $category_id) {
    // $slug = slugify($brand_name);
    $result = mysqli_query($conn, "SELECT slug FROM brands WHERE brand_id = $brand_id");
    $row = mysqli_fetch_assoc($result);
    $slug = $row['slug'];

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
      exit;
    } else {
      header("Location: brands.php?error=update_failed");
      exit;
    }
  } else {
    header("Location: brands.php?error=missing_fields");
    exit;
  }
}

if (isset($_POST['edit-product'])) {
  $product_id = intval($_POST['product_id']);

  // form fields
  $name = mysqli_real_escape_string($conn, $_POST['name']);
  $sku = mysqli_real_escape_string($conn, $_POST['sku']);
  $description = mysqli_real_escape_string($conn, $_POST['description']);
  $price = floatval($_POST['price']);
  $discount = floatval($_POST['discount']);
  $stock = intval($_POST['stock']);
  $weight = floatval($_POST['weight']);
  $category_id = intval($_POST['category']);
  $brand_id = intval($_POST['brand']);
  $is_featured = isset($_POST['is_featured']) ? 1 : 0;
  $is_active = isset($_POST['is_active']) ? 1 : 0;

  // get existing product images
  $res = mysqli_query($conn, "SELECT main_image, image_1, image_2, image_3 FROM products WHERE product_id = $product_id");
  $old = mysqli_fetch_assoc($res);

  // ensure upload dir
  $uploadDir = __DIR__ . '/../uploads/';
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

  // map form inputs to DB columns
  $map = [
    'image1' => 'main_image',
    'image2' => 'image_1',
    'image3' => 'image_2',
    'image4' => 'image_3',
  ];

  $extra_parts = [];
  foreach ($map as $inputName => $colName) {
    if (!empty($_FILES[$inputName]['name']) && $_FILES[$inputName]['error'] !== UPLOAD_ERR_NO_FILE) {
      $origName = $_FILES[$inputName]['name'];
      $ext = pathinfo($origName, PATHINFO_EXTENSION);
      $newName = time() . '_' . $inputName . '.' . $ext;
      if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $uploadDir . $newName)) {
        if (!empty($old[$colName]) && file_exists($uploadDir . $old[$colName])) @unlink($uploadDir . $old[$colName]);
        $extra_parts[] = "$colName = '" . mysqli_real_escape_string($conn, $newName) . "'";
      } else {
        message('popup-error', '<i class="ri-close-line"></i>', 'Failed to upload ' . $inputName);
        header('Location: edit-product.php?id=' . $product_id);
        exit;
      }
    }
  }

  $extra_sql = !empty($extra_parts) ? ", " . implode(', ', $extra_parts) : '';

  // Update products table
  $update_query = "UPDATE products SET 
        product_name = '$name',
        sku = '$sku',
        description = '$description',
        price = $price,
        discount = $discount,
        stock = $stock,
        weight = $weight,
        category_id = $category_id,
        brand_id = $brand_id,
        is_featured = $is_featured,
        is_active = $is_active
        $extra_sql
        WHERE product_id = $product_id";

  if (mysqli_query($conn, $update_query)) {
    // Specifications handling
    // Remove old specs
    mysqli_query($conn, "DELETE FROM product_specs WHERE product_id = $product_id");

    if (!empty($_POST['spec_group_name']) && is_array($_POST['spec_group_name'])) {
      foreach ($_POST['spec_group_name'] as $gName) {
        $gName = trim($gName);
        if ($gName === '') continue;
        // make safe key same as form
        $safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $gName);

        $names = $_POST["spec_name_{$safe}"] ?? [];
        $values = $_POST["spec_value_{$safe}"] ?? [];
        $orders = $_POST["spec_order_{$safe}"] ?? [];

        $count = max(count($names), count($values));
        for ($i = 0; $i < $count; $i++) {
          $sname = trim($names[$i] ?? '');
          $svalue = trim($values[$i] ?? '');
          $sorder = intval($orders[$i] ?? 0);
          if ($sname === '' && $svalue === '') continue;
          $gn = mysqli_real_escape_string($conn, $gName);
          $sn = mysqli_real_escape_string($conn, $sname);
          $sv = mysqli_real_escape_string($conn, $svalue);
          $so = intval($sorder);
          $ins = "INSERT INTO product_specs (product_id, spec_group, spec_name, spec_value, display_order) VALUES ($product_id, '$gn', '$sn', '$sv', $so)";
          mysqli_query($conn, $ins);
        }
      }
    }

    message('popup-success', '<i class="ri-check-line"></i>', 'Product updated successfully');
    header('Location: product.php');
    exit;
  } else {
    message('popup-error', '<i class="ri-close-line"></i>', 'Failed to update product: ' . mysqli_error($conn));
    header('Location: edit-product.php?id=' . $product_id);
    exit;
  }
}
// If no action, redirect to admin dashboard
header('Location: index.php');
exit;
