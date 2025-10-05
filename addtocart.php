<?php
session_start();
include('includes/functions.php');
include('includes/db_connect.php');

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Please Login First');
  header('Location: login.php');
  exit();
}

$userId = (int)$_SESSION['user_id'];

// HANDLE CUSTOM PC BUILD 
if (isset($_POST['part'])) {
  $parts_to_add = array_filter($_POST['part'], function ($id) {
    return (int)$id > 0;
  });

  if (empty($parts_to_add)) {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Please select at least one component.');
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
  }

  $added_count = 0;
  foreach ($parts_to_add as $productId) {
    $productId = (int)$productId;

    // Check if item already exists in cart
    $sql_check = "SELECT cart_item_id FROM cart WHERE user_id = $userId AND product_id = $productId";
    if (mysqli_num_rows(mysqli_query($conn, $sql_check)) > 0) {
      // If yes, update quantity
      $sql_update = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = $userId AND product_id = $productId";
      if (mysqli_query($conn, $sql_update)) $added_count++;
    } else {
      // If no, insert new item
      $sql_prod = "SELECT product_name, price FROM products WHERE product_id = $productId AND is_active = 1";
      $result_prod = mysqli_query($conn, $sql_prod);
      if (mysqli_num_rows($result_prod) > 0) {
        $product = mysqli_fetch_assoc($result_prod);
        $name = mysqli_real_escape_string($conn, $product['product_name']);
        $price = (float)$product['price'];
        $sql_insert = "INSERT INTO cart (user_id, product_id, quantity, product_name, price) VALUES ($userId, $productId, 1, '$name', $price)";
        if (mysqli_query($conn, $sql_insert)) $added_count++;
      }
    }
  }

  message('popup-success', '<i class="ri-check-line"></i>', "Your PC build ($added_count components) has been added to the cart!");
  header('Location: cart.php');
  exit();
} // HANDLE INDIVIDUAL PRODUCT
else if (isset($_POST['product_id'])) {
  $productId = (int)$_POST['product_id'];
  $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
  if ($quantity < 1) $quantity = 1;

  // Check if item already exists in cart
  $sql_check = "SELECT cart_item_id FROM cart WHERE user_id = $userId AND product_id = $productId";
  if (mysqli_num_rows(mysqli_query($conn, $sql_check)) > 0) {
    // If yes, update quantity
    $sql_update = "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = $userId AND product_id = $productId";
    mysqli_query($conn, $sql_update);
    message('popup-success', '<i class="ri-check-line"></i>', 'Cart updated successfully!');
  } else {
    // If no, get product info and insert new item
    $sql_prod = "SELECT product_name, price FROM products WHERE product_id = $productId AND is_active = 1";
    $result_prod = mysqli_query($conn, $sql_prod);
    if (mysqli_num_rows($result_prod) > 0) {
      $product = mysqli_fetch_assoc($result_prod);
      $name = mysqli_real_escape_string($conn, $product['product_name']);
      $price = (float)$product['price'];
      $sql_insert = "INSERT INTO cart (user_id, product_id, quantity, product_name, price) VALUES ($userId, $productId, $quantity, '$name', $price)";
      mysqli_query($conn, $sql_insert);
      message('popup-success', '<i class="ri-check-line"></i>', 'Product has been added to the cart!');
    } else {
      message('popup-error', '<i class="ri-close-line"></i>', 'Product not found or is out of stock.');
    }
  }

  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit();
} else {
  message('popup-error', '<i class="ri-close-line"></i>', 'No product selected.');
  header('Location: index.php');
  exit();
}
