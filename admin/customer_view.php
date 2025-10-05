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
$customer_q = mysqli_query(
  $conn,
  "SELECT user_id, username, email, phone, date_of_birth, gender, status, created_at, updated_at, NULL AS profile_image
       FROM users WHERE user_id = $customer_id"
);
$customer = $customer_q ? mysqli_fetch_assoc($customer_q) : null;
if (!$customer) die('Customer not found.');

// Get order statistics (total spent across all order rows)
$totals_q = mysqli_query(
  $conn,
  "SELECT COUNT(*) AS orders_count, COALESCE(SUM(total_price),0) AS total_purchases 
       FROM orders WHERE user_id = $customer_id"
);
$totals = $totals_q ? mysqli_fetch_assoc($totals_q) : ['orders_count' => 0, 'total_purchases' => 0];
$orders_count = (int)($totals['orders_count'] ?? 0);
$total_purchases = (float)($totals['total_purchases'] ?? 0);

// Get addresses
$addresses_res = mysqli_query(
  $conn,
  "SELECT address_id, full_name, phone, address_line1, address_line2, city, state, zip, country, is_default 
       FROM user_address WHERE user_id = $customer_id ORDER BY is_default DESC, address_id ASC"
);
$addresses = $addresses_res ? mysqli_fetch_all($addresses_res, MYSQLI_ASSOC) : [];

$orders_sql = "
  SELECT 
    MIN(o.order_id) AS group_order_id,
    o.created_at AS order_date,
    SUM(o.total_price) AS total_price,
    COUNT(*) AS items_count,
    GROUP_CONCAT(o.order_id) AS order_ids,
    GROUP_CONCAT(DISTINCT p.payment_method) AS payment_methods,
    GROUP_CONCAT(DISTINCT p.payment_status) AS payment_statuses,
    MAX(p.created_at) AS last_payment_at
  FROM orders o
  LEFT JOIN payments p ON p.order_id = o.order_id
  WHERE o.user_id = $customer_id
  GROUP BY o.created_at
  ORDER BY o.created_at DESC
";

$orders_res = mysqli_query($conn, $orders_sql);
$grouped_orders = $orders_res ? mysqli_fetch_all($orders_res, MYSQLI_ASSOC) : [];

