<?php

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

session_start();
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ./login1.php');
  exit;
}

// Fetch categories using helper functions (keeps UI & behavior unchanged)
// $allCategories = getAllCategories();    // returns array of all categories
// fetch categories with parent name
$sql = "
  SELECT c.*, p.category_name AS parent_name
  FROM categories c
  LEFT JOIN categories p ON c.parent_id = p.category_id
  ORDER BY c.level ASC, c.category_name ASC
";
$res = mysqli_query($conn, $sql);
$allCategories = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];

$rootCategories = getRootCategories();  // returns top-level categories
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PC-Zone Admin - Categories</title>

  <?php require './includes/header-link.php'; ?>
  <!-- <script src="./assets/js/script.js"></script> -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body>
  <?php include './includes/header.php'; ?>
  <?php $current_page = 'categories';
  include './includes/sidebar.php'; ?>

  <main class="main-content pt-5 mt-2">
    <div class="content-wrapper container my-4">
      <div class="products-header">
        <h1>Manage Categories</h1>
        <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
          <i class="fas fa-plus"></i> Add Category
        </button>
      </div>

      <!-- Categories Table -->
      <div class="table-box">
        <table class="data-table table table-hover align-middle">
          <thead>
            <tr>
              <th>#</th>
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
                    <button class="btn-edit"
                      data-bs-toggle="modal"
                      data-bs-target="#editCategoryModal"
                      data-id="<?= (int)$row['category_id'] ?>"
                      data-name="<?= htmlspecialchars($row['category_name'], ENT_QUOTES) ?>"
                      data-parent="<?= $row['parent_id'] ?? '' ?>">
                      <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn-delete" onclick="deleteCategory(<?= (int)$row['category_id'] ?>)">
                      <i class="fas fa-trash-alt"></i> Delete
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center">No categories found. <a href="#" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Add a category</a></td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <!-- apply form-container so modal form uses admin form styles -->
        <form class="modal-content form-container" method="POST" action="create.php">
          <div class="modal-header">
            <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
            <button type="button" class="btn close-btn position-static p-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="categoryName" class="form-label">Category Name <span class="required">*</span></label>
              <input type="text" class="form-control" name="name" id="categoryName" required placeholder="Enter category name">
            </div>
            <div class="mb-3">
              <label for="parentCategory" class="form-label">Parent Category</label>
              <select class="form-select" name="parent_id" id="parentCategory">
                <option value="">None (Top-level category)</option>
                <?php foreach ($rootCategories as $cat): ?>
                  <option value="<?= (int)$cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="add-category" class="btn btn-success">
              <i class="fas fa-plus me-1"></i> Add Category
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form class="modal-content form-container" method="POST" action="update_category.php">
          <input type="hidden" name="id" id="editCategoryId">

          <div class="modal-header">
            <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div class="mb-3">
              <label for="editCategoryName" class="form-label">Category Name <span class="required">*</span></label>
              <input type="text" class="form-control" name="name" id="editCategoryName" required>
            </div>

            <div class="mb-3">
              <label for="editParentCategory" class="form-label">Parent Category</label>
              <select class="form-select" name="parent_id" id="editParentCategory">
                <option value="">None (Top-level category)</option>
                <?php foreach ($rootCategories as $cat): ?>
                  <option value="<?= (int)$cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Status</label>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="editCategoryStatus" name="status" checked>
                <label class="form-check-label" for="editCategoryStatus">Active</label>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">
              <i class="fas fa-save me-1"></i> Update Category
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Toast script (keeps previous behavior) -->
    <?php
    if (function_exists('show_toast_script')) {
      message('Category');
    }
    ?>
  </main>

  <script>
    // Edit category modal initialization
    const editModal = document.getElementById('editCategoryModal');
    if (editModal) {
      editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const parentId = button.getAttribute('data-parent');

        document.getElementById('editCategoryId').value = id;
        document.getElementById('editCategoryName').value = name;
        document.getElementById('editParentCategory').value = parentId || '';
      });
    }

    // Delete category confirmation
    function deleteCategory(category_id) {
      if (confirm("Are you sure you want to delete this category? All subcategories will also be deleted.")) {
        window.location.href = "delete_category.php?id=" + category_id;
      }
    }
  </script>
</body>

</html>