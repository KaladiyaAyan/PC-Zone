<?php
session_start();
require('./includes/db_connect.php');
require('./includes/functions.php');

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Please Login First');
  header('Location: login.php');
  exit();
}

$conn = getConnection();
$userId = (int)$_SESSION['user_id'];

// 2. Fetch cart items to ensure the cart is not empty
$sql_cart = "SELECT c.*, p.slug, p.main_image FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = $userId";
$result_cart = mysqli_query($conn, $sql_cart);
$cart_items = mysqli_fetch_all($result_cart, MYSQLI_ASSOC);

// If cart is empty, redirect them back to the cart page
if (empty($cart_items)) {
  message('popup-warning', '<i class="ri-alert-line"></i>', 'Your cart is empty. Nothing to check out!');
  header('Location: cart.php');
  exit();
}

// 3. Handle the order placement when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Sanitize address details from the form
  $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
  $phone = mysqli_real_escape_string($conn, $_POST['phone']);
  $address_line1 = mysqli_real_escape_string($conn, $_POST['address_line1']);
  $city = mysqli_real_escape_string($conn, $_POST['city']);
  $state = mysqli_real_escape_string($conn, $_POST['state']);
  $zip = mysqli_real_escape_string($conn, $_POST['zip']);
  $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

  // Check if user already has a default address
  $sql_check_address = "SELECT address_id FROM user_address WHERE user_id = $userId AND is_default = 1";
  $result_addr = mysqli_query($conn, $sql_check_address);

  if (mysqli_num_rows($result_addr) > 0) {
    // User has an address, so UPDATE it
    $sql_address = "UPDATE user_address SET full_name='$full_name', phone='$phone', address_line1='$address_line1', city='$city', state='$state', zip='$zip' WHERE user_id = $userId AND is_default = 1";
    mysqli_query($conn, $sql_address);
    $address_id = mysqli_fetch_assoc($result_addr)['address_id'];
  } else {
    // User does not have an address, so INSERT a new one
    $sql_address = "INSERT INTO user_address (user_id, full_name, phone, address_line1, city, state, zip, is_default) VALUES ($userId, '$full_name', '$phone', '$address_line1', '$city', '$state', '$zip', 1)";
    mysqli_query($conn, $sql_address);
    $address_id = mysqli_insert_id($conn);
  }

  // Calculate total amount on the server side
  $total_amount = 0;
  foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
  }

  // Create a new order in the 'orders' table
  $sql_order = "INSERT INTO orders (user_id, billing_address_id, shipping_address_id, total_amount) VALUES ($userId, $address_id, $address_id, $total_amount)";
  mysqli_query($conn, $sql_order);
  $order_id = mysqli_insert_id($conn);

  // Move items from cart to 'order_items' table
  foreach ($cart_items as $item) {
    $product_id = (int)$item['product_id'];
    $quantity = (int)$item['quantity'];
    $price = (float)$item['price'];
    $total_price = $price * $quantity;

    $sql_order_item = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES ($order_id, $product_id, $quantity, $price, $total_price)";
    mysqli_query($conn, $sql_order_item);
  }

  // Clear the user's cart
  $sql_clear_cart = "DELETE FROM cart WHERE user_id = $userId";
  mysqli_query($conn, $sql_clear_cart);

  // Set success message and redirect to homepage
  message('popup-success', '<i class="ri-check-line"></i>', 'Your order has been placed successfully!');
  header('Location: index.php');
  exit();
}

// 4. Fetch existing user address to pre-fill the form (for GET request)
$sql_user_address = "SELECT * FROM user_address WHERE user_id = $userId AND is_default = 1";
$result_user_address = mysqli_query($conn, $sql_user_address);
$user_address = mysqli_fetch_assoc($result_user_address);

// Calculate subtotal for display
$subtotal = 0;
foreach ($cart_items as $item) {
  $subtotal += $item['price'] * $item['quantity'];
}

