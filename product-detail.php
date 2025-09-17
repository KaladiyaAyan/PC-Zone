<?php
session_start();

if (empty($_SESSION['user']) || empty($_SESSION['user_id'])) {
  header('Location: ./login.php');
  exit;
}

require('./includes/functions.php'); // provides getConnection(), formatPrice(), etc.

// Get slug
$slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
if ($slug === '') {
  http_response_code(404);
  die("Product not found.");
}

$conn = getConnection();

// fetch product
$sql = "SELECT p.*, b.brand_name, c.category_name
        FROM products p
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.slug = ? AND p.is_active = 1
        LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $slug);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$product) {
  http_response_code(404);
  die("Product not found.");
}

// resolve image helper
function resolveImageUrl($filename)
{
  $filename = trim((string)$filename);
  if ($filename !== '') {
    $uploads = __DIR__ . '/uploads/' . $filename;
    if (file_exists($uploads)) return './uploads/' . rawurlencode($filename);
    $assets = __DIR__ . '/assets/images/products/' . $filename;
    if (file_exists($assets)) return './assets/images/products/' . rawurlencode($filename);
  }
  return './assets/images/no-image.png';
}

// build image list from product columns (main_image, image_1..image_3)
$imageUrls = [];
$fields = ['main_image', 'image_1', 'image_2', 'image_3'];
foreach ($fields as $i => $f) {
  if (!empty($product[$f])) {
    $imageUrls[] = [
      'url' => resolveImageUrl($product[$f]),
      'is_main' => ($f === 'main_image')
    ];
  }
}
if (empty($imageUrls)) {
  $imageUrls[] = ['url' => resolveImageUrl(''), 'is_main' => true];
}

// fetch specs
$specs = [];
$sql = "SELECT spec_group, spec_name, spec_value, display_order FROM product_specs WHERE product_id = ? ORDER BY spec_group, display_order ASC";
$stmt = mysqli_prepare($conn, $sql);
$productId = (int)$product['product_id'];
mysqli_stmt_bind_param($stmt, "i", $productId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) {
  $grp = $row['spec_group'] ?: 'General';
  $specs[$grp][] = $row;
}
mysqli_stmt_close($stmt);

// rating
$sql = "SELECT COALESCE(AVG(rating),0) AS avg_rating, COUNT(*) AS review_count FROM product_reviews WHERE product_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $productId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$ratingRow = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);
$avgRating = round((float)($ratingRow['avg_rating'] ?? 0), 1);
$totalReviews = (int)($ratingRow['review_count'] ?? 0);

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title><?= e($product['product_name']); ?> - PCZone</title>

  <?php include('./includes/header-links.php') ?>
  <style>
    <?php include('./assets/css/navbar.css');
    include('./assets/css/product.css');
    ?>
  </style>
</head>

