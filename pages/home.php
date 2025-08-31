<?php
require './includes/db_connect.php';
// Get featured products (limit 8)
$featuredProducts = getFeaturedProducts(4, true);


// Sample pricing for custom builds (you can fetch from database or config)
$intelStartingPrice = 45000; // Starting price for Intel builds
$amdStartingPrice = 42000;   // Starting price for AMD builds
?>

<main class="container mt-4">
  <!-- Hero Section -->
  <div class="row">
    <div class="col-12">
      <div class="jumbotron bg-primary text-white p-5 rounded">
        <h1 class="display-4">Welcome to PC ZONE</h1>
        <p class="lead">Build your dream PC with premium components and expert guidance.</p>
        <a class="btn btn-light btn-lg me-3" href="pages/products.php" role="button">Shop Now</a>
        <a class="btn btn-outline-light btn-lg" href="pages/custom-pc.php" role="button">Build Custom PC</a>
      </div>
    </div>
  </div>

  <!-- Featured Products -->
  <div class="row mt-5">
    <div class="col-12">
      <h2 class="fw-bold text-center mb-4">Featured Products</h2>
    </div>
  </div>

  <div class="row">
    <?php if (empty($featuredProducts)): ?>
      <div class="col-12">
        <p class="text-muted text-center">No featured products found.</p>
      </div>
    <?php else: ?>
      <?php foreach ($featuredProducts as $product):
        $pid   = (int)($product['product_id'] ?? 0);
        $name  = $product['product_name'] ?? '';
        $desc  = $product['description'] ?? '';
        $img   = $product['main_image'] ?? ($product['image_path'] ?? 'placeholder.png');
        $avg   = round(floatval($product['avg_rating'] ?? 0) * 2) / 2;
        $reviews = (int)($product['review_count'] ?? 0);
        $full  = (int)floor($avg);
        $half  = (($avg - $full) == 0.5) ? 1 : 0;
        $empty = 5 - $full - $half;
        $priceToShow = $product['final_price'] ?? $product['price'];

        // NEW: preferred product URL by slug, fallback to id
        $slug = trim($product['slug'] ?? '');
        $productUrl = $slug !== ''
          ? 'pages/product.php?slug=' . urlencode($slug)
          : 'pages/product.php?id=' . $pid; // fallback if slug missing
      ?>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
          <div class="card h-100 shadow-sm border-0 product-card">
            <a href="<?= e($productUrl) ?>">
              <img src="assets/images/products/<?= e($img) ?>" class="card-img-top p-3" alt="<?= e($name) ?>">
            </a>

            <div class="card-body d-flex flex-column">
              <h5 class="card-title mb-1">
                <a href="<?= e($productUrl) ?>" class="product-link"><?= e($name) ?></a>
              </h5>

              <div class="mb-1">
                <?php
                for ($i = 0; $i < $full; $i++) echo '<i class="bi bi-star-fill star-fill"></i>';
                if ($half) echo '<i class="bi bi-star-half star-fill"></i>';
                for ($i = 0; $i < $empty; $i++) echo '<i class="bi bi-star star-empty"></i>';
                ?>
                <a href="<?= e($productUrl) ?>#reviews" class="review-count">
                  <?= $reviews > 0 ? ' ' . e($reviews) : ' 0' ?>
                </a>
              </div>

              <p class="card-text small text-muted mb-2"><?= e(mb_strimwidth($desc, 0, 80, '…')) ?></p>

              <div class="mb-2">
                <span class="fw-bold text-danger"><?= formatPrice($priceToShow) ?></span>
                <?php if (!empty($product['discount']) && $product['discount'] > 0): ?>
                  <span class="text-muted text-decoration-line-through ms-2"><?= formatPrice($product['price']) ?></span>
                  <span class="badge bg-success ms-2"><?= intval($product['discount']) ?>% off</span>
                <?php endif; ?>
              </div>

              <a href="<?= e($productUrl) ?>" class="btn btn-sm btn-warning mt-auto">View Details</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Custom PC Build Section -->
  <div class="custom-build-section">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Custom PC Builds</h2>
      <p class="text-muted">Choose your preferred processor and let us build your perfect gaming rig</p>
    </div>

    <div class="row g-4">
      <!-- Intel Build -->
      <div class="col-md-6">
        <div class="build-card">
          <img src="assets/images/Intel_Custom_Build.jpg" alt="Intel Custom PC Build"
            onerror="this.src='assets/images/placeholder-intel-build.jpg'">
          <div class="build-info">
            <div class="intel-brand brand-logo">
              <i class="fab fa-intel"></i>
            </div>
            <h4 class="fw-bold mb-3">Intel Custom Builds</h4>
            <p class="text-muted mb-3">Powered by latest Intel processors for exceptional single-core performance and gaming excellence.</p>

            <div class="price-tag">
              Starting from ₹<?php echo number_format($intelStartingPrice); ?>
            </div>

            <ul class="list-unstyled mt-3">
              <li><i class="bi bi-check-circle-fill text-success me-2"></i>Intel 12th/13th Gen Processors</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i>DDR5 Memory Support</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i>PCIe 5.0 Ready</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i>2 Year Warranty</li>
            </ul>

            <a href="pages/custom-pc.php?platform=intel" class="btn btn-primary w-100 mt-3">
              Build Intel PC
            </a>
          </div>
        </div>
      </div>

      <!-- AMD Build -->
      <div class="col-md-6">
        <div class="build-card">
          <img src="assets/images/AMD_Custom_Build.jpg" alt="AMD Custom PC Build"
            onerror="this.src='assets/images/placeholder-amd-build.jpg'">
          <div class="build-info">
            <div class="amd-brand brand-logo">
              AMD
            </div>
            <h4 class="fw-bold mb-3">AMD Custom Builds</h4>
            <p class="text-muted mb-3">Experience superior multi-core performance with AMD Ryzen processors for content creation and gaming.</p>

            <div class="price-tag">
              Starting from ₹<?php echo number_format($amdStartingPrice); ?>
            </div>

            <ul class="list-unstyled mt-3">
              <li><i class="bi bi-check-circle-fill text-success me-2"></i>AMD Ryzen 5000/7000 Series</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i>High Core Count Options</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i>Excellent Price-Performance</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i>2 Year Warranty</li>
            </ul>

            <a href="pages/custom-pc.php?platform=amd" class="btn btn-danger w-100 mt-3">
              Build AMD PC
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

</main>