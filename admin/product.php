<?php
require_once '../includes/db_connect.php';
session_start();

// Redirect if not logged in
if (empty($_SESSION['admin_logged_in'])) {
  header('Location: ./login.php');
  exit;
}

// Query remains the same
$query = "
    SELECT p.product_id, p.product_name, p.description, p.price, p.stock, 
           b.brand_name, c.category_name, p.main_image
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.product_id DESC";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PC ZONE Admin - Products</title>

  <!-- VENDOR CSS -->
  <link rel="stylesheet" href="./assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="./assets/vendor/fontawesome/css/all.min.css">

  <!-- CORE & PAGE CSS -->
  <link rel="stylesheet" href="./assets/css/style.css">
  <link rel="stylesheet" href="./assets/css/products.css">

  <script>
    (function() {
      const theme = localStorage.getItem('pczoneTheme');
      if (theme === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
      }
    })();
  </script>
</head>

<body>
  <?php
  $current_page = 'product';
  include './includes/header.php';
  include './includes/sidebar.php';
  ?>

  <main class="main-content">
    <!-- Flash Messages -->
    <div id="flash-alert">
      <?php if (isset($_GET['delete'])): ?>
        <div class="<?= $_GET['delete'] === 'success' ? 'alert-success' : 'alert-danger' ?>">
          <?= $_GET['delete'] === 'success' ? 'Product deleted successfully!' : 'Failed to delete product.' ?>
        </div>
      <?php endif; ?>
      <?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
        <div class="alert-success">Product updated successfully!</div>
      <?php endif; ?>
      <?php if (isset($_GET['insert']) && $_GET['insert'] === 'success'): ?>
        <div class="alert-success">Product added successfully!</div>
      <?php endif; ?>
    </div>

    <!-- Products Header -->
    <div class="products-header">
      <h1>Products</h1>
      <a href="./add_product.php" class="btn-add">
        <i class="fas fa-plus"></i> Add New Product
      </a>
    </div>

    <!-- Products Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Brand</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($product = $result->fetch_assoc()): ?>
              <tr class="product-row" data-product='<?= htmlspecialchars(json_encode($product)) ?>'>
                <td><?= (int)$product['product_id'] ?></td>
                <td class="product-image">
                  <?php
                  $image_path = '';
                  if (!empty($product['main_image'])) {
                    $primary_path = '../uploads/' . $product['main_image'];
                    $fallback_path = '../assets/images/products/' . $product['main_image'];

                    if (file_exists($primary_path)) {
                      $image_path = $primary_path;
                    } elseif (file_exists($fallback_path)) {
                      $image_path = $fallback_path;
                    }
                  }

                  if ($image_path):
                  ?>
                    <img src="<?= htmlspecialchars($image_path) ?>"
                      alt="<?= htmlspecialchars($product['product_name']) ?>"
                      class="product-thumb">
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($product['product_name']) ?></td>
                <td><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($product['brand_name'] ?? 'N/A') ?></td>
                <td>₹<?= number_format($product['price'], 2) ?></td>
                <td>
                  <?php
                  $stock = (int)$product['stock'];
                  $stockClass = ($stock <= 0) ? 'out-of-stock' : (($stock < 10) ? 'low-stock' : 'in-stock');
                  ?>
                  <span class="badge-status <?= $stockClass ?>"><?= $stock ?></span>
                </td>
                <td>
                  <a href="edit_product.php?product_id=<?= (int)$product['product_id'] ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                  <a href="delete.php?product=<?= (int)$product['product_id'] ?>" class="btn-delete" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i> Delete</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-4">No products found. <a href="add_product.php">Add a Product</a></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- Product Details Modal -->
  <div id="productModal" class="product-modal">
    <div class="modal-content">
      <span class="close-btn">&times;</span>
      <h4 id="modal-title" class="product-title"></h4>
      <div class="modal-images" id="modal-image"></div>
      <p><strong>Description:</strong> <span id="modal-description"></span></p>
      <p><strong>Price:</strong> ₹<span id="modal-price"></span></p>
      <p><strong>Stock:</strong> <span id="modal-stock"></span></p>
      <p><strong>Brand:</strong> <span id="modal-brand"></span></p>
      <p><strong>Category:</strong> <span id="modal-category"></span></p>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const modal = document.getElementById("productModal");
      const closeBtn = document.querySelector(".close-btn");

      function openModal(product) {
        document.getElementById("modal-title").textContent = product.product_name || '';
        document.getElementById("modal-description").textContent = product.description || 'No description available.';
        document.getElementById("modal-price").textContent = parseFloat(product.price || 0).toFixed(2);
        document.getElementById("modal-stock").textContent = product.stock || 0;
        document.getElementById("modal-brand").textContent = product.brand_name || 'N/A';
        document.getElementById("modal-category").textContent = product.category_name || 'N/A';

        const imgContainer = document.getElementById("modal-image");
        imgContainer.innerHTML = ""; // Clear previous image

        if (product.main_image) {
          // --- NEW: Image fallback logic for the modal ---
          const img = document.createElement('img');
          img.alt = product.product_name;

          // Set the primary source
          img.src = `../uploads/${product.main_image}`;

          // Set the fallback source via the onerror event
          img.onerror = function() {
            this.onerror = null; // Prevents infinite loops if fallback also fails
            this.src = `../assets/images/products/${product.main_image}`;
          };

          imgContainer.appendChild(img);
        }
        modal.style.display = "block";
      }

      function closeModal() {
        modal.style.display = "none";
      }

      // Event listeners for modal
      document.querySelectorAll(".product-row").forEach(row => {
        row.addEventListener("click", (e) => {
          if (e.target.closest('a')) return;
          const productData = JSON.parse(row.dataset.product || '{}');
          openModal(productData);
        });
      });

      closeBtn.addEventListener("click", closeModal);
      window.addEventListener("click", (event) => {
        if (event.target === modal) closeModal();
      });

      // Auto-hide flash messages
      setTimeout(() => {
        const alertBox = document.getElementById('flash-alert');
        if (alertBox && alertBox.children.length > 0) {
          alertBox.style.transition = 'opacity 0.5s';
          alertBox.style.opacity = 0;
          setTimeout(() => alertBox.remove(), 500);

          // Clean up URL
          if (window.history.replaceState) {
            const url = new URL(window.location);
            ['delete', 'update', 'insert'].forEach(param => url.searchParams.delete(param));
            window.history.replaceState({
              path: url.href
            }, '', url.href);
          }
        }
      }, 4000);
    });
  </script>
</body>

</html>