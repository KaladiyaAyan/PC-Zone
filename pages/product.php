<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

// Get slug from URL
$slug = isset($_GET['slug']) ? mysqli_real_escape_string($conn, $_GET['slug']) : '';
if (!$slug) die("Product not found.");

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
$res = mysqli_query($conn, "SELECT * FROM product_images WHERE product_id={$product['product_id']} ORDER BY is_main DESC, product_image_id ASC");
while ($row = mysqli_fetch_assoc($res)) $images[] = $row;

// helper to resolve image URL (checks uploads first, then assets)
function resolveImageUrl($filename)
{
  $uploadsPath = __DIR__ . '/../admin/uploads/' . $filename;
  if ($filename && file_exists($uploadsPath)) {
    return '../admin/uploads/' . rawurlencode($filename);
  }
  $assetsPath = __DIR__ . '/../assets/images/products/' . $filename;
  if ($filename && file_exists($assetsPath)) {
    return '../assets/images/products/' . rawurlencode($filename);
  }
  return '../assets/images/no-image.png';
}

// Build array of resolved urls
$imageUrls = [];
foreach ($images as $img) {
  $imageUrls[] = [
    'id' => $img['product_image_id'],
    'url' => resolveImageUrl($img['image_path']),
    'is_main' => (bool)$img['is_main']
  ];
}
if (empty($imageUrls)) {
  $imageUrls[] = ['id' => 0, 'url' => resolveImageUrl(''), 'is_main' => true];
}

// Fetch product specs and group them by spec_group
$specGroups = [];
$res = mysqli_query($conn, "SELECT * FROM product_specs WHERE product_id={$product['product_id']} ORDER BY FIELD(spec_group, 'Key Specs','Detailed Specs', spec_group), display_order ASC");
while ($row = mysqli_fetch_assoc($res)) {
  $group = $row['spec_group'] ?: 'General';
  if (!isset($specGroups[$group])) $specGroups[$group] = [];
  $specGroups[$group][] = $row;
}

// separate key and detailed specs
$keySpecs = $specGroups['Key Specs'] ?? [];
$detailedSpecs = $specGroups['Detailed Specs'] ?? [];

// Average rating
$ratingRes = mysqli_query($conn, "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                                  FROM product_reviews WHERE product_id={$product['product_id']}");
$rating = mysqli_fetch_assoc($ratingRes);
$avgRating = round((float)($rating['avg_rating'] ?? 0), 1);
$totalReviews = (int)($rating['total_reviews'] ?? 0);

// function e($v)
// {
//   return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
// }
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?= e($product['product_name']); ?> - PCZone</title>
  <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .product-row {
      display: flex;
      gap: 18px;
      align-items: flex-start;
    }

    .thumb-col {
      width: 90px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      align-items: center;
    }

    .thumb {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border-radius: 6px;
      cursor: pointer;
      border: 2px solid transparent;
    }

    .thumb.active {
      border-color: #0d6efd;
      box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.06);
    }

    .image-col {
      flex: 0 0 540px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .main-img {
      width: 100%;
      max-width: 540px;
      max-height: 680px;
      object-fit: contain;
      border-radius: 6px;
      background: #fff;
    }

    @media (max-width:991px) {
      .product-row {
        flex-direction: column;
      }

      .thumb-col {
        flex-direction: row;
        width: 100%;
        overflow-x: auto;
        padding-bottom: 8px;
      }

      .thumb {
        width: 70px;
        height: 70px;
      }

      .image-col {
        flex: 1 1 auto;
        max-width: 100%;
      }
    }

    /* right panel */
    .product-info {
      min-width: 320px;
    }

    .spec-table .list-group-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border: 0;
      padding: .6rem .75rem;
      border-bottom: 1px solid #eee;
    }

    .spec-group-title {
      font-weight: 700;
      margin-top: 8px;
      margin-bottom: 6px;
      font-size: .95rem;
      color: #444;
    }

    .info-box {
      background: #e9f2ff;
      border-radius: 8px;
      padding: 18px;
      margin-top: 16px;
      border: 1px solid #d7e8ff;
    }

    .brand-badge {
      display: inline-block;
      padding: .3rem .6rem;
      background: #4b2b87;
      color: #fff;
      border-radius: 4px;
      font-weight: 700;
      font-size: .85rem;
    }

    .sku-line {
      color: #666;
      font-size: .95rem;
      margin-bottom: 6px;
    }

    .tabs-content .desc-specs {
      margin-top: 12px;
    }

    .desc-specs .spec-row {
      display: flex;
      justify-content: space-between;
      padding: .45rem .6rem;
      border-bottom: 1px solid #f1f1f1;
    }
  </style>
