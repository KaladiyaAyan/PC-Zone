<?php
include '../includes/db_connect.php';
include './includes/functions.php';

session_start();
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ./login1.php');
  exit;
}

// Use products.main_image directly. There is no product_images table in your provided schema.
$query = "
    SELECT p.product_id,
           p.product_name,
           p.description,
           p.price,
           p.stock,
           b.brand_name,
           c.category_name,
           p.main_image AS main_image
    FROM products p
    LEFT JOIN brands b 
        ON p.brand_id = b.brand_id
    LEFT JOIN categories c 
        ON p.category_id = c.category_id
    ORDER BY p.product_id DESC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PC ZONE Admin - Products</title>
  <?php include "./includes/header-link.php" ?>
</head>

<body>
  <?php include './includes/header.php'; ?>
  <?php $current_page = 'product';
  include './includes/sidebar.php'; ?>

  <main class="main-content">
    <!-- Flash Messages -->
    <?php if (isset($_GET['delete']) || isset($_GET['update']) || isset($_GET['insert'])): ?>
      <div id="flash-alert">
        <?php if (isset($_GET['delete'])): ?>
          <?php if ($_GET['delete'] === 'success'): ?>
            <div class="alert-success">✅ Product deleted successfully!</div>
          <?php elseif ($_GET['delete'] === 'failed'): ?>
            <div class="alert-danger">❌ Failed to delete product. Please try again.</div>
          <?php elseif ($_GET['delete'] === 'invalid'): ?>
            <div class="alert-warning">⚠️ Invalid product ID.</div>
          <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
          <div class="alert-success">✅ Product updated successfully!</div>
        <?php endif; ?>

        <?php if (isset($_GET['insert']) && $_GET['insert'] === 'success'): ?>
          <div class="alert-success">✅ Product added successfully!</div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Products Header -->
    <div class="products-header">
      <h1>Products</h1>
      <a href="./add_product.php" class="btn-add">
        <i class="fa-solid fa-plus"></i> Add New Product
      </a>
    </div>

    <!-- Products Table -->
    <div class="table-container">
      <table class="product-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Brand</th>
            <th>Price (₹)</th>
            <th>Stock</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($product = mysqli_fetch_assoc($result)):
              // build product json for modal; ensure image is empty string when none
              $imgValue = trim((string)($product['main_image'] ?? ''));
              $pdata = [
                "id" => $product["product_id"],
                "name" => $product["product_name"],
                "description" => $product["description"],
                "price" => $product["price"],
                "stock" => $product["stock"],
                "brand" => $product["brand_name"],
                "category" => $product["category_name"],
                "image" => $imgValue ? $imgValue : '',
              ];
            ?>
              <tr class="product-row" data-product='<?= htmlspecialchars(json_encode($pdata, JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>'>
                <td><?= (int)$product['product_id'] ?></td>
                <td class="product-image">
                  <?php
                  // If main_image exists and file present in uploads or assets, show it.
                  $imagePath = '';
                  if (!empty($product['main_image'])) {
                    $candidate1 = '../uploads/' . $product['main_image'];
                    $candidate2 = '../assets/images/products/' . $product['main_image'];

                    if (file_exists($candidate1)) {
                      $imagePath = '../uploads/' . rawurlencode($product['main_image']);
                    } elseif (file_exists($candidate2)) {
                      $imagePath = '../assets/images/products/' . rawurlencode($product['main_image']);
                    } else {
                      // image not found physically; per your instruction show empty string (no image)
                      $imagePath = '';
                    }
                  }
                  if ($imagePath !== ''): ?>
                    <img src="<?= htmlspecialchars($imagePath, ENT_QUOTES) ?>"
                      alt="<?= htmlspecialchars($product['product_name'], ENT_QUOTES) ?>"
                      class="product-thumb img-fluid"
                      style="max-width: 100px;">
                  <?php else: ?>
                    <?= '' /* intentionally empty when no main image */ ?>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($product['product_name']) ?></td>
                <td><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($product['brand_name'] ?? 'N/A') ?></td>
                <td>₹<?= number_format($product['price'], 2) ?></td>
                <td>
                  <span class="stock-badge <?= $product['stock'] <= 0 ? 'out-of-stock' : ($product['stock'] < 10 ? 'low-stock' : 'in-stock') ?>">
                    <?= (int)$product['stock'] ?>
                  </span>
                </td>
                <td>
                  <a href="edit_product.php?product_id=<?= (int)$product['product_id'] ?>" class="btn-edit">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                  <a href="delete.php?product=<?= (int)$product['product_id'] ?>"
                    class="btn-delete d-flex gap-1 align-items-center"
                    onclick="return confirm('Are you sure you want to delete this product?')">
                    <i class="fas fa-trash"></i> Delete
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center">No products found. <a href="add_product.php">Add your first product</a></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- Product Details Modal -->
  <div id="productModal" class="product-modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeModal()">×</span>
      <h2 id="modal-title"></h2>
      <div id="modal-image"></div>
      <p><strong>Description:</strong> <span id="modal-description"></span></p>
      <p><strong>Price:</strong> ₹<span id="modal-price"></span></p>
      <p><strong>Stock:</strong> <span id="modal-stock"></span></p>
      <p><strong>Brand:</strong> <span id="modal-brand"></span></p>
      <p><strong>Category:</strong> <span id="modal-category"></span></p>
    </div>
  </div>

  <script>
    function closeModal() {
      document.getElementById("productModal").style.display = "none";
    }

    document.addEventListener("DOMContentLoaded", function() {

      // Prevent row click when clicking action buttons
      document.querySelectorAll(".btn-edit, .btn-delete").forEach(btn => {
        btn.addEventListener("click", function(e) {
          e.stopPropagation();
        });
      });

      // Product row click to open modal
      document.querySelectorAll(".product-row").forEach(row => {
        row.addEventListener("click", () => {
          const product = JSON.parse(row.dataset.product || '{}');

          document.getElementById("modal-title").textContent = product.name || '';
          document.getElementById("modal-description").textContent = product.description || 'No description available';
          document.getElementById("modal-price").textContent = parseFloat(product.price || 0).toFixed(2);
          document.getElementById("modal-stock").textContent = product.stock || 0;
          document.getElementById("modal-brand").textContent = product.brand || 'N/A';
          document.getElementById("modal-category").textContent = product.category || 'N/A';

          // Show image only if image string provided
          const imgContainer = document.getElementById("modal-image");
          imgContainer.innerHTML = "";
          if (product.image) {
            const img = document.createElement("img");
            // prefer uploads path; fallback to assets path in case file is placed there
            img.src = "../uploads/" + product.image;
            img.alt = product.name || '';
            img.style.maxWidth = "200px";
            img.style.height = "auto";
            img.style.border = "1px solid #ddd";
            img.style.borderRadius = "5px";
            img.onerror = function() {
              this.onerror = null;
              this.src = "../assets/images/products/" + product.image;
            };
            imgContainer.appendChild(img);
          } else {
            // keep empty string per your instruction
            imgContainer.innerHTML = "";
          }

          document.getElementById("productModal").style.display = "block";
        });
      });

      // Auto-hide flash messages and clean URL
      setTimeout(() => {
        const alertBox = document.getElementById('flash-alert');
        if (alertBox) {
          alertBox.style.transition = 'opacity 0.5s';
          alertBox.style.opacity = 0;
          setTimeout(() => alertBox.remove(), 500);
        }
        if (window.history.replaceState) {
          const url = new URL(window.location);
          url.searchParams.delete('delete');
          url.searchParams.delete('update');
          url.searchParams.delete('insert');
          window.history.replaceState({}, document.title, url.pathname);
        }
      }, 3000);

      // Close modal when clicking outside
      window.addEventListener('click', function(event) {
        const modal = document.getElementById("productModal");
        if (event.target === modal) {
          closeModal();
        }
      });
    });
  </script>

  <script src="./assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>