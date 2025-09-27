<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php'; // Assuming getRootCategories() is here
session_start();

// Redirect if not logged in
if (empty($_SESSION['admin_logged_in'])) {
  header('Location: ./login.php');
  exit;
}

// Fetch all categories with their parent's name
$query_all = "SELECT c.*, p.category_name AS parent_name
              FROM categories c
              LEFT JOIN categories p ON c.parent_id = p.category_id
              ORDER BY c.category_name ASC";
$result_all = $conn->query($query_all);
$allCategories = $result_all->fetch_all(MYSQLI_ASSOC);

// Fetch only top-level categories for the dropdowns
$rootCategories = getRootCategories($conn); // Pass connection if needed
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PC-Zone Admin - Categories</title>

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
  $current_page = 'categories';
  include './includes/header.php';
  include './includes/sidebar.php';
  ?>

  <main class="main-content">
    <div class="page-header">
      <h1>Manage Categories</h1>
      <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="fas fa-plus"></i> Add Category
      </button>
    </div>

    <!-- Categories Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Category Name</th>
            <th>Parent</th>
            <th>Slug</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($allCategories)): ?>
            <?php foreach ($allCategories as $row): ?>
              <tr>
                <td><?= (int)$row['category_id'] ?></td>
                <td><?= htmlspecialchars($row['category_name']) ?></td>
                <td><?= htmlspecialchars($row['parent_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['slug']) ?></td>
                <td>
                  <button class="btn-edit" data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                    data-id="<?= (int)$row['category_id'] ?>"
                    data-name="<?= htmlspecialchars($row['category_name'], ENT_QUOTES) ?>"
                    data-parent="<?= $row['parent_id'] ?? '' ?>">
                    <i class="fas fa-edit"></i> Edit
                  </button>
                  <button class="btn-delete" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal"
                    data-url="delete.php?category=<?= (int)$row['category_id'] ?>">
                    <i class="fas fa-trash-alt"></i> Delete
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center py-4">No categories found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- Add Category Modal -->
  <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content form-container" method="POST" action="create.php">
        <div class="modal-header">
          <h5 class="modal-title">Add New Category</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="categoryName" class="form-label">Category Name <span class="required">*</span></label>
            <input type="text" class="form-control" name="name" id="categoryName" required>
          </div>
          <div class="mb-3">
            <label for="parentCategory" class="form-label">Parent Category</label>
            <select class="form-select" name="parent_id" id="parentCategory">
              <option value="">— None —</option>
              <?php foreach ($rootCategories as $cat): ?>
                <option value="<?= (int)$cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="add-category" class="btn btn-success">Add Category</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Category Modal -->
  <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content form-container" method="POST" action="edit.php">
        <input type="hidden" name="id" id="editCategoryId">
        <div class="modal-header">
          <h5 class="modal-title">Edit Category</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="editCategoryName" class="form-label">Category Name <span class="required">*</span></label>
            <input type="text" class="form-control" name="name" id="editCategoryName" required>
          </div>
          <div class="mb-3">
            <label for="editParentCategory" class="form-label">Parent Category</label>
            <select class="form-select" name="parent_id" id="editParentCategory">
              <option value="">— None —</option>
              <?php foreach ($rootCategories as $cat): ?>
                <option value="<?= (int)$cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="edit-category" class="btn btn-success">Update Category</button>
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
          <p>This action cannot be undone. Deleting this category will also delete all its subcategories.</p>
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
      const editModal = document.getElementById('editCategoryModal');
      const deleteModal = document.getElementById('deleteConfirmModal');

      // Handle populating the edit modal
      editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        document.getElementById('editCategoryId').value = button.dataset.id;
        document.getElementById('editCategoryName').value = button.dataset.name;
        document.getElementById('editParentCategory').value = button.dataset.parent || '';
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