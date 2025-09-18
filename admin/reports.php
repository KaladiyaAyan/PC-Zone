<?php
include '../includes/db_connect.php';
include './includes/functions.php';

session_start();
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ./login.php');
  exit;
}

function h($s)
{
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// page marker for sidebar
$current_page = 'reports';

// date filters (default: last 30 days)
$from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$to = $_GET['to'] ?? date('Y-m-d');
// normalize to full-day range
$from_dt = $from . ' 00:00:00';
$to_dt = $to . ' 23:59:59';

// 1) Sales summary (orders count + revenue)
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS orders_count, COALESCE(SUM(total_amount),0) AS revenue FROM orders WHERE order_date BETWEEN ? AND ?");
mysqli_stmt_bind_param($stmt, 'ss', $from_dt, $to_dt);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$summ = mysqli_fetch_assoc($res) ?: ['orders_count' => 0, 'revenue' => 0];
mysqli_stmt_close($stmt);

// 2) Orders by day
$stmt = mysqli_prepare($conn, "SELECT DATE(order_date) AS day, COUNT(*) AS orders_count, COALESCE(SUM(total_amount),0) AS revenue FROM orders WHERE order_date BETWEEN ? AND ? GROUP BY day ORDER BY day ASC");
mysqli_stmt_bind_param($stmt, 'ss', $from_dt, $to_dt);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$orders_by_day = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// 3) Revenue by category (top 10)
$stmt = mysqli_prepare($conn, "SELECT c.category_id, c.category_name, COALESCE(SUM(oi.total_price),0) AS revenue
  FROM order_items oi
  JOIN products p ON p.product_id = oi.product_id
  JOIN categories c ON c.category_id = p.category_id
  JOIN orders o ON o.order_id = oi.order_id
  WHERE o.order_date BETWEEN ? AND ?
  GROUP BY c.category_id
  ORDER BY revenue DESC
  LIMIT 10");
mysqli_stmt_bind_param($stmt, 'ss', $from_dt, $to_dt);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$by_category = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// 4) Top products (by quantity sold)
$stmt = mysqli_prepare($conn, "SELECT p.product_id, p.product_name, COALESCE(SUM(oi.quantity),0) AS qty_sold, COALESCE(SUM(oi.total_price),0) AS revenue
  FROM order_items oi
  JOIN products p ON p.product_id = oi.product_id
  JOIN orders o ON o.order_id = oi.order_id
  WHERE o.order_date BETWEEN ? AND ?
  GROUP BY p.product_id
  ORDER BY qty_sold DESC
  LIMIT 10");
mysqli_stmt_bind_param($stmt, 'ss', $from_dt, $to_dt);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$top_products = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// 5) Payments summary by status
$stmt = mysqli_prepare($conn, "SELECT payment_status, COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS total_amount FROM payments p JOIN orders o ON o.order_id = p.order_id WHERE p.created_at BETWEEN ? AND ? GROUP BY payment_status");
mysqli_stmt_bind_param($stmt, 'ss', $from_dt, $to_dt);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$payments_summary = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// 6) Top customers
$stmt = mysqli_prepare($conn, "SELECT u.user_id, u.username AS name, u.email, COUNT(o.order_id) AS orders_count, COALESCE(SUM(o.total_amount),0) AS total_spent
  FROM orders o
  JOIN users u ON u.user_id = o.user_id
  WHERE o.order_date BETWEEN ? AND ?
  GROUP BY u.user_id
  ORDER BY total_spent DESC
  LIMIT 10");
mysqli_stmt_bind_param($stmt, 'ss', $from_dt, $to_dt);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$top_customers = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// 7) Low stock products
$stmt = mysqli_prepare($conn, "SELECT product_id, product_name, stock FROM products WHERE stock <= 5 ORDER BY stock ASC LIMIT 50");
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$low_stock = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Reports • PCZone Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <?php include './includes/header-link.php'; ?>

  <style>
    .report-card {
      border-radius: 6px;
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      padding: 12px
    }

    .small-muted {
      color: var(--text-muted);
      font-size: 13px
    }
  </style>
</head>

<body>
  <?php
  $current_page = 'reports';
  include './includes/header.php';
  include './includes/sidebar.php'; ?>
  <main class="main-content pt-5 mt-4">
    <div class="container mt-2">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Reports</h3>
        <form method="get" class="d-flex gap-2 align-items-center">
          <input type="date" name="from" value="<?= h($from) ?>" class="form-control form-control-sm">
          <input type="date" name="to" value="<?= h($to) ?>" class="form-control form-control-sm">
          <button class="btn btn-add">Apply</button>
        </form>
      </div>

      <div class="row gy-3">
        <div class="col-lg-4">
          <div class="card report-card">
            <div class="small-muted">Orders</div>
            <div class="h4 fw-bold"><?= (int)$summ['orders_count'] ?></div>
            <div class="small-muted mt-1">Revenue</div>
            <div class="h5">₹ <?= number_format((float)$summ['revenue'], 2) ?></div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card report-card">
            <div class="small-muted">Low stock products (≤5)</div>
            <div class="h5"><?= count($low_stock) ?> products</div>
            <div class="small-muted mt-1">Top low-stock</div>
            <?php if (count($low_stock)): ?>
              <ul class="list-unstyled small mt-2">
                <?php foreach (array_slice($low_stock, 0, 5) as $ls): ?>
                  <li><?= h($ls['product_name']) ?> <span class="small-muted">(<?= (int)$ls['stock'] ?>)</span></li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div class="small-muted mt-2">None</div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card report-card">
            <div class="small-muted">Payments summary</div>
            <div class="mt-2">
              <?php if (count($payments_summary)): ?>
                <ul class="list-unstyled small">
                  <?php foreach ($payments_summary as $ps): ?>
                    <li><?= h($ps['payment_status']) ?>: <?= (int)$ps['cnt'] ?> • ₹ <?= number_format((float)$ps['total_amount'], 2) ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <div class="small-muted">No payments</div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="col-12">
          <div class="theme-card p-3">
            <h5 class="mb-3">Top products (by qty sold)</h5>
            <div class="table-responsive">
              <table class="data-table table w-100">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Qty Sold</th>
                    <th>Revenue</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($top_products)): foreach ($top_products as $tp): ?>
                      <tr>
                        <td><?= h($tp['product_name']) ?></td>
                        <td><?= (int)$tp['qty_sold'] ?></td>
                        <td>₹ <?= number_format((float)$tp['revenue'], 2) ?></td>
                      </tr>
                    <?php endforeach;
                  else: ?>
                    <tr>
                      <td colspan="3" class="small-muted">No sales in range.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="theme-card p-3">
            <h5 class="mb-3">Revenue by category (top 10)</h5>
            <div class="table-responsive">
              <table class="data-table table w-100">
                <thead>
                  <tr>
                    <th>Category</th>
                    <th>Revenue</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($by_category)): foreach ($by_category as $c): ?>
                      <tr>
                        <td><?= h($c['category_name']) ?></td>
                        <td>₹ <?= number_format((float)$c['revenue'], 2) ?></td>
                      </tr>
                    <?php endforeach;
                  else: ?>
                    <tr>
                      <td colspan="2" class="small-muted">No data</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="theme-card p-3">
            <h5 class="mb-3">Top customers</h5>
            <div class="table-responsive">
              <table class="data-table table w-100">
                <thead>
                  <tr>
                    <th>Customer</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($top_customers)): foreach ($top_customers as $tc): ?>
                      <tr>
                        <td><?= h($tc['name']) ?><div class="small-muted"><?= h($tc['email']) ?></div>
                        </td>
                        <td><?= (int)$tc['orders_count'] ?></td>
                        <td>₹ <?= number_format((float)$tc['total_spent'], 2) ?></td>
                      </tr>
                    <?php endforeach;
                  else: ?>
                    <tr>
                      <td colspan="3" class="small-muted">No data</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-12">
          <div class="theme-card p-3">
            <h5 class="mb-3">Orders by day</h5>
            <div class="table-responsive">
              <table class="data-table table w-100">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Orders</th>
                    <th>Revenue</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($orders_by_day)): foreach ($orders_by_day as $d): ?>
                      <tr>
                        <td><?= h($d['day']) ?></td>
                        <td><?= (int)$d['orders_count'] ?></td>
                        <td>₹ <?= number_format((float)$d['revenue'], 2) ?></td>
                      </tr>
                    <?php endforeach;
                  else: ?>
                    <tr>
                      <td colspan="3" class="small-muted">No orders</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

    </div>
  </main>
  <script src="./assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>