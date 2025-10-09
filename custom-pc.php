<?php
session_start();
require('./includes/db_connect.php');
require('./includes/functions.php');

// Determine platform from URL, defaulting to 'intel'
$platform = isset($_GET['platform']) && $_GET['platform'] === 'amd' ? 'amd' : 'intel';

// Define the parts for the PC builder
$parts = [
  'processor'      => 'Processor',
  'motherboard'    => 'Motherboard',
  'ram'            => 'RAM',
  'graphics-card'  => 'Graphics Card',
  'storage'        => 'Storage',
  'power-supply'   => 'Power Supply',
  'cabinet'        => 'Cabinet',
  'cooling-system' => 'CPU Cooler',
];

$partsProducts = []; // This array will hold the products for each part

// FETCH PRODUCTS FOR EACH PART
foreach ($parts as $slug => $label) {
  // First, get the category ID for the current part's slug
  $safe_slug = mysqli_real_escape_string($conn, $slug);
  $sql_cat = "SELECT category_id FROM categories WHERE slug = '$safe_slug' LIMIT 1";
  $result_cat = mysqli_query($conn, $sql_cat);
  $cat_row = mysqli_fetch_assoc($result_cat);
  $cid = $cat_row['category_id'] ?? null;

  if (!$cid) {
    $partsProducts[$slug] = []; // If no category, there are no products
    continue; // Skip to the next part
  }

  // ---- START: CORRECTED QUERY LOGIC ----
  // Base query to get the products for the main category ID
  $sql_products = "
        SELECT p.product_id, p.product_name, p.price, p.discount, p.main_image AS image
        FROM products p
        WHERE p.is_active = 1
          AND p.stock > 0
          AND p.category_id = $cid
    ";

  // Conditionally add the platform filter for 'processor' and 'motherboard'
  if (in_array($slug, ['processor', 'motherboard'])) {
    $safe_platform = mysqli_real_escape_string($conn, $platform);
    $sql_products .= " AND (p.platform = '$safe_platform' OR p.platform = 'both')";
  }
  // ---- END: CORRECTED QUERY LOGIC ----

  $sql_products .= " ORDER BY (p.price - (p.price * (p.discount/100))) ASC LIMIT 500";

  $result_products = mysqli_query($conn, $sql_products);
  $products = [];
  if ($result_products) {
    while ($row = mysqli_fetch_assoc($result_products)) {
      // Calculate the final price after discount
      $row['final_price'] = round((float)$row['price'] - ((float)$row['price'] * (float)$row['discount'] / 100), 2);

      // Resolve the image URL directly
      $filename = trim((string)$row['image']);
      if ($filename && file_exists('./uploads/' . $filename)) {
        $row['image'] = './uploads/' . rawurlencode($filename);
      } else if ($filename && file_exists('./assets/images/products/' . $filename)) {
        $row['image'] = './assets/images/products/' . rawurlencode($filename);
      } else {
        $row['image'] = './assets/images/placeholder.png';
      }

      $products[] = $row;
    }
  }
  $partsProducts[$slug] = $products;
}

mysqli_close($conn);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e(ucfirst($platform)) ?> Custom PC Build</title>
  <?php include('./includes/header-link.php') ?>
  <link rel="stylesheet" href="assets/css/custom-pc.css">
</head>

