<?php
session_start();

include('../includes/db_connect.php');
include('../functions/message.php');

if (isset($_POST['edit-category'])) {
  $cate_id = $_POST['category_id'];

  $select_cate = "SELECT * FROM `category` WHERE id='$cate_id'";
  $select_cate_run = mysqli_query($conn, $select_cate);
  $category = mysqli_fetch_array($select_cate_run);

  $new_cate_name = $_POST['category_name'];
  $new_cate_slug = $_POST['category_slug'];
  $new_cate_image = $_FILES['category_image']['name'];
  $new_cate_status = isset($_POST['category_status']) ? 1 : 0;


  if ($new_cate_image == '') {
    $update_cate = "UPDATE `category` SET `name`='$new_cate_name', `slug`='$new_cate_slug', `status`='$new_cate_status' WHERE `id`='$cate_id'";
  } else {
    $image_ext = pathinfo($new_cate_image, PATHINFO_EXTENSION);
    $new_cate_image = time() . "." . $image_ext;
    move_uploaded_file($_FILES['category_image']['tmp_name'], '../uploads/' . $new_cate_image);

    $path = "../uploads/";
    unlink($path . $category['image']);

    $update_cate = "UPDATE `category` SET `name`='$new_cate_name', `slug`='$new_cate_slug', `image`='$new_cate_image', `status`='$new_cate_status' WHERE `id`='$cate_id'";
  }

  $update_cate_run = mysqli_query($conn, $update_cate);
  if ($update_cate_run) {
    message('popup-success', '<i class="ri-check-line"></i>', 'Category updated successfully');
    header('location: category.php');
  } else {
    message('popup-error', '<i class="ri-close-line"></i>', 'Failed to update category');
    header('location: edit-category.php?id=' . $cate_id);
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
    // --- Specifications handling (simple) ---
    // Remove old specs
    mysqli_query($conn, "DELETE FROM product_specs WHERE product_id = $product_id");

    // Expect spec_group_name[] and for each group the inputs named like:
    // spec_name_<safe>[], spec_value_<safe>[], spec_order_<safe>[]
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
          $ins = "INSERT INTO product_specs (product_id, group_name, spec_name, spec_value, sort_order) VALUES ($product_id, '$gn', '$sn', '$sv', $so)";
          mysqli_query($conn, $ins);
        }
      }
    }
    // ----------------------------

    message('popup-success', '<i class="ri-check-line"></i>', 'Product updated successfully');
    header('Location: product.php');
    exit;
  } else {
    message('popup-error', '<i class="ri-close-line"></i>', 'Failed to update product: ' . mysqli_error($conn));
    header('Location: edit-product.php?id=' . $product_id);
    exit;
  }
} else if (isset($_POST['edit-user'])) {
  $user_id = $_POST['user_id'];

  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);
  $status = mysqli_real_escape_string($conn, isset($_POST['status']) ? 1 : 0);

  if ($password == "") {
    $update_user = "UPDATE `users` SET `username`='$username', `status`='$status' WHERE `user_id`='$user_id'";
  } else {
    $password = password_hash($password, PASSWORD_BCRYPT);
    $update_user = "UPDATE `users` SET `username`='$username', `password`='$password', `status`='$status' WHERE `user_id`='$user_id'";
  }

  $update_cate_run = mysqli_query($conn, $update_user);

  if ($update_cate_run) {
    message('popup-success', '<i class="ri-check-line"></i>', 'User updated successfully');
    header('location: user.php');
  } else {
    message('popup-error', '<i class="ri-close-line"></i>', 'Failed to update user');
    header('location: edit-user.php?id=' . $user_id);
  }
} else if (isset($_POST['update-order'])) {
  $status = $_POST['status'];
  $user_id = $_POST['user_id'];
  $product_id = $_POST['product_id'];

  $update_status = "UPDATE `order` SET `status`='$status' WHERE user_id ='$user_id' AND product_id ='$product_id'";
  $order_update = mysqli_query($conn, $update_status);

  if ($order_update) {
    message('popup-success', '<i class="ri-check-line"></i>', 'Status updated successfully');
    header('location: orders.php');
  } else {
    message('popup-error', '<i class="ri-close-line"></i>', 'Failed to update Status');
    header('location: orders.php');
  }
}