mysqli_close($conn);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Checkout - PC Zone</title>
  <?php include('./includes/header-link.php') ?>
  <style>
    body {
      background-color: #f8f9fa;
    }

    .form-section,
    .summary-section {
      background-color: #fff;
      padding: 2rem;
      border-radius: 0.5rem;
      box-shadow: 0 4px 25px rgba(0, 0, 0, 0.05);
    }

    .summary-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .summary-item-img {
      width: 60px;
      height: 60px;
      object-fit: contain;
      border-radius: 0.25rem;
    }

    .summary-item-info {
      flex-grow: 1;
    }

    .payment-option {
      border: 1px solid #dee2e6;
      padding: 1rem;
      border-radius: 0.5rem;
      cursor: pointer;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .payment-option:has(input:checked) {
      border-color: #0d6efd;
      box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
    }

    .btn-place-order {
      background: linear-gradient(to bottom, #2b5876, #4e4376);
      color: #fff;
      border: none;
      transition: all 0.2s ease-in-out;
      font-weight: bold;
      width: 100%;
      padding: 0.8rem;
    }

    .btn-place-order:hover {
      opacity: 0.9;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
  </style>
</head>

<body>
  <?php include('./includes/alert.php'); ?>
  <?php include('./includes/navbar.php'); ?>

  <div class="container my-5">
    <form action="checkout.php" method="POST">
      <div class="row g-5">
        <!-- Left Column: Shipping & Payment -->
        <div class="col-lg-7">
          <div class="form-section">
            <h3 class="mb-4">Shipping Address</h3>
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="full_name" value="<?= e($user_address['full_name'] ?? '') ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Mobile Number</label>
                <input type="tel" class="form-control" name="phone" value="<?= e($user_address['phone'] ?? '') ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Pincode / ZIP</label>
                <input type="text" class="form-control" name="zip" value="<?= e($user_address['zip'] ?? '') ?>" required>
              </div>
              <div class="col-12">
                <label class="form-label">Address (Line 1)</label>
                <input type="text" class="form-control" name="address_line1" value="<?= e($user_address['address_line1'] ?? '') ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">City</label>
                <input type="text" class="form-control" name="city" value="<?= e($user_address['city'] ?? '') ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">State</label>
                <input type="text" class="form-control" name="state" value="<?= e($user_address['state'] ?? '') ?>" required>
              </div>
            </div>

            <hr class="my-4">

            <h3 class="mb-4">Payment Method</h3>
            <div class="d-grid gap-3">
              <label class="payment-option">
                <input type="radio" name="payment_method" value="cash_on_delivery" class="form-check-input" checked>
                <span class="ms-2 fw-bold">Cash on Delivery</span>
              </label>
              <label class="payment-option">
                <input type="radio" name="payment_method" value="upi" class="form-check-input">
                <span class="ms-2 fw-bold">UPI / QR Code</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Right Column: Order Summary -->
        <div class="col-lg-5">
          <div class="summary-section">
            <h3 class="mb-4">Your Order</h3>
            <?php foreach ($cart_items as $item):
              // Image fallback logic
              $img_filename = $item['main_image'] ?? '';
              $imgPath = 'assets/images/no-image.png';
              if (!empty($img_filename)) {
                if (file_exists('uploads/' . $img_filename)) {
                  $imgPath = 'uploads/' . $img_filename;
                } elseif (file_exists('assets/images/products/' . $img_filename)) {
                  $imgPath = 'assets/images/products/' . $img_filename;
                }
              }
            ?>
              <div class="summary-item">
                <img src="<?= e($imgPath) ?>" alt="<?= e($item['product_name']) ?>" class="summary-item-img">
                <div class="summary-item-info">
                  <div class="fw-bold"><?= e($item['product_name']) ?></div>
                  <div class="text-muted small">Qty: <?= $item['quantity'] ?></div>
                </div>
                <div class="fw-bold"><?= formatPrice($item['price'] * $item['quantity']) ?></div>
              </div>
            <?php endforeach; ?>

            <hr class="my-4">

            <div class="d-flex justify-content-between mb-2">
              <span>Subtotal</span>
              <strong><?= formatPrice($subtotal) ?></strong>
            </div>
            <div class="d-flex justify-content-between mb-3">
              <span>Shipping</span>
              <strong class="text-success">FREE</strong>
            </div>
            <hr>
            <div class="d-flex justify-content-between fw-bold fs-5 mb-4">
              <span>Total</span>
              <span><?= formatPrice($subtotal) ?></span>
            </div>
            <button type="submit" class="btn btn-place-order">Place Order</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <?php include './includes/footer.php'; ?>
  <?php include './includes/footer-link.php'; ?>
</body>

</html>