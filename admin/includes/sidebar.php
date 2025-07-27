<aside id="sidebar" class="sidebar">
  <div class="admin-profile">
    <img src="./assets/images/admin.jpg" class="admin-avatar" alt="Admin">
    <div class="admin-info">
      <strong>Administrator</strong>
      <span class="status online">● Online</span>
    </div>
  </div>
  <nav class="nav-links">
    <p class="nav-title">MAIN NAVIGATION</p>
    <a href="dashboard.php" class="nav-item <?= ($page == 'dashboard' ?  'active' : '') ?>">
      <i class="fas fa-chart-line icon"></i>
      <span class="label">Dashboard</span>
    </a>
    <a href="products.php" class="nav-item <?= ($page == 'products' ?  'active' : '') ?>">
      <i class="fas fa-boxes icon"></i>
      <span class="label">Products</span>
    </a>
    <a href="categories.php" class="nav-item">
      <i class="fas fa-layer-group icon"></i>
      <span class="label">Categories</span>
    </a>
    <a href="brands.php" class="nav-item">
      <i class="fas fa-tags icon"></i>
      <span class="label">Brands</span>
    </a>
    <a href="orders.php" class="nav-item">
      <i class="fas fa-shopping-cart icon"></i>
      <span class="label">Orders</span>
    </a>
    <a href="customers.php" class="nav-item">
      <i class="fas fa-users icon"></i>
      <span class="label">Customers</span>
    </a>
    <a href="reports.php" class="nav-item">
      <i class="fas fa-chart-pie icon"></i>
      <span class="label">Reports</span>
    </a>
    <a href="settings.php" class="nav-item">
      <i class="fas fa-cogs icon"></i>
      <span class="label">Settings</span>
    </a>
  </nav>
</aside>