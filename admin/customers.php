<?php
session_start();
require('../includes/db_connect.php');
require('../includes/functions.php');

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ../login.php');
  exit;
}

$search = trim($_GET['q'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && ctype_digit($_POST['id'])) {
  if ($_POST['action'] === 'update_status' && isset($_POST['status'])) {
    $id = (int)$_POST['id'];
    $status = $conn->real_escape_string($_POST['status']);

    if (in_array($status, ['active', 'inactive', 'banned'])) {
      $conn->query("UPDATE users SET status = '$status' WHERE user_id = $id");
    }

    // Redirect back to the same page/search query
    header('Location: customers.php?q=' . urlencode($search));
    exit;
  }
}

$whereClause = '';
if ($search !== '') {
  $searchTerm = $conn->real_escape_string($search);
  $whereClause = "WHERE (u.username LIKE '%$searchTerm%' OR u.email LIKE '%$searchTerm%' OR u.phone LIKE '%$searchTerm%')";
}

$sql = "
    SELECT u.*, 
           MAX(o.created_at) AS last_order, 
           COUNT(o.order_id) AS orders_count, 
           COALESCE(SUM(o.total_price), 0) AS total_purchases
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.user_id
    $whereClause
    GROUP BY u.user_id
    ORDER BY last_order DESC, u.created_at DESC
";

$result = $conn->query($sql);
if ($result) {
  $customers = $result->fetch_all(MYSQLI_ASSOC);
} else {
  // simple fallback to avoid fatal error if query fails
  $customers = [];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Customers - PCZone Admin</title>

  <?php require('./includes/header-link.php') ?>
</head>

<body>
  <?php require('./includes/alert.php');
  include './includes/header.php';
  $current_page = 'customers';
  include './includes/sidebar.php';
  ?>

  <main class="main-content">
    <div class="page-header">
      <div>
        <h2><i class="fas fa-users"></i> Customers</h2>
        <p class="text-muted mb-0">Manage all customer accounts.</p>
      </div>
      <form class="d-flex" method="get" action="customers.php">
        <input name="q" class="search-input" placeholder="Search..." value="<?= e($search) ?>">
        <button class="btn-add ms-2" type="submit">Search</button>
      </form>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Contact</th>
            <th>Orders</th>
            <th>Last Purchase</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($customers)): ?>
            <?php foreach ($customers as $index => $customer): ?>
              <tr>
                <td><?= $index + 1; ?></td>
                <td><?= e($customer['username']); ?></td>
                <td><?= e($customer['email']); ?></td>
                <td><?= (int)$customer['orders_count']; ?></td>
                <td><?= $customer['last_order'] ? date('d M Y', strtotime($customer['last_order'])) : '—'; ?></td>
                <td>
                  <form method="post" class="d-inline">
                    <input type="hidden" name="id" value="<?= (int)$customer['user_id'] ?>">
                    <input type="hidden" name="action" value="update_status">
                    <select name="status" class="status-select" onchange="this.form.submit()">
                      <option value="active" <?= $customer['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                      <option value="inactive" <?= $customer['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                      <option value="banned" <?= $customer['status'] === 'banned' ? 'selected' : '' ?>>Banned</option>
                    </select>
                  </form>
                </td>
                <td class="text-end">
                  <a href="customer_view.php?id=<?= (int)$customer['user_id'] ?>" class="btn-edit">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-4">No customers found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </main>
  <?php require('./includes/footer-link.php') ?>
</body>

</html>