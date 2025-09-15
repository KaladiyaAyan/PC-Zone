<?php
session_start();
require('./includes/db_connect.php');
require('./includes/functions.php');

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PC Zone</title>

  <!-- include css links page  -->
  <?php include('./includes/header-links.php') ?>

  <style>
    <?php include('./assets/css/navbar.css'); ?>

    /* Custom PC build cards */
    .build-card-modern {
      border: 1px solid var(--bs-border-color);
      border-radius: 1rem;
      overflow: hidden;
      transition: transform .18s ease, box-shadow .18s ease;
      background: #fff;
    }

    .build-card-modern:hover {
      transform: translateY(-2px);
      box-shadow: 0 1rem 2rem rgba(0, 0, 0, .08);
    }

    .build-card-modern .brand-badge {
      position: absolute;
      top: .75rem;
      left: .75rem;
      backdrop-filter: blur(6px);
    }

    .build-card-modern .price {
      font-weight: 700;
    }

    .build-card-modern .specs li {
      display: flex;
      align-items: center;
      gap: .5rem;
      margin: .25rem 0;
      font-size: .95rem;
    }

    .build-card-modern .custom-build-img {
      aspect-ratio: 16/9;
      padding: 12px 40px;
    }
  </style>
</head>

<body>

  <?php
  require('./includes/navbar.php');
  ?>

  <?php

  // Get featured products (limit 8)
  $featuredProducts = getFeaturedProducts(4, true);

  ?>

  <main class="container mt-4">
    <!-- Hero Section -->
    <div class="row">
      <div class="col-12">
        <div class="jumbotron bg-primary text-white p-5 rounded">
          <h1 class="display-4">Welcome to PC ZONE</h1>
          <p class="lead">Build your dream PC with premium components and expert guidance.</p>
          <a class="btn btn-light btn-lg me-3" href="products.php" role="button">Shop Now</a>
          <a class="btn btn-outline-light btn-lg" href="custom-pc.php" role="button">Build Custom PC</a>
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
          $img   = $product['main_image'] ?? ($product['image_path'] ?? 'placeholder.jpg');
          $avg   = round(floatval($product['avg_rating'] ?? 0) * 2) / 2;
          $reviews = (int)($product['review_count'] ?? 0);
          $price = $product['price'];

          // NEW: preferred product URL by slug, fallback to id
          $slug = trim($product['slug'] ?? '');
          $productUrl = $slug !== ''
            ? 'product-detail.php?slug=' . urlencode($slug)
            : 'product-detail.php?id=' . $pid; // fallback if slug missing
        ?>
          <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm border-0 product-card">
              <a href="product-detail.php?slug=<?= $slug ?>">
                <img src="assets/images/products/<?= e($img) ?>" class="card-img-top p-3" alt="<?= e($name) ?>">
              </a>

              <div class="card-body d-flex flex-column">
                <h5 class="card-title mb-1">
                  <a href="product-detail.php?slug=<?= $slug ?>" class="product-link"><?= e($name) ?></a>
                </h5>

                <div class="mb-1">
                  <span class="text-warning">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <i class="fa fa-star<?= $i <= $avg ? '' : '-o' ?>"></i>
                    <?php endfor; ?>
                  </span>
                  <span class="text-muted small ms-2">({{ $reviews }})</span>
                </div>

                <p class="card-text small text-muted mb-2"><?= e(mb_strimwidth($desc, 0, 80, '…')) ?></p>

                <div class="mb-2">
                  <span class="fw-bold text-danger"><?= formatPrice($price) ?></span>
                  <?php if (!empty($product['discount']) && $product['discount'] > 0): ?>
                    <span class="text-muted text-decoration-line-through ms-2"><?= formatPrice($product['price']) ?></span>
                    <span class="badge bg-success ms-2"><?= intval($product['discount']) ?>% off</span>
                  <?php endif; ?>
                </div>

                <a href="" class="btn btn-sm btn-warning mt-auto">Add to Cart</a>
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
                <span class="price h5 mb-0">Starting from ₹45000</span>
              </div>

              <ul class="specs list-unstyled text-secondary mb-3">
                <li><i class="bi bi-check-circle-fill text-success"></i> 12th/13th/14th Gen options</li>
                <li><i class="bi bi-check-circle-fill text-success"></i> DDR5 support</li>
                <li><i class="bi bi-check-circle-fill text-success"></i> PCIe 5.0 ready</li>
                <li><i class="bi bi-check-circle-fill text-success"></i> 2-year warranty</li>
              </ul>

              <a href="custom-pc.php?platform=intel" class="btn btn-primary w-100">
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
                <span class="price h5 mb-0">Starting from ₹42000</span>
              </div>

              <ul class="specs list-unstyled text-secondary mb-3">
                <li><i class="bi bi-check-circle-fill text-success"></i> Ryzen 5000/7000 series</li>
                <li><i class="bi bi-check-circle-fill text-success"></i> High core counts</li>
                <li><i class="bi bi-check-circle-fill text-success"></i> Strong price-performance</li>
                <li><i class="bi bi-check-circle-fill text-success"></i> 2-year warranty</li>
              </ul>

              <a href="custom-pc.php?platform=amd" class="btn btn-danger w-100">
                Build AMD PC
              </a>
            </div>
          </div>
        </div>


      </div>
    </div>

  </main>

  <?php include './includes/footer-link.php'; ?>
</body>

</html>