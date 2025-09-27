<?php
require_once '../includes/db_connect.php';
session_start();

// Redirect if not logged in
if (empty($_SESSION['admin_logged_in'])) {
  header('Location: ./login.php');
  exit;
}

// Fetch all brands with their associated category name for the main table
$brands_query = "SELECT b.brand_id, b.brand_name, b.slug, b.category_id, c.category_name 
                 FROM brands AS b
                 LEFT JOIN categories AS c ON b.category_id = c.category_id
                 ORDER BY b.brand_name ASC";
$brands_result = $conn->query($brands_query);
$allBrands = $brands_result->fetch_all(MYSQLI_ASSOC);

// Fetch all categories for the modal dropdowns
$categories_query = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
$categories_result = $conn->query($categories_query);
$allCategories = $categories_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale-1.0">
  <title>PC-Zone Admin - Brands</title>

  <?php require('./includes/header-link.php') ?>

  <script>
    // Immediately apply theme from localStorage
    (function() {
      const theme = localStorage.getItem('pczoneTheme');
      if (theme === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
      }
    })();
  </script>
</head>

<body>
  <?php require('./includes/alert.php'); ?>
  <?php
  $current_page = 'brands';
  include './includes/header.php';
  include './includes/sidebar.php';
  ?>

  <main class="main-content">
    <div class="page-header">
      <h1>Manage Brands</h1>
      <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addBrandModal">
        <i class="fas fa-plus"></i> Add Brand
      </button>
    </div>

    <!-- Brands Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
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
                <td><?= (int)$row['brand_id'] ?></td>
                <td><?= htmlspecialchars($row['brand_name']) ?></td>
                <td><?= htmlspecialchars($row['category_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['slug']) ?></td>
                <td>
                  <button class="btn-edit" data-bs-toggle="modal" data-bs-target="#editBrandModal"
                    data-id="<?= (int)$row['brand_id'] ?>"
                    data-name="<?= htmlspecialchars($row['brand_name'], ENT_QUOTES) ?>"
                    data-category="<?= $row['category_id'] ?? '' ?>">
                    <i class="fas fa-edit"></i> Edit
                  </button>
                  <button class="btn-delete" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal"
                    data-url="delete.php?brand=<?= (int)$row['brand_id'] ?>">
                    <i class="fas fa-trash-alt"></i> Delete
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center py-4">No brands found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- Add Brand Modal -->
  <div class="modal fade" id="addBrandModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content form-container" method="POST" action="create.php">
        <div class="modal-header">
          <h5 class="modal-title">Add New Brand</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="brandName" class="form-label">Brand Name <span class="required">*</span></label>
            <input type="text" class="form-control" name="name" id="brandName" required>
          </div>
          <div class="mb-3">
            <label for="brandCategory" class="form-label">Category</label>
            <select class="form-select" name="category_id" id="brandCategory">
              <option value="">— Select Category —</option>
              <?php foreach ($allCategories as $cat): ?>
                <option value="<?= (int)$cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="add-brand" class="btn btn-success">Add Brand</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Brand Modal -->
  <div class="modal fade" id="editBrandModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content form-container" method="POST" action="edit.php">
        <input type="hidden" name="id" id="editBrandId">
        <div class="modal-header">
          <h5 class="modal-title">Edit Brand</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="editBrandName" class="form-label">Brand Name <span class="required">*</span></label>
            <input type="text" class="form-control" name="name" id="editBrandName" required>
          </div>
          <div class="mb-3">
            <label for="editBrandCategory" class="form-label">Category</label>
            <select class="form-select" name="category_id" id="editBrandCategory">
              <option value="">— Select Category —</option>
              <?php foreach ($allCategories as $cat): ?>
                <option value="<?= (int)$cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="edit-brand" class="btn btn-success">Update Brand</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content form-container">
        <div class="modal-header">
          <h5 class="modal-title">Are you sure?</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Deleting this brand may affect associated products. This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Delete</a>
        </div>
      </div>
    </div>
  </div>
  <?php require('./includes/footer-link.php') ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const editModal = document.getElementById('editBrandModal');
      const deleteModal = document.getElementById('deleteConfirmModal');

      // Handle populating the edit modal
      editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        document.getElementById('editBrandId').value = button.dataset.id;
        document.getElementById('editBrandName').value = button.dataset.name;
        document.getElementById('editBrandCategory').value = button.dataset.category || '';
      });

      // Handle setting the delete URL in the confirmation modal
      deleteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        document.getElementById('confirmDeleteBtn').href = button.dataset.url;
      });
    });
  </script>
</body>

</html>