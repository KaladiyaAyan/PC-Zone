<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
  header('Location: ../login.php');
  exit;
}
require_once '../includes/db_connect.php'; // provides $conn (mysqli)

function h($s)
{
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// validate id
$customer_id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;
if ($customer_id <= 0) {
  http_response_code(400);
  echo 'Invalid customer id.';
  exit;
}

// Fetch customer
$stmt = mysqli_prepare($conn, "SELECT customer_id, first_name, last_name, email, phone, date_of_birth, gender, profile_image, newsletter_subscribed, status, created_at, updated_at FROM customers WHERE customer_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$customer = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$customer) {
  http_response_code(404);
  echo 'Customer not found.';
  exit;
}

// Total purchases and orders count
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS orders_count, COALESCE(SUM(total_amount),0) AS total_purchases FROM orders WHERE customer_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $orders_count, $total_purchases);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
$orders_count = (int)$orders_count;
$total_purchases = (float)$total_purchases;

// Addresses
$stmt = mysqli_prepare($conn, "SELECT address_id, full_name, phone, address_line1, address_line2, city, state, zip, country, is_default FROM addresses WHERE customer_id = ? ORDER BY is_default DESC, address_id ASC");
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$addresses_res = mysqli_stmt_get_result($stmt);
$addresses = mysqli_fetch_all($addresses_res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Orders list (basic info)
$stmt = mysqli_prepare($conn, "
  SELECT o.order_id, o.order_date, o.order_status, o.total_amount, o.tracking_number, o.shipping_method, o.paid_at,
         COALESCE(p.payment_status, 'Pending') AS payment_status,
         (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.order_id) AS items_count
  FROM orders o
  LEFT JOIN (
    SELECT order_id, MAX(payment_status) AS payment_status FROM payments GROUP BY order_id
  ) p ON p.order_id = o.order_id
  WHERE o.customer_id = ?
  ORDER BY o.order_date DESC
");
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$orders_res = mysqli_stmt_get_result($stmt);
$orders = mysqli_fetch_all($orders_res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// For each order fetch items, payments and shipments lazily (we'll prepare statements to reuse)
$stmt_items = mysqli_prepare($conn, "SELECT oi.order_item_id, oi.product_id, p.product_name, oi.quantity, oi.unit_price, oi.discount, oi.total_price FROM order_items oi LEFT JOIN products p ON p.product_id = oi.product_id WHERE oi.order_id = ?");
$stmt_payments = mysqli_prepare($conn, "SELECT payment_id, payment_method, transaction_id, amount, currency, payment_status, paid_at, created_at FROM payments WHERE order_id = ? ORDER BY created_at DESC");
$stmt_shipments = mysqli_prepare($conn, "SELECT shipment_id, tracking_number, shipping_method, shipped_date, delivered_date, status FROM shipments WHERE order_id = ? ORDER BY created_at DESC");

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Customer Profile - PCZone Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
  <?php
  $current_page = 'customers';
  include '../includes/header.php';
  include '../includes/sidebar.php';
  ?>
  <div class="main-content pt-5 mt-4">
    <div class="container mt-2 customer-profile">


      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <h3 class="mb-0"><?= h($customer['first_name'] . ' ' . $customer['last_name']) ?></h3>
          <small class="text-muted">Customer since <?= h(date('d M Y', strtotime($customer['created_at']))) ?></small>
        </div>
        <div class="text-end">
          <a href="customers.php" class="btn btn-sm btn-secondary">Back to list</a>
          <a href="edit_customer.php?id=<?= (int)$customer['customer_id'] ?>" class="btn btn-sm btn-edit">Edit</a>
        </div>
      </div>

      <div class="row ">
        <div class="col-md-4">
          <div class="card p-3 mb-3">
            <div class="text-center">
              <?php if (!empty($customer['profile_image']) && file_exists('../uploads/' . $customer['profile_image'])): ?>
                <img src="../uploads/<?= h($customer['profile_image']) ?>" alt="profile" class="img-fluid rounded mb-2" style="max-height:160px">
              <?php else: ?>
                <div class="avatar-placeholder rounded-circle mb-2" style="width:120px;height:120px;line-height:120px;margin:0 auto;font-size:28px;">
                  <?= strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)) ?>
                </div>

              <?php endif; ?>
              <h5 class="mt-2"><?= h($customer['first_name'] . ' ' . $customer['last_name']) ?></h5>
              <p class="mb-0"><?= h($customer['email']) ?></p>
              <p class="mb-0"><?= h($customer['phone'] ?: '—') ?></p>
              <p class="mb-0">Status: <strong><?= h(ucfirst($customer['status'])) ?></strong></p>
            </div>
            <hr>
            <div>
              <p class="mb-1"><strong>Orders:</strong> <?= $orders_count ?></p>
              <p class="mb-1"><strong>Total Purchases:</strong> ₹ <?= number_format($total_purchases, 2) ?></p>
              <p class="mb-1"><strong>Newsletter:</strong> <?= $customer['newsletter_subscribed'] ? 'Subscribed' : 'No' ?></p>
              <p class="mb-0"><strong>DOB:</strong> <?= $customer['date_of_birth'] ? h($customer['date_of_birth']) : '—' ?></p>
            </div>
          </div>

          <div class="card p-3">
            <h6>Addresses</h6>
            <?php if (count($addresses) === 0): ?>
              <p class="text-muted">No addresses available.</p>
            <?php else: ?>
              <?php foreach ($addresses as $addr): ?>
                <div class="address-block mb-2">
                  <div><strong><?= h($addr['full_name']) ?></strong> <?= $addr['is_default'] ? '<span class="badge bg-success ms-1">Default</span>' : '' ?></div>
                  <div class="text-muted small"><?= h($addr['address_line1']) ?> <?= h($addr['address_line2']) ?></div>
                  <div class="text-muted small"><?= h($addr['city']) ?>, <?= h($addr['state']) ?> <?= h($addr['zip']) ?>, <?= h($addr['country']) ?></div>
                  <div class="text-muted small">Phone: <?= h($addr['phone'] ?: '—') ?></div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

        </div>

        <div class="col-md-8 ">
          <div class="card p-3 mb-3">
            <h5>Order History</h5>
            <?php if (count($orders) === 0): ?>
              <p class="text-muted">No orders placed yet.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-sm table-hover">
                  <thead>
                    <tr class="data-table">
                      <th>Order #</th>
                      <th>Date</th>
                      <th>Items</th>
                      <th>Total</th>
                      <th>Payment</th>
                      <th>Shipping</th>
                      <th>Status</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($orders as $ord): ?>
                      <tr class="data-table">
                        <td>#<?= (int)$ord['order_id'] ?></td>
                        <td><?= h(date('d M Y, H:i', strtotime($ord['order_date']))) ?></td>
                        <td><?= (int)$ord['items_count'] ?></td>
                        <td>₹ <?= number_format((float)$ord['total_amount'], 2) ?></td>
                        <td><?= h($ord['payment_status']) ?><?= $ord['paid_at'] ? ' <small class="text-muted">(' . h(date('d M Y', strtotime($ord['paid_at']))) . ')</small>' : '' ?></td>
                        <td><?= $ord['tracking_number'] ? h($ord['tracking_number']) : '—' ?></td>
                        <td><?= h($ord['order_status']) ?></td>
                        <td class="text-end">
                          <a href="order_view.php?id=<?= (int)$ord['order_id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                          <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#items-<?= (int)$ord['order_id'] ?>">Items</button>
                        </td>
                      </tr>
                      <tr class="collapse-row">
                        <td colspan="8" class="p-0">
                          <div class="collapse" id="items-<?= (int)$ord['order_id'] ?>">
                            <div class="p-3">
                              <?php
                              // items for this order
                              mysqli_stmt_bind_param($stmt_items, 'i', $ord['order_id']);
                              mysqli_stmt_execute($stmt_items);
                              $items_res = mysqli_stmt_get_result($stmt_items);
                              $items = mysqli_fetch_all($items_res, MYSQLI_ASSOC);
                              ?>
                              <?php if (count($items) === 0): ?>
                                <div class="text-muted">No items found for this order.</div>
                              <?php else: ?>
                                <table class="table table-sm mb-0">
                                  <thead>
                                    <tr>
                                      <th>Product</th>
                                      <th>Qty</th>
                                      <th>Unit</th>
                                      <th>Discount</th>
                                      <th>Total</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php foreach ($items as $it): ?>
                                      <tr>
                                        <td><?= h($it['product_name'] ?: 'Product #' . $it['product_id']) ?></td>
                                        <td><?= (int)$it['quantity'] ?></td>
                                        <td>₹ <?= number_format((float)$it['unit_price'], 2) ?></td>
                                        <td>₹ <?= number_format((float)$it['discount'], 2) ?></td>
                                        <td>₹ <?= number_format((float)$it['total_price'], 2) ?></td>
                                      </tr>
                                    <?php endforeach; ?>
                                  </tbody>
                                </table>
                              <?php endif; ?>

                              <?php
                              // payments
                              mysqli_stmt_bind_param($stmt_payments, 'i', $ord['order_id']);
                              mysqli_stmt_execute($stmt_payments);
                              $p_res = mysqli_stmt_get_result($stmt_payments);
                              $payments = mysqli_fetch_all($p_res, MYSQLI_ASSOC);
                              ?>
                              <?php if (count($payments) > 0): ?>
                                <div class="mt-2">
                                  <strong>Payments</strong>
                                  <table class="table table-sm mb-0">
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
                                          <td><?= h($p['payment_method']) ?></td>
                                          <td><?= h($p['transaction_id'] ?: '—') ?></td>
                                          <td>₹ <?= number_format((float)$p['amount'], 2) . ' ' . h($p['currency']) ?></td>
                                          <td><?= h($p['payment_status']) ?></td>
                                          <td><?= h($p['paid_at'] ?: $p['created_at']) ?></td>
                                        </tr>
                                      <?php endforeach; ?>
                                    </tbody>
                                  </table>
                                </div>
                              <?php endif; ?>

                              <?php
                              // shipments
                              mysqli_stmt_bind_param($stmt_shipments, 'i', $ord['order_id']);
                              mysqli_stmt_execute($stmt_shipments);
                              $s_res = mysqli_stmt_get_result($stmt_shipments);
                              $ships = mysqli_fetch_all($s_res, MYSQLI_ASSOC);
                              ?>
                              <?php if (count($ships) > 0): ?>
                                <div class="mt-2">
                                  <strong>Shipments</strong>
                                  <table class="table table-sm mb-0">
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
                                          <td><?= h($sh['shipping_method']) ?></td>
                                          <td><?= h($sh['tracking_number']) ?></td>
                                          <td><?= h($sh['shipped_date'] ?: '—') ?></td>
                                          <td><?= h($sh['delivered_date'] ?: '—') ?></td>
                                          <td><?= h($sh['status']) ?></td>
                                        </tr>
                                      <?php endforeach; ?>
                                    </tbody>
                                  </table>
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
  </div>

  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
// cleanup
mysqli_stmt_close($stmt_items);
mysqli_stmt_close($stmt_payments);
mysqli_stmt_close($stmt_shipments);
?>