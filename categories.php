<?php
require_once './includes/db_connect.php'; // provides $conn (mysqli)

// sanitize slug
$slug = strtolower($_GET['slug'] ?? '');
$slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
if (!$slug) {
  header('Location:/');
  exit;
}

// get category id
$stmt = mysqli_prepare($conn, "SELECT category_id, name FROM categories WHERE slug = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $slug);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $cat_id, $cat_name);
if (!mysqli_stmt_fetch($stmt)) {
  http_response_code(404);
  echo "Category not found";
  exit;
}
mysqli_stmt_close($stmt);

// fetch products
$stmt = mysqli_prepare($conn, "SELECT product_id, name, price, main_image FROM products WHERE category_id = ? AND status = 1 ORDER BY name");
mysqli_stmt_bind_param($stmt, "i", $cat_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// rendering (simple)
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo htmlspecialchars($cat_name); ?></title>
  <meta name="description" content="PC ZONE - pre-built PCs, custom builds and premium components.">
  <link rel="stylesheet" href="./assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="./assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
  <h1><?php echo htmlspecialchars($cat_name); ?></h1>
  <div class="row">
    <?php while ($p = mysqli_fetch_assoc($result)): ?>
      <div class="col-6 col-md-3">
        <a href="/product.php?id=<?php echo $p['product_id']; ?>">
          <img src="/uploads/<?php echo htmlspecialchars($p['main_image']); ?>" alt="" class="img-fluid">
          <div><?php echo htmlspecialchars($p['name']); ?></div>
          <div>₹<?php echo number_format($p['price']); ?></div>
        </a>
      </div>
    <?php endwhile; ?>
  </div>
</body>

</html>
<?php mysqli_stmt_close($stmt); ?>