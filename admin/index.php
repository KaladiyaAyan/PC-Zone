<?php
// session_start();
require_once '../includes/functions.php'; // provides getConnection(), formatPrice()

$conn = getConnection();

// Validate simple identifier (table names)
function valid_identifier($s)
{
  return preg_match('/^[a-zA-Z0-9_]+$/', $s);
}

/**
 * Get row count for a table with optional condition.
 * Note: $table must be a plain identifier (validated).
 */
function getCount($conn, $table, $condition = '')
{
  if (!valid_identifier($table)) return 0;
  $sql = "SELECT COUNT(*) AS total FROM $table" . ($condition ? " WHERE $condition" : "");
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
  <!-- Font Awesome -->
  <link rel="stylesheet" href="./assets/vendor/fontawesome/css/all.min.css">
  <!-- Bootstrap -->
  <link rel="stylesheet" href="./assets/vendor/bootstrap/css/bootstrap.min.css">
  <!-- Main admin styles -->
  <link rel="stylesheet" href="./assets/css/style.css">

  <script src="./assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    (function() {
      const saved = localStorage.getItem('pczoneTheme');
      if (saved === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
      }
    })();
  </script>
</head>

<body>
  <?php include './includes/header.php'; ?>
  <?php $current_page = 'dashboard';
  include './includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="content-wrapper">

      <!-- Dashboard Cards -->
      <div class="dashboard-cards mb-4">
        <div class="card d-flex align-items-center p-3">
          <i class="fas fa-box"></i>
          <div class="ms-2">
            <h3>Total Products</h3>
            <p class="mb-0"><?= (int)$totalProducts ?></p>
          </div>
        </div>

        <div class="card d-flex align-items-center p-3">
          <i class="fas fa-shopping-cart"></i>
          <div class="ms-2">
            <h3>Total Orders</h3>
            <p class="mb-0"><?= (int)$totalOrders ?></p>
          </div>
        </div>

        <div class="card d-flex align-items-center p-3">
          <i class="fas fa-users"></i>
          <div class="ms-2">
            <h3>Total Customers</h3>
            <p class="mb-0"><?= (int)$totalCustomers ?></p>
          </div>
        </div>

        <div class="card d-flex align-items-center p-3">
          <i class="fas fa-dollar-sign"></i>
          <div class="ms-2">
            <h3>Total Revenue</h3>
            <p class="mb-0"><?= htmlspecialchars(formatPrice($totalRevenue), ENT_QUOTES) ?></p>
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
              $sql = "SELECT o.order_id, CONCAT(u.first_name, ' ', u.last_name) AS customer_name, o.total_amount, o.order_date, o.order_status
                      FROM orders o
                      JOIN users u ON o.user_id = u.user_id
                      ORDER BY o.order_date DESC
                      LIMIT 5";
              $stmt = mysqli_prepare($conn, $sql);
              if ($stmt) {
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                if ($res && mysqli_num_rows($res) > 0) {
                  while ($order = mysqli_fetch_assoc($res)) {
                    $statusClass = 'status-' . strtolower($order['order_status']);
                    $statusLabel = ucfirst($order['order_status']);
                    $custName = htmlspecialchars($order['customer_name'] ?? 'Guest', ENT_QUOTES);
                    $orderId  = (int)$order['order_id'];
                    $totalFmt = htmlspecialchars(formatPrice((float)$order['total_amount']), ENT_QUOTES);
                    $dateFmt  = date('M d, Y', strtotime($order['order_date']));
                    echo "<tr>
                            <td>{$orderId}</td>
                            <td>{$custName}</td>
                            <td>{$totalFmt}</td>
                            <td>{$dateFmt}</td>
                            <td><span class='{$statusClass}'>" . htmlspecialchars($statusLabel, ENT_QUOTES) . "</span></td>
                          </tr>";
                  }
                } else {
                  echo "<tr><td colspan='5'>No recent orders found.</td></tr>";
                }
                mysqli_stmt_close($stmt);
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
              $stmt2 = mysqli_prepare($conn, $sql2);
              if ($stmt2) {
                mysqli_stmt_execute($stmt2);
                $res2 = mysqli_stmt_get_result($stmt2);
                if ($res2 && mysqli_num_rows($res2) > 0) {
                  while ($product = mysqli_fetch_assoc($res2)) {
                    $pid = (int)$product['product_id'];
                    $pname = htmlspecialchars($product['product_name'], ENT_QUOTES);
                    $stock = (int)$product['stock'];
                    $statusClass = $stock === 0 ? 'out-of-stock' : 'low-stock';
                    $statusText = $stock === 0 ? 'Out of Stock' : 'Low Stock';
                    echo "<tr>
                            <td>{$pid}</td>
                            <td>{$pname}</td>
                            <td>{$stock}</td>
                            <td><span class='stock-badge {$statusClass}'>" . htmlspecialchars($statusText, ENT_QUOTES) . "</span></td>
                          </tr>";
                  }
                } else {
                  echo "<tr><td colspan='4'>All products sufficiently stocked.</td></tr>";
                }
                mysqli_stmt_close($stmt2);
              } else {
                echo "<tr><td colspan='4'>Unable to fetch stock data.</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </section>

    </div> <!-- /.content-wrapper -->
  </main>

  <script>
    // Sidebar toggle functionality (kept commented intentionally)
  </script>

  <script src="./assets/vendor/jquery/jquery-3.7.1.min.js"></script>
</body>

</html>