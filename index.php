<?php
require_once './config/config.php';
$pageTitle = SITE_NAME . ' - Home';
require_once './includes/db_connect.php';

// Get featured products (limit 8)
$featuredProducts = [];
$conn = getConnection();
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC 
        LIMIT 8";

$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $featuredProducts[] = $row;
  }
}
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
      <h2 class="mb-4">Featured Products</h2>
    </div>

    <?php if (count($featuredProducts) > 0): ?>
      <?php foreach ($featuredProducts as $product): ?>
        <div class="col-md-3 mb-4">
          <a href="pages/product-detail.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark">
            <div class="card h-100 border-0 shadow-sm product-card transition-hover">
              <div class="ratio ratio-4x3 rounded-top overflow-hidden">

                <img src="./uploads/<?= $product["image1"] ?>" alt="<?= $product["name"] ?>" class="img-fluid mt-1" style="max-height: 200px; min-width: 200px; object-fit: contain; ">

              </div>
              <div class="card-body">
                <h5 class="card-title fw-semibold"><?php echo $product['name']; ?></h5>
                <p class="card-text text-muted small" style="height: 3rem; overflow: hidden; text-overflow: ellipsis;">
                  <?php echo $product['description']; ?>
                </p>
                <div class="d-flex justify-content-between align-items-center mt-3">
                  <span class="text-primary fw-bold fs-6"><?php echo formatPrice($product['price']); ?></span>
                  <span class="text-end small text-muted">View More →</span>
                </div>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>

    <?php else: ?>
      <div class="col-12">
        <div class="alert alert-warning">No featured products found.</div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>