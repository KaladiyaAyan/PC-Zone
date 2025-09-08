<?php
// PCZone - functions.php (MySQLi Procedural Version - Updated for new schema)
// Helper functions aligned to provided schema.
function e($v)
{
  return htmlspecialchars($v ?? '');
}

// function clip($v, $len = 80)
// {
//   $s = (string)($v ?? '');
//   return function_exists('mb_substr')
//     ? (mb_strlen($s) > $len ? mb_substr($s, 0, $len) . '…' : $s)
//     : (strlen($s) > $len ? substr($s, 0, $len) . '…' : $s);
// }

function getConnection()
{
  $host = "localhost";
  $user = "root";
  $pass = "";
  $dbname = "pczone";

  $conn = mysqli_connect($host, $user, $pass, $dbname);

  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  return $conn;
}

/* ====================
   USER FUNCTIONS
   Table: users
==================== */

// function registerUser($first_name, $last_name, $email, $password, $phone = null, $role = 'user')
// {
//   $conn = getConnection();
//   $hashed = password_hash($password, PASSWORD_DEFAULT);
//   $full_name = $first_name . ' ' . $last_name;

//   $sql = "INSERT INTO users (first_name, last_name, full_name, email, password, phone, role) 
//           VALUES (?, ?, ?, ?, ?, ?, ?)";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "sssssss", $first_name, $last_name, $full_name, $email, $hashed, $phone, $role);
//   $result = mysqli_stmt_execute($stmt);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $result;
// }

// function loginUser($email, $password)
// {
//   $conn = getConnection();
//   $sql = "SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "s", $email);
//   mysqli_stmt_execute($stmt);
//   $result = mysqli_stmt_get_result($stmt);
//   $user = mysqli_fetch_assoc($result);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);

//   if ($user && password_verify($password, $user['password'])) {
//     $_SESSION['user_id'] = $user['user_id'];
//     $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
//     $_SESSION['user_email'] = $user['email'];
//     $_SESSION['role'] = $user['role'];
//     return true;
//   }
//   return false;
// }

// function getUserById($id)
// {
//   $conn = getConnection();
//   $sql = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "i", $id);
//   mysqli_stmt_execute($stmt);
//   $result = mysqli_stmt_get_result($stmt);
//   $user = mysqli_fetch_assoc($result);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $user;
// }

/* ====================
   ADDRESS FUNCTIONS
   Table: user_address
==================== */

// function getAddressesByUser($userId)
// {
//   $conn = getConnection();
//   $sql = "SELECT * FROM user_address WHERE user_id = ? ORDER BY is_default DESC, address_id DESC";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "i", $userId);
//   mysqli_stmt_execute($stmt);
//   $result = mysqli_stmt_get_result($stmt);
//   $addresses = mysqli_fetch_all($result, MYSQLI_ASSOC);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $addresses;
// }

// function addAddress($userId, $fullName, $phone, $addressLine1, $addressLine2, $city, $state, $zip, $country, $isDefault = false)
// {
//   $conn = getConnection();

//   // If setting as default, first remove default status from any existing addresses
//   if ($isDefault) {
//     $updateSql = "UPDATE user_address SET is_default = 0 WHERE user_id = ?";
//     $updateStmt = mysqli_prepare($conn, $updateSql);
//     mysqli_stmt_bind_param($updateStmt, "i", $userId);
//     mysqli_stmt_execute($updateStmt);
//     mysqli_stmt_close($updateStmt);
//   }

//   $sql = "INSERT INTO user_address (user_id, full_name, phone, address_line1, address_line2, city, state, zip, country, is_default) 
//           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
//   $stmt = mysqli_prepare($conn, $sql);
//   $isDefaultInt = $isDefault ? 1 : 0;
//   mysqli_stmt_bind_param($stmt, "issssssssi", $userId, $fullName, $phone, $addressLine1, $addressLine2, $city, $state, $zip, $country, $isDefaultInt);
//   $result = mysqli_stmt_execute($stmt);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $result;
// }

/* ====================
   CATEGORY & BRAND FUNCTIONS
==================== */

