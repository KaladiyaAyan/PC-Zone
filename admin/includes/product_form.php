  <?php
  include('./includes/db_connect.php');

  if (!isset($_SESSION['user_id']) || strlen($_SESSION['user_id']) == 0) {
    header('Location: logout.php');
    exit();
  }

  // Expecting $product to be passed from parent file (add/edit)
  if (!isset($product) || !is_array($product)) {
    $product = [
      'name' => '',
      'category' => '',
      'brand' => '',
      'stock' => '',
      'description' => '',
      'price' => '',
      'image1' => '',
      'image2' => '',
      'image3' => ''
    ];
  }
  ?>

  <form action="" method="POST" enctype="multipart/form-data">
    <div class="row mt-3">

      <div class="col-md-6 d-flex flex-column gap-3">
        <div class="form-group">
          <label for="name">Product Name</label>
          <input id="name" type="text" name="name" class="form-control mt-1" value="<?= htmlspecialchars($product['name'] ?? '') ?>"
            required>
        </div>

        <!-- Category Dropdown -->
        <div class="form-group position-relative mb-3">
          <label>Category</label>
          <div class="position-relative">
            <select name="category" class="form-control pe-5 mt-1" required>
              <option value="">Select Category</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= ($product['category'] === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
              <?php endforeach; ?>
            </select>
            <i class="fas fa-chevron-down position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #999;"></i>
          </div>
        </div>

        <!-- Brand Dropdown -->
        <div class="form-group position-relative mb-3">
          <label>Brand</label>
          <div class="position-relative">
            <select name="brand" class="form-control pe-5 mt-1" required>
              <option value="">Select Brand</option>
              <?php foreach ($brands as $brand): ?>
                <option value="<?= $brand ?>" <?= ($product['brand'] === $brand) ? 'selected' : '' ?>><?= $brand ?></option>
              <?php endforeach; ?>
            </select>
            <i class="fas fa-chevron-down position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #999;"></i>
          </div>
        </div>


        <div class="form-group">
          <label for="stock">Stock</label>
          <input id="stock" type="number" name="stock" class="form-control mt-1" value="<?= htmlspecialchars($product['stock'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label for="price">Price (₹)</label>
          <input id="price" type="number" name="price" class="form-control mt-1" step="0.01" value="<?= htmlspecialchars($product['price'] ?? '') ?>" required>
        </div>
      </div>

      <div class="col-md-6 d-flex flex-column gap-3">
        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description" class="form-control mt-1" rows="8"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label for="image1">Image 1</label>
          <input id="image1" type="file" name="image1" class="form-control mt-1">
        </div>
        <div class="form-group">
          <label for="image2">Image 2</label>
          <input id="image2" type="file" name="image2" class="form-control mt-1">
        </div>
        <div class="form-group">
          <label for="image3">Image 3</label>
          <input id="image3" type="file" name="image3" class="form-control mt-1">
        </div>
      </div>

    </div>

    <div class="form-group mt-3">
      <button type="submit" name="submit" class="btn btn-success btn-lg w-100">Submit Product</button>
    </div>
  </form>