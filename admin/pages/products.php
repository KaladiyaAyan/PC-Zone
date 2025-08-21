<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: index.php");
  exit;
}

include('../includes/db_connect.php');

// Get products with brand and category names and main image
// $query = "
//     SELECT 
//         p.*, 
//         c.name AS category_name, 
//         b.name AS brand_name,
//         (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.product_id AND pi.is_main = 1 LIMIT 1) AS main_image 
//     FROM products p
//     LEFT JOIN categories c ON p.category_id = c.id
//     LEFT JOIN brands b ON p.brand_id = b.id
//     ORDER BY p.product_id DESC
// ";

// $query = "
//     SELECT p.*, 
//        b.brand_name, 
//        c.category_name, 
//        i.image_path AS main_image
// FROM products p
// JOIN brands b ON p.brand_id = b.brand_id
// JOIN categories c ON p.category_id = c.category_id
// LEFT JOIN product_images i 
//        ON p.product_id = i.product_id 
//       AND i.is_main = 1
// ORDER BY p.product_id DESC
// ";

$query = "
    SELECT p.product_id,
           p.product_name,
           p.description,
           p.price,
           p.stock,
           b.brand_name,
           c.category_name,
           i.image_path AS main_image
    FROM products p
    LEFT JOIN brands b 
        ON p.brand_id = b.brand_id
    LEFT JOIN categories c 
        ON p.category_id = c.category_id
    LEFT JOIN product_images i 
        ON p.product_id = i.product_id 
       AND i.is_main = 1
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
  <!-- Bootstrap 5 -->
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
  <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
  <!-- Custom styles -->
  <link rel="stylesheet" href="../assets/css/style.css">
  <!-- <link rel="stylesheet" href="../assets/css/products.css"> -->
</head>

<body>
  <?php include '../includes/header.php'; ?>
  <?php $page = 'products';
  include '../includes/sidebar.php'; ?>

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
      <a href="add_product.php" class="btn-add">
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
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($product = mysqli_fetch_assoc($result)): ?>
              <tr class="product-row" data-product='<?= json_encode([
                                                      "id" => $product["product_id"],
                                                      "name" => $product["product_name"],
                                                      "description" => $product["description"],
                                                      "price" => $product["price"],
                                                      "stock" => $product["stock"],
                                                      "brand" => $product["brand_name"],
                                                      "category" => $product["category_name"],
                                                      "image" => $product["main_image"]
                                                    ]) ?>'>
                <td><?= $product['product_id'] ?></td>
                <td class="product-image ">
                  <?php if ($product['main_image']): ?>
                    <?php
                    $imagePath = "../uploads/" . $product['main_image'];
                    if (!file_exists($imagePath)) {
                      $imagePath = "../assets/images/" . $product['main_image'];
                    }
                    ?>
                    <img src="<?= htmlspecialchars($imagePath) ?>"
                      alt="<?= htmlspecialchars($product['product_name']) ?>"
                      class="product-thumb img-fluid"
                      style="max-width: 100px;">

                  <?php else: ?>
                    <div class="no-image">No Image</div>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($product['product_name']) ?></td>
                <td><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($product['brand_name'] ?? 'N/A') ?></td>
                <td>₹<?= number_format($product['price'], 2) ?></td>
                <td>
                  <span class="stock-badge <?= $product['stock'] <= 0 ? 'out-of-stock' : ($product['stock'] < 10 ? 'low-stock' : 'in-stock') ?>">
                    <?= $product['stock'] ?>
                  </span>
                </td>
                <td>
                  <a href="edit_product.php?product_id=<?= $product['product_id'] ?>" class="btn-edit">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                  <a href="delete_product.php?product_id=<?= $product['product_id'] ?>"
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
    // Modal functions
    function closeModal() {
      document.getElementById("productModal").style.display = "none";
    }

    // Sidebar functionality
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
          console.log(row);
          console.log(row.dataset);
          const product = JSON.parse(row.dataset.product);

          document.getElementById("modal-title").textContent = product.name;
          document.getElementById("modal-description").textContent = product.description || 'No description available';
          document.getElementById("modal-price").textContent = parseFloat(product.price).toFixed(2);
          document.getElementById("modal-stock").textContent = product.stock;
          document.getElementById("modal-brand").textContent = product.brand || 'N/A';
          document.getElementById("modal-category").textContent = product.category || 'N/A';

          // Show image
          const imgContainer = document.getElementById("modal-image");
          imgContainer.innerHTML = "";
          if (product.image) {
            console.log(product);
            const image = document.createElement("img");
            image.src = "../uploads/" + product.image;
            image.alt = product.name;
            image.style.maxWidth = "200px";
            image.style.height = "auto";
            image.style.border = "1px solid #ddd";
            image.style.borderRadius = "5px";
            imgContainer.appendChild(image);

            // Fallback if image not found in uploads
            image.onerror = function() {
              this.onerror = null; // Prevent infinite loop
              this.src = "../assets/images/" + product.image;
            };

            imgContainer.appendChild(image);
          } else {
            imgContainer.innerHTML = "<p>No image available</p>";
          }

          document.getElementById("productModal").style.display = "block";
        });
      });

      // Auto-hide flash messages
      setTimeout(() => {
        const alertBox = document.getElementById('flash-alert');
        if (alertBox) {
          alertBox.style.transition = 'opacity 0.5s';
          alertBox.style.opacity = 0;
          setTimeout(() => alertBox.remove(), 500);
        }

        // Clean URL
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

  <script src="./assets/vendor/jquery/jquery-3.7.1.min.js"></script>
  <script src="./assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>