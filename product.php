<?php
session_start();

require("./includes/db_connect.php");
require("./includes/functions.php");

$slug = trim($_GET['slug'] ?? '');

if ($slug === '') {
  $res = mysqli_query($conn, "SELECT * FROM products WHERE is_active=1 ORDER BY is_featured DESC, created_at DESC LIMIT 24");
} else {
  $sql = "
    SELECT p.* FROM products p
    WHERE p.is_active = 1
      AND (
        p.platform = ?
        OR p.category_id = (SELECT category_id FROM categories WHERE slug = ? LIMIT 1)
        OR p.category_id IN (
          SELECT category_id FROM categories
          WHERE parent_id = (SELECT category_id FROM categories WHERE slug = ? LIMIT 1)
        )
        OR p.platform IN (
          SELECT slug FROM categories
          WHERE parent_id = (SELECT category_id FROM categories WHERE slug = ? LIMIT 1)
        )
      )
    ORDER BY p.is_featured DESC, p.created_at DESC
  ";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, 'ssss', $slug, $slug, $slug, $slug);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  mysqli_stmt_close($stmt);
}

?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Products</title>

  <!-- include css links page  -->
  <?php include('./includes/header-link.php') ?>

  <style>
    <?php include('./assets/css/navbar.css'); ?>
  </style>

</head>

<body>

  <?php
  require('./includes/navbar.php');
  ?>

  <div class="container py-4">
    <div class="row">
      <?php if (empty($res)): ?>
        <div class="col-12">
          <p class="text-muted text-center">No products found.</p>
        </div>
      <?php else: ?>
        <?php foreach (mysqli_fetch_all($res, MYSQLI_ASSOC) as $product):
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
                  <!-- <span class="text-muted small ms-2">({{ $reviews }})</span> -->
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
  </div>

  <script>
    var title = document.querySelectorAll('.product-link');

    title.forEach((title) => {
      var text = title.textContent;
      var maxLength = 50;
      if (text.length > maxLength) {
        var trimmedText = text.substr(0, maxLength);
        trimmedText += '.....';
        title.textContent = trimmedText;
      }
    });
  </script>

</body>

</html>
<?php mysqli_close($conn); ?>