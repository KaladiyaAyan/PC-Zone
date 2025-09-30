<?php
session_start();
require('../includes/db_connect.php');
require('../includes/functions.php');

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ../login.php');
  exit;
}

// Validate customer ID
$customer_id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;
if ($customer_id <= 0) {
  die('Invalid customer ID.');
}

// Fetch customer data
$customer = mysqli_fetch_assoc(mysqli_query(
  $conn,
  "SELECT user_id, username, email, phone, date_of_birth, gender, status, created_at, updated_at 
     FROM users WHERE user_id = $customer_id"
));
if (!$customer) die('Customer not found.');

// Get order statistics
$totals = mysqli_fetch_assoc(mysqli_query(
  $conn,
  "SELECT COUNT(*) AS orders_count, COALESCE(SUM(total_amount),0) AS total_purchases 
     FROM orders WHERE user_id = $customer_id"
));
$orders_count = (int)($totals['orders_count'] ?? 0);
$total_purchases = (float)($totals['total_purchases'] ?? 0);

// Get addresses
$addresses_res = mysqli_query(
  $conn,
  "SELECT address_id, full_name, phone, address_line1, address_line2, city, state, zip, country, is_default 
     FROM user_address WHERE user_id = $customer_id ORDER BY is_default DESC, address_id ASC"
);
$addresses = mysqli_fetch_all($addresses_res, MYSQLI_ASSOC);

// Get orders with related data
$orders_res = mysqli_query(
  $conn,
  "SELECT o.order_id, o.order_date, o.order_status, o.total_amount, o.tracking_number, 
            o.shipping_method, o.paid_at, o.order_notes, o.cancelled_at, o.refunded_at, 
            o.shipped_date, o.delivered_date,
            COALESCE(p.payment_status,'Pending') AS payment_status,
            (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.order_id) AS items_count,
            b.full_name AS bill_name, b.address_line1 AS bill_line1, b.city AS bill_city, 
            b.state AS bill_state, b.zip AS bill_zip, b.country AS bill_country,
            s.full_name AS ship_name, s.address_line1 AS ship_line1, s.city AS ship_city, 
            s.state AS ship_state, s.zip AS ship_zip, s.country AS ship_country
     FROM orders o
     LEFT JOIN (SELECT order_id, MAX(payment_status) AS payment_status FROM payments GROUP BY order_id) p ON p.order_id = o.order_id
     LEFT JOIN user_address b ON b.address_id = o.billing_address_id
     LEFT JOIN user_address s ON s.address_id = o.shipping_address_id
     WHERE o.user_id = $customer_id
     ORDER BY o.order_date DESC"
);
$orders = mysqli_fetch_all($orders_res, MYSQLI_ASSOC);

// Prepare statements for detail data
$stmt_items = mysqli_prepare(
  $conn,
  "SELECT oi.order_item_id, oi.product_id, COALESCE(p.product_name, '') AS product_name, 
            oi.quantity, oi.unit_price, oi.discount, oi.total_price 
     FROM order_items oi 
     LEFT JOIN products p ON p.product_id = oi.product_id 
     WHERE oi.order_id = ?"
);
$stmt_payments = mysqli_prepare(
  $conn,
  "SELECT payment_id, payment_method, transaction_id, amount, currency, 
            payment_status, paid_at, created_at 
     FROM payments WHERE order_id = ? ORDER BY created_at DESC"
);
$stmt_shipments = mysqli_prepare(
  $conn,
  "SELECT shipment_id, tracking_number, shipping_method, shipped_date, 
            delivered_date, status, created_at 
     FROM shipments WHERE order_id = ? ORDER BY created_at DESC"
);
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Customer Profile • PCZone Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <?php include './includes/header-link.php'; ?>
  <style>
    .avatar-lg {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      background: var(--bg-section);
      color: var(--text-muted);
      border: 1px solid var(--border-color);
    }

    .stat-box {
      border-radius: 6px;
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      padding: 10px;
    }

    .muted-small {
      color: var(--text-muted);
      font-size: 13px;
    }

    .meta-row .col-6 {
      padding-bottom: 6px;
    }

    /* make order row clearly clickable but keep existing hover */
    .product-row[role="button"] {
      cursor: pointer;
    }
  </style>
</head>

