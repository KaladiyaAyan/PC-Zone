<?php
require_once './config/config.php';
$pageTitle = SITE_NAME . ' - Home';
require_once './includes/db_connect.php';

// Get featured products (limit 8)
$featuredProducts = [];
$conn = getConnection();
// $sql = "SELECT p.*, c.name as category_name 
//         FROM products p 
//         LEFT JOIN categories c ON p.category_id = c.id 
//         ORDER BY p.created_at DESC 
//         LIMIT 8";
// $sql = "SELECT p.*, c.name as category_name 
//         FROM products p 
//         LEFT JOIN categories c ON p.category_id = c.id 
//         ORDER BY RAND() 
//         LIMIT 12";
$sql = "SELECT 
          p.*, 
          c.name AS category_name,
          pi.image_path 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
        WHERE p.is_featured = 1 AND p.is_active = 1
        ORDER BY RAND()
        LIMIT 12";


$result = mysqli_query($conn, $sql);
if ($result) {
  if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      $featuredProducts[] = $row;
    }
  }
  mysqli_free_result($result);
}
mysqli_close($conn);
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-4">
  <!-- Hero Section -->
  <section class="row">
    <div class="col-12">
      <div class="jumbotron bg-primary text-white p-5 rounded">
        <h1 class="display-4">Welcome to <?= htmlspecialchars(SITE_NAME) ?></h1>
        <p class="lead">Discover amazing products at great prices.</p>
        <a class="btn btn-light btn-lg" href="pages/products.php" role="button">Shop Now</a>
      </div>
    </div>
  </section>

  <!-- Featured Products -->
  <section class="row mt-5">
    <div class="col-12">
      <h2 class="mb-4">Featured Products</h2>
    </div>



    <?php if (!empty($featuredProducts)) : ?>
      <?php foreach ($featuredProducts as $product) :
        // $escapedName = htmlspecialchars($product['name']);
        // $escapedDesc = htmlspecialchars($product['description']);
        // $formattedPrice = formatPrice($product['price']);
      ?>
        <div class="col-md-3 mb-4">
          <a href="pages/product-detail.php?id=<?= (int)$product['id'] ?>" class="text-decoration-none text-dark d-block h-100">
            <div class="card border-0 shadow-sm product-card">
              <div class="ratio ratio-4x3 rounded-top overflow-hidden bg-light">
                <img
                  <?php
                  // if (file_exists("./admin/assets/images/" . $product['image_path'])) {
                  //   $path = "./admin/assets/images/" . $product['image_path'];
                  // } else {
                  //   $path = "./uploads/" . $product['image_path'];
                  // }
                  // echo "src='" . $path . "'";
                  ?>
                  src="<?= file_exists("./admin/assets/images/" . $product['image_path']) ? "./admin/assets/images" : "./uploads" ?>/<?= $product['image_path'] ?>"
                  alt="<?= $product['name'] ?>"
                  class="img-fluid p-3 product-image"
                  loading="lazy">
              </div>
              <div class="card-body">
                <div class="mb-2">
                  <h5 class="card-title fw-semibold mb-1"><?= $product['name'] ?></h5>
                </div>
                <div class="mb-2">
                  <p class="card-text text-muted small line-clamp-3">
                    <?= $product['description'] ?>
                  </p>
                </div>
                <div class="mt-auto">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-primary fw-bold fs-6"><?= $product['price'] ?></span>
                    <span class="small text-muted">View More →</span>
                  </div>
                  <form action="add_to_cart.php" method="POST" class="add-to-cart">
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                      <i class="fas fa-shopping-cart me-1"></i> Add to Cart
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>

    <?php else: ?>
      <div class="col-12">
        <div class="alert alert-warning">No featured products found.</div>
      </div>
    <?php endif; ?>
  </section>
</div>

<?php include 'includes/footer.php'; ?>


<script>
  document.querySelectorAll('.add-to-cart').forEach(form => {
    form.addEventListener('click', e => e.stopPropagation());
    form.addEventListener('submit', async e => {
      e.preventDefault();

      const formData = new FormData(form);
      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: formData
        });

        if (response.ok) {
          alert('Added to cart successfully!');
        }
      } catch (error) {
        console.error('Error:', error);
      }
    });
  });
</script>