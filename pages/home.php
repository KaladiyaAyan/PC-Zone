<?php
require_once INCLUDES_PATH . 'db_connect.php';

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
        <div class="card build-card-modern  shadow-sm position-relative h-100">
          <div class="custom-build-img bg-light">
            <img src="assets/images/intel_custom_build.jpg"
              onerror="this.src='assets/images/placeholder-intel-build.jpg'"
              alt="Intel Custom PC Build" class="w-100 h-100 object-fit-cover">
          </div>

          <span class="brand-badge badge rounded-pill bg-light border text-dark fw-semibold">
            Intel
          </span>

          <div class="card-body">
            <h3 class="h5 fw-semibold mb-2">Intel Custom Builds</h3>
            <p class="text-secondary mb-3">
              Latest Intel CPUs for strong single-core performance and smooth gaming.
            </p>

            <div class="d-flex align-items-baseline gap-2 mb-2">
              <span class="price h5 mb-0">Starting from ₹<?= number_format($intelStartingPrice) ?></span>
            </div>

            <ul class="specs list-unstyled text-secondary mb-3">
              <li><i class="bi bi-check-circle-fill text-success"></i> 12th/13th/14th Gen options</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> DDR5 support</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> PCIe 5.0 ready</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> 2-year warranty</li>
            </ul>

            <a href="<?php echo BASE_URL . 'pages/custom-pc.php?platform=intel'; ?>" class="btn btn-primary w-100">
              Build Intel PC
            </a>
          </div>
        </div>
      </div>

      <!-- AMD Build -->
      <div class="col-md-6">
        <div class="card build-card-modern shadow-sm position-relative h-100">
          <div class="custom-build-img bg-light">
            <img src="assets/images/amd_custom_build.jpg"
              onerror="this.src='assets/images/placeholder-amd-build.jpg'"
              alt="AMD Custom PC Build" class="w-100 h-100 object-fit-cover">
          </div>

          <span class="brand-badge badge rounded-pill bg-light border text-dark fw-semibold">
            AMD
          </span>

          <div class="card-body">
            <h3 class="h5 fw-semibold mb-2">AMD Custom Builds</h3>
            <p class="text-secondary mb-3">
              Ryzen multi-core power for creation and gaming at great value.
            </p>

            <div class="d-flex align-items-baseline gap-2 mb-2">
              <span class="price h5 mb-0">Starting from ₹<?= number_format($amdStartingPrice) ?></span>
            </div>

            <ul class="specs list-unstyled text-secondary mb-3">
              <li><i class="bi bi-check-circle-fill text-success"></i> Ryzen 5000/7000 series</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> High core counts</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Strong price-performance</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> 2-year warranty</li>
            </ul>

            <a href="<?php echo BASE_URL . 'pages/custom-pc.php?platform=amd'; ?>" class="btn btn-danger w-100">
              Build AMD PC
            </a>
          </div>
        </div>
      </div>


    </div>
  </div>

</main>