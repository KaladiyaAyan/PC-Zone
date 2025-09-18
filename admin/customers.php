<?php
include '../includes/db_connect.php';
include './includes/functions.php';

session_start();
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ./login.php');
  exit;
}

/* helper */
function h($s)
{
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$perPage = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$search = trim($_GET['q'] ?? '');
$offset = ($page - 1) * $perPage;

/* POST action: update status */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && ctype_digit($_POST['id'])) {
  $id = (int)$_POST['id'];
  if ($_POST['action'] === 'update_status' && isset($_POST['status'])) {
    $allowed = ['active', 'inactive', 'banned'];
    $new = $_POST['status'];
    if (!in_array($new, $allowed, true)) $new = 'inactive';
    $stmt = mysqli_prepare($conn, "UPDATE users SET status = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $new, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('Location: customers.php?q=' . urlencode($search) . '&page=' . $page);
    exit;
  }
}

/* Count total matching customers */
if ($search !== '') {
  $like = '%' . $search . '%';
  $cnt_sql = "SELECT COUNT(*) FROM users WHERE (username LIKE ? OR email LIKE ? OR phone LIKE ?)";
  $stmt = mysqli_prepare($conn, $cnt_sql);
  mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
} else {
  $cnt_sql = "SELECT COUNT(*) FROM users";
  $stmt = mysqli_prepare($conn, $cnt_sql);
}
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $total);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
$total = (int)$total;
$totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;

/* Fetch customers with last purchase, orders count and total purchases */
if ($search !== '') {
  $like = '%' . $search . '%';
  $sql = "
    SELECT
      u.user_id,
      u.username,
      u.email,
      u.phone,
      u.status,
      u.created_at,
      MAX(o.created_at) AS last_order,
      COUNT(o.order_id) AS orders_count,
      COALESCE(SUM(o.total_amount), 0) AS total_purchases
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.user_id
    WHERE (u.username LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)
    GROUP BY u.user_id
    ORDER BY (MAX(o.created_at) IS NULL) ASC, MAX(o.created_at) DESC, u.created_at DESC
    LIMIT ?, ?
  ";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, 'sssii', $like, $like, $like, $offset, $perPage);
} else {
  $sql = "
    SELECT
      u.user_id,
      u.username,
      u.email,
      u.phone,
      u.status,
      u.created_at,
      MAX(o.created_at) AS last_order,
      COUNT(o.order_id) AS orders_count,
      COALESCE(SUM(o.total_amount), 0) AS total_purchases
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.user_id
    GROUP BY u.user_id
    ORDER BY (MAX(o.created_at) IS NULL) ASC, MAX(o.created_at) DESC, u.created_at DESC
    LIMIT ?, ?
  ";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, 'ii', $offset, $perPage);
}

mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Customers - PCZone Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <?php include './includes/header-link.php'; ?>

</head>

<body>
  <?php
  $current_page = 'customers';
  include './includes/header.php';
  include './includes/sidebar.php';
  ?>
  <div class="main-content pt-5 mt-4">
    <div class="container mt-2">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h2 class="mb-0">Customers</h2>
          <small class="text-muted">Sorted by most recent purchase. Use search to filter by name, email or phone.</small>
        </div>
        <form class="d-flex" method="get" action="customers.php">
          <input name="q" class="form-control form-control-sm search-input" placeholder="Search name/email/phone" value="<?= h($search) ?>">

          <button class="btn btn-add btn-sm ms-2" type="submit">Search</button>
        </form>
      </div>

      <div class="table-container">
        <table class="data-table table table-hover table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Orders</th>
              <th>Last Purchase</th>
              <th>Total Purchases</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i = $offset + 1;
            while ($r = mysqli_fetch_assoc($res)):
              $lastOrder = $r['last_order'];
            ?>
              <tr>
                <td><?= $i++; ?></td>
                <td><?= h($r['username']) ?></td>
                <td><?= h($r['email']); ?></td>
                <td><?= h($r['phone'] ?: '—'); ?></td>
                <td><?= (int)$r['orders_count']; ?></td>
                <td><?= $lastOrder ? h(date('d M Y, H:i', strtotime($lastOrder))) : '—'; ?></td>
                <td>₹ <?= number_format((float)$r['total_purchases'], 2) ?></td>
                <td>
                  <form method="post" style="display:inline-block">
                    <input type="hidden" name="id" value="<?= (int)$r['user_id'] ?>">
                    <input type="hidden" name="action" value="update_status">
                    <select name="status" class="form-select form-select-sm status-select" onchange="this.form.submit()">

                      <?php
                      $statuses = ['active', 'inactive', 'banned'];
                      foreach ($statuses as $s):
                      ?>
                        <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </td>
                <td class="text-end">
                  <a href="customer_view.php?id=<?= (int)$r['user_id'] ?>" class="btn btn-edit btn-sm">View</a>
                </td>
              </tr>
            <?php endwhile;
            if ($total === 0): ?>
              <tr>
                <td colspan="9" class="text-center text-muted">No customers found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- pagination -->
      <?php if ($totalPages > 1): ?>
        <nav class="mt-3">
          <ul class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
              <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link" href="customers.php?q=<?= urlencode($search) ?>&page=<?= $p ?>"><?= $p ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      <?php endif; ?>

    </div>
  </div>

  <script src="./assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
// cleanup
mysqli_free_result($res);
mysqli_stmt_close($stmt);
?>