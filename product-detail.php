<?php
session_start();

require('./includes/functions.php');
require('./includes/db_connect.php');

$conn = getConnection();

// Get the product slug from the URL
$slug = isset($_GET['slug']) ? mysqli_real_escape_string($conn, $_GET['slug']) : '';
if (!$slug) {
  header('Location: index.php');
  exit;
}

// --- Simplified Main Query ---
// Fetches product details, brand, category, average rating, and review count in one go.
$sql_product = "SELECT p.*, b.brand_name, c.category_name,
                    (SELECT COALESCE(AVG(rating), 0) FROM product_reviews WHERE product_id = p.product_id) as avg_rating,
                    (SELECT COUNT(*) FROM product_reviews WHERE product_id = p.product_id) as review_count
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.brand_id
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.slug = '$slug' AND p.is_active = 1 LIMIT 1";

$result_product = mysqli_query($conn, $sql_product);
$product = mysqli_fetch_assoc($result_product);

// If product not found, redirect to the homepage
if (!$product) {
  message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Product not found');
  header('Location: index.php');
  exit;
}
$productId = (int)$product['product_id'];

// Fetch product specifications (remains a separate query)
$sql_specs = "SELECT spec_group, spec_name, spec_value FROM product_specs WHERE product_id = $productId ORDER BY display_order ASC";
$result_specs = mysqli_query($conn, $sql_specs);
$specs = [];
while ($row = mysqli_fetch_assoc($result_specs)) {
  $group = $row['spec_group'] ?: 'General';
  $specs[$group][] = $row;
}

// Fetch all reviews (remains a separate query)
$sql_reviews = "SELECT r.comment, r.created_at, r.rating, u.username 
                FROM product_reviews r JOIN users u ON r.user_id = u.user_id 
                WHERE r.product_id = $productId ORDER BY r.created_at DESC";
$result_reviews = mysqli_query($conn, $sql_reviews);
$reviews = mysqli_fetch_all($result_reviews, MYSQLI_ASSOC);

mysqli_close($conn);

// --- Prepare Data for the Page ---

// Assign rating and review data directly from the main product query
$avgRating = round((float)($product['avg_rating'] ?? 0), 1);
$totalReviews = (int)($product['review_count'] ?? 0);

// Build the list of image URLs with fallback logic
$imageUrls = [];
$imageFields = ['main_image', 'image_1', 'image_2', 'image_3'];
foreach ($imageFields as $field) {
  if (!empty($product[$field])) {
    $filename = trim($product[$field]);
    if (file_exists('./uploads/' . $filename)) {
      $imageUrls[] = './uploads/' . rawurlencode($filename);
    } elseif (file_exists('./assets/images/products/' . $filename)) {
      $imageUrls[] = './assets/images/products/' . rawurlencode($filename);
    }
  }
}
if (empty($imageUrls)) {
  $imageUrls[] = './assets/images/no-image.png'; // Fallback to default image
}

