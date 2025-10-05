<?php
session_start();
require('../includes/db_connect.php');
require('../includes/functions.php');

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ../login.php');
  exit;
}

// safe simple conversion to int
$user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

/* Fetch user (simple mysqli query, no prepared stmt) */
$user = [
  'username' => '',
  'email' => '',
  'profile_image' => '',
  'date_of_birth' => '',
  'gender' => ''
];

if ($user_id > 0) {
  $sql = "SELECT username, email, profile_image, date_of_birth, gender FROM users WHERE user_id = $user_id LIMIT 1";
  $res = mysqli_query($conn, $sql);
  if ($res && mysqli_num_rows($res) > 0) {
    $user = mysqli_fetch_assoc($res);
  }
}

/* Fetch address (default/first) */
$address = [];

if ($user_id > 0) {
  $sql = "SELECT * FROM user_address WHERE user_id = $user_id ORDER BY is_default DESC LIMIT 1";
  $res = mysqli_query($conn, $sql);
  if ($res && mysqli_num_rows($res) > 0) {
    $address = mysqli_fetch_assoc($res);
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Account - PC Zone</title>
  <?php include('./includes/header-link.php') ?>
</head>

<body>
  <?php require('./includes/alert.php');
  include './includes/header.php';
  $current_page = 'settings';
  include './includes/sidebar.php'; ?>

  <div class="main-content">
    <div class="page-header">
      <h2>Account Settings</h2>
    </div>

    <!-- Edit Profile -->
    <div class="content-wrapper">
      <div class="col-lg-9 mt-4 mt-lg-0">
        <div class="theme-card mb-4">
          <div class="card-header">
            <h5 class="mb-0">Edit Profile</h5>
          </div>
          <div class="card-body">
            <!-- Note: enctype added for file upload -->
            <form method="POST" action="verify.php" enctype="multipart/form-data">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="username" class="form-label">Username</label>
                  <input type="text" class="form-control" id="username" name="username" value="<?php echo e($user['username'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="email" class="form-label">Email Address</label>
                  <input type="email" class="form-control" id="email" value="<?php echo e($user['email'] ?? ''); ?>" disabled readonly>
                  <div class="form-text text-small-muted">Email address cannot be changed.</div>
                </div>

                <div class="col-md-6 mb-3">
                  <label for="password" class="form-label">Password <span class="text-small-muted">(leave blank to keep current)</span></label>
                  <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password to change">
                </div>

                <!-- Profile Image field (new) -->
                <div class="col-md-6 mb-3">
                  <label for="profile_image" class="form-label">Profile Image <span class="text-small-muted">(optional)</span></label>
                  <?php if (!empty($user['profile_image'])): ?>
                    <div class="mb-2">
                      <img src="<?php echo e($user['profile_image']); ?>" alt="Profile Image" style="max-width:80px; max-height:80px; border-radius:6px;">
                    </div>
                  <?php endif; ?>
                  <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                  <div class="form-text text-small-muted">Recommended: JPG/PNG. Keep file size small.</div>
                </div>

                <div class="col-md-6 mb-3">
                  <label for="dob" class="form-label">Date of Birth</label>
                  <input type="date" class="form-control" id="dob" name="dob" value="<?php echo e($user['date_of_birth'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Gender</label>
                  <select class="form-select" name="gender">
                    <option value="" <?php echo !isset($user['gender']) ? 'selected' : ''; ?>>Select...</option>
                    <option value="Male" <?php echo ($user['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($user['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo ($user['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                  </select>
                </div>
              </div>
              <button type="submit" name="update_profile" class="btn-add">Save Profile Changes</button>
            </form>
          </div>
        </div>
        <!-- Manage Address -->
        <div class="theme-card">
          <div class="card-header">
            <h5 class="mb-0">Manage Address</h5>
          </div>
          <div class="card-body">
            <form method="POST" action="verify.php">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="full_name" class="form-label">Full Name</label>
                  <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo e($address['full_name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="address_phone" class="form-label">Phone Number</label>
                  <input type="tel" class="form-control" id="address_phone" name="address_phone" value="<?php echo e($address['phone'] ?? ''); ?>" required>
                </div>
                <div class="col-12 mb-3">
                  <label for="address1" class="form-label">Address Line 1</label>
                  <input type="text" class="form-control" id="address1" name="address1" placeholder="1234 Main St" value="<?php echo e($address['address_line1'] ?? ''); ?>" required>
                </div>
                <div class="col-12 mb-3">
                  <label for="address2" class="form-label">Address Line 2 <span class="text-small-muted">(Optional)</span></label>
                  <input type="text" class="form-control" id="address2" name="address2" placeholder="Apartment, studio, or floor" value="<?php echo e($address['address_line2'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="city" class="form-label">City</label>
                  <input type="text" class="form-control" id="city" name="city" value="<?php echo e($address['city'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label for="state" class="form-label">State</label>
                  <input type="text" class="form-control" id="state" name="state" value="<?php echo e($address['state'] ?? ''); ?>" required>
                </div>
                <div class="col-md-2 mb-3">
                  <label for="zip" class="form-label">Zip Code</label>
                  <input type="text" class="form-control" id="zip" name="zip" value="<?php echo e($address['zip'] ?? ''); ?>" required>
                </div>
                <div class="col-12 mb-3">
                  <label for="country" class="form-label">Country</label>
                  <input type="text" class="form-control" id="country" name="country" value="<?php echo e($address['country'] ?? ''); ?>" required>
                </div>
              </div>
              <button type="submit" name="update_address" class="btn-add">Save Address</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php include './includes/footer-link.php'; ?>
</body>

</html>