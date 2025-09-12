<?php

include '../includes/db_connect.php';
include './includes/functions.php';

session_start();
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ./login1.php');
  exit;
}

$sql = "SELECT b.brand_id, b.brand_name, b.slug, b.category_id,
              c.category_name 
              FROM brands AS b
              LEFT JOIN categories AS c 
              ON b.category_id = c.category_id
              ORDER BY b.brand_id ASC";
$res = mysqli_query($conn, $sql);
$allBrands = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];

$categories = mysqli_query($conn, "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY category_name");
$allCategories = mysqli_fetch_all($categories, MYSQLI_ASSOC) ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PC-Zone Admin - Brands</title>

  <?php include './includes/header-link.php'; ?>
  <!-- Bootstrap JS -->
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>

<body>
  <?php include './includes/header.php'; ?>
  <?php $current_page = 'brands';
  include './includes/sidebar.php'; ?>

  <main class="main-content pt-5 mt-2">
    <div class="content-wrapper container my-4">
      <div class="products-header">
        <h2 class="mb-0">Manage Brands</h2>
        <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addBrandModal">
          <i class="fas fa-plus"></i> Add Brand
        </button>
      </div>

      <!-- Brands Table -->
      <div class="table-box">
        <table class="data-table table table-hover align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Brand Name</th>
              <th>Category</th>
              <th>Slug</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($allBrands)): ?>
              <?php foreach ($allBrands as $row): ?>
                <tr>
                  <td><?= $row['brand_id'] ?></td>
                  <td><?= htmlspecialchars($row['brand_name']) ?? 'N/A' ?></td>
                  <td><?= htmlspecialchars($row['category_name'] ?? 'N/A') ?></td>
                  <td><?= htmlspecialchars($row['slug']) ?? 'N/A' ?></td>
                  <td>
                    <button class="btn-edit"
                      data-bs-toggle="modal"
                      data-bs-target="#editBrandModal"
                      data-id="<?= $row['brand_id'] ?>"
                      data-name="<?= htmlspecialchars($row['brand_name'], ENT_QUOTES) ?>"
                      data-category="<?= $row['category_id'] ?>">
                      <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn-delete" onclick="deleteBrand(<?= $row['brand_id'] ?>)">
                      <i class="fas fa-trash-alt"></i> Delete
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center">No brands found. <a href="#" data-bs-toggle="modal" data-bs-target="#editBrandModal">Add a brand</a></td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Brand Modal -->
    <div class="modal fade" id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form class="modal-content form-container" method="POST" action="create.php">
          <div class="modal-header">
            <h5 class="modal-title" id="addBrandModalLabel">Add New Brand</h5>
            <button type="button" class="btn close-btn position-static p-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
          </div>

          <div class="modal-body">
            <div class="mb-3">
              <label for="brandName" class="form-label">Brand Name</label>
              <input type="text" class="form-control" name="name" id="brandName" required>
            </div>
            <div class="mb-3">
              <label for="brandCategory" class="form-label">Category</label>
              <select class="form-select" name="brand_category" id="brandCategory" required>
                <option value="">Select Category</option>
                <?php foreach ($allCategories as $cat) : ?>
                  <option value="<?= (int)$cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>;
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="add-brand" class="btn btn-success"><i class="fas fa-plus me-1"></i>Add Brand</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Brand Modal -->
    <div class="modal fade" id="editBrandModal" tabindex="-1" aria-labelledby="editBrandModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form class="modal-content form-container" method="POST" action="edit.php">
          <input type="hidden" name="id" id="editBrandId">

          <div class="modal-header">
            <h5 class="modal-title" id="editBrandModalLabel">Edit Brand</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div class="mb-3">
              <label for="editBrandName" class="form-label">Brand Name</label>
              <input type="text" class="form-control" name="name" id="editBrandName" required>
            </div>
            <div class="mb-3">
              <label for="editBrandCategory" class="form-label">Category</label>
              <select class="form-select" name="category_id" id="editBrandCategory" required>
                <option value="">Select Category</option>
                <?php
                foreach ($allCategories as $cat) : ?>
                  <option value="<?= (int)$cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>;
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button name="edit-brand" type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Update Brand</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Toast Container -->
    <?php
    if (function_exists('show_toast_script')) {
      message('Brand');
    }
    ?>

  </main>


  <script>
    function deleteBrand(id) {
      if (confirm("Are you sure you want to delete this brand?")) {
        window.location.href = "delete_brand.php?id=" + id;
      }
    }

    const editBrandModal = document.getElementById('editBrandModal');
    editBrandModal.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      const category = button.getAttribute('data-category');

      document.getElementById('editBrandId').value = id;
      document.getElementById('editBrandName').value = name;
      document.getElementById('editBrandCategory').value = category;
    });
  </script>

</body>

</html>