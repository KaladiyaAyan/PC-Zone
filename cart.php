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

// 2. Handle cart actions (Update Quantity / Remove Item)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // --- Handle REMOVING an item ---
  if (isset($_POST['remove_item'])) {
    $cart_item_id = (int)$_POST['remove_item'];
    $sql_delete = "DELETE FROM cart WHERE cart_item_id = $cart_item_id AND user_id = $userId";
    mysqli_query($conn, $sql_delete);
  }
  // --- Handle UPDATING quantities ---
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
  // Redirect to the same page to prevent form resubmission
  header('Location: cart.php');
  exit();
}

// 3. Fetch all cart items for the user
$sql_cart = "SELECT 
                c.cart_item_id, c.product_id, c.quantity, c.product_name, c.price,
                p.main_image, p.slug
             FROM 
                cart c
             JOIN 
                products p ON c.product_id = p.product_id
             WHERE 
                c.user_id = $userId";
$result_cart = mysqli_query($conn, $sql_cart);
$cart_items = mysqli_fetch_all($result_cart, MYSQLI_ASSOC);

// 4. Calculate subtotal
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
  <title>Your Shopping Cart - PC Zone</title>
  <?php include('./includes/header-link.php') ?>
  <style>
    body {
      background-color: #f8f9fa;
    }

    .cart-item-row {
      display: flex;
      align-items: center;
      padding: 1.5rem 0;
      border-bottom: 1px solid #dee2e6;
    }

    .cart-item-image {
      width: 100px;
      height: 100px;
      object-fit: contain;
      margin-right: 1.5rem;
    }

    .cart-item-details {
      flex-grow: 1;
    }

    .cart-item-details a {
      color: #212529;
      text-decoration: none;
      font-weight: bold;
    }

    .cart-item-details a:hover {
      color: #0d6efd;
    }

    .cart-item-quantity input {
      width: 70px;
      text-align: center;
    }

    .cart-item-price,
    .cart-item-total {
      width: 120px;
      text-align: right;
      font-weight: 500;
    }

    .cart-item-remove button {
      background: none;
      border: none;
      color: #dc3545;
      font-size: 1.2rem;
    }

    .order-summary-card {
      background-color: #fff;
      padding: 1.5rem;
      border-radius: 0.5rem;
      box-shadow: 0 4px 25px rgba(0, 0, 0, 0.05);
      position: sticky;
      top: 120px;
      /* Adjust based on your header height */
    }

    /* Using the button style from your theme */
    .btn-checkout {
      background: linear-gradient(to bottom, #2b5876, #4e4376);
      color: #fff;
      border: none;
      transition: all 0.2s ease-in-out;
      font-weight: bold;
      width: 100%;
      padding: 0.8rem;
    }

    .btn-checkout:hover {
      opacity: 0.9;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
  </style>
</head>

<body>

  <?php include('./includes/navbar.php'); ?>

  <div class="container my-5">
    <h1 class="mb-4">Your Shopping Cart</h1>

    <div class="row">
      <div class="col-lg-8">
        <div class="bg-white p-4 rounded shadow-sm">
          <?php if (empty($cart_items)): ?>
            <div class="text-center py-5">
              <p class="fs-4">Your cart is empty.</p>
              <a href="product.php" class="btn btn-primary mt-2">Continue Shopping</a>
            </div>
          <?php else: ?>
            <form action="cart.php" method="POST">
              <?php foreach ($cart_items as $item):
                // Image fallback logic for each item
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
                    <p class="text-muted small mb-0">Unit Price: <?= formatPrice($item['price']) ?></p>
                  </div>
                  <div class="cart-item-quantity">
                    <input type="number" class="form-control" name="quantity[<?= $item['cart_item_id'] ?>]" value="<?= $item['quantity'] ?>" min="1">
                  </div>
                  <div class="cart-item-total d-none d-md-block">
                    <?= formatPrice($item['price'] * $item['quantity']) ?>
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
            <span>Subtotal</span>
            <strong><?= formatPrice($subtotal) ?></strong>
          </div>
          <div class="d-flex justify-content-between mb-3">
            <span>Shipping</span>
            <span class="text-muted">Calculated at checkout</span>
          </div>
          <hr>
          <div class="d-flex justify-content-between fw-bold fs-5 mb-4">
            <span>Total</span>
            <span><?= formatPrice($subtotal) ?></span>
          </div>
          <a href="checkout.php" class="btn btn-checkout">Proceed to Checkout</a>
        </div>
      </div>
    </div>
  </div>

  <?php include './includes/footer.php'; ?>
  <?php include './includes/footer-link.php'; ?>
</body>

</html>