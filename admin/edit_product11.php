<?php
require_once 'includes/db.php'; // your DB connection file

$id = $_GET['id'] ?? 0;

// Fetch existing data
$query = "SELECT * FROM products WHERE id = $id";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $brand = $_POST['brand'];
  $category = $_POST['category'];
  $price = $_POST['price'];
  $stock = $_POST['stock'];

  $update = "UPDATE products SET 
              name = '$name',
              brand = '$brand',
              category = '$category',
              price = '$price',
              stock = '$stock'
            WHERE id = $id";

  mysqli_query($conn, $update);
  header('Location: products.php');
  exit;
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
  <h2>Edit Product</h2>
  <form method="POST" class="edit-form">
    <label>Name: <input type="text" name="name" value="<?= $product['name'] ?>"></label>
    <label>Brand: <input type="text" name="brand" value="<?= $product['brand'] ?>"></label>
    <label>Category: <input type="text" name="category" value="<?= $product['category'] ?>"></label>
    <label>Price: <input type="number" name="price" value="<?= $product['price'] ?>"></label>
    <label>Stock: <input type="number" name="stock" value="<?= $product['stock'] ?>"></label>
    <button type="submit" class="btn-update">Update</button>
  </form>
</main>

<style>
  .edit-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 400px;
    margin-top: 20px;
  }

  .edit-form label {
    display: flex;
    flex-direction: column;
  }

  .btn-update {
    background-color: green;
    color: white;
    padding: 10px 14px;
    border-radius: 5px;
    font-weight: 600;
    border: none;
  }
</style>