// No prepared statements for single-id queries here.
// We'll fetch items and payments for each transaction using the order_ids produced above.

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
      font-size: 13px
    }

    .meta-row .col-6 {
      padding-bottom: 6px
    }

    .product-row[role="button"] {
      cursor: pointer
    }

    /* Add a hover effect to the main, clickable transaction rows */
    .product-table>tbody>tr.product-row:hover {
      background-color: var(--hover-bg);
    }

    /* ----- Order transaction spacing / visual polish ----- */
    .product-table th,
    .product-table td {
      padding: 14px 18px;
    }

    .product-table>tbody>tr.product-row td {
      padding-top: 18px;
      padding-bottom: 18px;
    }

    .product-table>tbody>tr:not(.product-row) td {
      padding-top: 12px;
      padding-bottom: 18px;
      background-color: var(--bg-section);
    }

    /* Increase padding inside the collapse details panel */
    .product-table .collapse .p-3 {
      padding: 18px 20px;
    }

    /* Add a soft divider and rounded detail box to improve readability */
    .product-table>tbody>tr.product-row {
      border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    }

    .product-table .collapse .table {
      border-radius: 6px;
      overflow: hidden;
      border: 1px solid var(--border-color);
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
        <div class="col-lg-3">
          <div class="card theme-card p-3 mb-3">
            <div class="card-body text-center d-flex flex-column align-items-center p-0">
              <?php if (!empty($customer['profile_image']) && file_exists('../uploads/' . $customer['profile_image'])) : ?>
                <img src="../uploads/<?= e($customer['profile_image']) ?>" alt="profile" class="avatar-lg mb-2" style="object-fit:cover;">
              <?php else : ?>
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
                  <div class="fw-bold"><?= count($grouped_orders) ?></div>
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
            <?php if (empty($addresses)) : ?>
              <p class="text-muted mb-0">No addresses available.</p>
            <?php else : ?>
              <?php foreach ($addresses as $addr) : ?>
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
        </div>

        <div class="col-lg-9">
          <div class="card theme-card p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h5 class="mb-0">Order History</h5>
              <small class="muted-small"><?= count($grouped_orders) ?> orders</small>
            </div>

            <?php if (empty($grouped_orders)) : ?>
              <p class="text-muted mb-0">No orders placed yet.</p>
            <?php else : ?>
              <div class="table-container">
                <table class="product-table">
                  <thead>
                    <tr>
                      <th>Order #</th>
                      <th>Date</th>
                      <th>Items</th>
                      <th>Total</th>
                      <th>Payment Method</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($grouped_orders as $ord) :
                      // order_ids is a comma-separated list like "12,13,14"
                      $order_ids_raw = $ord['order_ids'] ?? '';
                      // sanitize ids, produce CSV of ints
                      $ids = array_filter(array_map(function ($v) {
                        return intval($v);
                      }, explode(',', $order_ids_raw)));
                      $ids_sql = $ids ? implode(',', $ids) : '0';

                      // fetch items belonging to these order rows
                      $items_sql = "
                        SELECT o.order_id, o.product_id, COALESCE(p.product_name, '') AS product_name, o.quantity, o.unit_price, o.discount, o.total_price
                        FROM orders o
                        LEFT JOIN products p ON p.product_id = o.product_id
                        WHERE o.order_id IN ($ids_sql)
                        ORDER BY o.order_id ASC
                      ";
                      $items_res = mysqli_query($conn, $items_sql);
                      $items = $items_res ? mysqli_fetch_all($items_res, MYSQLI_ASSOC) : [];

                      // fetch payments for this transaction (any payments linked to any order row in the group)
                      $payments_sql = "
                        SELECT payment_id, payment_method, transaction_id, amount, currency, payment_status, paid_at, created_at
                        FROM payments
                        WHERE order_id IN ($ids_sql)
                        ORDER BY created_at DESC
                      ";
                      $payments_res = mysqli_query($conn, $payments_sql);
                      $payments = $payments_res ? mysqli_fetch_all($payments_res, MYSQLI_ASSOC) : [];

                      // choose a display payment method/status if available (first payment)
                      $display_payment_method = null;
                      $display_payment_status = 'Pending';
                      if (!empty($ord['payment_methods'])) {
                        $methods = explode(',', $ord['payment_methods']);
                        $display_payment_method = trim($methods[0]) ?: null;
                      }
                      if (!empty($ord['payment_statuses'])) {
                        $statuses = explode(',', $ord['payment_statuses']);
                        $display_payment_status = trim($statuses[0]) ?: 'Pending';
                      }
                    ?>
                      <tr class="product-row" role="button" data-group-id="<?= e($ord['group_order_id']) ?>" title="Click row to toggle details">
                        <td>#<?= e($ord['group_order_id']) ?></td>
                        <td><?= e(date('d M Y, H:i', strtotime($ord['order_date']))) ?></td>
                        <td><?= (int)$ord['items_count'] ?></td>
                        <td>₹ <?= number_format((float)$ord['total_price'], 2) ?></td>
                        <td><?= e(ucfirst(str_replace('_', ' ', $display_payment_method ?? 'Unknown'))) ?></td>
                        <td><span class="status-<?= strtolower(e($display_payment_status)) ?>"><?= e(ucfirst(strtolower($display_payment_status))) ?></span></td>
                      </tr>

                      <tr>
                        <td colspan="8" class="p-0">
                          <div class="collapse" id="details-<?= e($ord['group_order_id']) ?>">
                            <div class="p-3">
                              <div class="mb-3">
                                <div class="muted-small mb-1"><strong>Items</strong></div>
                                <?php if (empty($items)) : ?>
                                  <div class="text-muted small">No items found.</div>
                                <?php else : ?>
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
                                        <?php foreach ($items as $it) : ?>
                                          <tr>
                                            <td><?= e($it['product_name'] ?: 'Product #' . $it['product_id']) ?></td>
                                            <td><?= (int)$it['quantity'] ?></td>
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

                              <?php if (!empty($payments)) : ?>
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
                                        <?php foreach ($payments as $p) : ?>
                                          <tr>
                                            <td><?= e($p['payment_method']) ?></td>
                                            <td><?= e($p['transaction_id'] ?? '—') ?></td>
                                            <td>₹ <?= number_format((float)$p['amount'], 2) ?> <?= e($p['currency']) ?></td>
                                            <td><?= e($p['payment_status']) ?></td>
                                            <td><?= e(date('Y-m-d H:i:s', strtotime($p['paid_at'] ?? $p['created_at']))) ?></td>
                                          </tr>
                                        <?php endforeach; ?>
                                      </tbody>
                                    </table>
                                  </div>
                                </div>
                              <?php endif; ?>

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
    document.addEventListener('click', function(e) {
      const row = e.target.closest('.product-row[role="button"]');
      if (!row) return;

      const groupId = row.getAttribute('data-group-id');
      const detailsEl = document.getElementById('details-' + groupId);
      if (detailsEl) {
        new bootstrap.Collapse(detailsEl).toggle();
      }
    });
  </script>
</body>

</html>