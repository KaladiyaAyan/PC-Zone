<?php
require_once '../includes/db_connect.php';
session_start();

// Redirect if not logged in
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ../login.php');
  exit;
}

// Fetch orders with customer info
$query = "SELECT 
              o.order_id, o.order_date, o.total_amount, o.order_status,
              u.username, u.email, u.phone
          FROM orders o
          LEFT JOIN users u ON o.user_id = u.user_id
          ORDER BY o.order_date DESC";

$result = $conn->query($query);
$orders = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PC-Zone Admin - Orders</title>

  <?php require('./includes/header-link.php') ?>
  <script>
    // Immediately apply theme from localStorage
    (function() {
      const theme = localStorage.getItem('pczoneTheme');
      if (theme === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
      }
    })();
  </script>
</head>

<body>
  <?php require('./includes/alert.php'); ?>
  <?php
  $current_page = 'orders';
  include './includes/header.php';
  include './includes/sidebar.php';
  ?>

  <main class="main-content">
    <div class="page-header">
      <h1>Manage Orders</h1>
    </div>

    <!-- Orders Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Contact</th>
            <th>Date</th>
            <th>Total</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td>#<?= (int)$order['order_id'] ?></td>
                <td><?= htmlspecialchars($order['username'] ?? 'Guest') ?></td>
                <td><?= htmlspecialchars($order['email'] ?? '-') ?></td>
                <td><?= date("d M Y", strtotime($order['order_date'])) ?></td>
                <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                <td>
                  <?php
                  // Use strtolower for a reliable class name
                  $status_class = strtolower($order['order_status']);
                  ?>
                  <span class="badge-status <?= $status_class ?>">
                    <?= htmlspecialchars($order['order_status']) ?>
                  </span>
                </td>
                <td>
                  <a href="order_view.php?id=<?= (int)$order['order_id'] ?>" class="btn-view">
                    <i class="fas fa-eye"></i> View
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-4">No orders found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
  <?php require('./includes/footer-link.php') ?>
</body>

</html>