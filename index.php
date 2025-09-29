<?php
session_start();
require('./includes/db_connect.php');
require('./includes/functions.php');
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PC Zone</title>
  <?php include('./includes/header-link.php') ?>
  <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>
  <?php
  require('./includes/alert.php');
  require('./includes/navbar.php');
  $featuredProducts = getFeaturedProducts(4, true);
  ?>
  <main class="container mt-4">
    <section class="hero-section">
      <div class="hero-content">
        <h1>Welcome to PC Zone</h1>
        <p>Build your dream PC with premium components and expert guidance.</p>
        <div class="hero-buttons">
          <a class="btn btn-light" href="product.php" role="button">Shop Now</a>
          <a class="btn btn-outline-light" href="custom-pc.php" role="button">Build Custom PC</a>
        </div>
      </div>
    </section>

    <div class="row mt-5">
      <div class="col-12">
        <h2 class="fw-bold text-center mb-4">Featured Products</h2>
      </div>
    </div>

    <div class="row">
      <?php if (empty($featuredProducts)): ?>
        <div class="col-12">
          <p class="text-muted text-center">No featured products found.</p>
        </div>
      <?php else: ?>
        <?php foreach ($featuredProducts as $product):
          // Logic to get product details
          $pid = (int)$product['product_id'];
          $name = $product['product_name'];
          $slug = $product['slug'];
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
              <a href="product-detail.php?slug=<?= e($slug) ?>" class="product-image-container">
                <img src="<?= e($imgPath) ?>" alt="<?= e($name) ?>">
              </a>
              <div class="card-body d-flex flex-column">
                <h5 class="card-title">
                  <a href="product-detail.php?slug=<?= e($slug) ?>" class="product-title-link"><?= e($name) ?></a>
                </h5>
                <p class="card-text small text-muted mb-3"><?= e(mb_strimwidth($desc, 0, 80, '…')) ?></p>
                <div class="mt-auto">
                  <p class="fs-5 fw-bold text-dark m-0 mb-2">
                    <?= formatPrice($finalPrice) ?>
                    <?php if ($discount > 0): ?>
                      <span class="text-muted text-decoration-line-through small ms-1"><?= formatPrice($price) ?></span>
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


    <section class="build-section" id="custom">
      <div class="container">
        <div class="text-center mb-5">
          <h2 class="fw-bold">Custom PC Builds</h2>
          <p class="text-muted">Choose your platform and create your perfect rig.</p>
        </div>

        <div class="row g-4">
          <div class="col-md-6">
            <div class="build-card">
              <img src="assets/images/intel_custom_build.jpg" alt="Intel Custom PC Build">
              <div class="card-body">
                <h3 class="fw-bold">Intel Builds</h3>
                <p class="text-muted">Optimized for high-end gaming and single-core performance.</p>
                <ul class="specs-list">
                  <li><i class="ri-checkbox-circle-line"></i> Latest Gen Options</li>
                  <li><i class="ri-checkbox-circle-line"></i> DDR5 Memory Support</li>
                  <li><i class="ri-checkbox-circle-line"></i> PCIe 5.0 Ready</li>
                </ul>
                <a href="custom-pc.php?platform=intel" class="btn btn-gradient d-block">Build Intel PC</a>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="build-card">
              <img src="assets/images/amd_custom_build.jpg" alt="AMD Custom PC Build">
              <div class="card-body">
                <h3 class="fw-bold">AMD Builds</h3>
                <p class="text-muted">Excellent multi-core power for streaming and content creation.</p>
                <ul class="specs-list">
                  <li><i class="ri-checkbox-circle-line"></i> Ryzen 7000 Series</li>
                  <li><i class="ri-checkbox-circle-line"></i> High Core Counts</li>
                  <li><i class="ri-checkbox-circle-line"></i> Great Value & Performance</li>
                </ul>
                <a href="custom-pc.php?platform=amd" class="btn btn-gradient d-block">Build AMD PC</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="contact-section mt-5" id="contact">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-5 contact-info">
            <h2>Get In Touch</h2>
            <p>Have questions about a product or a custom build? We're here to help.</p>
            <ul class="contact-details mt-4">
              <li>
                <i class="ri-phone-fill"></i>
                <span>+1 234 567 8900</span>
              </li>
              <li>
                <i class="ri-mail-fill"></i>
                <span>support@pczone.com</span>
              </li>
              <li>
                <i class="ri-map-pin-fill"></i>
                <span>Surendranagar, Gujarat, India</span>
              </li>
            </ul>
          </div>

          <div class="col-lg-7">
            <form action="#" method="POST" class="contact-form">
              <div class="row g-3">
                <div class="col-md-6">
                  <input type="text" name="name" class="form-control" placeholder="Your Full Name" required>
                </div>
                <div class="col-md-6">
                  <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                </div>
                <div class="col-12">
                  <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                </div>
                <div class="col-12">
                  <textarea name="message" rows="5" class="form-control" placeholder="Write your message here..." required></textarea>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-gradient w-100">Send Message</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>

  </main>

  <?php include './includes/footer.php'; ?>
  <?php include './includes/footer-link.php'; ?>
  <script>
    var title = document.querySelectorAll('.product-title-link');

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