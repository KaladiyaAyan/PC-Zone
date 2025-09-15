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

// render
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Products</title>

  <!-- include css links page  -->
  <?php include('./includes/header-links.php') ?>

  <style>
    <?php include('./assets/css/navbar.css'); ?>
  </style>

</head>

<body>

  <?php
  require('./includes/navbar.php');
  ?>

  <div class="container py-4">
    <div class="row mb-3">
      <div class="col">
        <h4><?php echo $slug ? htmlspecialchars(ucfirst($slug)) : 'All Products'; ?></h4>
      </div>
    </div>

    <div class="row g-3 product-grid">
      <?php if ($res && mysqli_num_rows($res) > 0): ?>
        <?php while ($p = mysqli_fetch_assoc($res)): ?>
          <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100">
              <?php $img = $p['main_image'] ?: 'placeholder.png'; ?>
              <img src="<?php echo htmlspecialchars('uploads/products/' . $img); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($p['product_name']); ?>">
              <div class="card-body p-2">
                <h6 class="card-title mb-1" style="font-size:0.95rem;"><?php echo htmlspecialchars($p['product_name']); ?></h6>
                <p class="mb-1 small"><?php echo '₹' . number_format((float)$p['price'], 2); ?></p>
                <a href="product-details.php?slug=<?php echo urlencode($p['slug']); ?>" class="stretched-link"></a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="alert alert-warning mb-0">No products found.</div>
        </div>
      <?php endif; ?>
    </div>
  </div>

</body>

</html>
<?php mysqli_close($conn); ?>