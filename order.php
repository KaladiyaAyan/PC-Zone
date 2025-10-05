<?php
session_start();
require('./includes/db_connect.php');
require('./includes/functions.php');

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

/*
 Query:
 - list each order item for the user
 - include order id, order date, item price, quantity, status and product name
 - product image fetched separately (first image found) to avoid join-column name assumptions
*/
$sql = "SELECT o.order_id, o.created_at, o.status, oi.product_id, oi.quantity, oi.price, p.product_name
        FROM orders o
        JOIN order_items oi ON oi.order_id = o.order_id
        LEFT JOIN products p ON p.product_id = oi.product_id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC, o.order_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$rows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// helper to get one image filename for a product (returns empty string if none)
$getImageStmt = $conn->prepare("SELECT image FROM product_images WHERE product_id = ? LIMIT 1");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Orders - PC Zone</title>
  <?php include('./includes/header-link.php'); ?>
  <link rel="stylesheet" href="assets/css/account.css">
</head>

<body>
  <?php include('./includes/alert.php'); ?>
  <?php include('./includes/navbar.php'); ?>

  <div class="container py-5">
    <div class="row">

      <!-- Sidebar (matches account.php) -->
      <div class="col-lg-3">
        <div class="card p-3">
          <div class="d-flex align-items-center mb-3">
            <div class="ps-2">
              <h5 class="mb-0"><?php echo e($_SESSION['username'] ?? ''); ?></h5>
              <p class="text-muted mb-0 small"><?php echo e($_SESSION['email'] ?? ''); ?></p>
            </div>
          </div>
          <nav class="nav flex-column nav-pills account-nav">
            <a class="nav-link" href="account.php"><i class="fas fa-user-circle"></i> My Profile</a>
            <a class="nav-link active" href="order.php"><i class="fas fa-shopping-bag"></i> Order</a>
            <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
          </nav>
        </div>
      </div>

      <!-- Orders content -->
      <div class="col-lg-9 mt-4 mt-lg-0">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">My Orders</h5>
          </div>
          <div class="card-body">
            <?php if (count($rows) === 0): ?>
              <p class="text-muted">You have not placed any orders yet.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table align-middle">
                  <thead>
                    <tr>
                      <th>Order ID</th>
                      <th>Product</th>
                      <th>Order Date</th>
                      <th>Price</th>
                      <th>Quantity</th>
                      <th>Total Price</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($rows as $row):
                      // fetch product image (first found). safe guard for column missing by suppressing error
                      $productImage = '';
                      if (!empty($row['product_id'])) {
                        $getImageStmt->bind_param("i", $row['product_id']);
                        $getImageStmt->execute();
                        $imgRes = $getImageStmt->get_result();
                        if ($imgRow = $imgRes->fetch_assoc()) {
                          $productImage = $imgRow['image'] ?? '';
                        }
                      }

                      // build image path and fallback
                      $imgPath = !empty($productImage) ? 'assets/images/products/' . $productImage : 'assets/images/placeholder.png';

                      // formatting
                      $orderDate = !empty($row['created_at']) ? date("d-m-Y", strtotime($row['created_at'])) : '';
                      $price = number_format((float)$row['price'], 2);
                      $qty = (int)$row['quantity'];
                      $total = number_format($price * $qty, 2);

                      // status badge
                      $status = $row['status'] ?? 'Unknown';
                      $statusLower = strtolower($status);
                      if (strpos($statusLower, 'cancel') !== false) {
                        $badgeClass = 'badge bg-danger';
                      } elseif (strpos($statusLower, 'pend') !== false) {
                        $badgeClass = 'badge bg-warning text-dark';
                      } elseif (strpos($statusLower, 'comp') !== false || strpos($statusLower, 'deliv') !== false || strpos($statusLower, 'success') !== false) {
                        $badgeClass = 'badge bg-success';
                      } else {
                        $badgeClass = 'badge bg-secondary';
                      }
                    ?>
                      <tr>
                        <td>#<?php echo e($row['order_id']); ?></td>
                        <td style="min-width:250px;">
                          <div class="d-flex align-items-center">
                            <img src="<?php echo e($imgPath); ?>" alt="product" style="width:64px;height:64px;object-fit:cover;border-radius:6px;margin-right:12px;">
                            <div>
                              <div class="fw-semibold"><?php echo e($row['product_name'] ?? 'Product'); ?></div>
                            </div>
                          </div>
                        </td>
                        <td><?php echo e($orderDate); ?></td>
                        <td>₹<?php echo e($price); ?></td>
                        <td><?php echo e($qty); ?></td>
                        <td>₹<?php echo e($total); ?></td>
                        <td><span class="<?php echo $badgeClass; ?>"><?php echo e($status); ?></span></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </div>

  <?php include './includes/footer.php'; ?>
  <?php include './includes/footer-link.php'; ?>
</body>

</html>
<?php
$getImageStmt->close();
$conn->close();
?>