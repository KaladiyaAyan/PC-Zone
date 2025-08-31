<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

// Get slug from URL
$slug = isset($_GET['slug']) ? mysqli_real_escape_string($conn, $_GET['slug']) : '';

if (!$slug) {
  die("Product not found.");
}

// Fetch product
$sql = "SELECT p.*, b.brand_name, c.category_name 
        FROM products p
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.slug = '$slug' AND p.is_active = 1 LIMIT 1";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

if (!$product) die("Product not found");

// Fetch product images
$images = [];
$res = mysqli_query($conn, "SELECT * FROM product_images WHERE product_id={$product['product_id']}");
while ($row = mysqli_fetch_assoc($res)) $images[] = $row;

// Fetch product specs
$specs = [];
$res = mysqli_query($conn, "SELECT * FROM product_specs WHERE product_id={$product['product_id']}");
while ($row = mysqli_fetch_assoc($res)) $specs[] = $row;

// Average rating
$ratingRes = mysqli_query($conn, "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                                  FROM product_reviews WHERE product_id={$product['product_id']}");
$rating = mysqli_fetch_assoc($ratingRes);
// $avgRating = round($rating['avg_rating'], 1);
// $totalReviews = $rating['total_reviews'];
$avgRating = round((float)($rating['avg_rating'] ?? 0), 1);
$totalReviews = (int)($rating['total_reviews'] ?? 0);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?= $product['product_name']; ?> - PCZone</title>
  <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
  <?php include '../includes/navbar.php'; ?>
  <div class="container my-5">
    <div class="row g-4">
      <!-- Left: Images -->
      <div class="col-md-6">
        <div class="card shadow-sm">
          <img id="mainImage" src="../assets/images/products/<?= $images[0]['image_path'] ?? 'no-image.png'; ?>"
            class="card-img-top img-fluid rounded" alt="<?= e($product['sku']); ?>">
          <div class="d-flex flex-wrap gap-2 p-3">
            <?php foreach ($images as $img): ?>
              <img src="../assets/images/products/<?= e($img['image_path']); ?>"
                class="img-thumbnail thumb-image"
                style="width:70px; height:70px; object-fit:cover; cursor:pointer;"
                onclick="document.getElementById('mainImage').src=this.src"
                alt="<?= e($product['sku']); ?>">
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Right: Info -->
      <div class="col-md-6">
        <h2 class="fw-bold"><?= e($product['product_name']); ?></h2>
        <p class="mb-1"><span class="fw-semibold">Brand:</span> <?= e($product['brand_name']); ?></p>
        <p class="text-muted">SKU: <?= e($product['sku']); ?></p>

        <!-- Rating -->
        <div class="mb-3">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <i class="bi <?= $i <= round($avgRating) ? 'bi-star-fill text-warning' : 'bi-star text-muted'; ?>"></i>
          <?php endfor; ?>
          <small class="ms-2">(<?= $totalReviews; ?> Reviews)</small>
        </div>

        <!-- Price -->
        <div class="mb-3">
          <?php if ($product['discount'] > 0):
            $discountPrice = $product['price'] - ($product['price'] * $product['discount'] / 100); ?>
            <span class="fs-4 fw-bold text-danger">₹<?= number_format($discountPrice); ?></span>
            <span class="text-muted text-decoration-line-through ms-2">₹<?= number_format($product['price']); ?></span>
            <span class="badge bg-success ms-2">-<?= intval($product['discount']); ?>%</span>
          <?php else: ?>
            <span class="fs-4 fw-bold text-primary">₹<?= number_format($product['price']); ?></span>
          <?php endif; ?>
        </div>

        <!-- Actions -->
        <form method="post" action="cart.php" class="d-flex align-items-center gap-2 mb-3">
          <input type="hidden" name="product_id" value="<?= $product['product_id']; ?>">
          <input type="number" name="quantity" value="1" min="1" class="form-control w-25">
          <button type="submit" class="btn btn-warning flex-fill">
            <i class="bi bi-cart-plus"></i> Add to Cart
          </button>
        </form>
        <a href="wishlist.php?add=<?= $product['product_id']; ?>" class="btn btn-outline-secondary w-100 mb-4">
          <i class="bi bi-heart"></i> Add to Wishlist
        </a>

        <!-- Specs -->
        <h5 class="mb-2">Key Specifications</h5>
        <ul class="list-group list-group-flush small">
          <?php foreach ($specs as $s): ?>
            <li class="list-group-item"><b><?= e($s['spec_name']); ?>:</b> <?= e($s['spec_value']); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mt-5" id="productTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button" role="tab">Description</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">
          Reviews (<?= $totalReviews; ?>)
        </button>
      </li>
    </ul>
    <div class="tab-content border p-3 bg-white shadow-sm" id="productTabsContent">
      <div class="tab-pane fade show active" id="desc" role="tabpanel">
        <?= nl2br(e($product['description'])); ?>
      </div>
      <div class="tab-pane fade" id="reviews" role="tabpanel">
        <!-- reviews will load here -->
        <p>No reviews yet.</p>
      </div>
    </div>
  </div>

  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>