<?php
// PCZone - functions.php
// MySQLi procedural helper functions aligned to provided schema.
// Use require_once or include where needed.

session_start();

function e($v)
{
  return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

function clip($v, $len = 80)
{
  $s = (string)($v ?? '');
  return function_exists('mb_substr')
    ? (mb_strlen($s) > $len ? mb_substr($s, 0, $len) . '…' : $s)
    : (strlen($s) > $len ? substr($s, 0, $len) . '…' : $s);
}

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
  // ensure utf8
  mysqli_set_charset($conn, 'utf8mb4');
  return $conn;
}

/* ====================
   USER (admin) FUNCTIONS
   Table: users
==================== */

function registerUser($username, $email, $password, $phone = null, $full_name = null, $role = 'user')
{
  $conn = getConnection();
  $hashed = password_hash($password, PASSWORD_DEFAULT);
  $sql = "INSERT INTO users (username, full_name, email, password, phone, role) VALUES (?, ?, ?, ?, ?, ?)";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "ssssss", $username, $full_name, $email, $hashed, $phone, $role);
  $res = mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $res;
}

function loginUser($email, $password)
{
  $conn = getConnection();
  $sql = "SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "s", $email);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $user = mysqli_fetch_assoc($result);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_name'] = $user['username'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    return true;
  }
  return false;
}

function getUserById($id)
{
  $conn = getConnection();
  $sql = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $row;
}

/* ====================
   CUSTOMER FUNCTIONS
   Table: customers, addresses
==================== */

function registerCustomer($first_name, $last_name, $email, $password, $phone = null)
{
  $conn = getConnection();
  $hashed = password_hash($password, PASSWORD_DEFAULT);
  $sql = "INSERT INTO customers (first_name, last_name, email, password, phone) VALUES (?, ?, ?, ?, ?)";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "sssss", $first_name, $last_name, $email, $hashed, $phone);
  $res = mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $res;
}

function loginCustomer($email, $password)
{
  $conn = getConnection();
  $sql = "SELECT * FROM customers WHERE email = ? AND status = 'active' LIMIT 1";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "s", $email);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $customer = mysqli_fetch_assoc($result);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);

  if ($customer && password_verify($password, $customer['password'])) {
    $_SESSION['customer_id'] = $customer['customer_id'];
    $_SESSION['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name'];
    $_SESSION['customer_email'] = $customer['email'];
    return true;
  }
  return false;
}

function getCustomerById($id)
{
  $conn = getConnection();
  $sql = "SELECT * FROM customers WHERE customer_id = ? LIMIT 1";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $row;
}

function getAddressesByCustomer($customerId)
{
  $conn = getConnection();
  $sql = "SELECT * FROM addresses WHERE customer_id = ? ORDER BY is_default DESC, address_id DESC";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $customerId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $rows;
}

/* ====================
   CATEGORY & BRAND FUNCTIONS
   Tables: categories, brands
==================== */

function getAllCategories()
{
  $conn = getConnection();
  $sql = "SELECT * FROM categories ORDER BY level ASC, category_name ASC";
  $res = mysqli_query($conn, $sql);
  $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
  mysqli_close($conn);
  return $rows;
}

function getRootCategories()
{
  $conn = getConnection();
  $sql = "SELECT * FROM categories WHERE parent_id IS NULL OR parent_id = 0 ORDER BY category_name ASC";
  $res = mysqli_query($conn, $sql);
  $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
  mysqli_close($conn);
  return $rows;
}

function getSubcategories($parent_id)
{
  $conn = getConnection();
  $sql = "SELECT * FROM categories WHERE parent_id = ? ORDER BY category_name ASC";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $parent_id);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $rows;
}

function getCategoryById($id)
{
  $conn = getConnection();
  $sql = "SELECT * FROM categories WHERE category_id = ? LIMIT 1";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $row;
}

function getBrandsByCategory($categoryId)
{
  $conn = getConnection();
  $sql = "SELECT * FROM brands WHERE category_id = ? ORDER BY brand_name ASC";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $categoryId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $rows;
}

/* ====================
   PRODUCT FUNCTIONS
   Tables: products, product_images, product_specs, product_reviews
==================== */

function getProductRating($productId)
{
  $conn = getConnection();
  $sql = "SELECT COALESCE(AVG(rating),0) AS avg_rating, COUNT(*) AS review_count FROM product_reviews WHERE product_id = ?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $productId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return [
    'avg' => round((float)($row['avg_rating'] ?? 0), 2),
    'count' => (int)($row['review_count'] ?? 0)
  ];
}

