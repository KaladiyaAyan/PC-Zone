<?php
// Function to determine if a nav link is active
function active($name)
{
  // Prefer explicit page marker if set
  if (!empty($GLOBALS['current_page'])) {
    return ($GLOBALS['current_page'] === $name) ? 'active' : '';
  }
  // Fallback to filename check
  return (basename($_SERVER['PHP_SELF']) === $name . '.php') ? 'active' : '';
}
?>

<link rel="stylesheet" href="./assets/css/style.css">
<!-- Link to the dedicated sidebar stylesheet -->
<link rel="stylesheet" href="./assets/css/sidebar.css">

<aside id="sidebar" class="sidebar">
  <div class="admin-profile d-flex align-items-center px-3 mb-3">
    <img src="./assets/images/admin.jpg" alt="avatar" class="admin-avatar">
    <div class="admin-info ms-2">
      <strong><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Admin') ?></strong>
      <span class="status">Online</span>
    </div>
  </div>

  <nav class="nav-links px-2">
    <div class="nav-title">Main</div>
    <a href="index.php" class="<?= active('dashboard') ?>">
      <span class="icon"><i class="fas fa-tachometer-alt"></i></span>
      <span class="label">Dashboard</span>
    </a>
    <a href="./product.php" class="<?= active('product') ?>">
      <span class="icon"><i class="fas fa-boxes"></i></span>
      <span class="label">Products</span>
    </a>
    <a href="./categories.php" class="<?= active('categories') ?>">
      <span class="icon"><i class="fas fa-tags"></i></span>
      <span class="label">Categories</span>
    </a>
    <a href="./brands.php" class="<?= active('brands') ?>">
      <span class="icon"><i class="fas fa-tag"></i></span> <!-- Changed to singular tag for variety -->
      <span class="label">Brands</span>
    </a>
    <a href="./orders.php" class="<?= active('orders') ?>">
      <span class="icon"><i class="fas fa-shopping-cart"></i></span>
      <span class="label">Orders</span>
    </a>
    <a href="./customers.php" class="<?= active('customers') ?>">
      <span class="icon"><i class="fas fa-users"></i></span>
      <span class="label">Customers</span>
    </a>
    <a href="./payment.php" class="<?= active('payments') ?>">
      <span class="icon"><i class="fas fa-credit-card"></i></span>
      <span class="label">Payments</span>
    </a>

    <div class="nav-title mt-3">Reports</div>
    <a href="./reports.php" class="<?= active('reports') ?>">
      <span class="icon"><i class="fas fa-chart-line"></i></span>
      <span class="label">Reports</span>
    </a>

    <div class="nav-title mt-3">Settings</div>
    <a href="./settings.php" class="<?= active('settings') ?>">
      <span class="icon"><i class="fas fa-cog"></i></span>
      <span class="label">Settings</span>
    </a>
    <a href="./logout.php" class="mt-3">
      <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
      <span class="label">Logout</span>
    </a>
  </nav>
</aside>