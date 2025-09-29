<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();

// Redirect if not logged in
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ../login.php');
  exit;
}

// --- DATA FETCHING ---
$query = "SELECT p.product_id, p.product_name, p.description, p.price, p.stock, b.brand_name, c.category_name, p.main_image
          FROM products p
          LEFT JOIN brands b ON p.brand_id = b.brand_id
          LEFT JOIN categories c ON p.category_id = c.category_id
          ORDER BY p.product_id DESC";
$result = $conn->query($query);
$products = $result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Products - PCZone Admin</title>

  <?php require('./includes/header-link.php') ?>
  <link rel="stylesheet" href="./assets/css/products.css">

  <script>
    (function() {
      if (localStorage.getItem('pczoneTheme') === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
      }
    })();
  </script>
</head>

<body>
  <?php require('./includes/alert.php'); ?>
  <?php
  $current_page = 'product';
  include './includes/header.php';
  include './includes/sidebar.php';
  ?>

  <main class="main-content">
    <div class="page-header">
      <h2><i class="fas fa-boxes"></i> Products</h2>
      <a href="./add_product.php" class="btn-add">
        <i class="fas fa-plus"></i> Add New Product
      </a>
    </div>

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
          <?php if (empty($products)): ?>
            <tr>
              <td colspan="8" class="text-center py-4">No products found.</td>
            </tr>
            <?php else: foreach ($products as $product): ?>
              <?php
              $pdata = [
                "id" => (int)$product["product_id"],
                "name" => $product["product_name"],
                "description" => $product["description"],
                "price" => number_format((float)$product["price"], 2),
                "stock" => (int)$product["stock"],
                "brand" => $product["brand_name"] ?? 'N/A',
                "category" => $product["category_name"] ?? 'N/A',
                "image" => $product["main_image"] ?? ''
              ];
              ?>
              <tr class="product-row" data-product='<?= e(json_encode($pdata)) ?>' style="cursor: pointer;">
                <td><?= (int)$product['product_id'] ?></td>
                <td>
                  <?php
                  $imagePath = '';
                  if (!empty($product['main_image'])) {
                    $candidate1 = '../uploads/' . $product['main_image'];
                    $candidate2 = '../assets/images/products/' . $product['main_image'];
                    if (file_exists($candidate1)) $imagePath = $candidate1;
                    elseif (file_exists($candidate2)) $imagePath = $candidate2;
                  }
                  if ($imagePath): ?>
                    <img src="<?= e($imagePath) ?>" alt="<?= e($product['product_name']) ?>" class="product-thumb">
                  <?php endif; ?>
                </td>
                <td><?= e($product['product_name']) ?></td>
                <td><?= e($product['category_name'] ?? 'N/A') ?></td>
                <td><?= e($product['brand_name'] ?? 'N/A') ?></td>
                <td>₹<?= number_format((float)$product['price'], 2) ?></td>
                <td>
                  <?php
                  $stock = (int)$product['stock'];
                  $stock_class = 'completed'; // In stock
                  if ($stock < 10) $stock_class = 'pending'; // Low stock
                  if ($stock == 0) $stock_class = 'cancelled'; // Out of stock
                  ?>
                  <span class="badge-status <?= $stock_class ?>"><?= $stock ?></span>
                </td>
                <td>
                  <a href="edit_product.php?product_id=<?= (int)$product['product_id'] ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                  <button class="btn-delete"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteConfirmModal"
                    data-delete-url="delete.php?product=<?= (int)$product['product_id'] ?>">
                    <i class="fas fa-trash"></i> Delete
                  </button>
                </td>
              </tr>
          <?php endforeach;
          endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- Product Details Modal -->
  <div id="productModal" class="product-modal">
    <div class="product-modal-content">
      <span class="product-modal-close" id="closeModalBtn">&times;</span>
      <h2 id="modal-title"></h2>
      <div class="modal-image-container">
        <img id="modal-image" src="" alt="Product Image">
      </div>
      <p><strong>Description:</strong> <span id="modal-description"></span></p>
      <p><strong>Price:</strong> ₹<span id="modal-price"></span></p>
      <p><strong>Stock:</strong> <span id="modal-stock"></span></p>
      <p><strong>Brand:</strong> <span id="modal-brand"></span></p>
      <p><strong>Category:</strong> <span id="modal-category"></span></p>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content theme-card">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Deletion</h5>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete this product? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <a href="#" id="confirmDeleteBtn" class="btn-add" style="background-color: var(--danger); color: #fff;">Delete</a>
        </div>
      </div>
    </div>
  </div>

  <?php require('./includes/footer-link.php') ?>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const productModal = document.getElementById("productModal");
      const closeModalBtn = document.getElementById("closeModalBtn");

      // --- Product Details Modal Logic ---
      document.querySelectorAll(".product-row").forEach(row => {
        row.addEventListener("click", (e) => {
          // Don't open modal if an action button was clicked
          if (e.target.closest('a, button')) return;

          const data = JSON.parse(row.dataset.product || '{}');

          document.getElementById("modal-title").textContent = data.name || '';
          document.getElementById("modal-description").textContent = data.description || 'No description available.';
          document.getElementById("modal-price").textContent = data.price || '0.00';
          document.getElementById("modal-stock").textContent = data.stock || '0';
          document.getElementById("modal-brand").textContent = data.brand || 'N/A';
          document.getElementById("modal-category").textContent = data.category || 'N/A';

          const imgEl = document.getElementById("modal-image");
          if (data.image) {
            imgEl.style.display = 'inline-block';
            imgEl.src = `../uploads/${data.image}`;
            imgEl.onerror = () => {
              imgEl.src = `../assets/images/products/${data.image}`;
            };
          } else {
            imgEl.style.display = 'none';
          }

          productModal.style.display = "block";
        });
      });

      // Close modal functionality
      const closeModal = () => {
        productModal.style.display = "none";
      };
      closeModalBtn.onclick = closeModal;
      window.onclick = (event) => {
        if (event.target == productModal) closeModal();
      };

      // --- Delete Confirmation Modal Logic ---
      const deleteModal = document.getElementById('deleteConfirmModal');
      const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
      deleteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const deleteUrl = button.getAttribute('data-delete-url');
        confirmDeleteBtn.setAttribute('href', deleteUrl);
      });
    });
  </script>
</body>

</html>