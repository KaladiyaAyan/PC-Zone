<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();

// Redirect if not logged in
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ../login.php');
  exit;
}

// --- CONFIG & HELPERS ---
function h($s)
{
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
$current_page = 'reports';

// --- HANDLE DATE FILTERS (Default: last 30 days) ---
$from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$to = $_GET['to'] ?? date('Y-m-d');

// Sanitize dates and create a full-day range for the query
$from_dt = $conn->real_escape_string($from . ' 00:00:00');
$to_dt = $conn->real_escape_string($to . ' 23:59:59');
$date_filter_sql = "o.order_date BETWEEN '$from_dt' AND '$to_dt'";

// --- DATA FETCHING (SIMPLIFIED QUERIES) ---

// 1) Sales Summary
$summary_sql = "SELECT COUNT(*) AS orders_count, COALESCE(SUM(total_amount),0) AS revenue FROM orders o WHERE $date_filter_sql";
$summary_res = $conn->query($summary_sql);
$summary = $summary_res->fetch_assoc() ?: ['orders_count' => 0, 'revenue' => 0];

// 2) Top Products (by quantity)
$top_products_sql = "SELECT p.product_name, SUM(oi.quantity) AS qty_sold, SUM(oi.total_price) AS revenue
                     FROM order_items oi
                     JOIN products p ON p.product_id = oi.product_id
                     JOIN orders o ON o.order_id = oi.order_id
                     WHERE $date_filter_sql
                     GROUP BY p.product_id
                     ORDER BY qty_sold DESC
                     LIMIT 10";
$top_products_res = $conn->query($top_products_sql);
$top_products = $top_products_res->fetch_all(MYSQLI_ASSOC);

// 3) Revenue by Category
$category_revenue_sql = "SELECT c.category_name, SUM(oi.total_price) AS revenue
                         FROM order_items oi
                         JOIN products p ON p.product_id = oi.product_id
                         JOIN categories c ON c.category_id = p.category_id
                         JOIN orders o ON o.order_id = oi.order_id
                         WHERE $date_filter_sql
                         GROUP BY c.category_id
                         ORDER BY revenue DESC
                         LIMIT 10";
$category_revenue_res = $conn->query($category_revenue_sql);
$by_category = $category_revenue_res->fetch_all(MYSQLI_ASSOC);

// 4) Top Customers
$top_customers_sql = "SELECT u.username AS name, u.email, COUNT(o.order_id) AS orders_count, SUM(o.total_amount) AS total_spent
                      FROM orders o
                      JOIN users u ON u.user_id = o.user_id
                      WHERE $date_filter_sql
                      GROUP BY u.user_id
                      ORDER BY total_spent DESC
                      LIMIT 10";
$top_customers_res = $conn->query($top_customers_sql);
$top_customers = $top_customers_res->fetch_all(MYSQLI_ASSOC);

// 5) Orders by Day
$orders_by_day_sql = "SELECT DATE(order_date) AS day, COUNT(*) AS orders_count, SUM(total_amount) AS revenue 
                      FROM orders o 
                      WHERE $date_filter_sql 
                      GROUP BY day ORDER BY day ASC";
$orders_by_day_res = $conn->query($orders_by_day_sql);
$orders_by_day = $orders_by_day_res->fetch_all(MYSQLI_ASSOC);

// 6) Low Stock Products (not date-dependent)
$low_stock_sql = "SELECT product_name, stock FROM products WHERE stock <= 5 ORDER BY stock ASC LIMIT 5";
$low_stock_res = $conn->query($low_stock_sql);
$low_stock = $low_stock_res->fetch_all(MYSQLI_ASSOC);

// 7) Payments Summary
$payments_sql = "SELECT payment_status, COUNT(*) AS cnt, SUM(amount) AS total_amount 
                 FROM payments p 
                 JOIN orders o ON o.order_id = p.order_id
                 WHERE o.order_date BETWEEN '$from_dt' AND '$to_dt'
                 GROUP BY payment_status";
$payments_res = $conn->query($payments_sql);
$payments_summary = $payments_res->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reports - PCZone Admin</title>

  <?php require('./includes/header-link.php') ?>

  <script>
    (function() {
      if (localStorage.getItem('pczoneTheme') === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
      }
    })();
  </script>
</head>

<body>
  <?php require('./includes/alert.php'); ?>
  <?php
  include './includes/header.php';
  include './includes/sidebar.php';
  ?>

  <main class="main-content">
    <div class="page-header">
      <h3><i class="fas fa-chart-line"></i> Reports</h3>
      <form method="get" class="d-flex gap-2 align-items-center">
        <input type="date" name="from" value="<?= h($from) ?>" class="form-control">
        <input type="date" name="to" value="<?= h($to) ?>" class="form-control">
        <button class="btn-add" type="submit">Apply</button>
      </form>
    </div>

    <div class="row g-4">
      <!-- Summary Cards -->
      <div class="col-lg-4">
        <div class="theme-card p-3 h-100">
          <p class="text-small-muted mb-1">Orders</p>
          <h4 class="fw-bold mb-3"><?= (int)$summary['orders_count'] ?></h4>
          <p class="text-small-muted mb-1">Revenue</p>
          <h5 class="mb-0">₹ <?= number_format((float)$summary['revenue'], 2) ?></h5>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="theme-card p-3 h-100">
          <p class="text-small-muted mb-2">Low Stock Products (≤5)</p>
          <?php if (!empty($low_stock)): ?>
            <ul class="list-unstyled small mb-0">
              <?php foreach ($low_stock as $item): ?>
                <li><?= h($item['product_name']) ?> <span class="text-small-muted">(<?= (int)$item['stock'] ?> left)</span></li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-small-muted mb-0">All products are well-stocked.</p>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="theme-card p-3 h-100">
          <p class="text-small-muted mb-2">Payments Summary</p>
          <?php if (!empty($payments_summary)): ?>
            <ul class="list-unstyled small mb-0">
              <?php foreach ($payments_summary as $p_sum): ?>
                <li><?= h($p_sum['payment_status']) ?>: <?= (int)$p_sum['cnt'] ?> (₹ <?= number_format((float)$p_sum['total_amount'], 2) ?>)</li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-small-muted mb-0">No payment data in this period.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Data Tables -->
      <div class="col-12">
        <div class="theme-card p-3">
          <h5 class="mb-3">Top products (by qty sold)</h5>
          <div class="table-container p-0">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Qty Sold</th>
                  <th>Revenue</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($top_products)): ?>
                  <tr>
                    <td colspan="3" class="text-center py-3">No product sales in this period.</td>
                  </tr>
                  <?php else: foreach ($top_products as $tp): ?>
                    <tr>
                      <td><?= h($tp['product_name']) ?></td>
                      <td><?= (int)$tp['qty_sold'] ?></td>
                      <td>₹ <?= number_format((float)$tp['revenue'], 2) ?></td>
                    </tr>
                <?php endforeach;
                endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="theme-card p-3">
          <h5 class="mb-3">Revenue by category</h5>
          <div class="table-container p-0">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Category</th>
                  <th>Revenue</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($by_category)): ?>
                  <tr>
                    <td colspan="2" class="text-center py-3">No category revenue in this period.</td>
                  </tr>
                  <?php else: foreach ($by_category as $cat): ?>
                    <tr>
                      <td><?= h($cat['category_name']) ?></td>
                      <td>₹ <?= number_format((float)$cat['revenue'], 2) ?></td>
                    </tr>
                <?php endforeach;
                endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="theme-card p-3">
          <h5 class="mb-3">Top customers</h5>
          <div class="table-container p-0">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Customer</th>
                  <th>Orders</th>
                  <th>Total Spent</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($top_customers)): ?>
                  <tr>
                    <td colspan="3" class="text-center py-3">No customer data in this period.</td>
                  </tr>
                  <?php else: foreach ($top_customers as $tc): ?>
                    <tr>
                      <td>
                        <div><?= h($tc['name']) ?></div>
                        <div class="text-small-muted"><?= h($tc['email']) ?></div>
                      </td>
                      <td><?= (int)$tc['orders_count'] ?></td>
                      <td>₹ <?= number_format((float)$tc['total_spent'], 2) ?></td>
                    </tr>
                <?php endforeach;
                endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="theme-card p-3">
          <h5 class="mb-3">Orders by day</h5>
          <div class="table-container p-0">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Orders</th>
                  <th>Revenue</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($orders_by_day)): ?>
                  <tr>
                    <td colspan="3" class="text-center py-3">No orders in this period.</td>
                  </tr>
                  <?php else: foreach ($orders_by_day as $day): ?>
                    <tr>
                      <td><?= date('d M Y', strtotime($day['day'])) ?></td>
                      <td><?= (int)$day['orders_count'] ?></td>
                      <td>₹ <?= number_format((float)$day['revenue'], 2) ?></td>
                    </tr>
                <?php endforeach;
                endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
  <?php require('./includes/footer-link.php') ?>
</body>

</html>