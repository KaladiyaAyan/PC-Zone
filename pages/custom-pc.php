<?php
require_once '../config/config.php';
require_once INCLUDES_PATH . "db_connect.php";

$platform = in_array($_GET['platform'] ?? 'amd', ['amd', 'intel']) ? $_GET['platform'] : 'amd';
include __DIR__ . '/../includes/navbar.php';

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

function getProductsByCategorySlug($conn, $slug)
{
  $s = mysqli_real_escape_string($conn, $slug);
  $r = mysqli_query($conn, "SELECT category_id FROM categories WHERE slug='$s' LIMIT 1");
  $row = mysqli_fetch_assoc($r);
  if (!$row) return [];
  $cid = (int)$row['category_id'];

  $sql = "
    SELECT p.product_id, p.product_name, p.price, p.discount,
           COALESCE(pi.image_path, 'placeholder.jpg') AS image
    FROM products p
    LEFT JOIN product_images pi ON pi.product_id = p.product_id AND pi.is_main=1
    WHERE p.is_active=1 AND p.stock>0
      AND (p.category_id = $cid OR p.category_id IN (SELECT category_id FROM categories WHERE parent_id = $cid))
    ORDER BY (p.price - p.discount) ASC
    LIMIT 500
  ";
  $res = mysqli_query($conn, $sql);
  $out = [];
  while ($row = mysqli_fetch_assoc($res)) $out[] = $row;
  return $out;
}

