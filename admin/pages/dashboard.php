<?php
session_start();
include '../includes/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: index.php");
  exit;
}

// Get dashboard statistics
function getCount($conn, $table, $condition = '')
{
  $query = "SELECT COUNT(*) as total FROM $table $condition";
  $result = mysqli_query($conn, $query);
  $data = mysqli_fetch_assoc($result);
  return $data['total'] ?? 0;
}

function getTotalRevenue($conn)
{
  $query = "SELECT SUM(total_amount) as total FROM orders";
  $result = mysqli_query($conn, $query);
  $data = mysqli_fetch_assoc($result);
  return $data['total'] ?? 0;
}

$totalProducts = getCount($conn, 'products');
$totalOrders = getCount($conn, 'orders');
$totalCustomers = getCount($conn, 'customers');
$totalRevenue = getTotalRevenue($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PC ZONE Dashboard</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
  <!-- Bootstrap -->
  <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
  <!-- Main admin styles -->
  <link rel="stylesheet" href="../assets/css/style.css">

  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- Theme initialization script (inline to prevent flash) -->
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
  <?php include '../includes/header.php'; ?>
  <?php $page = 'dashboard';
  include '../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="content-wrapper">

      <!-- Dashboard Cards -->
      <div class="dashboard-cards mb-4">
        <div class="card d-flex align-items-center p-3">
          <i class="fas fa-box"></i>
          <div class="ms-2">
            <h3>Total Products</h3>
            <p class="mb-0"><?= $totalProducts ?></p>
          </div>
        </div>

        <div class="card d-flex align-items-center p-3">
          <i class="fas fa-shopping-cart"></i>
          <div class="ms-2">
            <h3>Total Orders</h3>
            <p class="mb-0"><?= $totalOrders ?></p>
          </div>
        </div>

        <div class="card d-flex align-items-center p-3">
          <i class="fas fa-users"></i>
          <div class="ms-2">
            <h3>Total Customers</h3>
            <p class="mb-0"><?= $totalCustomers ?></p>
          </div>
        </div>

        <div class="card d-flex align-items-center p-3">
          <i class="fas fa-dollar-sign"></i>
          <div class="ms-2">
            <h3>Total Revenue</h3>
            <p class="mb-0">₹<?= number_format($totalRevenue, 2) ?></p>
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
              $recentOrders = mysqli_query($conn, "
                SELECT o.order_id,
                       CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
                       o.total_amount,
                       o.order_date,
                       o.order_status
                FROM orders o
                JOIN customers c ON o.customer_id = c.customer_id
                ORDER BY o.order_date DESC
                LIMIT 5
              ");

              if (mysqli_num_rows($recentOrders) > 0) {
                while ($order = mysqli_fetch_assoc($recentOrders)) {
                  // normalize status class to lowercase for CSS mapping
                  $statusClass = 'status-' . strtolower($order['order_status']);
                  $statusLabel = ucfirst($order['order_status']);
                  echo "<tr>
                          <td>{$order['order_id']}</td>
                          <td>{$order['customer_name']}</td>
                          <td>₹" . number_format($order['total_amount'], 2) . "</td>
                          <td>" . date('M d, Y', strtotime($order['order_date'])) . "</td>
                          <td><span class='{$statusClass}'>{$statusLabel}</span></td>
                        </tr>";
                }
              } else {
                echo "<tr><td colspan='5'>No recent orders found.</td></tr>";
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
              $lowStock = mysqli_query($conn, "SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5");
              if (mysqli_num_rows($lowStock) > 0) {
                while ($product = mysqli_fetch_assoc($lowStock)) {
                  $statusClass = $product['stock'] == 0 ? 'out-of-stock' : 'low-stock';
                  $statusText = $product['stock'] == 0 ? 'Out of Stock' : 'Low Stock';
                  echo "<tr>
                          <td>{$product['product_id']}</td>
                          <td>{$product['product_name']}</td>
                          <td>{$product['stock']}</td>
                          <td><span class='stock-badge {$statusClass}'>{$statusText}</span></td>
                        </tr>";
                }
              } else {
                echo "<tr><td colspan='4'>All products sufficiently stocked.</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </section>

    </div> <!-- /.content-wrapper -->
  </main>

  <script>
    // Sidebar toggle functionality
    // document.addEventListener("DOMContentLoaded", function() {
    // const hamburger = document.getElementById("hamburger");
    // const sidebar = document.getElementById("sidebar");

    // Load sidebar state from localStorage
    // const isCollapsed = localStorage.getItem("sidebarCollapsed") === "true";
    // if (isCollapsed && sidebar) {
    //   sidebar.classList.add("collapsed");
    // }

    // Toggle sidebar and save state
    // if (hamburger && sidebar) {
    //   hamburger.addEventListener("click", () => {
    //     sidebar.classList.toggle("collapsed");
    //     localStorage.setItem("sidebarCollapsed", sidebar.classList.contains("collapsed"));
    //   });
    // }

    // Mobile sidebar toggle
    // function handleMobileMenu() {
    //   if (window.innerWidth <= 768) {
    //     if (hamburger && sidebar) {
    //       hamburger.addEventListener("click", (e) => {
    //         e.preventDefault();
    //         sidebar.classList.toggle("show");
    //       });
    //     }

    //     // Close sidebar when clicking outside
    //     document.addEventListener("click", (e) => {
    //       if (sidebar && !sidebar.contains(e.target) && !hamburger.contains(e.target)) {
    //         sidebar.classList.remove("show");
    //       }
    //     });
    //   }
    // }

    // handleMobileMenu();
    // window.addEventListener('resize', handleMobileMenu);
    // });
  </script>

  <!-- jquery and bootstrap (paths match head css links) -->
  <script src="../assets/vendor/jquery/jquery-3.7.1.min.js"></script>
</body>

</html>