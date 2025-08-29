<?php
require_once './includes/functions.php'; // uses getConnection() and helpers

// Get featured products (limit 8)
$featuredProducts = getFeaturedProducts(8);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PC ZONE - Home</title>
  <meta name="description" content="PC ZONE - pre-built PCs, custom builds and premium components.">
  <link rel="stylesheet" href="./assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="./assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>

  <?php include './includes/navbar.php'; ?>

  <div class="container mt-4">
    <!-- Hero Section -->
    <div class="row">
      <div class="col-12">
        <div class="jumbotron bg-primary text-white p-5 rounded">
          <h1 class="display-4">Welcome to PC ZONE</h1>
          <p class="lead">Discover amazing products at great prices.</p>
          <a class="btn btn-light btn-lg" href="pages/products.php" role="button">Shop Now</a>
        </div>
      </div>
    </div>

    <!-- Featured Products -->
    <div class="row mt-5">
      <div class="col-12">
        <h2>Featured Products</h2>
      </div>
    </div>

    <div class="row">
      <?php if (empty($featuredProducts)): ?>
        <div class="col-12">
          <p class="text-muted">No featured products found.</p>
        </div>
      <?php else: ?>
        <?php foreach ($featuredProducts as $product):
          $pid = (int)($product['product_id'] ?? 0);
          $name = $product['product_name'] ?? '';
          $desc = $product['description'] ?? '';
          // functions.php returns main_image alias
          $img  = $product['main_image'] ?? ($product['image_path'] ?? 'placeholder.png');
          $avg  = round(floatval($product['avg_rating'] ?? 0) * 2) / 2; // round to nearest 0.5
          $reviews = (int)($product['review_count'] ?? 0);
          $full = (int)floor($avg);
          $half = (($avg - $full) == 0.5) ? 1 : 0;
          $empty = 5 - $full - $half;
          $priceToShow = $product['final_price'] ?? $product['price'];
        ?>
          <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm border-0 product-card">
              <a href="pages/product-detail.php?id=<?php echo e($pid); ?>">
                <img src="assets/images/products/<?php echo e($img); ?>"
                  class="card-img-top p-3" alt="<?php echo e($name); ?>">
              </a>

              <div class="card-body d-flex flex-column">
                <h5 class="card-title mb-1">
                  <a href="pages/product-detail.php?id=<?php echo e($pid); ?>" class="product-link">
                    <?php echo e($name); ?>
                  </a>
                </h5>

                <!-- Rating stars + review count -->
                <div class="mb-1">
                  <?php
                  for ($i = 0; $i < $full; $i++) echo '<i class="bi bi-star-fill star-fill"></i>';
                  if ($half) echo '<i class="bi bi-star-half star-fill"></i>';
                  for ($i = 0; $i < $empty; $i++) echo '<i class="bi bi-star star-empty"></i>';
                  ?>
                  <a href="pages/product-detail.php?id=<?php echo e($pid); ?>#reviews" class="review-count">
                    <?php echo $reviews > 0 ? ' ' . e($reviews) : ' 0'; ?>
                  </a>
                </div>

                <p class="card-text small text-muted mb-2"><?php echo e(mb_strimwidth($desc, 0, 80, '…')); ?></p>

                <!-- Pricing -->
                <div class="mb-2">
                  <span class="fw-bold text-danger"><?php echo formatPrice($priceToShow); ?></span>
                  <?php if (!empty($product['discount']) && $product['discount'] > 0): ?>
                    <span class="text-muted text-decoration-line-through ms-2"><?php echo formatPrice($product['price']); ?></span>
                    <span class="badge bg-success ms-2"><?php echo intval($product['discount']); ?>% off</span>
                  <?php endif; ?>
                </div>

                <a href="pages/product-detail.php?id=<?php echo e($pid); ?>" class="btn btn-sm btn-warning mt-auto">View Details</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div>

  <?php include './includes/footer.php'; ?>

  <script src="./assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>