function getAllCategories()
{
  $conn = getConnection();
  $sql = "SELECT * FROM categories ORDER BY level ASC, category_name ASC";
  $result = mysqli_query($conn, $sql);
  $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
  mysqli_close($conn);
  return $categories;
}

function getRootCategories()
{
  $conn = getConnection();
  $sql = "SELECT * FROM categories WHERE parent_id IS NULL OR parent_id = 0 ORDER BY category_name ASC";
  $result = mysqli_query($conn, $sql);
  $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
  mysqli_close($conn);
  return $categories;
}
function getAllSubCategories()
{
  $conn = getConnection();
  $sql = "SELECT * FROM categories WHERE parent_id != 0 ORDER BY category_name ASC";
  $result = mysqli_query($conn, $sql);
  $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
  mysqli_close($conn);
  return $categories;
}

// function getSubcategories($parent_id)
// {
//   $conn = getConnection();
//   $sql = "SELECT * FROM categories WHERE parent_id = ? ORDER BY category_name ASC";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "i", $parent_id);
//   mysqli_stmt_execute($stmt);
//   $result = mysqli_stmt_get_result($stmt);
//   $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $categories;
// }

// function getCategoryById($id)
// {
//   $conn = getConnection();
//   $sql = "SELECT * FROM categories WHERE category_id = ? LIMIT 1";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "i", $id);
//   mysqli_stmt_execute($stmt);
//   $result = mysqli_stmt_get_result($stmt);
//   $category = mysqli_fetch_assoc($result);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $category;
// }

// function getBrandsByCategory($categoryId)
// {
//   $conn = getConnection();
//   $sql = "SELECT * FROM brands WHERE category_id = ? ORDER BY brand_name ASC";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "i", $categoryId);
//   mysqli_stmt_execute($stmt);
//   $result = mysqli_stmt_get_result($stmt);
//   $brands = mysqli_fetch_all($result, MYSQLI_ASSOC);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $brands;
// }

/* ====================
   PRODUCT FUNCTIONS
==================== */

// function getProductRating($productId)
// {
//   $conn = getConnection();
//   $sql = "SELECT COALESCE(AVG(rating),0) AS avg_rating, COUNT(*) AS review_count FROM product_reviews WHERE product_id = ?";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "i", $productId);
//   mysqli_stmt_execute($stmt);
//   $result = mysqli_stmt_get_result($stmt);
//   $row = mysqli_fetch_assoc($result);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);

//   return [
//     'avg' => round((float)($row['avg_rating'] ?? 0), 2),
//     'count' => (int)($row['review_count'] ?? 0)
//   ];
// }

// function getProductSpecs($productId)
// {
//   $conn = getConnection();
//   $sql = "SELECT * FROM product_specs WHERE product_id = ? ORDER BY spec_group, display_order ASC";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "i", $productId);
//   mysqli_stmt_execute($stmt);
//   $result = mysqli_stmt_get_result($stmt);
//   $specs = mysqli_fetch_all($result, MYSQLI_ASSOC);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $specs;
// }

// function getAllProducts($limit = null, $offset = 0, $onlyActive = true)
// {
//   $conn = getConnection();
//   $where = $onlyActive ? "WHERE p.is_active = 1" : "";

//   $sql = "SELECT 
//               p.*, c.category_name, b.brand_name,
//               (p.price - (p.price * (p.discount/100))) AS final_price,
//               p.main_image,
//               pr.avg_rating, pr.review_count
//           FROM products p
//           LEFT JOIN categories c ON p.category_id = c.category_id
//           LEFT JOIN brands b ON p.brand_id = b.brand_id
//           LEFT JOIN (
//               SELECT product_id, AVG(rating) AS avg_rating, COUNT(*) AS review_count
//               FROM product_reviews GROUP BY product_id
//           ) pr ON p.product_id = pr.product_id
//           $where
//           ORDER BY p.created_at DESC";

//   if ($limit !== null) {
//     $sql .= " LIMIT ?, ?";
//     $stmt = mysqli_prepare($conn, $sql);
//     $offset = (int)$offset;
//     $limit = (int)$limit;
//     mysqli_stmt_bind_param($stmt, "ii", $offset, $limit);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
//     mysqli_stmt_close($stmt);
//   } else {
//     $result = mysqli_query($conn, $sql);
//     $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
//   }

