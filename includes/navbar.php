<?php
require('./includes/db_connect.php');
// 1. Fetch all active categories from the database in a single query.
$sql_all_categories = "SELECT category_id, category_name, slug, parent_id 
                       FROM categories 
                       WHERE status = 'active' 
                       ORDER BY parent_id, sort_order, category_name";
// Note: Assumes $conn (database connection) is available from an included file.
$result = mysqli_query($conn, $sql_all_categories);
$all_categories = mysqli_fetch_all($result, MYSQLI_ASSOC);

// 2. Loop through the results once to create the parent and child arrays.
$parents = [];
$children = [];
foreach ($all_categories as $category) {
  if ($category['parent_id'] === NULL) {
    // If parent_id is NULL, it's a top-level category.
    $parents[] = $category;
  } else {
    // Otherwise, it's a child. Group it by its parent's ID.
    $pid = (int)$category['parent_id'];
    $children[$pid][] = $category;
  }
}


// --- The rest of your existing code stays the same ---

// logged-in checks
$isLogged = isset($_SESSION['user_id']) || isset($_SESSION['customer_id']) || !empty($_SESSION['user']);

// cart info
$cartCount = 0;
$cartTotal = 0.0;
$customerId = $_SESSION['customer_id'] ?? $_SESSION['user_id'] ?? null;
if ($customerId) {
  // These functions would still come from your functions.php file
  $cartItems = getCartItems($customerId);
  $cartCount = is_array($cartItems) ? count($cartItems) : 0;
  $cartTotal = getCartTotal($customerId);
}

// logged-in checks
$isLogged = isset($_SESSION['user_id']) || isset($_SESSION['customer_id']) || !empty($_SESSION['user']);

// cart info
$cartCount = 0;
$cartTotal = 0.0;
$customerId = $_SESSION['customer_id'] ?? $_SESSION['user_id'] ?? null;
if ($customerId) {
  $cartItems = getCartItems($customerId);
  $cartCount = is_array($cartItems) ? count($cartItems) : 0;
  $cartTotal = getCartTotal($customerId);
}
?>

<!-- Top Bar -->
<div class="top-header-bar">
  <div class="container top-bar-container">

    <div class="top-bar-contact d-none d-md-flex"> <a href="#"><i class="ri-phone-line"></i> +1 234 567 8900</a>
      <a href="#"><i class="ri-mail-line"></i> support@pczone.com</a>
    </div>

    <div class="top-bar-actions">
      <a href="#"><i class="ri-map-pin-line"></i> Track Order</a>

      <?php if ($isLogged): ?>
        <form action="logout.php" method="post">
          <button type="submit" class="logout-button">
            <i class="ri-logout-box-r-line"></i> Logout
          </button>
        </form>
      <?php else: ?>
        <a href="signup.php"><i class="ri-user-add-line"></i> Signup</a>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- Navbar -->
<header class="site-header">
  <div class="container header-container">

    <a class="header-logo" href="./index.php">
      <i class="ri-cpu-line"></i>
      <span>PC Zone</span>
    </a>

    <form class="header-search" role="search" action="/search.php" method="get">
      <i class="ri-search-line search-icon"></i>
      <input class="search-input" name="q" type="search" placeholder="Search for products...">
    </form>

    <div class="header-actions">
      <?php if ($isLogged): ?>
        <a href="/account.php" class="action-link">
          <i class="ri-user-line"></i>
          <span class="d-none d-md-inline">Account</span>
        </a>
      <?php else: ?>
        <a href="login.php" class="action-link">
          <i class="ri-login-box-line"></i>
          <span class="d-none d-md-inline">Login</span>
        </a>
      <?php endif; ?>

      <a href="cart.php" class="action-link cart-link">
        <i class="ri-shopping-cart-line"></i>
        <div class="d-none d-md-flex flex-column" style="line-height: 1.2;">
          <span>Cart</span>
          <strong class="cart-total"><?= formatPrice($cartTotal); ?></strong>
        </div>
        <?php if ($cartCount > 0): ?>
          <span class="cart-badge"><?= (int)$cartCount; ?></span>
        <?php endif; ?>
      </a>
    </div>

  </div>
</header>

<nav class="site-navigation navbar navbar-expand-lg">
  <div class="container">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="main-nav-menu navbar-nav">
        <li class="main-nav-item">
          <a class="main-nav-link all-categories-link" href="product.php" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="ri-menu-line"></i> All Categories
          </a>
          <ul class="dropdown-menu">
            <?php foreach ($parents as $cat):
              $catId = (int)$cat['category_id'];
              $subs = $children[$catId] ?? [];
            ?>
              <li class="main-nav-item">
                <a class="dropdown-item <?= !empty($subs) ? 'has-submenu' : '' ?>" href="product.php?slug=<?= e($cat['slug']) ?>">
                  <?= e($cat['category_name']) ?>
                </a>
                <?php if (!empty($subs)): ?>
                  <ul class="dropdown-menu submenu">
                    <?php foreach ($subs as $sub): ?>
                      <li><a class="dropdown-item" href="product.php?slug=<?= e($sub['slug']) ?>"><?= e($sub['category_name']) ?></a></li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </li>

        <li class="main-nav-item"><a class="main-nav-link" href="index.php#custom">Custom PC</a></li>
        <li class="main-nav-item"><a class="main-nav-link" href="index.php#contact">Contact Us</a></li>
        <li class="main-nav-item"><a class="main-nav-link" href="/pages/about.php">About Us</a></li>
      </ul>
    </div>
  </div>
</nav>