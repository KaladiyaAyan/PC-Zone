<?php
session_start();

require('./includes/functions.php');
require('./includes/db_connect.php');

function resolveImageUrl(?string $filename): string
{
  $filename = trim((string)$filename);
  if ($filename && file_exists('./uploads/' . $filename)) {
    return './uploads/' . rawurlencode($filename);
  } else {
    if ($filename && file_exists('./assets/images/products/' . $filename)) {
      return './assets/images/products/' . rawurlencode($filename);
    }
  }
  return './assets/images/no-image.png';
}


// --- 1. GET CONNECTION AND SLUG ---
$conn = getConnection();

$slug = isset($_GET['slug']) ? mysqli_real_escape_string($conn, $_GET['slug']) : '';
if (!$slug) {
  header('Location: index.php');
  exit;
}

// Fetch main product data
$sql_product = "SELECT p.*, b.brand_name, c.category_name
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.brand_id
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.slug = '$slug' AND p.is_active = 1 LIMIT 1";

$result_product = mysqli_query($conn, $sql_product);
$product = mysqli_fetch_assoc($result_product);

// If product not found, stop and redirect
if (!$product) {
  message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Product not found');
  header('Location: index.php');
  exit;
}
$productId = (int)$product['product_id'];

// Fetch product specifications
$sql_specs = "SELECT spec_group, spec_name, spec_value FROM product_specs WHERE product_id = $productId ORDER BY display_order ASC";
$result_specs = mysqli_query($conn, $sql_specs);
$specs = [];
while ($row = mysqli_fetch_assoc($result_specs)) {
  $group = $row['spec_group'] ?: 'General';
  $specs[$group][] = $row;
}

// Fetch average rating and review count
$sql_rating = "SELECT COALESCE(AVG(rating), 0) AS avg_rating, COUNT(*) AS review_count FROM product_reviews WHERE product_id = $productId";
$result_rating = mysqli_query($conn, $sql_rating);
$ratingData = mysqli_fetch_assoc($result_rating);
$avgRating = round((float)($ratingData['avg_rating'] ?? 0), 1);
$totalReviews = (int)($ratingData['review_count'] ?? 0);

// Fetch all reviews
$sql_reviews = "SELECT r.comment, r.created_at, r.rating, u.username 
                FROM product_reviews r JOIN users u ON r.user_id = u.user_id 
                WHERE r.product_id = $productId ORDER BY r.created_at DESC";
$result_reviews = mysqli_query($conn, $sql_reviews);
$reviews = mysqli_fetch_all($result_reviews, MYSQLI_ASSOC);

mysqli_close($conn);

$imageUrls = [];
$imageFields = ['main_image', 'image_1', 'image_2', 'image_3'];
foreach ($imageFields as $field) {
  if (!empty($product[$field])) {
    $imageUrls[] = resolveImageUrl($product[$field]);
  }
}
if (empty($imageUrls)) {
  $imageUrls[] = resolveImageUrl(''); // Fallback to default image
}
$finalPrice = (float)$product['price'] - ((float)$product['price'] * (float)$product['discount'] / 100);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($product['product_name']); ?> - PCZone</title>
  <?php include('./includes/header-link.php') ?>
  <style>
    <?php
    include('./assets/css/navbar.css');
    include('./assets/css/product.css');
    ?>
  </style>
</head>