//   mysqli_close($conn);
//   return $products;
// }

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

// function getProductById($id)
// {
//   $conn = getConnection();
//   $sql = "SELECT p.*, c.category_name, b.brand_name
//           FROM products p
//           LEFT JOIN categories c ON p.category_id = c.category_id
//           LEFT JOIN brands b ON p.brand_id = b.brand_id
//           WHERE p.product_id = ?
//           LIMIT 1";

//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "i", $id);
//   mysqli_stmt_execute($stmt);
//   $result = mysqli_stmt_get_result($stmt);
//   $product = mysqli_fetch_assoc($result);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $product;
// }

// function searchProducts($keyword)
// {
//   $conn = getConnection();
//   $term = '%' . $keyword . '%';
//   $sql = "SELECT p.*, c.category_name, p.main_image
//           FROM products p
//           LEFT JOIN categories c ON p.category_id = c.category_id
//           WHERE p.product_name LIKE ? OR p.description LIKE ?
//           ORDER BY p.created_at DESC";

//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "ss", $term, $term);
//   mysqli_stmt_execute($stmt);
//   $result = mysqli_stmt_get_result($stmt);
//   $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $products;
// }

// function getProductsByCategory($categoryId, $limit = null, $offset = 0)
// {
//   $conn = getConnection();

//   if ($limit !== null) {
//     $sql = "SELECT p.*, p.main_image
//             FROM products p
//             WHERE p.category_id = ? AND p.is_active = 1
//             ORDER BY p.created_at DESC
//             LIMIT ?, ?";

//     $stmt = mysqli_prepare($conn, $sql);
//     $offset = (int)$offset;
//     $limit = (int)$limit;
//     mysqli_stmt_bind_param($stmt, "iii", $categoryId, $offset, $limit);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
//     mysqli_stmt_close($stmt);
//   } else {
//     $sql = "SELECT p.*, p.main_image
//             FROM products p
//             WHERE p.category_id = ? AND p.is_active = 1
//             ORDER BY p.created_at DESC";

//     $stmt = mysqli_prepare($conn, $sql);
//     mysqli_stmt_bind_param($stmt, "i", $categoryId);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
//     mysqli_stmt_close($stmt);
//   }

//   mysqli_close($conn);
//   return $products;
// }

// =================== CART FUNCTIONS ===================

