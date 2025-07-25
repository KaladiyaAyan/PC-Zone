<?php
// File: pcparts-admin-panel/pages/dashboard.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}
// … your dashboard code …

$current = basename($_SERVER['PHP_SELF']);
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/db_connect.php'; // DB connection file
?>
<main class="main-content">

  <!-- Dashboard Cards -->
  <div class="dashboard-cards">
    <div class="card">
      <i class="fas fa-box"></i>
      <div>
        <h3>Total Products</h3>
        <p>
          <?php
          $result = $conn->query("SELECT COUNT(*) AS total FROM products");
          $data = $result->fetch_assoc();
          echo ($data['total'] ?? 0);
          ?>
        </p>
      </div>
    </div>

    <div class="card">
      <i class="fas fa-shopping-cart"></i>
      <div>
        <h3>Total Orders</h3>
        <p>
          <?php
          $result = $conn->query("SELECT COUNT(*) AS total FROM orders");
          $data = $result->fetch_assoc();
          echo ($data['total'] ?? 0);
          ?>
        </p>
      </div>
    </div>

    <div class="card">
      <i class="fas fa-users"></i>
      <div>
        <h3>Total Customers</h3>
        <p>
          <?php
          $result = $conn->query("SELECT COUNT(DISTINCT customer_name) AS total FROM orders");
          $data = $result->fetch_assoc();
          echo ($data['total'] ?? 0);
          ?>
        </p>
      </div>
    </div>

    <div class="card">
      <i class="fas fa-dollar-sign"></i>
      <div>
        <h3>Total Revenue</h3>
        <p>
          <?php
          $result = $conn->query("SELECT SUM(total_amount) AS total FROM orders");
          $data = $result->fetch_assoc();
          echo '₹' . ($data['total'] ?? 0);
          ?>
        </p>
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
          </tr>
        </thead>
        <tbody>
          <?php
          if (isset($conn) && $conn) {
            $recentOrders = $conn->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT 5");
            if ($recentOrders->num_rows > 0) {
              while ($row = $recentOrders->fetch_assoc()) {
                echo "<tr>
              <td>{$row['id']}</td>
              <td>{$row['customer_name']}</td>
              <td>₹{$row['total_amount']}</td>
              <td>{$row['order_date']}</td>
            </tr>";
              }
            } else {
              echo "<tr><td colspan='4'>No recent orders found.</td></tr>";
            }
          } else {
            // echo "<tr><td colspan='4'>Database connection error.</td></tr>";
            echo "<tr>
                <td>#1</td>
                <td>John Doe</td>
                <td>1000</td>
                <td>2023-08-01</td>
              </tr>";
            echo "<tr>
                <td>#2</td>
                <td>John Doe</td>
                <td>1000</td>
                <td>2023-08-01</td>
              </tr>";
            echo "<tr>
                <td>#3</td>
                <td>John Doe</td>
                <td>1000</td>
                <td>2023-08-01</td>
              </tr>";
            echo "<tr>
                <td>#4</td>
                <td>John Doe</td>
                <td>1000</td>
                <td>2023-08-01</td>
              </tr>";
            echo "<tr>
                <td>#5</td>
                <td>John Doe</td>
                <td>1000</td>
                <td>2023-08-01</td>
              </tr>";
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
            <th>Quantity</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if (isset($conn) && $conn) {
            $lowStock = $conn->query("SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5");
            if ($lowStock->num_rows > 0) {
              while ($row = $lowStock->fetch_assoc()) {
                echo "<tr>
              <td>{$row['id']}</td>
              <td>{$row['name']}</td>
              <td>{$row['stock']}</td>
            </tr>";
              }
            } else {
              echo "<tr><td colspan='3'>All products sufficiently stocked.</td></tr>";
            }
          } else {
            // echo "<li>Database not connected.</li>";
            echo "<tr>
                <td>1</td>
                <td>inter i9 14900k</td>
                <td>10</td>
              </tr>";
            echo "<tr>
                <td>2</td>
                <td>inter i9 14900k</td>
                <td>10</td>
              </tr>";
            echo "<tr>
                <td>3</td>
                <td>inter i9 14900k</td>
                <td>10</td>
              </tr>";
            echo "<tr>
                <td>4</td>
                <td>inter i9 14900k</td>
                <td>10</td>
              </tr>";
            echo "<tr>
                <td>5</td>
                <td>inter i9 14900k</td>
                <td>10</td>
              </tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </section>

</main>