<?php
session_start();
// if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
//   header('Location: ../login.php');
//   exit;
// }
require_once '../includes/db_connect.php'; // $conn (mysqli)

function h($s)
{
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// page marker for sidebar
$current_page = 'payments';

// filters
$q = trim($_GET['q'] ?? ''); // search txn, order id, customer email/name
$status = $_GET['status'] ?? '';
$method = $_GET['method'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// build WHERE clauses safely with prepared statement
$where = [];
$params = [];
$types = '';

if ($q !== '') {
  $where[] = "(p.transaction_id LIKE ? OR CAST(p.order_id AS CHAR) LIKE ? OR u.email LIKE ? OR CONCAT(u.first_name,' ',u.last_name) LIKE ? )";
  $like = '%' . $q . '%';
  $params[] = &$like;
  $params[] = &$like;
  $params[] = &$like;
  $params[] = &$like;
  $types .= 'ssss';
}
if ($status !== '') {
  $where[] = "p.payment_status = ?";
  $params[] = &$status;
  $types .= 's';
}
if ($method !== '') {
  $where[] = "p.payment_method = ?";
  $params[] = &$method;
  $types .= 's';
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// totals for header
$count_sql = "SELECT COUNT(*) AS total, COALESCE(SUM(p.amount),0) AS amount_sum FROM payments p LEFT JOIN orders o ON o.order_id=p.order_id LEFT JOIN users u ON u.user_id=o.user_id $where_sql";
$stmtc = mysqli_prepare($conn, $count_sql);
if ($params) {
  // bind dynamically
  mysqli_stmt_bind_param($stmtc, $types, ...$params);
}
mysqli_stmt_execute($stmtc);
$resc = mysqli_stmt_get_result($stmtc);
$meta = mysqli_fetch_assoc($resc);
mysqli_stmt_close($stmtc);
$total_count = (int)($meta['total'] ?? 0);
$total_amount = (float)($meta['amount_sum'] ?? 0.0);

// fetch page rows
$sql = "SELECT p.payment_id, p.order_id, p.payment_method, p.transaction_id, p.amount, p.currency, p.payment_status, p.paid_at, p.created_at, u.first_name, u.last_name, u.email
        FROM payments p
        LEFT JOIN orders o ON o.order_id = p.order_id
        LEFT JOIN users u ON u.user_id = o.user_id
        $where_sql
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $sql);
// need to bind params + two ints for limit offset
if ($params) {
  // build types with two ints
  $allTypes = $types . 'ii';
  $params2 = array_merge($params, [$perPage, $offset]);
  mysqli_stmt_bind_param($stmt, $allTypes, ...$params2);
} else {
  mysqli_stmt_bind_param($stmt, 'ii', $perPage, $offset);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$payments = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// helper to build page links
function page_url($p)
{
  $qs = $_GET;
  $qs['page'] = $p;
  return basename($_SERVER['PHP_SELF']) . '?' . http_build_query($qs);
}

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Payments • PCZone Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <?php include './includes/header-link.php'; ?>

  <style>
    .small-muted {
      color: var(--text-muted);
      font-size: 13px
    }
  </style>
</head>

<body>
  <?php include './includes/header.php';
  include './includes/sidebar.php'; ?>
  <div class="main-content pt-5 mt-4">
    <div class="container mt-2">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Payments</h3>
        <div class="text-end small-muted">Total: <?= $total_count ?> payments • ₹ <?= number_format($total_amount, 2) ?></div>
      </div>

      <div class="theme-card p-3 mb-3">
        <form method="get" class="row g-2 align-items-center">
          <div class="col-auto">
            <input type="search" name="q" value="<?= h($q) ?>" class="search-input" placeholder="Search txn, order#, email, name">
          </div>
          <div class="col-auto">
            <select name="method" class="status-select">
              <option value="">All methods</option>
              <option value="cash_on_delivery" <?= $method === 'cash_on_delivery' ? 'selected' : '' ?>>Cash on Delivery</option>
              <option value="credit_card" <?= $method === 'credit_card' ? 'selected' : '' ?>>Credit Card</option>
              <option value="debit_card" <?= $method === 'debit_card' ? 'selected' : '' ?>>Debit Card</option>
              <option value="upi" <?= $method === 'upi' ? 'selected' : '' ?>>UPI</option>
              <option value="net_banking" <?= $method === 'net_banking' ? 'selected' : '' ?>>Net Banking</option>
              <option value="wallet" <?= $method === 'wallet' ? 'selected' : '' ?>>Wallet</option>
            </select>
          </div>
          <div class="col-auto">
            <select name="status" class="status-select">
              <option value="">All statuses</option>
              <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
              <option value="Paid" <?= $status === 'Paid' ? 'selected' : '' ?>>Paid</option>
              <option value="Failed" <?= $status === 'Failed' ? 'selected' : '' ?>>Failed</option>
              <option value="Refunded" <?= $status === 'Refunded' ? 'selected' : '' ?>>Refunded</option>
            </select>
          </div>
          <div class="col-auto">
            <button class="btn btn-sm btn-outline-primary">Filter</button>
            <a href="payments.php" class="btn btn-sm btn-secondary ms-1">Reset</a>
          </div>
        </form>
      </div>

      <div class="theme-card p-3">
        <div class="table-container">
          <table class="data-table w-100">
            <thead>
              <tr>
                <th>ID</th>
                <th>Order #</th>
                <th>Customer</th>
                <th>Method</th>
                <th>Txn</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($payments) === 0): ?>
                <tr>
                  <td colspan="9" class="text-muted small p-3">No payments found.</td>
                </tr>
                <?php else: foreach ($payments as $p): ?>
                  <tr class="product-row" role="button" data-payment-id="<?= (int)$p['payment_id'] ?>">
                    <td><?= (int)$p['payment_id'] ?></td>
                    <td><a href="order_view.php?id=<?= (int)$p['order_id'] ?>">#<?= (int)$p['order_id'] ?></a></td>
                    <td><?= h($p['first_name'] . ' ' . $p['last_name']) ?><div class="muted-small"><?= h($p['email']) ?></div>
                    </td>
                    <td><?= h($p['payment_method']) ?></td>
                    <td><?= h($p['transaction_id'] ?: '—') ?></td>
                    <td>₹ <?= number_format((float)$p['amount'], 2) ?> <?= h($p['currency']) ?></td>
                    <td><span class="status-<?= strtolower($p['payment_status']) ?>"><?= h($p['payment_status']) ?></span></td>
                    <td><?= h($p['paid_at'] ?: $p['created_at']) ?></td>
                    <td class="text-end">
                      <button class="btn btn-sm btn-outline-secondary btn-view" data-bs-toggle="collapse" data-bs-target="#pay-details-<?= (int)$p['payment_id'] ?>">Details</button>
                      <a href="payments_edit.php?id=<?= (int)$p['payment_id'] ?>" class="btn btn-sm btn-outline-primary ms-1">Edit</a>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="9" class="p-0">
                      <div class="collapse" id="pay-details-<?= (int)$p['payment_id'] ?>">
                        <div class="p-3">
                          <div class="row">
                            <div class="col-md-4"><strong>Payment Method</strong>
                              <div class="muted-small"><?= h($p['payment_method']) ?></div>
                            </div>
                            <div class="col-md-4"><strong>Txn ID</strong>
                              <div class="muted-small"><?= h($p['transaction_id'] ?: '—') ?></div>
                            </div>
                            <div class="col-md-4"><strong>Amount</strong>
                              <div class="muted-small">₹ <?= number_format((float)$p['amount'], 2) ?> <?= h($p['currency']) ?></div>
                            </div>
                          </div>
                          <div class="mt-2 row">
                            <div class="col-md-6"><strong>Status</strong>
                              <div class="muted-small"><?= h($p['payment_status']) ?></div>
                            </div>
                            <div class="col-md-6"><strong>Recorded</strong>
                              <div class="muted-small"><?= h($p['created_at']) ?></div>
                            </div>
                          </div>

                          <!-- backend endpoints like payments_action.php or payments_edit.php should handle status changes securely -->
                          <div class="mt-3">
                            <form action="payments_action.php" method="post" class="d-flex gap-2 align-items-center">
                              <input type="hidden" name="payment_id" value="<?= (int)$p['payment_id'] ?>">
                              <select name="new_status" class="status-select">
                                <option value="Paid">Mark Paid</option>
                                <option value="Pending">Mark Pending</option>
                                <option value="Failed">Mark Failed</option>
                                <option value="Refunded">Mark Refunded</option>
                              </select>
                              <button class="btn btn-sm btn-success" type="submit">Update</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
              <?php endforeach;
              endif; ?>
            </tbody>
          </table>
        </div>

        <!-- pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="small-muted">Showing <?= count($payments) ?> of <?= $total_count ?></div>
          <nav>
            <ul class="pagination mb-0">
              <?php $last = (int)ceil($total_count / $perPage);
              $start = max(1, $page - 2);
              $end = min($last, $page + 2);
              if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="<?= h(page_url($page - 1)) ?>">Prev</a></li>
              <?php endif; ?>
              <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="<?= h(page_url($i)) ?>"><?= $i ?></a></li>
              <?php endfor; ?>
              <?php if ($page < $last): ?>
                <li class="page-item"><a class="page-link" href="<?= h(page_url($page + 1)) ?>">Next</a></li>
              <?php endif; ?>
            </ul>
          </nav>
        </div>

      </div>
    </div>
  </div>

  <script src="./assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>