<body>
  <?php include('./includes/navbar.php'); ?>

  <div class="container my-5">
    <div class="product-row">
      <div class="thumb-col" id="thumbCol" aria-hidden="false">
        <?php foreach ($imageUrls as $idx => $img): ?>
          <img src="<?= e($img['url']); ?>" data-full="<?= e($img['url']); ?>"
            class="thumb <?= $idx === 0 ? 'active' : ''; ?>" alt="<?= e($product['sku']); ?>">
        <?php endforeach; ?>
      </div>

      <div class="image-col">
        <img id="mainImage" class="main-img" src="<?= e($imageUrls[0]['url']); ?>" alt="<?= e($product['sku']); ?>">
      </div>

      <div class="product-info">
        <h2 class="fw-bold"><?= e($product['product_name']); ?></h2>

        <div class="d-flex align-items-center mb-2">
          <div class="me-3"><span class="fw-semibold">Brand:</span></div>
          <div class="brand-badge"><?= e($product['brand_name'] ?: 'N/A'); ?></div>
        </div>

        <div class="sku-line"><strong>SKU:</strong> <?= e($product['sku']); ?> <span class="text-muted ms-3">Category: <?= e($product['category_name'] ?? ''); ?></span></div>

        <div class="mb-3">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <i class="bi <?= $i <= round($avgRating) ? 'bi-star-fill text-warning' : 'bi-star text-muted'; ?>"></i>
          <?php endfor; ?>
          <small class="ms-2">(<?= $totalReviews; ?> Reviews)</small>
        </div>

        <div class="mb-3">
          <?php
          $price = (float)$product['price'];
          $discount = (float)$product['discount'];
          if ($discount > 0) {
            $discountPrice = $price - ($price * $discount / 100);
            echo '<span class="fs-4 fw-bold text-danger">' . formatPrice($discountPrice) . '</span>';
            echo '<span class="text-muted text-decoration-line-through ms-2">' . formatPrice($price) . '</span>';
            echo '<span class="badge bg-success ms-2">-' . intval($discount) . '%</span>';
          } else {
            echo '<span class="fs-4 fw-bold text-primary">' . formatPrice($price) . '</span>';
          }
          ?>
        </div>

        <form method="post" action="/cart.php" class="d-flex align-items-center gap-2 mb-3">
          <input type="hidden" name="product_id" value="<?= (int)$product['product_id']; ?>">
          <input type="number" name="quantity" value="1" min="1" class="form-control w-25">
          <button type="submit" class="btn btn-warning flex-fill">
            <i class="bi bi-cart-plus"></i> Add to Cart
          </button>
        </form>

        <a href="/wishlist.php?add=<?= (int)$product['product_id']; ?>" class="btn btn-outline-secondary w-100 mb-4">
          <i class="bi bi-heart"></i> Add to Wishlist
        </a>

        <h5 class="mb-2">Key Specifications</h5>
        <?php if (!empty($specs['Key Specs'])): ?>
          <ul class="list-group list-group-flush spec-table small mb-1">
            <?php foreach ($specs['Key Specs'] as $s): ?>
              <li class="list-group-item"><span><?= e($s['spec_name']); ?></span><span class="text-end"><?= e($s['spec_value']); ?></span></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted small">No key specifications available.</p>
        <?php endif; ?>

        <div class="info-box">
          <div class="fw-bold">🔥 100% Genuine Products Guaranteed</div>
          <div class="small text-muted">PCZone provides authentic products with full authenticity. All warranties are valid as per manufacturer's terms.</div>
          <hr>
          <div class="fw-semibold">👉 Cash on Delivery Available</div>
        </div>
      </div>
    </div>

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
        <div class="mb-3"><?= nl2br(e($product['description'])); ?></div>

        <?php if (!empty($specs['Detailed Specs'])): ?>
          <div class="desc-specs">
            <h6 class="mb-2">Detailed Specifications</h6>
            <?php foreach ($specs['Detailed Specs'] as $d): ?>
              <div class="spec-row">
                <div><?= e($d['spec_name']); ?></div>
                <div class="text-muted"><?= e($d['spec_value']); ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="tab-pane fade" id="reviews" role="tabpanel">
        <?php
        // simple review list
        $sql = "SELECT r.*, u.first_name, u.last_name FROM product_reviews r JOIN users u ON r.user_id = u.user_id WHERE r.product_id = ? ORDER BY r.created_at DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        $rres = mysqli_stmt_get_result($stmt);
        $hasReviews = false;
        while ($rev = mysqli_fetch_assoc($rres)) {
          $hasReviews = true;
          echo '<div class="mb-3"><strong>' . e($rev['first_name'] . ' ' . $rev['last_name']) . '</strong> <small class="text-muted">' . e($rev['created_at']) . '</small><div>' . nl2br(e($rev['comment'])) . '</div></div>';
        }
        mysqli_stmt_close($stmt);
        if (!$hasReviews) echo '<p>No reviews yet.</p>';
        ?>
      </div>
    </div>
  </div>

  <script>
    (function() {
      const thumbCol = document.getElementById('thumbCol');
      const mainImage = document.getElementById('mainImage');
      if (!thumbCol || !mainImage) return;

      thumbCol.addEventListener('click', function(e) {
        const t = e.target;
        if (!t || t.tagName !== 'IMG') return;
        const full = t.getAttribute('data-full');
        if (full) mainImage.src = full;
        thumbCol.querySelectorAll('img.thumb').forEach(img => img.classList.remove('active'));
        t.classList.add('active');
      });

      thumbCol.querySelectorAll('img.thumb').forEach(img => {
        img.tabIndex = 0;
        img.addEventListener('keydown', function(ev) {
          if (ev.key === 'Enter' || ev.key === ' ') {
            ev.preventDefault();
            img.click();
          }
        });
      });
    })();
  </script>

  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>