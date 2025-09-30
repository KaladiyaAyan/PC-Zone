<?php
// admin/add_product.php
session_start();
require('../includes/db_connect.php');
require('../includes/functions.php');

// Admin protection
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ../login.php');
  exit;
}

// Get categories and brands for dropdowns
$categories = getRootCategories();
$brandsQuery = "SELECT brand_id, brand_name, category_id FROM brands ORDER BY brand_name ASC";
$brandsResult = mysqli_query($conn, $brandsQuery);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Add Product - PC ZONE Admin</title>
  <?php require './includes/header-link.php'; ?>
</head>

<body>
  <?php require('./includes/alert.php'); ?>
  <?php include './includes/header.php'; ?>
  <?php $current_page = 'product';
  include './includes/sidebar.php'; ?>

  <main class="main-content my-4">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h1>Add New Product</h1>
              <a href="product.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Products
              </a>
            </div>

            <form action="create.php" method="POST" enctype="multipart/form-data" id="addProductForm">
              <div class="row g-3">
                <!-- Product Name -->
                <div class="col-md-6">
                  <label class="form-label">Product Name <span class="required">*</span></label>
                  <input type="text" name="name" class="form-control" required>
                </div>

                <!-- SKU -->
                <div class="col-md-6">
                  <label class="form-label">SKU <span class="required">*</span></label>
                  <input type="text" name="sku" class="form-control" required>
                </div>

                <!-- Description -->
                <div class="col-12">
                  <label class="form-label">Description <span class="required">*</span></label>
                  <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>

                <!-- Price -->
                <div class="col-md-4">
                  <label class="form-label">Price (₹) <span class="required">*</span></label>
                  <input type="number" step="0.01" min="0.01" name="price" class="form-control" required>
                </div>

                <!-- Discount -->
                <div class="col-md-4">
                  <label class="form-label">Discount (%)</label>
                  <input type="number" step="0.01" min="0" max="100" name="discount" class="form-control" value="0">
                </div>

                <!-- Stock -->
                <div class="col-md-4">
                  <label class="form-label">Stock Quantity <span class="required">*</span></label>
                  <input type="number" name="stock" class="form-control" min="0" required>
                </div>

                <!-- Category -->
                <div class="col-md-6">
                  <label class="form-label">Category <span class="required">*</span></label>
                  <select name="category" class="form-select" id="categorySelect" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $category): ?>
                      <option value="<?= $category['category_id'] ?>">
                        <?= htmlspecialchars($category['category_name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Brand -->
                <div class="col-md-6">
                  <label class="form-label">Brand <span class="required">*</span></label>
                  <select name="brand" class="form-select" id="brandSelect" required>
                    <option value="">-- Select Brand --</option>
                    <?php while ($brand = mysqli_fetch_assoc($brandsResult)): ?>
                      <option value="<?= $brand['brand_id'] ?>" data-category="<?= $brand['category_id'] ?>">
                        <?= htmlspecialchars($brand['brand_name']) ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <!-- Weight -->
                <div class="col-md-6">
                  <label class="form-label">Weight (kg)</label>
                  <input type="number" step="0.01" min="0" name="weight" class="form-control">
                </div>

                <!-- Status Options -->
                <div class="col-md-6">
                  <div class="row">
                    <div class="col-6">
                      <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
                        <label class="form-check-label" for="is_featured">Featured Product</label>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Product Specifications -->
                <div class="col-12">
                  <h5 class="mt-4 mb-3">Product Specifications</h5>

                  <div id="specGroupsContainer">
                    <!-- default group -->
                    <div class="spec-group mb-3 rounded p-2">
                      <div class="d-flex mb-2">
                        <input name="spec_group_name[]" class="form-control me-2 spec-group-name" value="General" placeholder="Group name (e.g. Processor)">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-group-btn">Remove Group</button>
                      </div>

                      <div class="spec-rows">
                        <div class="input-group mb-2 spec-row">
                          <input name="spec_name_General[]" class="form-control" placeholder="Spec name (e.g. Cores)">
                          <input name="spec_value_General[]" class="form-control" placeholder="Spec value (e.g. 8)">
                          <input type="number" name="spec_order_General[]" class="form-control w-25" placeholder="Order" value="10">
                          <button type="button" class="btn btn-outline-secondary remove-row-btn">−</button>
                        </div>
                      </div>

                      <div>
                        <button type="button" class="btn btn-sm btn-primary add-row-btn">+ Add spec</button>
                      </div>
                    </div>
                  </div>

                  <div class="mt-2">
                    <button id="addGroupBtn" type="button" class="btn btn-sm btn-success">+ Add Group</button>
                    <small class="text-muted d-block mt-2">Create groups like "Processor", "Performance" and add related specs.</small>
                  </div>
                </div>

                <!-- Product Images -->
                <div class="col-12">
                  <h5 class="mt-4 mb-3">Product Images</h5>
                  <div class="row">
                    <div class="col-md-3">
                      <label class="form-label">Main Image <span class="required">*</span></label>
                      <input type="file" name="image1" class="form-control" accept="image/*"
                        onchange="previewImage(this, 'preview1')" required>
                      <div id="preview1" class="preview-images"></div>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Image 2</label>
                      <input type="file" name="image2" class="form-control" accept="image/*"
                        onchange="previewImage(this, 'preview2')">
                      <div id="preview2" class="preview-images"></div>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Image 3</label>
                      <input type="file" name="image3" class="form-control" accept="image/*"
                        onchange="previewImage(this, 'preview3')">
                      <div id="preview3" class="preview-images"></div>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Image 4</label>
                      <input type="file" name="image4" class="form-control" accept="image/*"
                        onchange="previewImage(this, 'preview4')">
                      <div id="preview4" class="preview-images"></div>
                    </div>
                  </div>
                </div>

                <!-- Submit Buttons -->
                <div class="col-12 mt-4">
                  <div class="d-flex gap-2">
                    <button type="submit" name="add-product" class="btn btn-success">
                      <i class="fas fa-save"></i> Add Product
                    </button>
                    <a href="product.php" class="btn btn-secondary">
                      <i class="fas fa-times"></i> Cancel
                    </a>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php require('./includes/footer-link.php') ?>

  <script>
    // simplified JS for specifications. No draft, no complex helpers.
    (function() {
      const specContainer = document.getElementById('specGroupsContainer');
      const addGroupBtn = document.getElementById('addGroupBtn');

      function toSafeKey(name) {
        return String(name || 'Group').replace(/[^a-zA-Z0-9_-]/g, '_');
      }

      // Update names of rows in a group when group name changes or when rows added.
      function updateGroupNames(groupEl) {
        const groupName = groupEl.querySelector('.spec-group-name').value || 'General';
        const safe = toSafeKey(groupName);
        groupEl.querySelectorAll('.spec-row').forEach(row => {
          const inputs = row.querySelectorAll('input');
          if (inputs.length >= 3) {
            inputs[0].name = 'spec_name_' + safe + '[]';
            inputs[1].name = 'spec_value_' + safe + '[]';
            inputs[2].name = 'spec_order_' + safe + '[]';
          }
        });
      }

      // create a new spec row
      function newSpecRow(safe, name = '', value = '', order = 10) {
        const div = document.createElement('div');
        div.className = 'input-group mb-2 spec-row';
        div.innerHTML = `
          <input name="spec_name_${safe}[]" class="form-control" placeholder="Spec name" value="${name}">
          <input name="spec_value_${safe}[]" class="form-control" placeholder="Spec value" value="${value}">
          <input type="number" name="spec_order_${safe}[]" class="form-control w-25" placeholder="Order" value="${order}">
          <button type="button" class="btn btn-outline-secondary remove-row-btn">−</button>
        `;
        return div;
      }

      // create a whole group DOM
      function newGroup(groupTitle = 'New Group') {
        const wrapper = document.createElement('div');
        wrapper.className = 'spec-group mb-3 border rounded p-2';
        wrapper.innerHTML = `
          <div class="d-flex mb-2">
            <input name="spec_group_name[]" class="form-control me-2 spec-group-name" value="${groupTitle}" placeholder="Group name (e.g. Processor)">
            <button type="button" class="btn btn-outline-danger btn-sm remove-group-btn">Remove Group</button>
          </div>
          <div class="spec-rows"></div>
          <div>
            <button type="button" class="btn btn-sm btn-primary add-row-btn">+ Add spec</button>
          </div>
        `;
        // default row
        const safe = toSafeKey(groupTitle);
        wrapper.querySelector('.spec-rows').appendChild(newSpecRow(safe));
        return wrapper;
      }

      // event delegation
      specContainer.addEventListener('click', function(e) {
        // remove group
        if (e.target.matches('.remove-group-btn')) {
          const g = e.target.closest('.spec-group');
          if (g) g.remove();
          return;
        }
        // add row
        if (e.target.matches('.add-row-btn')) {
          const g = e.target.closest('.spec-group');
          const title = g.querySelector('.spec-group-name').value || 'General';
          const safe = toSafeKey(title);
          g.querySelector('.spec-rows').appendChild(newSpecRow(safe));
          return;
        }
        // remove row
        if (e.target.matches('.remove-row-btn')) {
          const row = e.target.closest('.spec-row');
          if (row) row.remove();
          return;
        }
      });

      // update names when group title changes
      specContainer.addEventListener('input', function(e) {
        if (e.target.matches('.spec-group-name')) {
          const g = e.target.closest('.spec-group');
          updateGroupNames(g);
        }
      });

      addGroupBtn.addEventListener('click', function() {
        const g = newGroup('New Group');
        specContainer.appendChild(g);
      });

      // on form submit ensure all groups have correct input names (handles groups added/renamed)
      const form = document.getElementById('addProductForm');
      form.addEventListener('submit', function() {
        document.querySelectorAll('.spec-group').forEach(g => updateGroupNames(g));
      });

      // rest of page JS (image preview, brand filter, sidebar toggle)
      window.previewImage = function(input, previewId) {
        const preview = document.getElementById(previewId);
        preview.innerHTML = '';
        if (input.files && input.files[0]) {
          const reader = new FileReader();
          reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            preview.appendChild(img);
          };
          reader.readAsDataURL(input.files[0]);
        }
      };

      function filterBrands() {
        const selectedCategory = document.getElementById('categorySelect').value;
        const brandSelect = document.getElementById('brandSelect');
        const brandOptions = brandSelect.options;
        brandSelect.value = '';
        for (let i = 0; i < brandOptions.length; i++) {
          const option = brandOptions[i];
          if (option.value === '') {
            option.style.display = 'block';
            continue;
          }
          const brandCategory = option.getAttribute('data-category');
          option.style.display = (selectedCategory === '' || brandCategory === selectedCategory) ? 'block' : 'none';
        }
      }
      document.getElementById('categorySelect').addEventListener('change', filterBrands);
      filterBrands();

    })();
  </script>

</body>

</html>