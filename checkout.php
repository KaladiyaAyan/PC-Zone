<?php
session_start();
require('./includes/db_connect.php');
require('./includes/functions.php');

if (!isset($_SESSION['user_id'])) {
  message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Please Login First');
  header('Location: login.php');
  exit();
}

$userId = (int)$_SESSION['user_id'];

// Fetch cart items with product details
$sql = "SELECT c.*, p.main_image, p.price AS orig_price, COALESCE(p.discount,0) AS discount_percent
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.user_id = $userId";
$res = mysqli_query($conn, $sql);
$cart_items = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];

if (empty($cart_items)) {
  message('popup-warning', '<i class="ri-alert-line"></i>', 'Your cart is empty. Nothing to check out!');
  header('Location: cart.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get form data
  $full_name = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
  $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
  $address_line1 = mysqli_real_escape_string($conn, $_POST['address_line1'] ?? '');
  $city = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
  $state = mysqli_real_escape_string($conn, $_POST['state'] ?? '');
  $zip = mysqli_real_escape_string($conn, $_POST['zip'] ?? '');
  $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? '');

  // Update user address
  mysqli_query($conn, "DELETE FROM user_address WHERE user_id = $userId AND is_default = 1");
  mysqli_query($conn, "INSERT INTO user_address (user_id, full_name, phone, address_line1, city, state, zip, is_default)
                       VALUES ($userId, '$full_name', '$phone', '$address_line1', '$city', '$state', '$zip', 1)");

  // Process each cart item
  foreach ($cart_items as $item) {
    $product_id = (int)$item['product_id'];
    $qty = (int)$item['quantity'];
    $orig = (float)$item['orig_price'];
    $disc_percent = (float)$item['discount_percent'];

    $discount = $disc_percent > 0 ? ($orig * $disc_percent / 100) : 0;
    $final_price = max(0, $orig - $discount);
    $total_price = $final_price * $qty;

    // Insert order
    mysqli_query($conn, "INSERT INTO orders (user_id, product_id, quantity, unit_price, discount, total_price) 
                         VALUES ($userId, $product_id, $qty, $final_price, $disc_percent, $total_price)");

    $order_id = mysqli_insert_id($conn);

    // Insert payment
    if ($order_id) {
      mysqli_query($conn, "INSERT INTO payments (order_id, payment_method, amount) 
                           VALUES ($order_id, '$payment_method', $total_price)");
    }
  }

  // Clear cart and redirect
  mysqli_query($conn, "DELETE FROM cart WHERE user_id = $userId");

  message('popup-success', '<i class="ri-check-line"></i>', 'Your order has been placed successfully!');
  header('Location: index.php');
  exit();
}

// Get user address
$res_addr = mysqli_query($conn, "SELECT * FROM user_address WHERE user_id = $userId AND is_default = 1 LIMIT 1");
$user_address = $res_addr ? mysqli_fetch_assoc($res_addr) : null;

// Calculate totals
$subtotal_original = 0.0;
$total_discount = 0.0;
$subtotal_after = 0.0;

foreach ($cart_items as $item) {
  $orig = (float)$item['orig_price'];
  $disc_percent = (float)$item['discount_percent'];
  $discount = $disc_percent > 0 ? ($orig * $disc_percent / 100) : 0;
  $final = max(0, $orig - $discount);
  $qty = (int)$item['quantity'];

  $subtotal_original += $orig * $qty;
  $total_discount += $discount * $qty;
  $subtotal_after += $final * $qty;
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
  <link rel="stylesheet" href="assets/css/checkout.css">
</head>

<body>
  <?php include('./includes/alert.php'); ?>
  <?php include('./includes/navbar.php'); ?>

  <div class="container my-5">
    <form action="checkout.php" method="POST">
      <div class="row g-5">
        <div class="col-lg-7">
          <div class="checkout-panel">
            <h3 class="mb-4">Shipping Address</h3>
            <div class="row g-3">
              <div class="col-12"><label class="form-label">Full Name</label><input type="text" class="form-control" name="full_name" value="<?= e($user_address['full_name'] ?? '') ?>" required></div>
              <div class="col-md-6"><label class="form-label">Mobile Number</label><input type="tel" class="form-control" name="phone" value="<?= e($user_address['phone'] ?? '') ?>" required></div>
              <div class="col-md-6"><label class="form-label">Pincode / ZIP</label><input type="text" class="form-control" name="zip" value="<?= e($user_address['zip'] ?? '') ?>" required></div>
              <div class="col-12"><label class="form-label">Address (Line 1)</label><input type="text" class="form-control" name="address_line1" value="<?= e($user_address['address_line1'] ?? '') ?>" required></div>
              <div class="col-md-6"><label class="form-label">City</label><input type="text" class="form-control" name="city" value="<?= e($user_address['city'] ?? '') ?>" required></div>
              <div class="col-md-6"><label class="form-label">State</label><input type="text" class="form-control" name="state" value="<?= e($user_address['state'] ?? '') ?>" required></div>
            </div>

            <hr class="my-4">

            <h3 class="mb-4">Payment Method</h3>
            <div class="d-grid gap-3">
              <label class="payment-option"><input type="radio" name="payment_method" value="cash_on_delivery" class="form-check-input" checked> <span class="ms-2 fw-bold">Cash on Delivery</span></label>
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="checkout-panel">
            <h3 class="mb-4">Your Order</h3>
            <?php foreach ($cart_items as $item):
              $imgPath = 'assets/images/no-image.png';
              if (!empty($item['main_image'])) {
                if (file_exists('uploads/' . $item['main_image'])) $imgPath = 'uploads/' . $item['main_image'];
                elseif (file_exists('assets/images/products/' . $item['main_image'])) $imgPath = 'assets/images/products/' . $item['main_image'];
              }

              $orig = (float)$item['orig_price'];
              $disc_percent = (float)$item['discount_percent'];
              $discount = $disc_percent > 0 ? ($orig * $disc_percent / 100) : 0;
              $final = max(0, $orig - $discount);
            ?>
              <div class="summary-item">
                <img src="<?= e($imgPath) ?>" alt="<?= e($item['product_name']) ?>" class="summary-item-img">
                <div class="summary-item-info">
                  <div class="fw-bold"><?= e($item['product_name']) ?></div>
                  <div class="text-muted small">Qty: <?= $item['quantity'] ?></div>
                </div>
                <div class="text-end">
                  <?php if ($disc_percent > 0): ?>
                    <div><span class="original-price"><?= formatPrice($orig * $item['quantity']) ?></span></div>
                    <div><span class="final-price"><?= formatPrice($final * $item['quantity']) ?></span><span class="discount-text">-<?= floatval($disc_percent) ?>%</span></div>
                  <?php else: ?>
                    <div class="final-price"><?= formatPrice($orig * $item['quantity']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>

            <hr class="my-4">
            <div class="d-flex justify-content-between mb-2"><span>Subtotal (Original)</span><strong><?= formatPrice($subtotal_original) ?></strong></div>
            <div class="d-flex justify-content-between mb-2"><span>Total Discount</span><strong class="text-danger">- <?= formatPrice($total_discount) ?></strong></div>
            <div class="d-flex justify-content-between mb-3"><span>Shipping</span><strong class="text-success">FREE</strong></div>
            <hr>
            <div class="d-flex justify-content-between fw-bold fs-5 mb-4"><span>Total After Discount</span><span><?= formatPrice($subtotal_after) ?></span></div>
            <button type="submit" class="btn btn-place-order">Place Order</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <?php include('./includes/footer.php'); ?>
  <?php include('./includes/footer-link.php'); ?>
</body>

</html>