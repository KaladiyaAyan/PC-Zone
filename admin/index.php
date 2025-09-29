<?php
// admin/index.php
session_start();
require('../includes/db_connect.php');
require('../includes/functions.php');

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ../login.php');
  exit;
}

// safe count helper using validated table name and optional condition (simple use)
function getCount($conn, $table, $condition = '')
{
  if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) return 0;

  $sql = "SELECT COUNT(*) AS total FROM `$table`" . ($condition ? " WHERE $condition" : "");
  $res = mysqli_query($conn, $sql);

  if (!$res) return 0;
  $row = mysqli_fetch_assoc($res);
  return (int)($row['total'] ?? 0);
}

function getTotalRevenue($conn)
{
  $sql = "SELECT COALESCE(SUM(total_amount),0) AS total FROM orders";
  $res = mysqli_query($conn, $sql);
  if (!$res) return 0.0;
  $row = mysqli_fetch_assoc($res);
  return (float)($row['total'] ?? 0.0);
}

$totalProducts  = getCount($conn, 'products');
$totalOrders    = getCount($conn, 'orders');
$totalCustomers = getCount($conn, 'users', "role = 'user' AND status = 'active'");
$totalRevenue   = getTotalRevenue($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PC ZONE Dashboard</title>

  <?php require('./includes/header-link.php') ?>
  <link rel="stylesheet" href="./assets/css/dashboard.css">

</head>

<body>
  <?php require('./includes/alert.php'); ?>

  <?php include './includes/header.php';
  $current_page = 'dashboard';
  include './includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="content-wrapper">

      <!-- Dashboard Cards -->
      <div class="dashboard-cards mb-4 d-grid">
        <div class="card d-flex align-items-center p-3">
          <i class="fas fa-box fa-2x"></i>
          <div class="ms-3">
            <h3 class="h6 mb-0">Total Products</h3>
            <p class="mb-0 fs-5"><?= (int)$totalProducts ?></p>
          </div>
        </div>

        <div class="card d-flex align-items-center p-3">
          <i class="fas fa-shopping-cart fa-2x"></i>
          <div class="ms-3">
            <h3 class="h6 mb-0">Total Orders</h3>
            <p class="mb-0 fs-5"><?= (int)$totalOrders ?></p>
          </div>
        </div>

        <div class="card d-flex align-items-center p-3">
          <i class="fas fa-users fa-2x"></i>
          <div class="ms-3">
            <h3 class="h6 mb-0">Total Customers</h3>
            <p class="mb-0 fs-5"><?= (int)$totalCustomers ?></p>
          </div>
        </div>

        <div class="card d-flex align-items-center p-3">
          <i class="fas fa-dollar-sign fa-2x"></i>
          <div class="ms-3">
            <h3 class="h6 mb-0">Total Revenue</h3>
            <p class="mb-0 fs-5"><?= e(formatPrice($totalRevenue)) ?></p>
          </div>
        </div>
      </div>

      <!-- Recent Orders Table -->
      <section class="dashboard-section mb-4">
        <h2>Recent Orders</h2>
        <div class="table-box mt-2">
          <table class="data-table table table-sm table-hover align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $sql = "SELECT o.order_id, u.username AS customer_name, o.total_amount, o.order_date, o.order_status
                      FROM orders o
                      LEFT JOIN users u ON o.user_id = u.user_id
                      ORDER BY o.order_date DESC
                      LIMIT 5";
              if ($res = mysqli_query($conn, $sql)) {
                if (mysqli_num_rows($res) > 0) {
                  while ($order = mysqli_fetch_assoc($res)) {
                    $statusClass = 'status-' . strtolower(preg_replace('/\s+/', '-', $order['order_status']));
                    $statusLabel = e(ucfirst(strtolower($order['order_status'])));
                    $custName = e($order['customer_name'] ?? 'Guest');
                    $orderId  = (int)$order['order_id'];
                    $totalFmt = e(formatPrice((float)$order['total_amount']));
                    $dateFmt  = $order['order_date'] ? date('M d, Y', strtotime($order['order_date'])) : '-';
                    echo "<tr>
                            <td>{$orderId}</td>
                            <td>{$custName}</td>
                            <td>{$totalFmt}</td>
                            <td>{$dateFmt}</td>
                            <td><span class='{$statusClass}'>{$statusLabel}</span></td>
                          </tr>";
                  }
                } else {
                  echo "<tr><td colspan='5'>No recent orders found.</td></tr>";
                }
              } else {
                echo "<tr><td colspan='5'>Unable to fetch recent orders.</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Low Stock Alerts Table -->
      <section class="dashboard-section mb-4">
        <h2>Low Stock Alerts</h2>
        <div class="table-box mt-2">
          <table class="data-table table table-sm table-hover align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Stock</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $sql2 = "SELECT product_id, product_name, stock FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5";
              if ($res2 = mysqli_query($conn, $sql2)) {
                if (mysqli_num_rows($res2) > 0) {
                  while ($product = mysqli_fetch_assoc($res2)) {
                    $pid = (int)$product['product_id'];
                    $pname = e($product['product_name']);
                    $stock = (int)$product['stock'];
                    $statusClass = $stock === 0 ? 'out-of-stock' : 'low-stock';
                    $statusText = $stock === 0 ? 'Out of Stock' : 'Low Stock';
                    echo "<tr>
                            <td>{$pid}</td>
                            <td>{$pname}</td>
                            <td>{$stock}</td>
                            <td><span class='stock-badge {$statusClass}'>" . e($statusText) . "</span></td>
                          </tr>";
                  }
                } else {
                  echo "<tr><td colspan='4'>All products sufficiently stocked.</td></tr>";
                }
              } else {
                echo "<tr><td colspan='4'>Unable to fetch stock data.</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </section>

    </div>
  </main>

  <?php require('./includes/footer-link.php') ?>
</body>

</html>