<?php
session_start();
require('../includes/db_connect.php');
require('../includes/functions.php');

// Simple admin check
if (empty($_SESSION['admin_logged_in'])) {
  header('Location: ../login.php');
  exit;
}

$order_id = (int)$_GET['id'];
if ($order_id <= 0) {
  header("Location: orders.php");
  exit;
}

// Get order details
$order = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT o.*, u.username, u.email, u.phone,
           sa.full_name AS ship_name, sa.phone AS ship_phone, sa.address_line1 AS ship_addr1, 
           sa.address_line2 AS ship_addr2, sa.city AS ship_city, sa.state AS ship_state, 
           sa.zip AS ship_zip, sa.country AS ship_country,
           ba.full_name AS bill_name, ba.phone AS bill_phone, ba.address_line1 AS bill_addr1, 
           ba.address_line2 AS bill_addr2, ba.city AS bill_city, ba.state AS bill_state, 
           ba.zip AS bill_zip, ba.country AS bill_country
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    JOIN user_address sa ON o.shipping_address_id = sa.address_id
    LEFT JOIN user_address ba ON o.billing_address_id = ba.address_id
    WHERE o.order_id = $order_id
"));

if (!$order) {
  header("Location: orders.php");
  exit;
}

// Get payments
$payments_result = mysqli_query($conn, "SELECT * FROM payments WHERE order_id = $order_id ORDER BY paid_at DESC");
$payments = mysqli_fetch_all($payments_result, MYSQLI_ASSOC);

// Get order items  
$items_result = mysqli_query($conn, "
    SELECT oi.*, p.product_name, p.sku
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = $order_id
");
$items = mysqli_fetch_all($items_result, MYSQLI_ASSOC);

// Get shipment
$shipment = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM shipments WHERE order_id = $order_id ORDER BY shipped_date DESC LIMIT 1
"));

// Simple helper functions
function show($text)
{
  return htmlspecialchars($text ?? '');
}

function showDate($date)
{
  return $date ? date('d M Y, h:i A', strtotime($date)) : '—';
}

function showMoney($amount)
{
  return '₹' . number_format((float)$amount, 2);
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
              <div class="mb-1"><strong>Date:</strong> <?= showDate($order['order_date']) ?></div>
              <div class="mb-1"><strong>Status:</strong> <?= show($order['order_status']) ?></div>
              <div class="mb-1"><strong>Total:</strong> <?= showMoney($order['total_amount']) ?></div>
              <div class="mb-1"><strong>Shipping Method:</strong> <?= show($order['shipping_method']) ?></div>
              <div class="mb-1"><strong>Tracking #:</strong> <?= show($order['tracking_number']) ?></div>
              <?php if ($order['order_notes']): ?>
                <div class="mt-2"><strong>Notes:</strong><br><?= nl2br(show($order['order_notes'])) ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Customer & Addresses -->
        <div class="col-lg-6">
          <div class="card mb-3 theme-card">
            <div class="card-header">Customer</div>
            <div class="card-body">
              <div class="mb-1"><strong>Name:</strong> <?= show($order['username']) ?></div>
              <div class="mb-1"><strong>Email:</strong> <?= show($order['email']) ?></div>
              <div class="mb-1"><strong>Phone:</strong> <?= show($order['phone']) ?></div>
            </div>
          </div>

          <div class="card mb-3 theme-card">
            <div class="card-header">Shipping Address</div>
            <div class="card-body">
              <div class="mb-1"><strong>Contact:</strong> <?= show($order['ship_name']) ?></div>
              <div><?= show("{$order['ship_addr1']}, {$order['ship_city']}, {$order['ship_state']} {$order['ship_zip']}") ?></div>
            </div>
          </div>

          <div class="card theme-card">
            <div class="card-header">Billing Address</div>
            <div class="card-body">
              <div class="mb-1"><strong>Contact:</strong> <?= show($order['bill_name']) ?></div>
              <div><?= show("{$order['bill_addr1']}, {$order['bill_city']}, {$order['bill_state']} {$order['bill_zip']}") ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Items -->
      <div class="card mt-3 theme-card">
        <div class="card-header">Items</div>
        <div class="card-body table-responsive">
          <table class="table">
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
                  <td><?= show($item['product_name']) ?></td>
                  <td><?= show($item['sku']) ?></td>
                  <td><?= number_format((float)$item['unit_price'], 2) ?></td>
                  <td><?= number_format((float)$item['discount'], 2) ?></td>
                  <td><?= (int)$item['quantity'] ?></td>
                  <td><?= number_format((float)$item['total_price'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="6" class="text-end"><strong>Order Total</strong></td>
                <td><strong><?= showMoney($order['total_amount']) ?></strong></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Payments -->
      <div class="card mt-3 theme-card">
        <div class="card-header">Payments</div>
        <div class="card-body table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Method</th>
                <th>Txn ID</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Paid At</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($payments)): ?>
                <?php foreach ($payments as $payment): ?>
                  <tr>
                    <td><?= show($payment['payment_method']) ?></td>
                    <td><?= show($payment['transaction_id']) ?></td>
                    <td><?= showMoney($payment['amount']) ?></td>
                    <td><?= show($payment['payment_status']) ?></td>
                    <td><?= showDate($payment['paid_at']) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center">No payments</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Shipment -->
      <?php if ($shipment): ?>
        <div class="card mt-3 theme-card">
          <div class="card-header">Shipment</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-3"><strong>Tracking #</strong><br><?= show($shipment['tracking_number']) ?></div>
              <div class="col-md-3"><strong>Method</strong><br><?= show($shipment['shipping_method']) ?></div>
              <div class="col-md-3"><strong>Status</strong><br><?= show($shipment['status']) ?></div>
              <div class="col-md-3"><strong>Shipped</strong><br><?= showDate($shipment['shipped_date']) ?></div>
            </div>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </main>

  <?php require('./includes/footer-link.php') ?>
</body>

</html>