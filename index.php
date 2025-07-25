<?php
require_once './config/config.php';
$pageTitle = SITE_NAME . ' - Home';
// require_once './includes/functions.php';

// Get featured products
$featuredProducts = getAllProducts(8);
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-4">
  <!-- Hero Section -->
  <div class="row">
    <div class="col-12">
      <div class="jumbotron bg-primary text-white p-5 rounded">
        <h1 class="display-4">Welcome to <?php echo SITE_NAME; ?></h1>
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
    <?php foreach ($featuredProducts as $product): ?>
      <div class="col-md-3 mb-4">
        <div class="card">
          <img src="assets/images/products/<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
          <div class="card-body">
            <h5 class="card-title"><?php echo $product['name']; ?></h5>
            <p class="card-text"><?php echo substr($product['description'], 0, 100); ?>...</p>
            <p class="text-primary fw-bold"><?php echo formatPrice($product['price']); ?></p>
            <a href="pages/product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>