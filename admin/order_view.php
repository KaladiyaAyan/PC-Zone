<?php
session_start();
require('../includes/db_connect.php');
require('../includes/functions.php');

// Simple admin check
if (empty($_SESSION['admin_logged_in'])) {
  header('Location: ../login.php');
  exit;
}

$order_id = (int)($_GET['id'] ?? 0);
if ($order_id <= 0) {
  header("Location: orders.php");
  exit;
}

// Helpers
function showDate($date)
{
  return $date ? date('d M Y, h:i A', strtotime($date)) : '—';
}
function showMoney($amount)
{
  return '₹' . number_format((float)$amount, 2);
}

// Get order header information
$orderStmt = $conn->prepare("
  SELECT o.order_id, o.user_id, o.created_at, o.updated_at,
         u.username, u.email, u.phone,
         SUM(o.quantity * (o.unit_price - o.discount)) as order_total
  FROM orders o
  JOIN users u ON o.user_id = u.user_id
  WHERE o.order_id = ?
  GROUP BY o.order_id, o.user_id, o.created_at, o.updated_at, u.username, u.email, u.phone
  LIMIT 1
");
$orderStmt->bind_param("i", $order_id);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();
$order = $orderResult->fetch_assoc();
$orderStmt->close();

if (!$order) {
  header("Location: orders.php");
  exit;
}

// Fetch user's default address
$addrStmt = $conn->prepare("
  SELECT * FROM user_address
  WHERE user_id = ?
  ORDER BY is_default DESC, address_id DESC
  LIMIT 1
");
$addrStmt->bind_param("i", $order['user_id']);
$addrStmt->execute();
$addrRes = $addrStmt->get_result();
$defaultAddress = $addrRes->fetch_assoc();
$addrStmt->close();

// Fetch payments for this order
$payStmt = $conn->prepare("
  SELECT p.*, o.order_id 
  FROM payments p 
  JOIN orders o ON p.order_id = o.order_id 
  WHERE o.order_id = ? 
  ORDER BY p.created_at DESC
");
$payStmt->bind_param("i", $order_id);
$payStmt->execute();
$payments = $payStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$payStmt->close();

// Determine display status
$displayStatus = '—';
if (!empty($payments)) {
  $displayStatus = $payments[0]['payment_status'] ?? '—';
}

// Fetch order items
$itemStmt = $conn->prepare("
  SELECT o.product_id, o.quantity, o.unit_price, o.discount, 
         (o.quantity * (o.unit_price - o.discount)) as line_total,
         p.product_name, p.sku, p.main_image
  FROM orders o
  LEFT JOIN products p ON o.product_id = p.product_id
  WHERE o.order_id = ?
");
$itemStmt->bind_param("i", $order_id);
$itemStmt->execute();
$items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$itemStmt->close();

// Calculate order total from items
$calculatedTotal = 0;
foreach ($items as $item) {
  $calculatedTotal += $item['line_total'];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Order #<?= $order_id ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php include './includes/header-link.php'; ?>
</head>

<body>
  <?php require('./includes/alert.php'); ?>
  <?php include './includes/header.php'; ?>
  <?php $current_page = 'orders';
  include './includes/sidebar.php'; ?>

  <main class="main-content pt-5 mt-4">
    <div class="container mt-2">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Order #<?= $order_id ?></h2>
        <a href="orders.php" class="btn btn-add">Back</a>
      </div>

      <div class="row g-3">
        <!-- Order Information -->
        <div class="col-lg-6">
          <div class="card theme-card">
            <div class="card-header">Order Information</div>
            <div class="card-body">
              <div class="mb-1"><strong>Date:</strong> <?= showDate($order['created_at'] ?? null) ?></div>
              <div class="mb-1"><strong>Status:</strong> <?= e($displayStatus) ?></div>
              <div class="mb-1"><strong>Total:</strong> <?= showMoney($calculatedTotal) ?></div>
              <?php if (!empty($order['updated_at']) && $order['updated_at'] != $order['created_at']): ?>
                <div class="mb-1"><strong>Last Updated:</strong> <?= showDate($order['updated_at']) ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Customer & Addresses -->
        <div class="col-lg-6">
          <div class="card mb-3 theme-card">
            <div class="card-header">Customer</div>
            <div class="card-body">
              <div class="mb-1"><strong>Name:</strong> <?= e($order['username']) ?></div>
              <div class="mb-1"><strong>Email:</strong> <?= e($order['email']) ?></div>
              <div class="mb-1"><strong>Phone:</strong> <?= e($order['phone']) ?></div>
            </div>
          </div>

          <div class="card mb-3 theme-card">
            <div class="card-header">Shipping Address</div>
            <div class="card-body">
              <?php if ($defaultAddress): ?>
                <div class="mb-1"><strong>Contact:</strong> <?= e($defaultAddress['full_name']) ?> - <?= e($defaultAddress['phone']) ?></div>
                <div><?= e($defaultAddress['address_line1']) ?></div>
                <?php if (!empty($defaultAddress['address_line2'])): ?>
                  <div><?= e($defaultAddress['address_line2']) ?></div>
                <?php endif; ?>
                <div><?= e($defaultAddress['city']) ?>, <?= e($defaultAddress['state']) ?> - <?= e($defaultAddress['zip']) ?></div>
                <div><?= e($defaultAddress['country']) ?></div>
              <?php else: ?>
                <div class="text-muted">No address on file</div>
              <?php endif; ?>
            </div>
          </div>

          <div class="card theme-card">
            <div class="card-header">Billing Address</div>
            <div class="card-body">
              <?php if ($defaultAddress): ?>
                <div class="mb-1"><strong>Contact:</strong> <?= e($defaultAddress['full_name']) ?> - <?= e($defaultAddress['phone']) ?></div>
                <div><?= e($defaultAddress['address_line1']) ?></div>
                <?php if (!empty($defaultAddress['address_line2'])): ?>
                  <div><?= e($defaultAddress['address_line2']) ?></div>
                <?php endif; ?>
                <div><?= e($defaultAddress['city']) ?>, <?= e($defaultAddress['state']) ?> - <?= e($defaultAddress['zip']) ?></div>
                <div><?= e($defaultAddress['country']) ?></div>
              <?php else: ?>
                <div class="text-muted">No address on file</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Items -->
      <div class="card mt-3 theme-card">
        <div class="card-header">Items</div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Product</th>
                <th>SKU</th>
                <th>Unit Price</th>
                <th>Discount</th>
                <th>Qty</th>
                <th>Line Total</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $index => $item): ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td>
                    <?= e($item['product_name'] ?? 'Product Not Found') ?>
                    <?php if (!empty($item['product_id'])): ?>
                      <br><small class="text-muted">ID: <?= $item['product_id'] ?></small>
                    <?php endif; ?>
                  </td>
                  <td><?= e($item['sku'] ?? 'N/A') ?></td>
                  <td><?= showMoney($item['unit_price'] ?? 0) ?></td>
                  <td><?= showMoney($item['discount'] ?? 0) ?></td>
                  <td><?= (int)($item['quantity'] ?? 0) ?></td>
                  <td><?= showMoney($item['line_total'] ?? 0) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="6" class="text-end"><strong>Order Total</strong></td>
                <td><strong><?= showMoney($calculatedTotal) ?></strong></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Payments -->
      <div class="card mt-3 theme-card">
        <div class="card-header">Payments</div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Method</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Paid At</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($payments)): ?>
                <?php foreach ($payments as $payment): ?>
                  <tr>
                    <td><?= e(ucfirst(str_replace('_', ' ', $payment['payment_method']))) ?></td>
                    <td><?= showMoney($payment['amount']) ?></td>
                    <td><?= e($payment['payment_status']) ?></td>
                    <td><?= showDate($payment['created_at']) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" class="text-center">No payments recorded</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>

  <?php require('./includes/footer-link.php') ?>
</body>

</html>