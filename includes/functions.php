<?php

function message($type, $icon, $title)
{
  $_SESSION['message'] = [
    "type" => $type,
    "icon" =>  $icon,
    "title" => $title
  ];
}
function e($v)
{
  return htmlspecialchars($v ?? '');
}

function getConnection()
{
  $host = "localhost";
  $username = "root";
  $password = "";
  $dbname = "pczone";

  $conn = mysqli_connect($host, $username, $password, $dbname);

  if (!$conn) {
    die("Connection Failed : " . mysqli_connect_error());
  }
  return $conn;
}

/* ====================
   CATEGORY & BRAND FUNCTIONS
==================== */
function getRootCategories()
{
  $conn = getConnection();
  $sql = "SELECT * FROM categories ORDER BY category_name ASC";
  $result = mysqli_query($conn, $sql);
  $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
  mysqli_close($conn);
  return $categories;
}

function getFeaturedProducts($limit = 8, $random = false)
{
  $conn = getConnection();
  $orderBy = $random ? "ORDER BY RAND()" : "ORDER BY p.created_at DESC";

  $sql = "SELECT p.*, p.main_image 
          FROM products p
          WHERE p.is_featured = 1 AND p.is_active = 1
          $orderBy 
          LIMIT ?";

  $stmt = mysqli_prepare($conn, $sql);
  $limit = (int)$limit;
  mysqli_stmt_bind_param($stmt, "i", $limit);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $products;
}

// =================== CART FUNCTIONS ===================

function getCartItems($userId)
{
  $conn = getConnection();
  $sql = "SELECT ci.cart_item_id, ci.quantity, 
                 p.product_id, p.product_name, p.price, p.discount,
                 p.main_image
          FROM cart ci
          JOIN products p ON ci.product_id = p.product_id
          WHERE ci.user_id = ?";

  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $userId);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $items = mysqli_fetch_all($result, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $items;
}

function getCartTotal($userId)
{
  $conn = getConnection();
  $sql = "SELECT COALESCE(SUM(ci.quantity * (p.price - (p.price * (p.discount/100)))), 0) AS total
          FROM cart ci
          JOIN products p ON ci.product_id = p.product_id
          WHERE ci.user_id = ?";

  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $userId);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($result);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return (float)($row['total'] ?? 0);
}

// =================== UTILITY FUNCTIONS ===================

function formatPrice($amount)
{
  return '₹' . number_format($amount, 2);
}
