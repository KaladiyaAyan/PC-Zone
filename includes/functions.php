<?php
// Database connection function
function getConnection()
{
  global $pdo;
  return $pdo;
}

// User functions
function registerUser($name, $email, $password, $phone = null)
{
  $pdo = getConnection();
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, created_at) VALUES (?, ?, ?, ?, NOW())");
  return $stmt->execute([$name, $email, $hashedPassword, $phone]);
}

function loginUser($email, $password)
{
  $pdo = getConnection();
  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
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
  $pdo = getConnection();
  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$id]);
  return $stmt->fetch();
}

// Product functions
function getAllProducts($limit = null, $offset = 0)
{
  $pdo = getConnection();
  $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active' ORDER BY p.created_at DESC";

  if ($limit) {
    $sql .= " LIMIT $limit OFFSET $offset";
  }

  $stmt = $pdo->query($sql);
  return $stmt->fetchAll();
}

function getProductById($id)
{
  $pdo = getConnection();
  $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.id = ? AND p.status = 'active'");
  $stmt->execute([$id]);
  return $stmt->fetch();
}

function getProductsByCategory($categoryId, $limit = null)
{
  $pdo = getConnection();
  $sql = "SELECT * FROM products WHERE category_id = ? AND status = 'active' ORDER BY created_at DESC";
  if ($limit) $sql .= " LIMIT $limit";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$categoryId]);
  return $stmt->fetchAll();
}

function searchProducts($keyword)
{
  $pdo = getConnection();
  $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE (p.name LIKE ? OR p.description LIKE ?) AND p.status = 'active'");
  $searchTerm = "%$keyword%";
  $stmt->execute([$searchTerm, $searchTerm]);
  return $stmt->fetchAll();
}

// Category functions
function getAllCategories()
{
  $pdo = getConnection();
  $stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
  return $stmt->fetchAll();
}

function getCategoryById($id)
{
  $pdo = getConnection();
  $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? AND status = 'active'");
  $stmt->execute([$id]);
  return $stmt->fetch();
}

// Cart functions
function addToCart($userId, $productId, $quantity = 1)
{
  $pdo = getConnection();

  // Check if item already exists in cart
  $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
  $stmt->execute([$userId, $productId]);
  $existing = $stmt->fetch();

  if ($existing) {
    // Update quantity
    $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
    return $stmt->execute([$quantity, $userId, $productId]);
  } else {
    // Insert new item
    $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
    return $stmt->execute([$userId, $productId, $quantity]);
  }
}

function getCartItems($userId)
{
  $pdo = getConnection();
  $stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.image FROM cart c 
                          JOIN products p ON c.product_id = p.id 
                          WHERE c.user_id = ?");
  $stmt->execute([$userId]);
  return $stmt->fetchAll();
}

function getCartTotal($userId)
{
  $pdo = getConnection();
  $stmt = $pdo->prepare("SELECT SUM(c.quantity * p.price) as total FROM cart c 
                          JOIN products p ON c.product_id = p.id 
                          WHERE c.user_id = ?");
  $stmt->execute([$userId]);
  $result = $stmt->fetch();
  return $result['total'] ?? 0;
}

function removeFromCart($userId, $productId)
{
  $pdo = getConnection();
  $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
  return $stmt->execute([$userId, $productId]);
}

function clearCart($userId)
{
  $pdo = getConnection();
  $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
  return $stmt->execute([$userId]);
}

// Order functions
function createOrder($userId, $totalAmount, $shippingAddress)
{
  $pdo = getConnection();

  try {
    $pdo->beginTransaction();

    // Create order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, status, created_at) 
                              VALUES (?, ?, ?, 'pending', NOW())");
    $stmt->execute([$userId, $totalAmount, $shippingAddress]);
    $orderId = $pdo->lastInsertId();

    // Get cart items
    $cartItems = getCartItems($userId);

    // Create order items
    foreach ($cartItems as $item) {
      $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                  VALUES (?, ?, ?, ?)");
      $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
    }

    // Clear cart
    clearCart($userId);

    $pdo->commit();
    return $orderId;
  } catch (Exception $e) {
    $pdo->rollback();
    return false;
  }
}

function getUserOrders($userId)
{
  $pdo = getConnection();
  $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
  $stmt->execute([$userId]);
  return $stmt->fetchAll();
}

// Utility functions
function formatPrice($price)
{
  return '$' . number_format($price, 2);
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