function getProductImages($productId)
{
  $conn = getConnection();
  $sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC, product_image_id ASC";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $productId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $rows;
}

function getProductSpecs($productId)
{
  $conn = getConnection();
  $sql = "SELECT * FROM product_specs WHERE product_id = ? ORDER BY product_spec_id ASC";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $productId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $rows;
}

function getAllProducts($limit = null, $offset = 0, $onlyActive = true)
{
  $conn = getConnection();
  $where = $onlyActive ? "WHERE p.is_active = 1" : "";
  $sql = "SELECT 
                p.*,
                c.category_name,
                b.brand_name,
                (p.price - (p.price * (p.discount/100))) AS final_price,
                pi.image_path AS main_image,
                pr.avg_rating,
                pr.review_count
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN brands b ON p.brand_id = b.brand_id
            LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_main = 1
            LEFT JOIN (
                SELECT product_id, AVG(rating) AS avg_rating, COUNT(*) AS review_count
                FROM product_reviews
                GROUP BY product_id
            ) pr ON p.product_id = pr.product_id
            $where
            ORDER BY p.created_at DESC";

  if ($limit !== null) {
    $sql .= " LIMIT ?, ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $offset, $limit);
  } else {
    $stmt = mysqli_prepare($conn, $sql);
  }

  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $rows;
}

// function getFeaturedProducts($limit = 8)
// {
//   $conn = getConnection();
//   $sql = "SELECT p.*, pi.image_path AS main_image FROM products p
//             LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_main = 1
//             WHERE p.is_featured = 1 AND p.is_active = 1
//             ORDER BY p.created_at DESC LIMIT ?";
//   $stmt = mysqli_prepare($conn, $sql);
//   mysqli_stmt_bind_param($stmt, "i", $limit);
//   mysqli_stmt_execute($stmt);
//   $res = mysqli_stmt_get_result($stmt);
//   $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
//   mysqli_stmt_close($stmt);
//   mysqli_close($conn);
//   return $rows;
// }

function getFeaturedProducts($limit = 8, $random = false)
{
  $conn = getConnection();

  // Choose ordering based on $random flag
  $orderBy = $random ? "ORDER BY RAND()" : "ORDER BY p.created_at DESC";

  $sql = "SELECT p.*, pi.image_path AS main_image 
            FROM products p
            LEFT JOIN product_images pi 
              ON p.product_id = pi.product_id AND pi.is_main = 1
            WHERE p.is_featured = 1 AND p.is_active = 1
            $orderBy 
            LIMIT ?";

  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $limit);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);

  mysqli_stmt_close($stmt);
  mysqli_close($conn);

  return $rows;
}


function getProductById($id)
{
  $conn = getConnection();
  $sql = "SELECT p.*, c.category_name, b.brand_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN brands b ON p.brand_id = b.brand_id
            WHERE p.product_id = ? LIMIT 1";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $row;
}

function searchProducts($keyword)
{
  $conn = getConnection();
  $term = '%' . $keyword . '%';
  $sql = "SELECT p.*, c.category_name, pi.image_path AS main_image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_main = 1
            WHERE p.product_name LIKE ? OR p.description LIKE ?
            ORDER BY p.created_at DESC";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "ss", $term, $term);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $rows;
}

function getProductsByCategory($categoryId, $limit = null, $offset = 0)
{
  $conn = getConnection();
  $sql = "SELECT p.*, pi.image_path AS main_image FROM products p
            LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_main = 1
            WHERE p.category_id = ? AND p.is_active = 1
            ORDER BY p.created_at DESC";
  if ($limit !== null) {
    $sql .= " LIMIT ?, ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $categoryId, $offset, $limit);
  } else {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $categoryId);
  }
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $rows;
}

/* ====================
   CART FUNCTIONS
   Table: cart_items
==================== */

function addToCart($customerId, $productId, $quantity = 1)
{
  $conn = getConnection();

  // ensure product exists and stock check can be added here
  $sql = "SELECT cart_item_id, quantity FROM cart_items WHERE customer_id = ? AND product_id = ? LIMIT 1";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "ii", $customerId, $productId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $existing = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);

  if ($existing) {
    $sql = "UPDATE cart_items SET quantity = quantity + ? WHERE cart_item_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $quantity, $existing['cart_item_id']);
  } else {
    $sql = "INSERT INTO cart_items (customer_id, product_id, quantity) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $customerId, $productId, $quantity);
  }

  $res = mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $res;
}

