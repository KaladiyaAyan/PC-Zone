<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db_connect.php';

// read JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
  echo json_encode(['success' => false, 'message' => 'Invalid request']);
  exit;
}
$product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
$quantity = isset($data['quantity']) ? max(1, (int)$data['quantity']) : 1;
if ($product_id <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid product']);
  exit;
}

// if user logged in -> insert/update cart_items
if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
  $user_id = (int)$_SESSION['user_id'];

  // check existing row
  $stmt = mysqli_prepare($conn, "SELECT cart_item_id, quantity FROM cart_items WHERE customer_id = ? AND product_id = ?");
  mysqli_stmt_bind_param($stmt, 'ii', $user_id, $product_id);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $existing = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);

  if ($existing) {
    $newQ = $existing['quantity'] + $quantity;
    $upd = mysqli_prepare($conn, "UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
    mysqli_stmt_bind_param($upd, 'ii', $newQ, $existing['cart_item_id']);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);
  } else {
    $ins = mysqli_prepare($conn, "INSERT INTO cart_items (customer_id, product_id, quantity) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($ins, 'iii', $user_id, $product_id, $quantity);
    mysqli_stmt_execute($ins);
    mysqli_stmt_close($ins);
  }

  // get cart count
  $cRes = mysqli_query($conn, "SELECT SUM(quantity) as cnt FROM cart_items WHERE customer_id = $user_id");
  $cRow = mysqli_fetch_assoc($cRes);
  $count = (int)($cRow['cnt'] ?? 0);

  echo json_encode(['success' => true, 'cart_count' => $count]);
  exit;
}

// guest -> session cart
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) $_SESSION['cart'] = [];
if (isset($_SESSION['cart'][$product_id])) {
  $_SESSION['cart'][$product_id] += $quantity;
} else {
  $_SESSION['cart'][$product_id] = $quantity;
}
$cartCount = array_sum($_SESSION['cart']);
echo json_encode(['success' => true, 'cart_count' => $cartCount]);
exit;
