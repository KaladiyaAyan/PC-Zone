<?php

// fetch categories via functions.php
$rows = getAllCategories(); // flat array ordered by level,category_name
$parents = getRootCategories();
$children_flat = getAllSubCategories();

// ensure slug and build children map: parent_id => [children]
$children = [];
foreach ($rows as $r) {
  if (empty($r['slug'])) {
    $r['slug'] = strtolower(preg_replace('/[^a-z0-9\-]+/i', '-', trim($r['category_name'])));
  }
}
foreach ($children_flat as $c) {
  $pid = (int)($c['parent_id'] ?? 0);
  if (!isset($children[$pid])) $children[$pid] = [];
  $children[$pid][] = $c;
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
<style>

</style>

<!-- Top Bar -->
<div class="top-bar">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex gap-3">
        <a href="#"><i class="fa-solid fa-phone me-1"></i> +1 234 567 8900</a>
        <a href="#"><i class="fa-solid fa-envelope me-1"></i> support@pczone.com</a>
      </div>
      <div class="d-flex gap-3 align-items-center">
        <a href="#">Track Order</a>
        <a href="#">Wishlist</a>

        <?php if ($isLogged): ?>
          <form action="logout.php" method="post" style="display:inline;">
            <button type="submit" class="top-red-btn"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</button>
          </form>
        <?php else: ?>
          <a href="login.php" class="top-red-btn"><i class="fa-solid fa-right-to-bracket me-1"></i> Login</a>
          <a href="signup.php" class="top-red-btn" style="background:#28a745;"><i class="fa-solid fa-user-plus me-1"></i> Signup</a>
        <?php endif; ?>

        <a href="#" class="top-red-btn"><i class="fa-solid fa-tag me-1"></i> Special Offers</a>
      </div>
    </div>
  </div>
</div>

<!-- Navbar -->
<header style="z-index:1100;">
  <div class="container px-3 px-lg-4">
    <div class="d-flex align-items-center py-3 gap-3">
      <a class="d-flex align-items-center text-decoration-none" href="./index.php">
        <span class="fs-5 fw-bold text-danger ms-2 d-none d-sm-inline">PC Zone</span>
      </a>

      <form class="search-form flex-fill mx-sm-3" role="search" action="/search.php" method="get">
        <div class="input-group">
          <input class="form-control border-danger" name="q" type="search" placeholder="Search products..." aria-label="Search">
          <button class="btn btn-danger" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </div>
      </form>

      <div class="d-flex align-items-center gap-2">
        <?php if ($isLogged): ?>
          <a href="/account.php" class="btn btn-outline-danger d-flex align-items-center justify-content-center" style="height:40px;padding:0 12px;">
            <i class="fa-solid fa-user me-2"></i> Account
          </a>
        <?php else: ?>
          <a href="/login.php" class="btn btn-outline-danger d-flex align-items-center justify-content-center" style="height:40px;padding:0 12px;">
            <i class="fa-solid fa-right-to-bracket me-2"></i> Login
          </a>
        <?php endif; ?>

        <a href="/cart.php" class="btn btn-danger rounded-circle d-flex align-items-center justify-content-center position-relative" style="width:40px;height:40px">
          <i class="fa-solid fa-cart-shopping text-white"></i>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark"><?php echo (int)$cartCount; ?></span>
        </a>
        <span class="fw-semibold d-none d-md-inline"><?php echo formatPrice($cartTotal); ?></span>
      </div>
    </div>
  </div>
</header>

<nav class="navbar navbar-expand-lg navbar-light p-0 site-nav">
  <div class="container px-3 px-lg-4">
    <div class="container-fluid px-0">
      <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav align-items-center gap-3">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle fw-semibold text-danger d-flex align-items-center" href="#" id="catsToggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fa-solid fa-bars me-1"></i> All Categories
            </a>

            <ul class="dropdown-menu categories-dropdown" aria-labelledby="catsToggle">
              <?php if (!empty($parents)): foreach ($parents as $cat):
                  $catId = (int)$cat['category_id'];
                  $name  = htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8');
                  $slug  = rawurlencode($cat['slug']);
                  $subs  = $children[$catId] ?? [];
              ?>
                  <?php if (empty($subs)): ?>
                    <li><a class="dropdown-item" href="product.php?slug=<?php echo $slug; ?>"><?php echo $name; ?></a></li>
                  <?php else: ?>
                    <li class="dropdown-submenu">
                      <a class="dropdown-item dropdown-toggle" href="product.php?slug=<?php echo $slug; ?>"><?php echo $name; ?></a>
                      <ul class="dropdown-menu">
                        <?php foreach ($subs as $s): ?>
                          <li><a class="dropdown-item" href="product.php?slug=<?php echo rawurlencode($s['slug']); ?>"><?php echo htmlspecialchars($s['category_name'], ENT_QUOTES, 'UTF-8'); ?></a></li>
                        <?php endforeach; ?>
                      </ul>
                    </li>
                  <?php endif; ?>
                <?php endforeach;
              else: ?>
                <li><a class="dropdown-item" href="/product.php?slug=processor">Processor</a></li>
              <?php endif; ?>
            </ul>
          </li>

          <li class="nav-item"><a class="nav-link fw-semibold" href="/pages/custom-pc.php">Custom PC</a></li>
          <li class="nav-item"><a class="nav-link fw-semibold" href="/pages/prebuilt-pc.php">Pre-built PC</a></li>
          <li class="nav-item"><a class="nav-link fw-semibold" href="/pages/contact.php">Contact Us</a></li>
          <li class="nav-item"><a class="nav-link fw-semibold" href="/pages/about.php">About Us</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>
<script>
  (function() {
    const isDesktop = () => window.matchMedia('(min-width:992px)').matches;
    const catsToggle = document.getElementById('catsToggle');
    const catsMenu = document.querySelector('.dropdown-menu.categories-dropdown[aria-labelledby="catsToggle"]');
    if (catsToggle && catsMenu) {
      const show = () => {
        catsMenu.classList.add('show');
        catsToggle.setAttribute('aria-expanded', 'true');
      };
      const hide = () => {
        catsMenu.classList.remove('show');
        catsToggle.setAttribute('aria-expanded', 'false');
      };
      catsToggle.addEventListener('mouseenter', () => {
        if (isDesktop()) show();
      });
      catsMenu.addEventListener('mouseenter', () => {
        if (isDesktop()) show();
      });
      catsToggle.addEventListener('mouseleave', () => {
        if (isDesktop()) hide();
      });
      catsMenu.addEventListener('mouseleave', () => {
        if (isDesktop()) hide();
      });
    }

    // submenus
    document.querySelectorAll('.dropdown-submenu').forEach(item => {
      const submenu = item.querySelector('> .dropdown-menu');
      if (!submenu) return;
      item.addEventListener('mouseenter', () => {
        if (isDesktop()) submenu.classList.add('show');
      });
      item.addEventListener('mouseleave', () => {
        if (isDesktop()) submenu.classList.remove('show');
      });
    });

    // cleanup when switching between mobile/desktop
    let prev = isDesktop();
    window.addEventListener('resize', () => {
      const now = isDesktop();
      if (now !== prev) {
        document.querySelectorAll('.dropdown-menu.show').forEach(el => el.classList.remove('show'));
        if (catsToggle) catsToggle.setAttribute('aria-expanded', 'false');
        prev = now;
      }
    });
  })();
</script>