function getCartItems($customerId)
{
  $conn = getConnection();
  $sql = "SELECT c.*, p.product_name, p.price, p.discount, pi.image_path AS main_image
            FROM cart_items c
            JOIN products p ON c.product_id = p.product_id
            LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_main = 1
            WHERE c.customer_id = ?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $customerId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $rows;
}

function getCartTotal($customerId)
{
  $conn = getConnection();
  $sql = "SELECT SUM(c.quantity * (p.price - (p.price * (p.discount/100)))) as total
            FROM cart_items c
            JOIN products p ON c.product_id = p.product_id
            WHERE c.customer_id = ?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $customerId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return (float)($row['total'] ?? 0);
}

function clearCart($customerId)
{
  $conn = getConnection();
  $sql = "DELETE FROM cart_items WHERE customer_id = ?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $customerId);
  $res = mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $res;
}

/* ====================
   ORDER FUNCTIONS
   Tables: orders, order_items, payments, shipments
==================== */

function createOrder($customerId, $billingAddressId, $shippingAddressId, $totalAmount, $order_notes = null)
{
  $conn = getConnection();
  mysqli_begin_transaction($conn);

  try {
    $sql = "INSERT INTO orders (customer_id, billing_address_id, shipping_address_id, total_amount, order_notes, order_status, order_date)
                VALUES (?, ?, ?, ?, ?, 'Pending', NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiids", $customerId, $billingAddressId, $shippingAddressId, $totalAmount, $order_notes);
    mysqli_stmt_execute($stmt);
    $orderId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    $cartItems = getCartItems($customerId);
    foreach ($cartItems as $item) {
      $unit_price = (float)$item['price'] - ((float)$item['price'] * ((float)$item['discount'] / 100));
      $total_price = $unit_price * (int)$item['quantity'];
      $sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, discount, total_price)
                    VALUES (?, ?, ?, ?, ?, ?)";
      $stmt = mysqli_prepare($conn, $sql);
      mysqli_stmt_bind_param($stmt, "iiiddd", $orderId, $item['product_id'], $item['quantity'], $unit_price, $item['discount'], $total_price);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);
    }

    clearCart($customerId);
    mysqli_commit($conn);
    mysqli_close($conn);
    return $orderId;
  } catch (Exception $e) {
    mysqli_rollback($conn);
    mysqli_close($conn);
    return false;
  }
}

function getUserOrders($customerId)
{
  $conn = getConnection();
  $sql = "SELECT * FROM orders WHERE customer_id = ? ORDER BY order_date DESC";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $customerId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $rows;
}

function getOrderById($orderId)
{
  $conn = getConnection();
  $sql = "SELECT o.*, c.first_name, c.last_name FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.customer_id
            WHERE o.order_id = ? LIMIT 1";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $orderId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $row;
}

function getOrderItems($orderId)
{
  $conn = getConnection();
  $sql = "SELECT oi.*, p.product_name, pi.image_path AS main_image
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.product_id
            LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_main = 1
            WHERE oi.order_id = ?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $orderId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $rows;
}

/* ====================
   REVIEWS & RATINGS
==================== */

function addProductReview($productId, $customerId, $rating, $comment = null)
{
  $conn = getConnection();
  $sql = "INSERT INTO product_reviews (product_id, customer_id, rating, comment) VALUES (?, ?, ?, ?)";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "iiis", $productId, $customerId, $rating, $comment);
  $res = mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $res;
}

function getProductReviews($productId, $limit = 20)
{
  $conn = getConnection();
  $sql = "SELECT pr.*, c.first_name, c.last_name FROM product_reviews pr
            LEFT JOIN customers c ON pr.customer_id = c.customer_id
            WHERE pr.product_id = ?
            ORDER BY pr.created_at DESC LIMIT ?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "ii", $productId, $limit);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  return $rows;
}

/* ====================
   UTILITIES
==================== */

function formatPrice($price)
{
  return '₹' . number_format((float)$price, 2);
}

function sanitizeInput($input)
{
  return htmlspecialchars(strip_tags(trim($input)));
}

function redirect($url)
{
  header("Location: $url");
  exit();
}
