<?php
session_start();
require('./includes/db_connect.php');
require('./includes/functions.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Please Login First');
  header('Location: login.php');
  exit();
}

$userId = (int)$_SESSION['user_id'];

// Handle cart actions (Update Quantity / Remove Item)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  //Handle REMOVING an item
  if (isset($_POST['remove_item'])) {
    $cart_item_id = (int)$_POST['remove_item'];
    $sql_delete = "DELETE FROM cart WHERE cart_item_id = $cart_item_id AND user_id = $userId";
    mysqli_query($conn, $sql_delete);
  }
  //Handle UPDATING quantities
  if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cart_item_id => $quantity) {
      $cart_item_id = (int)$cart_item_id;
      $quantity = (int)$quantity;
      if ($quantity > 0) {
        $sql_update = "UPDATE cart SET quantity = $quantity WHERE cart_item_id = $cart_item_id AND user_id = $userId";
        mysqli_query($conn, $sql_update);
      } else {
        // If quantity is 0 or less, remove the item
        $sql_delete = "DELETE FROM cart WHERE cart_item_id = $cart_item_id AND user_id = $userId";
        mysqli_query($conn, $sql_delete);
      }
    }
  }

  header('Location: cart.php');
  exit();
}

// Fetch all cart items for the user
// Also select product's original price and discount
$sql_cart = "SELECT 
                c.cart_item_id, c.product_id, c.quantity, c.product_name, c.price AS cart_price,
                p.main_image, p.slug, p.price AS orig_price, COALESCE(p.discount,0) AS discount
             FROM 
                cart c
             JOIN 
                products p ON c.product_id = p.product_id
             WHERE 
                c.user_id = $userId";
$result_cart = mysqli_query($conn, $sql_cart);
$cart_items = mysqli_fetch_all($result_cart, MYSQLI_ASSOC);

// Calculate totals (original, discount, after-discount)
$total_original = 0.0;
$total_discount = 0.0;
$total_after = 0.0;

foreach ($cart_items as $item) {
  $orig_price = (float)($item['orig_price'] ?? $item['cart_price']);
  $discount_value = (float)($item['discount'] ?? 0);
  $discount_amount_per_unit = ($discount_value > 0) ? ($orig_price * $discount_value / 100) : 0;
  $final_price_per_unit = max(0, $orig_price - $discount_amount_per_unit);
  $qty = (int)$item['quantity'];

  $total_original += $orig_price * $qty;
  $total_discount += $discount_amount_per_unit * $qty;
  $total_after += $final_price_per_unit * $qty;
}

mysqli_close($conn);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Your Shopping Cart - PC Zone</title>
  <?php include('./includes/header-link.php') ?>
  <link rel="stylesheet" href="assets/css/cart.css">
</head>

<body>
  <?php include('./includes/alert.php'); ?>
  <?php include('./includes/navbar.php'); ?>

  <div class="container my-5">
    <h1 class="mb-4">Your Shopping Cart</h1>

    <div class="row">
      <div class="col-lg-8">
        <div class="bg-white p-4 rounded shadow-sm">
          <?php if (empty($cart_items)): ?>
            <div class="text-center py-5">
              <p class="fs-4">Your cart is empty.</p>
              <a href="product.php" class="shopping-button btn mt-2">Continue Shopping</a>
            </div>
          <?php else: ?>
            <form action="cart.php" method="POST">
              <?php foreach ($cart_items as $item):

                $orig_price = (float)($item['orig_price'] ?? $item['cart_price']);
                $discount_value = (float)($item['discount'] ?? 0);

                $discount_per_unit = ($discount_value > 0) ? ($orig_price * $discount_value / 100) : 0;
                $final_price = max(0, $orig_price - $discount_per_unit);
                $qty = (int)$item['quantity'];

                // image fallback logic for each item
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
                <div class="cart-item-row">
                  <img src="<?= e($imgPath) ?>" alt="<?= e($item['product_name']) ?>" class="cart-item-image">
                  <div class="cart-item-details">
                    <a href="product-detail.php?slug=<?= e($item['slug']) ?>"><?= e($item['product_name']) ?></a>
                    <p class="small-muted mb-0">
                      Unit Price:
                      <?php if ($discount_per_unit > 0): ?>
                        <span class="original-price"><?= formatPrice($orig_price) ?></span>
                        <span class="final-price"><?= formatPrice($final_price) ?></span>
                        <span class="discount-badge">-<?= formatPrice($discount_per_unit) ?></span>
                      <?php else: ?>
                        <span class="final-price"><?= formatPrice($orig_price) ?></span>
                      <?php endif; ?>
                    </p>
                  </div>

                  <div class="cart-item-quantity">
                    <input type="number" class="form-control" name="quantity[<?= $item['cart_item_id'] ?>]" value="<?= $qty ?>" min="1">
                  </div>

                  <div class="cart-item-total d-none d-md-block">
                    <?php
                    $line_total = $final_price * $qty;
                    echo formatPrice($line_total);
                    ?>
                  </div>

                  <div class="cart-item-remove">
                    <button type="submit" name="remove_item" value="<?= $item['cart_item_id'] ?>" title="Remove Item">
                      <i class="ri-delete-bin-line"></i>
                    </button>
                  </div>
                </div>
              <?php endforeach; ?>
              <div class="d-flex justify-content-end mt-3">
                <button type="submit" name="update_cart" class="btn btn-secondary">Update Cart</button>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="order-summary-card">
          <h4 class="mb-4">Order Summary</h4>
          <div class="d-flex justify-content-between mb-2">
            <span>Subtotal (Original)</span>
            <strong><?= formatPrice($total_original) ?></strong>
          </div>

          <div class="d-flex justify-content-between mb-2">
            <span>Total Discount</span>
            <strong class="text-danger">- <?= formatPrice($total_discount) ?></strong>
          </div>

          <div class="d-flex justify-content-between mb-3">
            <span>Shipping</span>
            <span class="text-muted">Calculated at checkout</span>
          </div>
          <hr>
          <div class="d-flex justify-content-between fw-bold fs-5 mb-4">
            <span>Total After Discount</span>
            <span><?= formatPrice($total_after) ?></span>
          </div>
          <a href="checkout.php" class="btn btn-gradient w-100 py-2">Proceed to Checkout</a>
        </div>
      </div>
    </div>
  </div>

  <?php include './includes/footer.php'; ?>
  <?php include './includes/footer-link.php'; ?>
</body>

</html>