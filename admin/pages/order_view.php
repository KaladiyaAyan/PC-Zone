<?php
session_start();

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

require_once '../includes/db_connect.php';

// Validate order id
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
  header("Location: orders.php");
  exit;
}
$order_id = (int)$_GET['id'];

/* -------- Order + Customer + Addresses -------- */
$sqlOrder = "
  SELECT
    o.order_id,
    o.order_date,
    o.total_amount,
    o.order_status,
    o.tracking_number,
    o.shipping_method,
    o.order_notes,
    o.paid_at,
    o.cancelled_at,
    o.refunded_at,
    o.shipped_date,
    o.delivered_date,

    c.customer_id,
    c.first_name,
    c.last_name,
    c.email,
    c.phone,

    sa.full_name  AS ship_full_name,
    sa.phone      AS ship_phone,
    sa.address_line1 AS ship_addr1,
    sa.address_line2 AS ship_addr2,
    sa.city       AS ship_city,
    sa.state      AS ship_state,
    sa.zip        AS ship_zip,
    sa.country    AS ship_country,

    ba.full_name  AS bill_full_name,
    ba.phone      AS bill_phone,
    ba.address_line1 AS bill_addr1,
    ba.address_line2 AS bill_addr2,
    ba.city       AS bill_city,
    ba.state      AS bill_state,
    ba.zip        AS bill_zip,
    ba.country    AS bill_country
  FROM orders o
  JOIN customers c        ON o.customer_id = c.customer_id
  JOIN addresses sa       ON o.shipping_address_id = sa.address_id
  LEFT JOIN addresses ba  ON o.billing_address_id = ba.address_id
  WHERE o.order_id = ?
  LIMIT 1
";
$stmt = mysqli_prepare($conn, $sqlOrder);
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$orderRes = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($orderRes);

if (!$order) {
  header("Location: orders.php");
  exit;
}

/* -------- Payments (latest first) -------- */
$sqlPayments = "
  SELECT payment_id, payment_method, transaction_id, amount, currency,
         payment_status, paid_at, created_at
  FROM payments
  WHERE order_id = ?
  ORDER BY COALESCE(paid_at, created_at) DESC
";
$stmtPay = mysqli_prepare($conn, $sqlPayments);
mysqli_stmt_bind_param($stmtPay, 'i', $order_id);
mysqli_stmt_execute($stmtPay);
$payRes = mysqli_stmt_get_result($stmtPay);
$latestPayment = mysqli_fetch_assoc($payRes);
mysqli_data_seek($payRes, 0);

/* -------- Order Items -------- */
$sqlItems = "
  SELECT
    oi.order_item_id,
    oi.product_id,
    p.product_name,
    p.sku,
    oi.quantity,
    oi.unit_price,
    oi.discount,
    oi.total_price
  FROM order_items oi
  JOIN products p ON oi.product_id = p.product_id
  WHERE oi.order_id = ?
  ORDER BY oi.order_item_id ASC
";
$stmtItems = mysqli_prepare($conn, $sqlItems);
mysqli_stmt_bind_param($stmtItems, 'i', $order_id);
mysqli_stmt_execute($stmtItems);
$itemsRes = mysqli_stmt_get_result($stmtItems);

/* -------- Latest Shipment -------- */
$sqlShipment = "
  SELECT shipment_id, tracking_number, shipping_method, shipped_date,
         delivered_date, status, created_at
  FROM shipments
  WHERE order_id = ?
  ORDER BY COALESCE(shipped_date, created_at) DESC
  LIMIT 1
";
$stmtShip = mysqli_prepare($conn, $sqlShipment);
mysqli_stmt_bind_param($stmtShip, 'i', $order_id);
mysqli_stmt_execute($stmtShip);
$shipRes = mysqli_stmt_get_result($stmtShip);
$shipment = mysqli_fetch_assoc($shipRes);

