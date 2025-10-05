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

// --- Fetch User Details ---
$stmt = $conn->prepare("SELECT username, email, phone, date_of_birth, gender FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// --- Fetch Address Details ---
$stmt = $conn->prepare("SELECT * FROM user_address WHERE user_id = ? ORDER BY is_default DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$address = $result->fetch_assoc();
if (!is_array($address)) {
  $address = [];
}
$stmt->close();

// --- Fetch Order History ---
$orders_sql = "
  SELECT
    MIN(o.order_id) AS group_order_id,
    o.created_at AS order_date,
    SUM(o.total_price) AS total_price,
    COUNT(o.order_id) AS items_count,
    MAX(pay.payment_status) AS payment_status
  FROM orders o
  LEFT JOIN payments pay ON pay.order_id = o.order_id
  WHERE o.user_id = ?
  GROUP BY o.created_at
  ORDER BY o.created_at DESC
";
$stmt_orders = $conn->prepare($orders_sql);
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$grouped_orders = $stmt_orders->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_orders->close();

// Prepared statements for order details
$stmt_items = $conn->prepare(
  "SELECT p.product_name, o.quantity, o.total_price
     FROM orders o JOIN products p ON o.product_id = p.product_id
     WHERE o.user_id = ? AND o.created_at = ?"
);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Account - PC Zone</title>
  <?php include('./includes/header-link.php') ?>
  <link rel="stylesheet" href="assets/css/account.css">
  <style>
    /* Simple styles to make our custom tabs work */
    .tab-content .tab-pane {
      display: none;
    }

    .tab-content .tab-pane.active {
      display: block;
    }

    .account-nav .nav-link {
      cursor: pointer;
    }
  </style>
</head>

<body>
  <?php include('./includes/alert.php'); ?>
  <?php include('./includes/navbar.php'); ?>

  <div class="container py-5">
    <div class="row">
      <div class="col-lg-3">
        <div class="card p-3">
          <div class="d-flex align-items-center mb-3">
            <div class="ps-2">
              <h5 class="mb-0"><?php echo e($user['username']); ?></h5>
              <p class="text-muted mb-0 small"><?php echo e($user['email']); ?></p>
            </div>
          </div>
          <nav class="nav flex-column nav-pills account-nav">
            <a class="nav-link active" data-tab-target="#profile-pane"><i class="fas fa-user-circle"></i> My Profile</a>
            <a class="nav-link" data-tab-target="#orders-pane"><i class="fas fa-box"></i> Orders</a>
            <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
          </nav>
        </div>
      </div>

      <div class="col-lg-9 mt-4 mt-lg-0">
        <div class="tab-content">
          <div class="tab-pane active" id="profile-pane">
            <div class="card mb-4">
              <div class="card-header">
                <h5 class="mb-0">Edit Profile</h5>
              </div>
              <div class="card-body">
                <form method="POST" action="verify.php">
                  <div class="row">
                    <div class="col-md-6 mb-3"><label for="username" class="form-label">Username</label><input type="text" class="form-control" id="username" name="username" value="<?php echo e($user['username'] ?? ''); ?>" required></div>
                    <div class="col-md-6 mb-3"><label for="email" class="form-label">Email Address</label><input type="email" class="form-control" id="email" value="<?php echo e($user['email'] ?? ''); ?>" disabled readonly>
                      <div class="form-text">Email address cannot be changed.</div>
                    </div>
                    <div class="col-md-6 mb-3"><label for="password" class="form-label">Password <span class="text-muted">(leave blank to keep current)</span></label><input type="password" class="form-control" id="password" name="password" placeholder="Enter new password if you want to change"></div>
                    <div class="col-md-6 mb-3"><label for="phone" class="form-label">Phone Number</label><input type="tel" class="form-control" id="phone" name="phone" value="<?php echo e($user['phone'] ?? ''); ?>"></div>
                    <div class="col-md-6 mb-3"><label for="dob" class="form-label">Date of Birth</label><input type="date" class="form-control" id="dob" name="dob" value="<?php echo e($user['date_of_birth'] ?? ''); ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Gender</label><select class="form-select" name="gender">
                        <option value="" <?php echo !isset($user['gender']) ? 'selected' : ''; ?>>Select...</option>
                        <option value="Male" <?php echo ($user['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($user['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($user['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                      </select></div>
                  </div><button type="submit" name="update_profile" class="btn btn-gradient">Save Profile Changes</button>
                </form>
              </div>
            </div>
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">Manage Address</h5>
              </div>
              <div class="card-body">
                <form method="POST" action="verify.php">
                  <div class="row">
                    <div class="col-md-6 mb-3"><label for="full_name" class="form-label">Full Name</label><input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo e($address['full_name'] ?? ''); ?>" required></div>
                    <div class="col-md-6 mb-3"><label for="address_phone" class="form-label">Phone Number</label><input type="tel" class="form-control" id="address_phone" name="address_phone" value="<?php echo e($address['phone'] ?? ''); ?>" required></div>
                    <div class="col-12 mb-3"><label for="address1" class="form-label">Address Line 1</label><input type="text" class="form-control" id="address1" name="address1" placeholder="1234 Main St" value="<?php echo e($address['address_line1'] ?? ''); ?>" required></div>
                    <div class="col-12 mb-3"><label for="address2" class="form-label">Address Line 2 <span class="text-muted">(Optional)</span></label><input type="text" class="form-control" id="address2" name="address2" placeholder="Apartment, studio, or floor" value="<?php echo e($address['address_line2'] ?? ''); ?>"></div>
                    <div class="col-md-6 mb-3"><label for="city" class="form-label">City</label><input type="text" class="form-control" id="city" name="city" value="<?php echo e($address['city'] ?? ''); ?>" required></div>
                    <div class="col-md-4 mb-3"><label for="state" class="form-label">State</label><input type="text" class="form-control" id="state" name="state" value="<?php echo e($address['state'] ?? ''); ?>" required></div>
                    <div class="col-md-2 mb-3"><label for="zip" class="form-label">Zip Code</label><input type="text" class="form-control" id="zip" name="zip" value="<?php echo e($address['zip'] ?? ''); ?>" required></div>
                    <div class="col-12 mb-3"><label for="country" class="form-label">Country</label><input type="text" class="form-control" id="country" name="country" value="<?php echo e($address['country'] ?? ''); ?>" required></div>
                  </div><button type="submit" name="update_address" class="btn btn-gradient">Save Address</button>
                </form>
              </div>
            </div>
          </div>

          <div class="tab-pane" id="orders-pane">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">My Order History</h5>
              </div>
              <div class="card-body">
                <?php if (empty($grouped_orders)) : ?>
                  <p class="text-center text-muted">You haven't placed any orders yet.</p>
                <?php else : ?>
                  <div class="table-responsive">
                    <table class="table">
                      <thead>
                        <tr>
                          <th>Order #</th>
                          <th>Date</th>
                          <th>Total</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($grouped_orders as $order) : ?>
                          <tr>
                            <td>#<?php echo $order['group_order_id']; ?></td>
                            <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                            <td>₹<?php echo number_format($order['total_price'], 2); ?></td>
                            <td><span class="badge bg-secondary"><?php echo e(ucfirst($order['payment_status'])); ?></span></td>
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
    </div>
  </div>
  <?php include './includes/footer.php'; ?>
  <?php include './includes/footer-link.php'; ?>

  <script>
    const tabs = document.querySelectorAll('.account-nav .nav-link[data-tab-target]');
    const tabContents = document.querySelectorAll('.tab-content .tab-pane');

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        const target = document.querySelector(tab.dataset.tabTarget);

        // Update content panes
        tabContents.forEach(pane => {
          pane.classList.remove('active');
        });
        target.classList.add('active');

        // Update tab links
        tabs.forEach(t => {
          t.classList.remove('active');
        });
        tab.classList.add('active');
      });
    });
  </script>
</body>

</html>