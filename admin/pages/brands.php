<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: index.php");
  exit;
}

include '../includes/db_connect.php';
include '../includes/functions.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PC-Zone Admin - Brands</title>

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
  <!-- Custom styles -->
  <link rel="stylesheet" href="../assets/css/style.css">
  <!-- Bootstrap JS -->
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>

<body>
  <?php include '../includes/header.php'; ?>
  <?php $current_page = 'brands';
  include '../includes/sidebar.php'; ?>

  <main class="main-content pt-5 mt-2">
    <div class="container my-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Manage Brands</h2>
        <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addBrandModal">
          <i class="fas fa-plus"></i> Add Brand
        </button>
      </div>

      <!-- Brands Table -->
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Brand Name</th>
              <th>Category</th>
              <th>Slug</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sql = "SELECT b.brand_id, b.brand_name, b.slug, b.category_id,
               c.category_name 
        FROM brands AS b
        LEFT JOIN categories AS c 
               ON b.category_id = c.category_id
        ORDER BY b.brand_id ASC";
            $result = mysqli_query($conn, $sql);

            while ($row = mysqli_fetch_assoc($result)) {
            ?>
              <tr>
                <td><?= $row['brand_id'] ?></td>
                <td><?= $row['brand_name'] ? htmlspecialchars($row['brand_name']) : 'N/A' ?></td>
                <td><?= htmlspecialchars($row['category_name'] ?? 'N/A') ?></td>
                <td><?= $row['slug'] ?? 'N/A' ?></td>
                <td>
                  <button class="btn-edit"
                    data-bs-toggle="modal"
                    data-bs-target="#editBrandModal"
                    data-id="<?= $row['brand_id'] ?>"
                    data-name="<?= htmlspecialchars($row['brand_name']) ?>"
                    data-category="<?= $row['category_id'] ?>">
                    <i class="fas fa-edit"></i> Edit
                  </button>
                  <button class="btn-delete" onclick="deleteBrand(<?= $row['brand_id'] ?>)">
                    <i class="fas fa-trash-alt"></i> Delete
                  </button>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Brand Modal -->
    <div class="modal fade" id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form class="modal-content" method="POST" action="add_brand.php">
          <div class="modal-header">
            <h5 class="modal-title">Add New Brand</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Brand Name</label>
              <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Category</label>
              <select class="form-select" name="category_id" required>
                <option value="">Select Category</option>
                <?php
                $categories = mysqli_query($conn, "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY category_name");
                while ($cat = mysqli_fetch_assoc($categories)) {
                  echo '<option value="' . $cat['category_id'] . '">' . htmlspecialchars($cat['category_name']) . '</option>';
                }
                ?>
              </select>
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Add Brand</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Brand Modal -->
    <div class="modal fade" id="editBrandModal" tabindex="-1" aria-labelledby="editBrandModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form class="modal-content" method="POST" action="update_brand.php">
          <input type="hidden" name="id" id="editBrandId">

          <div class="modal-header">
            <h5 class="modal-title">Edit Brand</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Brand Name</label>
              <input type="text" class="form-control" name="name" id="editBrandName" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Category</label>
              <select class="form-select" name="category_id" id="editBrandCategory" required>
                <option value="">Select Category</option>
                <?php
                $categories = mysqli_query($conn, "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY category_name");
                while ($cat = mysqli_fetch_assoc($categories)) {
                  echo '<option value="' . $cat['category_id'] . '">' . htmlspecialchars($cat['category_name']) . '</option>';
                }
                ?>
              </select>
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Update Brand</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Toast Container -->
    <?php
    show_toast_script('Brand');
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