function getCartItems($userId)
{
  $conn = getConnection();
  $sql = "SELECT ci.cart_item_id, ci.quantity, 
                 p.product_id, p.product_name, p.price, p.discount,
                 p.main_image
          FROM cart_items ci
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
          FROM cart_items ci
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

// function addToCart($userId, $productId, $qty = 1)
// {
//   $conn = getConnection();

//   // Check if item already exists
//   $sql = "SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "ii", $userId, $productId);
//   mysqli_stmt_execute($stmt);
//   $result = mysqli_stmt_get_result($stmt);
//   $row = mysqli_fetch_assoc($result);
//   mysqli_stmt_close($stmt);

//   if ($row) {
//     $newQty = $row['quantity'] + $qty;
//     $sql = "UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?";
//     $stmt = mysqli_prepare($conn, $sql);
//     mysqli_stmt_bind_param($stmt, "iii", $newQty, $userId, $productId);
//     $result = mysqli_stmt_execute($stmt);
//     mysqli_stmt_close($stmt);
//     mysqli_close($conn);
//     return $result;
//   } else {
//     $sql = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)";
//     $stmt = mysqli_prepare($conn, $sql);
//     mysqli_stmt_bind_param($stmt, "iii", $userId, $productId, $qty);
//     $result = mysqli_stmt_execute($stmt);
//     mysqli_stmt_close($stmt);
//     mysqli_close($conn);
//     return $result;
//   }
// }

// function updateCartItem($userId, $productId, $qty)
// {
//   $conn = getConnection();
//   $sql = "UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "iii", $qty, $userId, $productId);
//   $result = mysqli_stmt_execute($stmt);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $result;
// }

// function removeCartItem($userId, $productId)
// {
//   $conn = getConnection();
//   $sql = "DELETE FROM cart_items WHERE user_id = ? AND product_id = ?";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "ii", $userId, $productId);
//   $result = mysqli_stmt_execute($stmt);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $result;
// }

// function clearCart($userId)
// {
//   $conn = getConnection();
//   $sql = "DELETE FROM cart_items WHERE user_id = ?";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "i", $userId);
//   $result = mysqli_stmt_execute($stmt);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $result;
// }

// =================== ORDER FUNCTIONS ===================

// function createOrder($userId, $billingAddressId, $shippingAddressId, $totalAmount, $status = 'Pending')
// {
//   $conn = getConnection();
//   $sql = "INSERT INTO orders (user_id, billing_address_id, shipping_address_id, total_amount, order_status) 
//           VALUES (?, ?, ?, ?, ?)";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "iiids", $userId, $billingAddressId, $shippingAddressId, $totalAmount, $status);
//   $result = mysqli_stmt_execute($stmt);
//   $orderId = mysqli_insert_id($conn);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $orderId;
// }

// function addOrderItem($orderId, $productId, $qty, $price, $discount = 0)
// {
//   $conn = getConnection();
//   $totalPrice = $qty * ($price - ($price * ($discount / 100)));
//   $sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, discount, total_price) 
//           VALUES (?, ?, ?, ?, ?, ?)";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "iiiddd", $orderId, $productId, $qty, $price, $discount, $totalPrice);
//   $result = mysqli_stmt_execute($stmt);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $result;
// }

// function getOrdersByUser($userId)
// {
//   $conn = getConnection();
//   $sql = "SELECT o.*, 
//                  ba.full_name as billing_name, ba.address_line1 as billing_address, ba.city as billing_city, ba.state as billing_state, ba.zip as billing_zip,
//                  sa.full_name as shipping_name, sa.address_line1 as shipping_address, sa.city as shipping_city, sa.state as shipping_state, sa.zip as shipping_zip
//           FROM orders o
//           JOIN user_address ba ON o.billing_address_id = ba.address_id
//           JOIN user_address sa ON o.shipping_address_id = sa.address_id
//           WHERE o.user_id = ? 
//           ORDER BY o.created_at DESC";

//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "i", $userId);
//   mysqli_stmt_execute($stmt);
//   $result = mysqli_stmt_get_result($stmt);
//   $orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $orders;
// }

// function getOrderItems($orderId)
// {
//   $conn = getConnection();
//   $sql = "SELECT oi.*, p.product_name, p.main_image
//           FROM order_items oi
//           JOIN products p ON oi.product_id = p.product_id
//           WHERE oi.order_id = ?";

//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "i", $orderId);
//   mysqli_stmt_execute($stmt);
//   $result = mysqli_stmt_get_result($stmt);
//   $items = mysqli_fetch_all($result, MYSQLI_ASSOC);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $items;
// }

// =================== REVIEWS ===================

// function addReview($userId, $productId, $rating, $comment)
// {
//   $conn = getConnection();
//   $sql = "INSERT INTO product_reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "iiis", $userId, $productId, $rating, $comment);
//   $result = mysqli_stmt_execute($stmt);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $result;
// }

// function getReviewsByProduct($productId)
// {
//   $conn = getConnection();
//   $sql = "SELECT r.*, u.first_name, u.last_name 
//           FROM product_reviews r
//           JOIN users u ON r.user_id = u.user_id
//           WHERE r.product_id = ?
//           ORDER BY r.created_at DESC";

//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "i", $productId);
//   mysqli_stmt_execute($stmt);
//   $result = mysqli_stmt_get_result($stmt);
//   $reviews = mysqli_fetch_all($result, MYSQLI_ASSOC);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $reviews;
// }

// =================== UTILITY FUNCTIONS ===================

function formatPrice($amount)
{
  return '₹' . number_format($amount, 2);
}

function isLoggedIn()
{
  return isset($_SESSION['user_id']);
}

function redirect($url)
{
  header("Location: $url");
  exit();
}