/* -------- Helpers -------- */
function h($s)
{
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function fmtDate($d)
{
  return $d ? date('d M Y, h:i A', strtotime($d)) : '—';
}
function fmtDateShort($d)
{
  return $d ? date('d M Y', strtotime($d)) : '—';
}
function moneyINR($n)
{
  return '₹' . number_format((float)$n, 2);
}
function addrLine($a1, $a2, $city, $state, $zip, $country)
{
  $parts = array_filter([$a1, $a2, $city, $state, $zip, $country]);
  return $parts ? h(implode(', ', $parts)) : '—';
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
  <meta charset="UTF-8" />
  <title>Order #<?= (int)$order['order_id']; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
  <?php include '../includes/header.php'; ?>
  <?php $current_page = 'orders';
  include '../includes/sidebar.php'; ?>

  <main class="main-content pt-5 mt-4">
    <div class="container mt-2">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0"><i class="fas fa-receipt me-2"></i>Order #<?= (int)$order['order_id']; ?></h2>
        <a href="orders.php" class="btn btn-add"><i class="fas fa-arrow-left me-1"></i>Back</a>
      </div>

      <!-- Order summary -->
      <div class="row g-3 ">
        <div class="col-lg-6">
          <div class="card theme-card">
            <div class="card-header">Order Information</div>
            <div class="card-body">
              <div class="mb-1"><strong>Date:</strong> <?= fmtDate($order['order_date']); ?></div>
              <div class="mb-1">
                <strong>Order Status:</strong>
                <?php
                $osClass = match ($order['order_status']) {
                  'Delivered' => 'status-completed',
                  'Shipped', 'Processing' => 'status-pending',
                  'Cancelled' => 'status-cancelled',
                  'Returned' => 'stock-badge low-stock',
                  default => 'stock-badge'
                };
                ?>
                <span class="<?= h($osClass); ?> badge-status"><?= h($order['order_status']); ?></span>
              </div>
              <div class="mb-1">
                <strong>Payment:</strong>
                <?= $latestPayment ? h($latestPayment['payment_method']) : '—'; ?> /
                <?php
                $ps = $latestPayment ? $latestPayment['payment_status'] : '—';
                $psClass = match ($ps) {
                  'Paid' => 'status-completed',
                  'Failed' => 'status-cancelled',
                  'Refunded' => 'stock-badge low-stock',
                  default => 'stock-badge'
                };
                ?>
                <span class="<?= h($psClass); ?> badge-status"><?= h($ps); ?></span>
              </div>
              <div class="mb-1"><strong>Total:</strong> <?= moneyINR($order['total_amount']); ?></div>
              <div class="mb-1"><strong>Shipping Method:</strong> <?= $order['shipping_method'] ? h($order['shipping_method']) : '—'; ?></div>
              <div class="mb-1"><strong>Tracking #:</strong> <?= $order['tracking_number'] ? h($order['tracking_number']) : '—'; ?></div>
              <div class="mb-1"><strong>Shipped:</strong> <?= fmtDateShort($order['shipped_date']); ?> | <strong>Delivered:</strong> <?= fmtDateShort($order['delivered_date']); ?></div>
              <?php if (!empty($order['order_notes'])): ?>
                <div class="mt-2"><strong>Notes:</strong><br><?= nl2br(h($order['order_notes'])); ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Customer + addresses -->
        <div class="col-lg-6">
          <div class="card mb-3 theme-card">
            <div class="card-header">Customer</div>
            <div class="card-body">
              <div class="mb-1"><strong>Name:</strong> <?= h($order['first_name'] . ' ' . $order['last_name']); ?></div>
              <div class="mb-1"><strong>Email:</strong> <?= h($order['email']); ?></div>
              <div class="mb-1"><strong>Phone:</strong> <?= h($order['phone']); ?></div>
            </div>
          </div>

          <div class="card mb-3 theme-card">
            <div class="card-header">Shipping Address</div>
            <div class="card-body">
              <div class="mb-1"><strong>Contact:</strong> <?= $order['ship_full_name'] ? h($order['ship_full_name']) : '—'; ?><?= $order['ship_phone'] ? ' · ' . h($order['ship_phone']) : ''; ?></div>
              <div><?= addrLine($order['ship_addr1'], $order['ship_addr2'], $order['ship_city'], $order['ship_state'], $order['ship_zip'], $order['ship_country']); ?></div>
            </div>
          </div>

          <div class="card theme-card">
            <div class="card-header">Billing Address</div>
            <div class="card-body">
              <?php if ($order['bill_addr1'] || $order['bill_addr2'] || $order['bill_city'] || $order['bill_state'] || $order['bill_zip'] || $order['bill_country']): ?>
                <div class="mb-1"><strong>Contact:</strong> <?= $order['bill_full_name'] ? h($order['bill_full_name']) : '—'; ?><?= $order['bill_phone'] ? ' · ' . h($order['bill_phone']) : ''; ?></div>
                <div><?= addrLine($order['bill_addr1'], $order['bill_addr2'], $order['bill_city'], $order['bill_state'], $order['bill_zip'], $order['bill_country']); ?></div>
              <?php else: ?>
                <div>—</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Items -->
      <div class="card mt-3 theme-card">
        <div class="card-header">Items</div>
        <div class="card-body table-responsive table-container">
          <table class="data-table table align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Product</th>
                <th>SKU</th>
                <th class="text-end">Unit Price (₹)</th>
                <th class="text-end">Discount</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Line Total (₹)</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $calcTotal = 0.0;
              $i = 1;
              while ($it = mysqli_fetch_assoc($itemsRes)):
                $calcTotal += (float)$it['total_price'];
              ?>
                <tr>
                  <td><?= $i++; ?></td>
                  <td><?= h($it['product_name']); ?></td>
                  <td><?= h($it['sku']); ?></td>
                  <td class="text-end"><?= number_format((float)$it['unit_price'], 2); ?></td>
                  <td class="text-end"><?= number_format((float)$it['discount'], 2); ?></td>
                  <td class="text-end"><?= (int)$it['quantity']; ?></td>
                  <td class="text-end"><?= number_format((float)$it['total_price'], 2); ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="6" class="text-end">Items Total</td>
                <td class="text-end"><?= moneyINR($calcTotal); ?></td>
              </tr>
              <tr>
                <td colspan="6" class="text-end">Order Total</td>
                <td class="text-end"><?= moneyINR($order['total_amount']); ?></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Payments -->
      <div class="card mt-3 theme-card">
        <div class="card-header">Payments</div>
        <div class="card-body table-responsive table-container">
          <table class="data-table table align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Method</th>
                <th>Txn ID</th>
                <th class="text-end">Amount</th>
                <th>Currency</th>
                <th>Status</th>
                <th>Paid At</th>
                <th>Created</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($payRes) > 0): ?>
                <?php while ($p = mysqli_fetch_assoc($payRes)):
                  $payBadge = match ($p['payment_status']) {
                    'Paid' => 'status-completed',
                    'Failed' => 'status-cancelled',
                    'Refunded' => 'stock-badge low-stock',
                    default => 'stock-badge'
                  };
                ?>
                  <tr>
                    <td><?= (int)$p['payment_id']; ?></td>
                    <td><?= h($p['payment_method']); ?></td>
                    <td><?= h($p['transaction_id']); ?></td>
                    <td class="text-end"><?= number_format((float)$p['amount'], 2); ?></td>
                    <td><?= h($p['currency']); ?></td>
                    <td><span class="<?= h($payBadge); ?> badge-status"><?= h($p['payment_status']); ?></span></td>
                    <td><?= fmtDate($p['paid_at']); ?></td>
                    <td><?= fmtDate($p['created_at']); ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center text-muted">No payments recorded.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Shipment -->
      <div class="card mt-3 mb-4 theme-card">
        <div class="card-header">Latest Shipment</div>
        <div class="card-body">
          <?php if ($shipment): ?>
            <div class="row">
              <div class="col-md-3"><strong>Tracking #</strong><br><?= h($shipment['tracking_number']); ?></div>
              <div class="col-md-3"><strong>Method</strong><br><?= h($shipment['shipping_method']); ?></div>
              <div class="col-md-3"><strong>Status</strong><br><?= h($shipment['status']); ?></div>
              <div class="col-md-3"><strong>Shipped / Delivered</strong><br><?= fmtDate($shipment['shipped_date']); ?> / <?= fmtDate($shipment['delivered_date']); ?></div>
            </div>
          <?php else: ?>
            <div class="text-muted">No shipment created.</div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </main>

  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>