// Calculate the final price after discount
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
    ?>

    /* --- Product Detail Page Styles --- */
    body {
      background-color: #f8f9fa;
      /* A light gray background for the page */
    }

    /* Main Product Section */
    .product-view-container {
      background-color: #fff;
      padding: 2rem;
      border-radius: 1rem;
      box-shadow: 0 4px 25px rgba(0, 0, 0, 0.05);
    }

    /* Image Gallery (Left Column) */
    .product-gallery-main {
      border: 1px solid #dee2e6;
      border-radius: 0.5rem;
      padding: 1rem;
      margin-bottom: 1rem;
      height: 450px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .product-gallery-main img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
    }

    .product-gallery-thumbs {
      display: flex;
      gap: 1rem;
      justify-content: center;
    }

    .thumb-item {
      width: 80px;
      height: 80px;
      border: 2px solid #dee2e6;
      border-radius: 0.5rem;
      padding: 0.5rem;
      cursor: pointer;
      opacity: 0.6;
      transition: all 0.2s ease;
    }

    .thumb-item:hover,
    .thumb-item.active {
      opacity: 1;
      border-color: #0d6efd;
      /* Theme blue */
    }

    /* Product Info (Right Column) */
    .product-title {
      font-weight: 700;
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }

    .product-meta {
      font-size: 0.9rem;
      color: #6c757d;
      margin-bottom: 1rem;
    }

    .product-rating {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1.5rem;
      color: #fd7e14;
      /* Orange/Yellow for stars */
    }

    .product-rating .review-count {
      color: #6c757d;
      font-size: 0.9rem;
    }

    .product-price-box {
      background-color: #f8f9fa;
      padding: 1rem;
      border-radius: 0.5rem;
      margin-bottom: 1.5rem;
    }

    .product-price-box .final-price {
      font-size: 2rem;
      font-weight: 700;
      color: #d9534f;
      /* A shade of red */
    }

    .product-price-box .original-price {
      color: #6c757d;
    }

    .add-to-cart-form {
      display: grid;
      grid-template-columns: 100px 1fr;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    .add-to-cart-form .form-control {
      border-radius: 0.5rem;
      text-align: center;
    }

    /* Reusing the gradient button style */
    .btn-add-to-cart {
      background: linear-gradient(to bottom, #2b5876, #4e4376);
      color: #fff;
      border: none;
      transition: all 0.2s ease-in-out;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      font-weight: bold;
    }

    .btn-add-to-cart:hover {
      opacity: 0.9;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Details & Reviews Tabs */
    .product-details-tabs {
      margin-top: 3rem;
    }

    .product-details-tabs .nav-tabs {
      border-bottom: 2px solid #dee2e6;
    }

    .product-details-tabs .nav-link {
      color: #495057;
      font-weight: 500;
      border: none;
      border-bottom: 2px solid transparent;
      padding: 1rem;
    }

    .product-details-tabs .nav-link.active {
      color: #0d6efd;
      border-bottom-color: #0d6efd;
    }

    .spec-table {
      margin-top: 1.5rem;
    }

    .spec-row {
      display: grid;
      grid-template-columns: 200px 1fr;
      padding: 0.8rem 0;
      border-bottom: 1px solid #f1f1f1;
    }

    .spec-row .spec-name {
      font-weight: 500;
      color: #343a40;
    }

    .spec-row .spec-value {
      color: #6c757d;
    }

    .review-item {
      padding: 1.5rem 0;
      border-bottom: 1px solid #f1f1f1;
    }

    .review-item:last-child {
      border-bottom: none;
    }
  </style>
</head>

<body>
  <?php include('./includes/navbar.php'); ?>

  <div class="container my-5">
    <div class="product-view-container">
      <div class="row g-5">
        <div class="col-lg-6">
          <div class="product-gallery-main">
            <img id="mainImage" src="<?= e($imageUrls[0]); ?>" alt="<?= e($product['product_name']); ?>">
          </div>
          <div class="product-gallery-thumbs" id="thumbCol">
            <?php foreach ($imageUrls as $url) : ?>
              <img src="<?= e($url); ?>" class="thumb-item" alt="Product Thumbnail">
            <?php endforeach; ?>
          </div>
        </div>

        <div class="col-lg-6">
          <h1 class="product-title"><?= e($product['product_name']); ?></h1>

          <div class="product-meta">
            <span>Brand: <strong><?= e($product['brand_name'] ?? 'N/A'); ?></strong></span> |
            <span>Category: <strong><?= e($product['category_name'] ?? 'N/A'); ?></strong></span> |
            <span>SKU: <strong><?= e($product['sku']); ?></strong></span>
          </div>

          <div class="product-rating">
            <?php for ($i = 1; $i <= 5; $i++) echo '<i class="ri-star-' . ($i <= $avgRating ? 'fill' : 'line') . '"></i>'; ?>
            <a href="#reviews" class="review-count">(<?= $totalReviews; ?> Reviews)</a>
          </div>

          <div class="product-price-box">
            <span class="final-price"><?= formatPrice($finalPrice) ?></span>
            <?php if ((float)$product['discount'] > 0) : ?>
              <span class="original-price text-decoration-line-through ms-2"><?= formatPrice($product['price']) ?></span>
              <span class="badge bg-success ms-2"><?= (int)$product['discount'] ?>% OFF</span>
            <?php endif; ?>
          </div>

          <?php if ((int)$product['stock'] > 0): ?>
            <span class="badge bg-success mb-3">In Stock</span>
            <form method="post" action="addtocart.php" class="add-to-cart-form">
              <input type="number" name="quantity" value="1" min="1" max="<?= (int)$product['stock'] ?>" class="form-control">
              <button type="submit" class="btn btn-add-to-cart"><i class="ri-shopping-cart-line"></i> Add to Cart</button>
            </form>
          <?php else: ?>
            <span class="badge bg-danger mb-3">Out of Stock</span>
            <button class="btn btn-secondary w-100" disabled>Add to Cart</button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="product-details-tabs">
      <ul class="nav nav-tabs" id="productTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#specs" type="button">Specifications</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews" type="button">Reviews (<?= $totalReviews; ?>)</button>
        </li>
      </ul>
      <div class="tab-content bg-white p-4 rounded-bottom">
        <div class="tab-pane fade show active" id="specs" role="tabpanel">
          <h3><?= e($product['product_name']); ?></h3>
          <p class="text-muted"><?= nl2br(e($product['description'])); ?></p>
          <?php if (!empty($specs)) : ?>
            <div class="spec-table">
              <?php foreach ($specs as $group => $spec_items): ?>
                <?php foreach ($spec_items as $spec) : ?>
                  <div class="spec-row">
                    <div class="spec-name"><?= e($spec['spec_name']); ?></div>
                    <div class="spec-value"><?= e($spec['spec_value']); ?></div>
                  </div>
                <?php endforeach; ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="tab-pane fade" id="reviews" role="tabpanel">
          <?php if (empty($reviews)) : ?>
            <p>No reviews yet for this product.</p>
            <?php else : foreach ($reviews as $rev) : ?>
              <div class="review-item">
                <div class="d-flex justify-content-between">
                  <strong><?= e($rev['username']) ?></strong>
                  <small class="text-muted"><?= date('d M Y', strtotime($rev['created_at'])) ?></small>
                </div>
                <div class="text-warning my-1">
                  <?php for ($i = 1; $i <= 5; $i++) echo '<i class="ri-star-' . ($i <= $rev['rating'] ? 'fill' : 'line') . '"></i>'; ?>
                </div>
                <p class="m-0"><?= nl2br(e($rev['comment'])) ?></p>
              </div>
          <?php endforeach;
          endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php include './includes/footer.php'; ?>
  <?php include './includes/footer-link.php'; ?>

  <script>
    // Simple script to switch main image when a thumbnail is clicked
    document.addEventListener('DOMContentLoaded', () => {
      const mainImage = document.getElementById('mainImage');
      const thumbContainer = document.getElementById('thumbCol');
      const thumbs = thumbContainer.querySelectorAll('.thumb-item');

      // Set the first thumb as active initially
      if (thumbs.length > 0) {
        thumbs[0].classList.add('active');
      }

      thumbContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('thumb-item')) {
          mainImage.src = e.target.src;
          // Update active state
          thumbs.forEach(thumb => thumb.classList.remove('active'));
          e.target.classList.add('active');
        }
      });
    });
  </script>
</body>

</html>