<body>
  <?php include('./includes/alert.php'); ?>
  <?php include('./includes/navbar.php'); ?>

  <main class="container my-4">
    <form id="buildForm" action="addtocart.php" method="POST" class="custom-build-row">
      <div class="left-col">
        <div class="left-hero" aria-hidden="true">
          <img src="./assets/images/<?= e($platform) ?>_custom_build.jpg" alt="<?= e($platform) ?> build image">
        </div>
      </div>

      <div class="right-col">
        <div class="right-panel">
          <div>
            <h3 class="h5 mb-1">Custom PC Builder</h3>
            <div class="small text-muted">Choose your components to build the perfect PC.</div>
          </div>

          <div class="parts-grid">
            <?php foreach ($parts as $slug => $label) :
              $items = $partsProducts[$slug];
            ?>
              <div class="part-item" data-slug="<?= e($slug) ?>">
                <div class="dd-label"><?= e($label) ?></div>

                <div class="dd-control" tabindex="0" role="button" aria-haspopup="listbox">
                  <div class="dd-selected">
                    <div class="name">No, thanks</div>
                    <div class="price">₹0</div>
                  </div>
                  <div class="caret">▾</div>
                  <input type="hidden" name="part[<?= e($slug) ?>]" value="0" class="dd-value">
                </div>

                <div class="dd-menu" role="listbox" aria-label="<?= e($label) ?> options">
                  <?php if (empty($items)) : ?>
                    <div class="dd-item">
                      <div class="meta">
                        <div class="title">No items found</div>
                      </div>
                    </div>
                    <?php else : foreach ($items as $it) :
                      $final_price = (float)$it['final_price'];
                      $original_price = (float)$it['price'];
                      $has_discount = (float)$it['discount'] > 0;
                    ?>
                      <div class="dd-item" data-id="<?= (int)$it['product_id'] ?>" data-price="<?= $final_price ?>" data-orig="<?= $has_discount ? $original_price : $final_price ?>" data-name="<?= e($it['product_name']) ?>">
                        <div class="thumb"><img src="<?= e($it['image']) ?>" alt="<?= e($it['product_name']) ?>"></div>
                        <div class="meta">
                          <div class="title"><?= e($it['product_name']) ?></div>
                          <div class="price-line">
                            <?php if ($has_discount) : ?>
                              <div class="orig">₹<?= number_format($original_price, 2) ?></div>
                            <?php endif; ?>
                            <div class="disc">₹<?= number_format($final_price, 2) ?></div>
                          </div>
                        </div>
                      </div>
                  <?php endforeach;
                  endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="cart-footer">
            <div class="price-box" aria-hidden="true">
              <div class="totals">
                <div class="label">Estimated Total</div>
                <div>
                  <span id="totalAmount" class="amount">₹0</span>
                  <span id="origAmount" class="orig" style="display:none;">₹0</span>
                </div>
              </div>
              <div class="small text-muted">Inclusive of all taxes</div>
            </div>
            <div>
              <button type="submit" id="addToCart" class="btn-add" disabled>Add To Cart</button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </main>
  <?php require('./includes/footer.php') ?>
  <?php require('./includes/footer-link.php') ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const partItems = document.querySelectorAll('.part-item');
      const addBtn = document.getElementById('addToCart');
      const totalEl = document.getElementById('totalAmount');
      const origEl = document.getElementById('origAmount');

      const closeAllDropdowns = () => {
        document.querySelectorAll('.dd-menu').forEach(menu => menu.style.display = 'none');
      };

      const recalculateTotal = () => {
        let total = 0;
        let origTotal = 0;

        partItems.forEach(container => {
          total += Number(container.dataset.selPrice || 0);
          origTotal += Number(container.dataset.selOrig || 0);
        });

        const options = {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        };
        totalEl.textContent = '₹' + total.toLocaleString('en-IN', options);
        addBtn.disabled = total === 0;

        if (origTotal > total) {
          origEl.style.display = 'inline-block';
          origEl.textContent = '₹' + origTotal.toLocaleString('en-IN', options);
        } else {
          origEl.style.display = 'none';
        }
      };

      partItems.forEach(container => {
        const control = container.querySelector('.dd-control');
        const menu = container.querySelector('.dd-menu');
        const hiddenInput = container.querySelector('.dd-value');
        const selectedName = container.querySelector('.dd-selected .name');

        control.addEventListener('click', e => {
          e.stopPropagation();
          const isOpen = menu.style.display === 'block';
          closeAllDropdowns();
          menu.style.display = isOpen ? 'none' : 'block';
        });

        menu.addEventListener('click', e => {
          const item = e.target.closest('.dd-item');
          if (!item || !item.dataset.id) return;

          hiddenInput.value = item.dataset.id;
          selectedName.textContent = item.dataset.name;
          container.dataset.selPrice = item.dataset.price;
          container.dataset.selOrig = item.dataset.orig;

          closeAllDropdowns();
          recalculateTotal();
        });
      });

      document.addEventListener('click', closeAllDropdowns);

      document.getElementById('buildForm').addEventListener('submit', e => {
        let total = 0;
        partItems.forEach(container => {
          total += Number(container.dataset.selPrice || 0);
        });

        if (total === 0) {
          e.preventDefault();
          alert('Please select at least one component.');
        }
      });

      recalculateTotal(); // Calculate total on page load
    });
  </script>
</body>

</html>