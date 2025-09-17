<?php
session_start();

if (empty($_SESSION['user']) || empty($_SESSION['user_id'])) {
  header('Location: ./login.php');
  exit;
}

require("./includes/db_connect.php");
require("./includes/functions.php");

$platform = in_array($_GET['platform'] ?? 'amd', ['amd', 'intel']) ? $_GET['platform'] : 'amd';

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

$conn = getConnection();

/**
 * Resolve image path (admin uploads then assets, fallback)
 */
function resolveImageUrl($filename)
{
  $filename = trim((string)$filename);
  if ($filename !== '') {
    $uploads = '/uploads/' . $filename;
    if (file_exists($uploads)) return './uploads/' . rawurlencode($filename);
    $assets = '/assets/images/products/' . $filename;
    if (file_exists($assets)) return './assets/images/products/' . rawurlencode($filename);
  }
  return './assets/images/no-image.png';
}

/**
 * Return category_id for a given slug or null
 */
function getCategoryIdBySlug($conn, $slug)
{
  $sql = "SELECT category_id FROM categories WHERE slug = ? LIMIT 1";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "s", $slug);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);
  return $row['category_id'] ?? null;
}

/**
 * Get products for category slug (includes direct children categories)
 * Uses products.main_image column.
 *
 * For 'processor' and 'motherboard' we filter by platform:
 *   show product when p.platform = $platform OR p.platform = 'both'
 * For other categories we show all active products.
 */
function getProductsByCategorySlug($conn, $slug, $platform = 'amd')
{
  $cid = getCategoryIdBySlug($conn, $slug);
  if (!$cid) return [];

  $isPlatformSensitive = in_array($slug, ['processor', 'motherboard'], true);

  if ($isPlatformSensitive) {
    $sql = "
      SELECT p.product_id, p.product_name, p.price, p.discount, p.platform,
             p.main_image AS image
      FROM products p
      WHERE p.is_active = 1
        AND p.stock > 0
        AND (p.category_id = ? OR p.category_id IN (SELECT category_id FROM categories WHERE parent_id = ?))
        AND (p.platform = ? OR p.platform = 'both')
      ORDER BY (p.price - (p.price * (p.discount/100))) ASC
      LIMIT 500
    ";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iis", $cid, $cid, $platform);
  } else {
    $sql = "
      SELECT p.product_id, p.product_name, p.price, p.discount, p.platform,
             p.main_image AS image
      FROM products p
      WHERE p.is_active = 1
        AND p.stock > 0
        AND (p.category_id = ? OR p.category_id IN (SELECT category_id FROM categories WHERE parent_id = ?))
      ORDER BY (p.price - (p.price * (p.discount/100))) ASC
      LIMIT 500
    ";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $cid, $cid);
  }

  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $out = [];
  while ($r = mysqli_fetch_assoc($res)) {
    $r['final_price'] = round((float)$r['price'] - ((float)$r['price'] * ((float)$r['discount'] / 100)), 2);
    $r['image'] = resolveImageUrl($r['image'] ?? '');
    $out[] = $r;
  }
  mysqli_stmt_close($stmt);
  return $out;
}

