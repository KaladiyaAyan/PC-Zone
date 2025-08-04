<?php
// Database connection function (MySQLi Object-Oriented)
function getConnection()
{
  $host = "localhost";
  $user = "root";
  $pass = "";
  $dbname = "pczone";

  $mysqli = new mysqli($host, $user, $pass, $dbname);

  if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
  }

  return $mysqli;
}

// User functions
function registerUser($username, $email, $password, $phone = null)
{
  $mysqli = getConnection();
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  $stmt = $mysqli->prepare("INSERT INTO users (username, email, password, phone) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $username, $email, $hashedPassword, $phone);
  return $stmt->execute();
}

function loginUser($email, $password)
{
  $mysqli = getConnection();
  $stmt = $mysqli->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['username'];
    $_SESSION['user_email'] = $user['email'];
    return true;
  }
  return false;
}

function isLoggedIn()
{
  return isset($_SESSION['user_id']);
}

function getUserById($id)
{
  $mysqli = getConnection();
  $stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  return $result->fetch_assoc();
}

// Product functions
function getAllProducts($limit = null, $offset = 0)
{
  $mysqli = getConnection();
  $sql = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          ORDER BY p.created_at DESC";

  if ($limit !== null) {
    $sql .= " LIMIT ?, ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", $offset, $limit);
  } else {
    $stmt = $mysqli->prepare($sql);
  }

  $stmt->execute();
  $result = $stmt->get_result();
  return $result->fetch_all(MYSQLI_ASSOC);
}

function getProductById($id)
{
  $mysqli = getConnection();
  $stmt = $mysqli->prepare("SELECT p.*, c.name as category_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            WHERE p.id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  return $result->fetch_assoc();
}

function getProductsByCategory($categoryId, $limit = null)
{
  $mysqli = getConnection();
  $sql = "SELECT * FROM products WHERE category_id = ? ORDER BY created_at DESC";
  if ($limit !== null) {
    $sql .= " LIMIT ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", $categoryId, $limit);
  } else {
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $categoryId);
  }

  $stmt->execute();
  $result = $stmt->get_result();
  return $result->fetch_all(MYSQLI_ASSOC);
}

function searchProducts($keyword)
{
  $mysqli = getConnection();
  $searchTerm = '%' . $keyword . '%';
  $stmt = $mysqli->prepare("SELECT p.*, c.name as category_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            WHERE p.name LIKE ? OR p.description LIKE ?");
  $stmt->bind_param("ss", $searchTerm, $searchTerm);
  $stmt->execute();
  $result = $stmt->get_result();
  return $result->fetch_all(MYSQLI_ASSOC);
}

// Category functions
// function getAllCategories()
// {
//   $mysqli = getConnection();
//   $result = $mysqli->query("SELECT * FROM categories ORDER BY name");
//   return $result->fetch_all(MYSQLI_ASSOC);
// }

// Header dropdown functions
function getAllCategories()
{
  global $conn;
  $sql = "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name ASC";
  $result = mysqli_query($conn, $sql);
  return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getSubcategories($parent_id)
{
  global $conn;
  $sql = "SELECT * FROM categories WHERE parent_id = $parent_id ORDER BY name ASC";
  $result = mysqli_query($conn, $sql);
  return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getCategoryImage($categoryName)
{
  $map = [
    'Processor' => 'Processor-Icon.webp',
    'Motherboard' => 'motherboard-icon.webp',
    'CPU Cooler' => 'liquid-cooler-icon.webp',
    'RAM' => 'RAM-icon.webp',
    'Graphics Card' => 'graphics-card-icon.webp',
    // 'SSD' => 'ssd-icon.webp',
    // 'HDD' => 'hdd-icon.webp',
    'Storage' => 'hdd-icon.webp',
    'Power Supply' => 'psu-icon.webp',
    'Cabinet' => 'cabinet-icon.webp',
    'Cooling System' => 'liquid-cooler-icon.webp',
    'Monitor' => 'monitor-icon.webp',
    'Keyboard' => 'keyboard-icon.webp',
    'Mouse' => 'mouse-icon.webp',
  ];
  return $map[$categoryName] ?? 'default-icon-300x300.webp';
}

function getCategoryById($id)
{
  $mysqli = getConnection();
  $stmt = $mysqli->prepare("SELECT * FROM categories WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  return $result->fetch_assoc();
}

// Cart functions
function addToCart($userId, $productId, $quantity = 1)
{
  $mysqli = getConnection();
  $stmt = $mysqli->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
  $stmt->bind_param("ii", $userId, $productId);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $stmt = $mysqli->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("iii", $quantity, $userId, $productId);
  } else {
    $stmt = $mysqli->prepare("INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iii", $userId, $productId, $quantity);
  }

  return $stmt->execute();
}

function getCartItems($userId)
{
  $mysqli = getConnection();
  $stmt = $mysqli->prepare("SELECT c.*, p.name, p.price, p.image1 as image 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id 
                            WHERE c.user_id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $result = $stmt->get_result();
  return $result->fetch_all(MYSQLI_ASSOC);
}

function getCartTotal($userId)
{
  $mysqli = getConnection();
  $stmt = $mysqli->prepare("SELECT SUM(c.quantity * p.price) as total 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id 
                            WHERE c.user_id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $result = $stmt->get_result()->fetch_assoc();
  return $result['total'] ?? 0;
}

function removeFromCart($userId, $productId)
{
  $mysqli = getConnection();
  $stmt = $mysqli->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
  $stmt->bind_param("ii", $userId, $productId);
  return $stmt->execute();
}

function clearCart($userId)
{
  $mysqli = getConnection();
  $stmt = $mysqli->prepare("DELETE FROM cart WHERE user_id = ?");
  $stmt->bind_param("i", $userId);
  return $stmt->execute();
}

// Order functions
function createOrder($userId, $totalAmount, $shippingAddress)
{
  $mysqli = getConnection();
  $mysqli->begin_transaction();

  try {
    $stmt = $mysqli->prepare("INSERT INTO orders (customer_id, customer_name, total_amount, status, order_date) 
                              VALUES (?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("isd", $userId, $shippingAddress, $totalAmount);
    $stmt->execute();
    $orderId = $mysqli->insert_id;

    $cartItems = getCartItems($userId);
    foreach ($cartItems as $item) {
      $stmt = $mysqli->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                                VALUES (?, ?, ?, ?)");
      $stmt->bind_param("iiid", $orderId, $item['product_id'], $item['quantity'], $item['price']);
      $stmt->execute();
    }

    clearCart($userId);
    $mysqli->commit();
    return $orderId;
  } catch (Exception $e) {
    $mysqli->rollback();
    return false;
  }
}

function getUserOrders($userId)
{
  $mysqli = getConnection();
  $stmt = $mysqli->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY order_date DESC");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $result = $stmt->get_result();
  return $result->fetch_all(MYSQLI_ASSOC);
}

// Utility functions
function formatPrice($price)
{
  return '₹' . number_format($price, 2);
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