<body>
  <?php require('./includes/alert.php'); ?>
  <?php
  $current_page = 'customers';
  include './includes/header.php';
  include './includes/sidebar.php';
  ?>

  <main class="main-content pt-5 mt-4">
    <div class="container mt-2 customer-profile">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <h3 class="mb-0"><?= e($customer['username']) ?></h3>
          <small class="text-muted">Customer since <?= e(date('d M Y', strtotime($customer['created_at']))) ?></small>
        </div>
        <div class="text-end">
          <a href="customers.php" class="btn btn-sm btn-secondary">Back to list</a>
        </div>
      </div>

      <div class="row gy-3">
        <!-- Left Column: Profile & Addresses -->
        <div class="col-lg-3">
          <div class="card theme-card p-3 mb-3">
            <div class="card-body text-center d-flex flex-column align-items-center p-0">
              <?php if (!empty($customer['profile_image']) && file_exists('../uploads/' . $customer['profile_image'])): ?>
                <img src="../uploads/<?= e($customer['profile_image']) ?>" alt="profile" class="avatar-lg mb-2" style="object-fit:cover;">
              <?php else: ?>
                <div class="avatar-lg mb-2"><?= strtoupper(substr($customer['username'], 0, 1)) ?></div>
              <?php endif; ?>

              <h5 class="mt-1 mb-0"><?= e($customer['username']) ?></h5>
              <div class="muted-small"><?= e($customer['email']) ?></div>
              <div class="muted-small mb-2"><?= e($customer['phone'] ?: '—') ?></div>

              <div class="d-flex justify-content-center gap-2 mt-2">
                <span class="stock-badge <?= $customer['status'] === 'active' ? 'in-stock' : ($customer['status'] === 'inactive' ? 'low-stock' : 'out-of-stock') ?>">
                  <?= e(ucfirst($customer['status'])) ?>
                </span>
              </div>
            </div>

            <hr>
            <div class="row meta-row">
              <div class="col-6">
                <div class="stat-box text-center">
                  <div class="muted-small">Orders</div>
                  <div class="fw-bold"><?= $orders_count ?></div>
                </div>
              </div>
              <div class="col-6">
                <div class="stat-box text-center">
                  <div class="muted-small">Total Spent</div>
                  <div class="fw-bold">₹ <?= number_format($total_purchases, 2) ?></div>
                </div>
              </div>
              <div class="col-12 mt-2">
                <div class="stat-box">
                  <div class="muted-small">Date of Birth</div>
                  <div class="fw-bold"><?= $customer['date_of_birth'] ? e($customer['date_of_birth']) : '—' ?></div>
                </div>
              </div>
            </div>
          </div>

          <div class="card theme-card p-3 mb-3">
            <div class="card-header mb-2">Addresses</div>
            <?php if (empty($addresses)): ?>
              <p class="text-muted mb-0">No addresses available.</p>
            <?php else: ?>
              <?php foreach ($addresses as $addr): ?>
                <div class="address-block mb-2 p-2">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <strong><?= e($addr['full_name']) ?></strong>
                      <div class="muted-small"><?= e($addr['phone'] ?: '—') ?></div>
                    </div>
                    <?= $addr['is_default'] ? '<span class="badge bg-success">Default</span>' : '' ?>
                  </div>
                  <div class="muted-small small mt-1"><?= e($addr['address_line1']) ?> <?= e($addr['address_line2']) ?></div>
                  <div class="muted-small small"><?= e($addr['city']) ?>, <?= e($addr['state']) ?> <?= e($addr['zip']) ?>, <?= e($addr['country']) ?></div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <div class="card theme-card p-3">
            <div class="card-header mb-2">Account</div>
            <div class="muted-small mb-1"><strong>Created</strong> <?= e($customer['created_at']) ?></div>
            <div class="muted-small mb-1"><strong>Last updated</strong> <?= e($customer['updated_at']) ?></div>
            <div class="muted-small"><strong>Gender</strong> <?= e($customer['gender'] ?: '—') ?></div>
          </div>
        </div>

        <!-- Right Column: Order History -->
        <div class="col-lg-9">
          <div class="card theme-card p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h5 class="mb-0">Order History</h5>
              <small class="muted-small"><?= count($orders) ?> orders</small>
            </div>

            <?php if (empty($orders)): ?>
              <p class="text-muted mb-0">No orders placed yet.</p>
            <?php else: ?>
              <div class="table-container">
                <table class="product-table">
                  <thead>
                    <tr>
                      <th>Order #</th>
                      <th>Date</th>
                      <th>Items</th>
                      <th>Total</th>
                      <th>Payment</th>
                      <th>Shipping</th>
                      <th>Status</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($orders as $ord): ?>
                      <tr class="product-row" role="button" data-order-id="<?= $ord['order_id'] ?>" title="Click row to toggle details">
                        <td>#<?= $ord['order_id'] ?></td>
                        <td><?= e(date('d M Y, H:i', strtotime($ord['order_date']))) ?></td>
                        <td><?= $ord['items_count'] ?></td>
                        <td>₹ <?= number_format((float)$ord['total_amount'], 2) ?></td>
                        <td><?= e($ord['payment_status']) ?><?= $ord['paid_at'] ? ' <small class="text-muted">(' . e(date('d M Y', strtotime($ord['paid_at']))) . ')</small>' : '' ?></td>
                        <td><?= $ord['tracking_number'] ? e($ord['tracking_number']) : '—' ?></td>
                        <td><span class="status-<?= strtolower($ord['order_status']) ?>"><?= e($ord['order_status']) ?></span></td>
                        <td class="text-end">
                          <a href="order_view.php?id=<?= $ord['order_id'] ?>" class="btn-add open-order">Open</a>
                        </td>
                      </tr>

                      <tr>
                        <td colspan="8" class="p-0">
                          <div class="collapse" id="details-<?= $ord['order_id'] ?>">
                            <div class="p-3">
                              <!-- Billing/Shipping -->
                              <div class="row mb-2">
                                <div class="col-md-6">
                                  <div class="muted-small">Billing Address</div>
                                  <div class="small"><?= e($ord['bill_name'] ?: '—') ?></div>
                                  <div class="muted-small small"><?= e($ord['bill_line1'] ?: '') ?><?= $ord['bill_city'] ? ', ' . e($ord['bill_city']) : '' ?><?= $ord['bill_state'] ? ', ' . e($ord['bill_state']) : '' ?><?= $ord['bill_zip'] ? ' - ' . e($ord['bill_zip']) : '' ?></div>
                                  <div class="muted-small small"><?= e($ord['bill_country'] ?: '') ?></div>
                                </div>
                                <div class="col-md-6">
                                  <div class="muted-small">Shipping Address</div>
                                  <div class="small"><?= e($ord['ship_name'] ?: '—') ?></div>
                                  <div class="muted-small small"><?= e($ord['ship_line1'] ?: '') ?><?= $ord['ship_city'] ? ', ' . e($ord['ship_city']) : '' ?><?= $ord['ship_state'] ? ', ' . e($ord['ship_state']) : '' ?><?= $ord['ship_zip'] ? ' - ' . e($ord['ship_zip']) : '' ?></div>
                                  <div class="muted-small small"><?= e($ord['ship_country'] ?: '') ?></div>
                                </div>
                              </div>

                              <!-- Order Items -->
                              <?php
                              mysqli_stmt_bind_param($stmt_items, 'i', $ord['order_id']);
                              mysqli_stmt_execute($stmt_items);
                              $items = mysqli_fetch_all(mysqli_stmt_get_result($stmt_items), MYSQLI_ASSOC);
                              ?>
                              <div class="mb-3">
                                <div class="muted-small mb-1"><strong>Items</strong></div>
                                <?php if (empty($items)): ?>
                                  <div class="text-muted small">No items found.</div>
                                <?php else: ?>
                                  <div class="table-container">
                                    <table class="product-table table">
                                      <thead>
                                        <tr>
                                          <th>Product</th>
                                          <th>Qty</th>
                                          <th>Unit</th>
                                          <th>Discount</th>
                                          <th>Line Total</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        <?php foreach ($items as $it): ?>
                                          <tr>
                                            <td><?= e($it['product_name'] ?: 'Product #' . $it['product_id']) ?></td>
                                            <td><?= $it['quantity'] ?></td>
                                            <td>₹ <?= number_format((float)$it['unit_price'], 2) ?></td>
                                            <td>₹ <?= number_format((float)$it['discount'], 2) ?></td>
                                            <td>₹ <?= number_format((float)$it['total_price'], 2) ?></td>
                                          </tr>
                                        <?php endforeach; ?>
                                      </tbody>
                                    </table>
                                  </div>
                                <?php endif; ?>
                              </div>

                              <!-- Payments -->
                              <?php
                              mysqli_stmt_bind_param($stmt_payments, 'i', $ord['order_id']);
                              mysqli_stmt_execute($stmt_payments);
                              $payments = mysqli_fetch_all(mysqli_stmt_get_result($stmt_payments), MYSQLI_ASSOC);
                              ?>
                              <?php if (!empty($payments)): ?>
                                <div class="mb-3">
                                  <div class="muted-small mb-1"><strong>Payments</strong></div>
                                  <div class="table-container">
                                    <table class="product-table table">
                                      <thead>
                                        <tr>
                                          <th>Method</th>
                                          <th>Txn</th>
                                          <th>Amount</th>
                                          <th>Status</th>
                                          <th>Date</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        <?php foreach ($payments as $p): ?>
                                          <tr>
                                            <td><?= e($p['payment_method']) ?></td>
                                            <td><?= e($p['transaction_id'] ?: '—') ?></td>
                                            <td>₹ <?= number_format((float)$p['amount'], 2) ?> <?= e($p['currency']) ?></td>
                                            <td><?= e($p['payment_status']) ?></td>
                                            <td><?= e($p['paid_at'] ?: $p['created_at']) ?></td>
                                          </tr>
                                        <?php endforeach; ?>
                                      </tbody>
                                    </table>
                                  </div>
                                </div>
                              <?php endif; ?>

                              <!-- Shipments -->
                              <?php
                              mysqli_stmt_bind_param($stmt_shipments, 'i', $ord['order_id']);
                              mysqli_stmt_execute($stmt_shipments);
                              $ships = mysqli_fetch_all(mysqli_stmt_get_result($stmt_shipments), MYSQLI_ASSOC);
                              ?>
                              <?php if (!empty($ships)): ?>
                                <div class="mb-3">
                                  <div class="muted-small mb-1"><strong>Shipments</strong></div>
                                  <div class="table-container">
                                    <table class="product-table table">
                                      <thead>
                                        <tr>
                                          <th>Method</th>
                                          <th>Tracking</th>
                                          <th>Shipped</th>
                                          <th>Delivered</th>
                                          <th>Status</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        <?php foreach ($ships as $sh): ?>
                                          <tr>
                                            <td><?= e($sh['shipping_method']) ?></td>
                                            <td><?= e($sh['tracking_number']) ?></td>
                                            <td><?= e($sh['shipped_date'] ?: '—') ?></td>
                                            <td><?= e($sh['delivered_date'] ?: '—') ?></td>
                                            <td><?= e($sh['status']) ?></td>
                                          </tr>
                                        <?php endforeach; ?>
                                      </tbody>
                                    </table>
                                  </div>
                                </div>
                              <?php endif; ?>

                              <!-- Order Meta -->
                              <div class="muted-small">
                                <div><strong>Order Notes</strong> <?= $ord['order_notes'] ? '<div class="small mt-1">' . e($ord['order_notes']) . '</div>' : '<span class="small text-muted">—</span>' ?></div>
                                <div class="small mt-2"><strong>Paid at</strong> <?= e($ord['paid_at'] ?: '—') ?></div>
                                <div class="small"><strong>Cancelled</strong> <?= e($ord['cancelled_at'] ?: '—') ?></div>
                                <div class="small"><strong>Refunded</strong> <?= e($ord['refunded_at'] ?: '—') ?></div>
                                <div class="small"><strong>Shipped</strong> <?= e($ord['shipped_date'] ?: '—') ?> <strong class="ms-2">Delivered</strong> <?= e($ord['delivered_date'] ?: '—') ?></div>
                              </div>
                            </div>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php require('./includes/footer-link.php') ?>
  <script>
    // Toggle order details
    document.addEventListener('click', function(e) {
      const row = e.target.closest('.product-row[role="button"]');
      if (!row) return;
      if (e.target.closest('a, button, input, select')) return;

      const orderId = row.getAttribute('data-order-id');
      const detailsEl = document.getElementById('details-' + orderId);
      if (detailsEl) {
        new bootstrap.Collapse(detailsEl).toggle();
      }
    });

    // Prevent open link from toggling collapse
    document.querySelectorAll('.open-order').forEach(function(el) {
      el.addEventListener('click', function(ev) {
        ev.stopPropagation();
      });
    });
  </script>
</body>

</html>

<?php
// Cleanup
mysqli_stmt_close($stmt_items);
mysqli_stmt_close($stmt_payments);
mysqli_stmt_close($stmt_shipments);
?>