$partsProducts = [];
foreach ($parts as $slug => $label) {
  $partsProducts[$slug] = getProductsByCategorySlug($conn, $slug, $platform);
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($platform) ?> Custom PC Build</title>

  <?php include('./includes/header-links.php') ?>

  <style>
    <?php include('./assets/css/navbar.css');
    include('./assets/css/custom-pc.css'); ?>
  </style>
</head>

<body>
  <?php include('./includes/navbar.php'); ?>

  <main class="container my-4">
    <form id="buildForm" action="/add_to_cart.php" method="POST" class="custom-build-row">
      <div class="left-col">
        <div class="left-hero" aria-hidden="true">
          <img src="./assets/images/<?php echo e($platform) ?>_custom_build.jpg" alt="build image">
        </div>
      </div>

      <div class="right-col">
        <div class="right-panel">
          <div>
            <h3 class="h5 mb-1">Custom PC</h3>
            <div class="small text-muted">Choose components. Data loaded from DB.</div>
          </div>

          <div class="parts-grid">
            <?php foreach ($parts as $slug => $label):
              $items = $partsProducts[$slug] ?? [];
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
                  <?php if (empty($items)): ?>
                    <div class="dd-item">
                      <div class="meta">
                        <div class="title">No items</div>
                      </div>
                    </div>
                    <?php else: foreach ($items as $it):
                      $final = number_format((float)$it['final_price'], 2);
                      $orig = (float)$it['discount'] > 0 ? number_format((float)$it['price'], 2) : 0;
                      $img = htmlspecialchars(basename($it['image']), ENT_QUOTES);
                      $name = htmlspecialchars($it['product_name'], ENT_QUOTES);
                    ?>
                      <div class="dd-item"
                        data-id="<?= (int)$it['product_id'] ?>"
                        data-price="<?= (float)$it['final_price'] ?>"
                        data-orig="<?= (float)$orig ?>"
                        data-name="<?= $name ?>"
                        data-img="<?= rawurlencode($img) ?>">
                        <div class="thumb"><img src="<?= e($it['image']) ?>" alt=""></div>
                        <div class="meta">
                          <div class="title"><?= e($it['product_name']) ?></div>
                          <div class="price-line">
                            <?php if ($orig): ?><div class="orig">₹<?= $orig ?></div><?php endif; ?>
                            <div class="disc">₹<?= $final ?></div>
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
              <div class="small text-muted">Inclusive of taxes</div>
            </div>

            <div>
              <button type="submit" id="addToCart" class="btn-add" disabled>Add To Cart</button>
            </div>
          </div>

        </div>
      </div>
    </form>
  </main>

  <?php require('./includes/footer-link.php') ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const partItems = Array.from(document.querySelectorAll('.part-item'));
      const addBtn = document.getElementById('addToCart');
      const totalEl = document.getElementById('totalAmount');
      const origEl = document.getElementById('origAmount');

      function closeAll() {
        document.querySelectorAll('.dd-menu').forEach(m => m.style.display = 'none');
      }

      function recalc() {
        let total = 0,
          origTotal = 0;
        partItems.forEach(c => {
          total += Number(c.dataset.selPrice || 0);
          origTotal += Number(c.dataset.selOrig || 0);
        });
        totalEl.textContent = '₹' + total.toLocaleString('en-IN');
        if (origTotal > total) {
          origEl.style.display = 'inline-block';
          origEl.textContent = '₹' + origTotal.toLocaleString('en-IN');
        } else {
          origEl.style.display = 'none';
        }
        addBtn.disabled = total === 0;
      }

      partItems.forEach(container => {
        const control = container.querySelector('.dd-control');
        const menu = container.querySelector('.dd-menu');
        const hidden = container.querySelector('.dd-value');
        const selName = container.querySelector('.dd-selected .name');

        control.addEventListener('click', function(e) {
          e.stopPropagation();
          const open = menu.style.display === 'block';
          closeAll();
          menu.style.display = open ? 'none' : 'block';
        });

        menu.addEventListener('click', function(e) {
          const item = e.target.closest('.dd-item');
          if (!item) return;
          const id = item.dataset.id || '0';
          const name = item.dataset.name || (item.querySelector('.title') || {}).textContent || '';
          const price = Number(item.dataset.price || 0);
          const orig = Number(item.dataset.orig || 0);

          selName.textContent = name;
          hidden.value = id;
          container.dataset.selPrice = price;
          container.dataset.selOrig = orig;

          menu.style.display = 'none';
          recalc();
        });
      });

      document.addEventListener('click', closeAll);

      document.getElementById('buildForm').addEventListener('submit', function(e) {
        const total = Number(document.getElementById('totalAmount').textContent.replace(/\D/g, '')) || 0;
        if (total === 0) {
          e.preventDefault();
          alert('Please select at least one component.');
        }
      });

      recalc();
    });
  </script>
</body>

</html>