<body>
  <?php include('./includes/navbar.php'); ?>

  <div class="container my-5">
    <div class="product-row">
      <div class="thumb-col" id="thumbCol">
        <?php foreach ($imageUrls as $idx => $url) : ?>
          <img src="<?= e($url); ?>" class="thumb <?= $idx === 0 ? 'active' : ''; ?>" alt="Thumbnail <?= $idx + 1 ?>">
        <?php endforeach; ?>
      </div>
      <div class="image-col">
        <img id="mainImage" class="main-img" src="<?= e($imageUrls[0]); ?>" alt="<?= e($product['product_name']); ?>">
      </div>

      <div class="product-info">
        <h2 class="fw-bold"><?= e($product['product_name']); ?></h2>
        <div class="sku-line"><strong>SKU:</strong> <?= e($product['sku']); ?></div>
        <div class="mb-3">
          <?php for ($i = 1; $i <= 5; $i++) : ?>
            <i class="bi <?= $i <= $avgRating ? 'bi-star-fill text-warning' : 'bi-star text-muted'; ?>"></i>
          <?php endfor; ?>
          <small class="ms-2">(<?= $totalReviews; ?> Reviews)</small>
        </div>

        <div class="mb-3">
          <?php if ((float)$product['discount'] > 0) : ?>
            <span class="fs-4 fw-bold text-danger"><?= formatPrice($finalPrice) ?></span>
            <span class="text-muted text-decoration-line-through ms-2"><?= formatPrice($product['price']) ?></span>
            <span class="badge bg-success ms-2"><?= (int)$product['discount'] ?>% OFF</span>
          <?php else : ?>
            <span class="fs-4 fw-bold text-primary"><?= formatPrice($product['price']) ?></span>
          <?php endif; ?>
        </div>

        <form method="post" action="/cart.php" class="d-flex align-items-center gap-2 mb-3">
          <input type="hidden" name="product_id" value="<?= $productId; ?>">
          <input type="number" name="quantity" value="1" min="1" class="form-control" style="width: 80px;">
          <button type="submit" class="btn btn-warning flex-fill"><i class="bi bi-cart-plus"></i> Add to Cart</button>
        </form>

        <?php if (!empty($specs['Key Specs'])) : ?>
          <h5 class="mb-2">Key Specifications</h5>
          <ul class="list-group list-group-flush spec-table small mb-1">
            <?php foreach ($specs['Key Specs'] as $s) : ?>
              <li class="list-group-item"><span><?= e($s['spec_name']); ?></span><span class="text-end"><?= e($s['spec_value']); ?></span></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <ul class="nav nav-tabs mt-5" id="productTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desc" type="button">Description</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews" type="button">Reviews (<?= $totalReviews; ?>)</button>
      </li>
    </ul>

    <div class="tab-content border border-top-0 p-3 bg-white shadow-sm">
      <div class="tab-pane fade show active" id="desc" role="tabpanel">
        <div class="mb-3"><?= nl2br(e($product['description'])); ?></div>
        <?php if (!empty($specs)) : ?>
          <h6 class="mb-2">All Specifications</h6>
          <div class="desc-specs">
            <?php foreach ($specs as $group => $spec_items): ?>
              <?php foreach ($spec_items as $d) : ?>
                <div class="spec-row">
                  <div><?= e($d['spec_name']); ?></div>
                  <div class="text-muted"><?= e($d['spec_value']); ?></div>
                </div>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="tab-pane fade" id="reviews" role="tabpanel">
        <h5 class="mb-3">Customer Reviews</h5>
        <?php if (empty($reviews)) : ?>
          <p>No reviews yet for this product.</p>
          <?php else : foreach ($reviews as $rev) : ?>
            <div class="review-item">
              <strong><?= e($rev['username']) ?></strong>
              <div class="mb-1">
                <?php for ($i = 1; $i <= 5; $i++) : ?>
                  <i class="bi <?= $i <= $rev['rating'] ? 'bi-star-fill text-warning' : 'bi-star text-muted'; ?>"></i>
                <?php endfor; ?>
              </div>
              <div><?= nl2br(e($rev['comment'])) ?></div>
            </div>
        <?php endforeach;
        endif; ?>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const thumbContainer = document.getElementById('thumbCol');
      const mainImage = document.getElementById('mainImage');
      if (thumbContainer && mainImage) {
        thumbContainer.addEventListener('click', (e) => {
          if (e.target.tagName !== 'IMG') return;
          mainImage.src = e.target.src;
          thumbContainer.querySelectorAll('img.thumb').forEach(img => img.classList.remove('active'));
          e.target.classList.add('active');
        });
      }
    });
  </script>
  <?php include('./includes/footer-link.php') ?>
</body>

</html>