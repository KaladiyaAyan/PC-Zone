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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PC ZONE Dashboard</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
  <!-- Bootstrap -->
  <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
  <!-- Main admin styles -->
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
  <?php include '../includes/header.php'; ?>
  <?php $page = 'dashboard';
  include '../includes/sidebar.php'; ?>

  <main class="main-content">
    <!-- Dashboard Cards -->
    <div class="dashboard-cards">
      <div class="card">
        <i class="fas fa-box"></i>
        <div>
          <h3>Total Products</h3>
          <p><?= $totalProducts ?></p>
        </div>
      </div>

      <div class="card">
        <i class="fas fa-shopping-cart"></i>
        <div>
          <h3>Total Orders</h3>
          <p><?= $totalOrders ?></p>
        </div>
      </div>

      <div class="card">
        <i class="fas fa-users"></i>
        <div>
          <h3>Total Customers</h3>
          <p><?= $totalCustomers ?></p>
        </div>
      </div>

      <div class="card">
        <i class="fas fa-dollar-sign"></i>
        <div>
          <h3>Total Revenue</h3>
          <p>₹<?= number_format($totalRevenue, 2) ?></p>
        </div>
      </div>
    </div>

    <!-- Recent Orders Table -->
    <section class="dashboard-section">
      <h2>Recent Orders</h2>
      <div class="table-box">
        <table class="data-table">
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
            $recentOrders = mysqli_query($conn, "SELECT * FROM orders ORDER BY order_date DESC LIMIT 5");
            if (mysqli_num_rows($recentOrders) > 0) {
              while ($order = mysqli_fetch_assoc($recentOrders)) {
                echo "<tr>
                                    <td>{$order['id']}</td>
                                    <td>{$order['customer_name']}</td>
                                    <td>₹" . number_format($order['total_amount'], 2) . "</td>
                                    <td>" . date('M d, Y', strtotime($order['order_date'])) . "</td>
                                    <td><span class='status-{$order['status']}'>{$order['status']}</span></td>
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
    <section class="dashboard-section">
      <h2>Low Stock Alerts</h2>
      <div class="table-box">
        <table class="data-table">
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
                                    <td>{$product['id']}</td>
                                    <td>{$product['name']}</td>
                                    <td>{$product['stock']}</td>
                                    <td><span class='status-{$statusClass}'>{$statusText}</span></td>
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
  </main>

  <script>
    // Sidebar toggle functionality
    document.addEventListener("DOMContentLoaded", function() {
      const hamburger = document.getElementById("hamburger");
      const sidebar = document.getElementById("sidebar");

      // Load sidebar state from localStorage
      const isCollapsed = localStorage.getItem("sidebarCollapsed") === "true";
      if (isCollapsed) {
        sidebar.classList.add("collapsed");
      }

      // Toggle sidebar and save state
      if (hamburger) {
        hamburger.addEventListener("click", () => {
          sidebar.classList.toggle("collapsed");
          localStorage.setItem("sidebarCollapsed", sidebar.classList.contains("collapsed"));
        });
      }
    });
  </script>

  <script src="./assets/vendor/jquery/jquery-3.7.1.min.js"></script>
  <script src="./assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>