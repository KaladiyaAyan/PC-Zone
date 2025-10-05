<?php
session_start();
require('../includes/db_connect.php');
require('../includes/functions.php');

// Redirect if not logged in
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ../login.php');
  exit;
}

// Fetch orders with customer info and payment status
$query = "SELECT 
            o.order_id,
            o.created_at AS order_date,
            COALESCE(o.total_price, 0) AS total_price,
            p.payment_status,
            u.username,
            u.email,
            u.phone
          FROM orders o
          LEFT JOIN users u ON o.user_id = u.user_id
          LEFT JOIN payments p ON p.order_id = o.order_id
          ORDER BY o.created_at DESC";

$result = $conn->query($query);
$orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PC-Zone Admin - Orders</title>

  <?php require('./includes/header-link.php') ?>
</head>

<body>
  <?php require('./includes/alert.php');
  include './includes/header.php';
  $current_page = 'orders';
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
                <td><?= e($order['username'] ?? 'Guest') ?></td>
                <td><?= e($order['email'] ?? '-') ?></td>
                <td><?= $order['order_date'] ? date("d M Y", strtotime($order['order_date'])) : '-' ?></td>
                <td><?= e(formatPrice((float)($order['total_price'] ?? 0))) ?></td>
                <td>
                  <?php
                  $status_raw = $order['payment_status'] ?? 'Pending';
                  $status_class = strtolower(preg_replace('/\s+/', '-', $status_raw));
                  ?>
                  <span class="badge-status <?= e($status_class) ?>">
                    <?= e(ucfirst(strtolower($status_raw))) ?>
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