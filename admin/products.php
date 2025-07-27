<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: ./../index.php");
  exit;
}

// $current = basename($_SERVER['PHP_SELF']);
include './includes/db_connect.php';

// Fetch products with brand and category name
$sql = "
  SELECT 
    p.*, 
    c.name AS category_name, 
    b.name AS brand_name 
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.id
  LEFT JOIN brands b ON p.brand_id = b.id
";


$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
  <div class="alert-success">✅ Product updated successfully!</div>
<?php endif; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PC ZONE Admin</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 5 -->
  <!-- <link rel="stylesheet" href="./assets/vendor/bootstrap/css/bootstrap.min.css"> -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel=" stylesheet" href="./assets/vendor/fontawesome/css/all.min.css">

  <!-- custom styles -->
  <link rel="stylesheet" href="./assets/css/style.css">
  <link rel="stylesheet" href="./assets/css/products.css">
</head>

<body>
  <?php include './includes/header.php'; ?>

  <?php $page = 'products';
  include './includes/sidebar.php' ?>

  <main class="main-content">
    <div class="products-header">
      <h1>Products</h1>
      <a href="add_product.php" class="btn-add"><i class="fa-solid fa-plus"></i> Add New Product</a>
    </div>

    <div class="table-container">
      <table class="product-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Category</th>
            <th>Brand</th>
            <th>Price (₹)</th>
            <th>Stock</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="product-row" data-product='<?= json_encode([
                                                    "id" => $row["id"],
                                                    "name" => $row["name"],
                                                    "description" => $row["description"],
                                                    "price" => $row["price"],
                                                    "stock" => $row["stock"],
                                                    "brand" => $row["brand_name"],
                                                    "category" => $row["category_name"],
                                                    "images" => array_filter([$row["image1"], $row["image2"], $row["image3"], $row["image4"]])
                                                  ]) ?>'>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
              <td><?= htmlspecialchars($row['category_name'] ?? '') ?></td>
              <td><?= htmlspecialchars($row['brand_name'] ?? '') ?></td>
              <td><?= number_format($row['price'], 2) ?></td>
              <td><?= $row['stock'] ?></td>
              <td>
                <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                <a href="delete_product.php?id=<?= $row['id'] ?>" class="btn-delete"
                  onclick="return confirm('Are you sure?')">Delete</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>

      </table>
    </div>
  </main>

  <!-- Popup Modal -->
  <div id="productModal" class="product-modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeModal()">×</span>
      <h2 id="modal-title"></h2>
      <p><strong>Description:</strong> <span id="modal-description"></span></p>
      <p><strong>Price:</strong> ₹<span id="modal-price"></span></p>
      <p><strong>Stock:</strong> <span id="modal-stock"></span></p>
      <p><strong>Brand:</strong> <span id="modal-brand"></span></p>
      <p><strong>Category:</strong> <span id="modal-category"></span></p>
      <div id="modal-images" class="modal-images"></div>
    </div>
  </div>


  <script>
    // Close modal
    function closeModal() {
      document.getElementById("productModal").style.display = "none";
    }

    // Open modal with row data
    document.querySelectorAll(".product-row").forEach(row => {
      row.addEventListener("click", () => {
        const product = JSON.parse(row.dataset.product);
        document.getElementById("modal-title").textContent = product.name;
        document.getElementById("modal-description").textContent = product.description;
        document.getElementById("modal-price").textContent = product.price;
        document.getElementById("modal-stock").textContent = product.stock;
        document.getElementById("modal-brand").textContent = product.brand;
        document.getElementById("modal-category").textContent = product.category;

        const imgContainer = document.getElementById("modal-images");
        imgContainer.innerHTML = "";
        product.images.forEach(img => {
          const image = document.createElement("img");
          image.src = "../assets/images/" + img;
          image.alt = product.name;
          image.style.maxWidth = "100px";
          image.style.margin = "5px";
          image.style.border = "1px solid #ccc";
          image.style.borderRadius = "5px";
          imgContainer.appendChild(image);
        });

        document.getElementById("productModal").style.display = "block";
      });
    });
  </script>


  <script src="./assets/vendor/jquery/jquery-3.7.1.min.js"></script>
  <script src="./assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>

</html>