$partsProducts = [];
foreach ($parts as $slug => $label) {
  $partsProducts[$slug] = getProductsByCategorySlug($conn, $slug);
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php $platform ?>Custom PC Build</title>

  <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">

  <style>
    :root {
      --page-bg: #f7f8fb;
      --card-bg: #fff;
      --muted: #6c757d;
      --border: #e9ecef;
      --accent: #ff5a4d;
    }

    body {
      background: var(--page-bg);
      color: #212529;
      font-family: system-ui, Segoe UI, Roboto, Arial;
    }

    .custom-build-row {
      display: flex;
      gap: 1.25rem;
      align-items: stretch;
    }

    .left-col {
      flex: 0 0 25%;
      max-width: 25%;
    }

    .right-col {
      flex: 1 1 75%;
      max-width: 75%;
    }

    .left-hero {
      width: 100%;
      border-radius: 12px;
      overflow: hidden;
      background: #fff;
      box-shadow: 0 8px 30px rgba(2, 6, 23, .04);
      min-height: 440px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .left-hero img {
      width: 92%;
      max-height: 86%;
      object-fit: cover;
      border-radius: 8px;
      display: block;
    }

    .right-panel {
      background: var(--card-bg);
      border-radius: 12px;
      padding: 1.25rem;
      border: 1px solid var(--border);
      box-shadow: 0 6px 20px rgba(12, 20, 30, .03);
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .parts-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: .9rem;
    }

    .part-item {
      position: relative;
      padding: .25rem;
    }

    .dd-label {
      font-size: .85rem;
      color: var(--muted);
      margin-bottom: .35rem;
    }

    .dd-control {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      justify-content: center;
      gap: 6px;
      padding: 10px 44px 10px 12px;
      border-radius: 8px;
      background: #fbfcfd;
      border: 1px solid var(--border);
      min-height: 48px;
      position: relative;
      cursor: pointer;
      box-sizing: border-box;
      white-space: normal;
      overflow: hidden;
    }

    .dd-selected {
      width: 100%;
      overflow: hidden;
      white-space: normal;
    }

    .dd-selected .name {
      font-weight: 600;
      font-size: .95rem;
      color: #212529;
      line-height: 1.15;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      text-overflow: ellipsis;
      word-break: break-word;
      white-space: normal;
    }

    .dd-selected .price {
      display: none !important;
    }

    .caret {
      position: absolute;
      right: 12px;
      top: 12px;
      color: var(--muted);
    }

    .dd-menu {
      position: absolute;
      z-index: 50;
      left: 0;
      right: 0;
      top: 72px;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 8px;
      box-shadow: 0 12px 30px rgba(10, 20, 30, .08);
      max-height: 320px;
      overflow: auto;
      padding: .4rem;
      display: none;
    }

    .dd-item {
      display: flex;
      gap: .75rem;
      align-items: flex-start;
      padding: .6rem;
      border-radius: 6px;
      cursor: pointer;
    }

    .dd-item:hover {
      background: #f6f8fa;
    }

    .thumb {
      width: 46px;
      height: 46px;
      border-radius: 6px;
      overflow: hidden;
      border: 1px solid var(--border);
      background: #fff;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .meta .title {
      font-weight: 600;
      font-size: .95rem;
      color: #212529;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      text-overflow: ellipsis;
      word-break: break-word;
    }

    .price-line {
      font-size: .88rem;
      color: var(--muted);
      margin-top: .18rem;
      display: flex;
      gap: .6rem;
      align-items: center;
    }

    .price-line .orig {
      text-decoration: line-through;
      color: #9aa0a6;
      font-size: .85rem;
    }

    .price-line .disc {
      color: var(--accent);
      font-weight: 700;
      font-size: .95rem;
    }

    .cart-footer {
      display: flex;
      gap: 1rem;
      align-items: center;
      margin-top: 1rem;
    }

    .price-box {
      flex: 1;
      padding: .7rem 1rem;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .price-box .totals .label {
      font-size: .85rem;
      color: var(--muted);
    }

    .price-box .totals .amount {
      font-size: 1.3rem;
      color: var(--accent);
      font-weight: 700;
    }

    .price-box .totals .orig {
      font-size: .9rem;
      color: #9aa0a6;
      text-decoration: line-through;
      margin-left: .5rem;
    }

    .btn-add {
      background: var(--accent);
      color: #fff;
      border: none;
      padding: .7rem 1.25rem;
      border-radius: 8px;
      font-weight: 600;
    }

    .btn-add:disabled {
      opacity: .45;
      cursor: not-allowed;
    }

    @media (max-width:991px) {

      .left-col,
      .right-col {
        max-width: 100%;
        flex: 1 1 100%;
      }

      .parts-grid {
        grid-template-columns: repeat(1, 1fr);
      }

      .dd-menu {
        position: static;
        top: auto;
      }

      .cart-footer {
        flex-direction: column;
        align-items: stretch;
        gap: .6rem;
      }
    }
  </style>
</head>

<body>
  <main class="container my-4">
    <form id="buildForm" action="<?php echo BASE_URL . 'pages/add_to_cart.php'; ?>" method="POST" class="custom-build-row">
      <div class="left-col">
        <div class="left-hero" aria-hidden="true">
          <img src="../assets/images/<?= e($platform) ?>_custom_build.jpg" alt="build image">
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

                <div class="dd-control" tabindex="0">
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
                      $final = floatval($it['price']) - floatval($it['discount']);
                      $orig = floatval($it['discount']) > 0 ? floatval($it['price']) : 0;
                      $p_fmt = number_format($final, 2);
                      $orig_fmt = $orig ? number_format($orig, 2) : 0;
                      $img = htmlspecialchars($it['image'], ENT_QUOTES);
                      $name = htmlspecialchars($it['product_name'], ENT_QUOTES);
                    ?>
                      <div class="dd-item"
                        data-id="<?= (int)$it['product_id'] ?>"
                        data-price="<?= $final ?>"
                        data-orig="<?= $orig ?>"
                        data-name="<?= $name ?>"
                        data-img="<?= $img ?>">
                        <div class="thumb"><img src="../assets/images/products/<?= e($img) ?>" alt=""></div>
                        <div class="meta">
                          <div class="title"><?= e($it['product_name']) ?></div>
                          <div class="price-line">
                            <?php if ($orig): ?><div class="orig">₹<?= $orig_fmt ?></div><?php endif; ?>
                            <div class="disc">₹<?= $p_fmt ?></div>
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

  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
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