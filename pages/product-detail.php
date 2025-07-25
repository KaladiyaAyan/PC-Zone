<?php
require_once '../config/config.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = getProductById($productId);

if (!$product) {
  redirect('products.php');
}

$pageTitle = $product['name'] . ' - ' . SITE_NAME;
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-4">
  <div class="row">
    <div class="col-md-6">
      <img src="../assets/images/products/<?php echo $product['image']; ?>" class="img-fluid" alt="<?php echo $product['name']; ?>">
    </div>
    <div class="col-md-6">
      <h1><?php echo $product['name']; ?></h1>
      <p class="text-muted">Category: <?php echo $product['category_name']; ?></p>
      <p class="h3 text-primary"><?php echo formatPrice($product['price']); ?></p>

      <div class="mb-4">
        <h5>Description</h5>
        <p><?php echo nl2br($product['description']); ?></p>
      </div>

      <?php if (isLoggedIn()): ?>
        <form id="addToCartForm">
          <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
          </div>
          <button type="submit" class="btn btn-success btn-lg">Add to Cart</button>
          <input type="hidden" id="productId" value="<?php echo $product['id']; ?>">
        </form>
      <?php else: ?>
        <p class="text-muted">Please <a href="../auth/login.php">login</a> to add items to cart.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>