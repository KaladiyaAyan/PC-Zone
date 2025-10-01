<?php
session_start();
require('./includes/db_connect.php');
require('./includes/functions.php');

$slug = trim($_GET['slug'] ?? '');
$query = trim($_GET['q'] ?? '');
$products = [];

// If there is a search query
if ($query !== '') {
  $safe_q = mysqli_real_escape_string($conn, $query);
  $like = "%$safe_q%";
  $sql_search = "
    SELECT * FROM products
    WHERE is_active = 1
      AND (
        product_name LIKE '$like'
        OR slug LIKE '$like'
        OR description LIKE '$like'
        OR sku LIKE '$like'
      )
    ORDER BY is_featured DESC, created_at DESC
  ";
  $result_search = mysqli_query($conn, $sql_search);
  if ($result_search) {
    $products = mysqli_fetch_all($result_search, MYSQLI_ASSOC);
  }
} elseif ($slug === '') {
  // If no slug, get latest featured products
  $sql = "SELECT * FROM products WHERE is_active=1 ORDER BY is_featured DESC, created_at DESC LIMIT 24";
  $result = mysqli_query($conn, $sql);
  $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
  // If there is a slug, get the category ID
  $safe_slug = mysqli_real_escape_string($conn, $slug);
  $sql_cat = "SELECT category_id FROM categories WHERE slug = '$safe_slug' LIMIT 1";
  $result_cat = mysqli_query($conn, $sql_cat);
  $cid = mysqli_fetch_assoc($result_cat)['category_id'] ?? 0;

  // find all products that match the category, its children, OR the platform
  $sql_products = "
      SELECT * FROM products
      WHERE is_active = 1 AND (
          platform = '$safe_slug'
          OR category_id = $cid
          OR category_id IN (SELECT category_id FROM categories WHERE parent_id = $cid)
      )
      ORDER BY is_featured DESC, created_at DESC
    ";
  $result_products = mysqli_query($conn, $sql_products);
  if ($result_products) {
    $products = mysqli_fetch_all($result_products, MYSQLI_ASSOC);
  }
}
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Products</title>
  <?php include('./includes/header-link.php') ?>
</head>

<body>
  <?php require('./includes/alert.php'); ?>
  <?php require('./includes/navbar.php'); ?>

  <div class="container py-4">
    <h2 class="mb-4 text-center text-capitalize"><?= e($query !== '' ? 'Search results for: ' . $query : ($slug ?: 'All Products')) ?></h2>
    <div class="row">
      <?php if (empty($products)): ?>
        <div class="col-12">
          <p class="text-muted text-center">No products found.</p>
        </div>
      <?php else: ?>
        <?php foreach ($products as $product):

          $pid = (int)$product['product_id'];
          $name = $product['product_name'];
          $slug_product = $product['slug'];
          $desc = $product['description'] ?? '';
          $price = (float)$product['price'];
          $discount = (float)$product['discount'];
          $finalPrice = $price - ($price * $discount / 100);

          // Image fallback logic
          $img_filename = $product['main_image'] ?? '';
          $imgPath = 'assets/images/no-image.png';
          if (!empty($img_filename)) {
            if (file_exists('uploads/' . $img_filename)) {
              $imgPath = 'uploads/' . $img_filename;
            } elseif (file_exists('assets/images/products/' . $img_filename)) {
              $imgPath = 'assets/images/products/' . $img_filename;
            }
          }
        ?>
          <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
              <a href="product-detail.php?slug=<?= e($slug_product) ?>" class="product-image-container">
                <img src="<?= e($imgPath) ?>" alt="<?= e($name) ?>">
              </a>
              <div class="card-body d-flex flex-column">
                <h5 class="card-title">
                  <a href="product-detail.php?slug=<?= e($slug_product) ?>" class="product-title-link">
                    <?= e($name) ?>
                  </a>
                </h5>

                <p class="card-text small text-muted mb-3">
                  <?= e(mb_strimwidth($desc, 0, 80, '…')) ?>
                </p>

                <div class="mt-auto">
                  <p class="fs-5 fw-bold text-dark m-0 mb-2">
                    <?= formatPrice($finalPrice) ?>
                    <?php if ($discount > 0): ?>
                      <span class="text-muted text-decoration-line-through small ms-1">
                        <?= formatPrice($price) ?>
                      </span>
                    <?php endif; ?>
                  </p>

                  <?php if ((int)$product['stock'] > 0): ?>
                    <form action="addtocart.php" method="POST" class="d-grid">
                      <input type="hidden" name="product_id" value="<?= $pid ?>">
                      <button type="submit" class="btn btn-gradient">Add to Cart</button>
                    </form>
                  <?php else: ?>
                    <button class="btn btn-secondary w-100" disabled>Out of Stock</button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <?php include('./includes/footer.php') ?>
  <?php include('./includes/footer-link.php') ?>
  <script>
    var title = document.querySelectorAll('.product-title-link');

    title.forEach((title) => {
      var text = title.textContent;
      var maxLength = 70;
      if (text.length > maxLength) {
        var trimmedText = text.substr(0, maxLength);
        trimmedText += '.....';
        title.textContent = trimmedText;
      }
    });
  </script>
</body>

</html>