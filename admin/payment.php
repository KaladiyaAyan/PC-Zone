<?php
session_start();
require('../includes/db_connect.php');
require('../includes/functions.php');

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ../login.php');
  exit;
}

// --- HANDLE FILTERS ---
$q = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
$method = trim($_GET['method'] ?? '');

// --- BUILD QUERY ---
$whereConditions = [];

if ($q !== '') {
  $searchTerm = $conn->real_escape_string($q);
  $whereConditions[] = "(p.transaction_id LIKE '%$searchTerm%' OR p.order_id LIKE '%$searchTerm%' OR u.email LIKE '%$searchTerm%' OR u.username LIKE '%$searchTerm%')";
}
if ($status !== '') {
  $whereConditions[] = "p.payment_status = '" . $conn->real_escape_string($status) . "'";
}
if ($method !== '') {
  $whereConditions[] = "p.payment_method = '" . $conn->real_escape_string($method) . "'";
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// --- FETCH DATA ---

// Get totals for header
$totalsQuery = "SELECT COUNT(p.payment_id) AS total_count, COALESCE(SUM(p.amount), 0) AS total_amount 
                FROM payments p 
                LEFT JOIN orders o ON p.order_id = o.order_id
                LEFT JOIN users u ON o.user_id = u.user_id
                $whereClause";
$totalsResult = $conn->query($totalsQuery);
$totals = $totalsResult->fetch_assoc();
$total_count = (int)$totals['total_count'];
$total_amount = (float)$totals['total_amount'];

// Get all matching payments (no pagination)
$sql = "SELECT p.*, u.username, u.email
        FROM payments p
        LEFT JOIN orders o ON p.order_id = o.order_id
        LEFT JOIN users u ON o.user_id = u.user_id
        $whereClause
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);
$payments = $result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Payments - PCZone Admin</title>

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
  $current_page = 'payments';
  include './includes/header.php';
  include './includes/sidebar.php';
  ?>

  <main class="main-content">
    <div class="page-header">
      <h3><i class="fas fa-credit-card"></i> Payments</h3>
      <div class="text-end text-small-muted">
        Total: <?= $total_count ?> payments • ₹ <?= number_format($total_amount, 2) ?>
      </div>
    </div>

    <div class="theme-card p-3 mb-4">
      <form method="get" class="row g-2 align-items-center">
        <div class="col-md-5 col-12">
          <input type="search" name="q" value="<?= e($q) ?>" class="form-control" placeholder="Search by Txn, Order ID, Email, or Name">
        </div>
        <div class="col-md-2 col-6">
          <select name="method" class="form-select">
            <option value="">All Methods</option>
            <option value="cash_on_delivery" <?= $method === 'cash_on_delivery' ? 'selected' : '' ?>>Cash on Delivery</option>
            <option value="credit_card" <?= $method === 'credit_card' ? 'selected' : '' ?>>Credit Card</option>
            <option value="upi" <?= $method === 'upi' ? 'selected' : '' ?>>UPI</option>
          </select>
        </div>
        <div class="col-md-2 col-6">
          <select name="status" class="form-select">
            <option value="">All Statuses</option>
            <option value="Paid" <?= $status === 'Paid' ? 'selected' : '' ?>>Paid</option>
            <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="Failed" <?= $status === 'Failed' ? 'selected' : '' ?>>Failed</option>
            <option value="Refunded" <?= $status === 'Refunded' ? 'selected' : '' ?>>Refunded</option>
          </select>
        </div>
        <div class="col-md-3 col-12 d-flex justify-content-end">
          <button class="btn-add" type="submit">Filter</button>
          <a href="payment.php" class="btn-secondary ms-2">Reset</a>
        </div>
      </form>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Order</th>
            <th>Customer</th>
            <th>Method</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($payments)): ?>
            <tr>
              <td colspan="7" class="text-center py-4">No payments found matching your criteria.</td>
            </tr>
            <?php else: foreach ($payments as $p): ?>
              <tr>
                <td><?= (int)$p['payment_id'] ?></td>
                <td><a href="order_view.php?id=<?= (int)$p['order_id'] ?>">#<?= (int)$p['order_id'] ?></a></td>
                <td>
                  <div><?= e($p['username']) ?></div>
                  <div class="text-small-muted"><?= e($p['email']) ?></div>
                </td>
                <td><?= e($p['payment_method']) ?></td>
                <td>₹ <?= number_format((float)$p['amount'], 2) ?></td>
                <td><span class="badge-status <?= strtolower(e($p['payment_status'])) ?>"><?= e($p['payment_status']) ?></span></td>
                <td><?= date('d M Y, h:i A', strtotime($p['paid_at'] ?? $p['created_at'])) ?></td>
              </tr>
          <?php endforeach;
          endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination removed -->
  </main>

  <?php require('./includes/footer-link.php') ?>
</body>

</html>