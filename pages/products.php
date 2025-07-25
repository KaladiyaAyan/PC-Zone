<?php
require_once '../config/config.php';
$pageTitle = 'Products - ' . SITE_NAME;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * PRODUCTS_PER_PAGE;

$products = getAllProducts(PRODUCTS_PER_PAGE, $offset);
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-4">
  <div class="row">
    <div class="col-12">
      <h2>All Products</h2>
    </div>
  </div>

  <div class="row">
    <?php foreach ($products as $product): ?>
      <div class="col-md-4 mb-4">
        <div class="card">
          <img src="../assets/images/products/<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
          <div class="card-body">
            <h5 class="card-title"><?php echo $product['name']; ?></h5>
            <p class="card-text"><?php echo substr($product['description'], 0, 100); ?>...</p>
            <p class="text-primary fw-bold"><?php echo formatPrice($product['price']); ?></p>
            <div class="d-flex justify-content-between">
              <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
              <?php if (isLoggedIn()): ?>
                <button class="btn btn-success add-to-cart" data-product-id="<?php echo $product['id']; ?>">Add to Cart</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include '../includes/footer.php'; ?>