</head>

<body>
  <?php include '../includes/navbar.php'; ?>

  <div class="container my-5">
    <div class="product-row">
      <!-- thumbnails -->
      <div class="thumb-col" id="thumbCol" aria-hidden="false">
        <?php foreach ($imageUrls as $idx => $img): ?>
          <img src="<?= e($img['url']); ?>" data-full="<?= e($img['url']); ?>"
            class="thumb <?= $idx === 0 ? 'active' : ''; ?>" alt="<?= e($product['sku']); ?>">
        <?php endforeach; ?>
      </div>

      <!-- main image -->
      <div class="image-col">
        <img id="mainImage" class="main-img" src="<?= e($imageUrls[0]['url']); ?>" alt="<?= e($product['sku']); ?>">
      </div>

      <!-- right: product info -->
      <div class="product-info">
        <h2 class="fw-bold"><?= e($product['product_name']); ?></h2>

        <div class="d-flex align-items-center mb-2">
          <div class="me-3"><span class="fw-semibold">Brand:</span></div>
          <div class="brand-badge"><?= e($product['brand_name'] ?: 'N/A'); ?></div>
        </div>

        <div class="sku-line"><strong>SKU:</strong> <?= e($product['sku']); ?> <span class="text-muted ms-3">Category: <?= e($product['category_name'] ?? ''); ?></span></div>

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

        <!-- Key Specs (only Key Specs shown here) -->
        <h5 class="mb-2">Key Specifications</h5>
        <?php if (!empty($keySpecs)): ?>
          <ul class="list-group list-group-flush spec-table small mb-1">
            <?php foreach ($keySpecs as $s): ?>
              <li class="list-group-item"><span><?= e($s['spec_name']); ?></span><span class="text-end"><?= e($s['spec_value']); ?></span></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted small">No key specifications available.</p>
        <?php endif; ?>

        <!-- Info box -->
        <div class="info-box">
          <div class="fw-bold">🔥 100% Genuine Products Guaranteed</div>
          <div class="small text-muted">PCZone provides authentic products with full authenticity. All warranties are valid as per manufacturer's terms.</div>
          <hr>
          <div class="fw-semibold">👉 Cash on Delivery Available</div>
        </div>

      </div><!-- /product-info -->
    </div><!-- /product-row -->

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
        <!-- product description -->
        <div class="mb-3"><?= nl2br(e($product['description'])); ?></div>

        <!-- Detailed specs rendered here -->
        <?php if (!empty($detailedSpecs)): ?>
          <div class="desc-specs">
            <h6 class="mb-2">Detailed Specifications</h6>
            <?php foreach ($detailedSpecs as $d): ?>
              <div class="spec-row">
                <div><?= e($d['spec_name']); ?></div>
                <div class="text-muted"><?= e($d['spec_value']); ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="tab-pane fade" id="reviews" role="tabpanel">
        <p>No reviews yet.</p>
      </div>
    </div>

  </div>

  <script>
    (function() {
      const thumbCol = document.getElementById('thumbCol');
      const mainImage = document.getElementById('mainImage');

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