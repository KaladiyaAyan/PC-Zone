<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

$platform = in_array($_GET['platform'] ?? 'amd', ['amd', 'intel']) ? $_GET['platform'] : 'amd';

// map part slugs you want to show and whether they need platform filter
$parts = [
  'processor'    => ['label' => 'Processor', 'platformed' => true],
  'motherboard'  => ['label' => 'Motherboard', 'platformed' => true],
  'ram'          => ['label' => 'RAM', 'platformed' => false],
  'graphics-card' => ['label' => 'Graphics Card', 'platformed' => false],
  'storage'      => ['label' => 'Storage', 'platformed' => false],
  'power-supply' => ['label' => 'Power Supply', 'platformed' => false],
  'cabinet'      => ['label' => 'Cabinet', 'platformed' => false],
  'cooling-system' => ['label' => 'CPU Cooler', 'platformed' => false],
];

// helper: get category_id by slug
function catId($conn, $slug)
{
  $slug = mysqli_real_escape_string($conn, $slug);
  $q = "SELECT category_id FROM categories WHERE slug='$slug' LIMIT 1";
  $r = mysqli_query($conn, $q);
  $row = mysqli_fetch_assoc($r);
  return $row['category_id'] ?? 0;
}

// fetch products by parent category (including child categories)
function fetchProductsForPart($conn, $parentSlug, $platform = null)
{
  $pid = (int)catId($conn, $parentSlug);
  if (!$pid) return [];
  $platformSql = "";
  if ($platform !== null) {
    $p = mysqli_real_escape_string($conn, $platform);
    $platformSql = " AND p.platform IN ('$p','both') ";
  }
  $sql = "
    SELECT p.product_id, p.product_name, p.price, p.discount, p.slug,
           COALESCE(pi.image_path,'placeholder.png') AS image
    FROM products p
    LEFT JOIN product_images pi ON pi.product_id = p.product_id AND pi.is_main=1
    WHERE (p.category_id = $pid OR p.category_id IN (SELECT category_id FROM categories WHERE parent_id = $pid))
      AND p.is_active=1 AND p.stock>0
      $platformSql
    ORDER BY p.is_featured DESC, p.price ASC
    LIMIT 200
  ";
  $res = mysqli_query($conn, $sql);
  $out = [];
  while ($r = mysqli_fetch_assoc($res)) $out[] = $r;
  return $out;
}

$partsData = [];
foreach ($parts as $slug => $meta) {
  $partsData[$slug] = fetchProductsForPart($conn, $slug, $meta['platformed'] ? $platform : null);
}

include __DIR__ . '/../includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= strtoupper(e($platform)) ?> Custom PC</title>

  <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">

  <style>
    .object-fit-cover {
      object-fit: cover;
    }

    .dd-thumb {
      width: 60px;
      height: 46px;
      flex-shrink: 0;
      border-radius: 6px;
      overflow: hidden;
    }

    .dd-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .dd-btn {
      text-align: left;
    }

    .strike {
      text-decoration: line-through;
      color: #9aa0a6;
    }

    .promo-note {
      font-size: .95rem;
      line-height: 1.35;
      color: #333;
    }
  </style>
</head>

<body>

  <main class="container my-4">
    <div class="row g-4">
      <div class="col-lg-6">
        <!-- hero image: left unchanged as requested -->
        <div class="ratio ratio-1x1 bg-light rounded">
          <img src="../assets/images/<?= e($platform) ?>_custom_build.jpg"
            alt="<?= e($platform) ?> build"
            class="w-100 h-100 object-fit-cover">
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card p-3">
          <h3 class="h5 mb-2"><?= strtoupper(e($platform)) ?> Custom PC</h3>

          <!-- PROMO text block (user-provided) -->
          <div class="mb-3">
            <div class="d-flex align-items-baseline gap-3">
              <div class="strike">₹79,890</div>
              <div class="fs-3 text-danger fw-bold">₹46,320</div>
            </div>

            <div class="mt-3 promo-note">
              <strong>Get Rs 500/- instant cashback. Follow these 4 steps to claim-</strong>
              <ol class="mb-2">
                <li>Purchase a Custom PC from ModxComputers.</li>
                <li>Make a 5-10 min review video (You can show unboxing or do a review of your PC).</li>
                <li>Upload it on YouTube and also tag ModxComputers Channel in title.</li>
                <li>Share the video link to us on whatsapp 7303986007.</li>
              </ol>

              <div class="mt-2"><strong>FREE WINDOWS KEY 😍🔥</strong></div>
              <div class="text-muted small">Get free Windows 10/11 Pro License Key during this offer.</div>
            </div>
          </div>

          <!-- small assurance box -->
          <div class="mb-3">
            <div class="p-3 bg-light rounded">
              <div class="mb-2"><i class="bi bi-patch-check-fill text-success me-2"></i>100% Genuine Products Guaranteed</div>
              <div class="mb-2"><i class="bi bi-cash-stack text-warning me-2"></i>Cash on Delivery Available</div>
              <div><i class="bi bi-credit-card-2-front-fill text-info me-2"></i>EMI Available</div>
            </div>
          </div>

          <!-- custom image-style dropdowns (replaces select) -->
          <form id="customBuildForm">
            <input type="hidden" name="platform" value="<?= e($platform) ?>">

            <?php foreach ($partsData as $slug => $items): ?>
              <div class="mb-3">
                <label class="form-label fw-semibold"><?= e($parts[$slug]['label']) ?></label>

                <div class="dropdown">
                  <button class="btn btn-outline-secondary dropdown-toggle w-100 dd-btn d-flex justify-content-between align-items-center"
                    id="ddBtn_<?= e($slug) ?>" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="d-flex gap-2 align-items-center">
                      <div class="dd-thumb bg-white border">
                        <img id="thumb_<?= e($slug) ?>" src="../assets/images/placeholder.png" alt="">
                      </div>
                      <div class="text-start">
                        <div id="label_<?= e($slug) ?>" class="small text-muted">No, thanks</div>
                      </div>
                    </div>
                    <div id="price_<?= e($slug) ?>" class="small text-muted">₹0</div>
                  </button>

                  <ul class="dropdown-menu p-2" aria-labelledby="ddBtn_<?= e($slug) ?>" style="max-height:300px; overflow:auto;">
                    <li>
                      <a href="#" class="dropdown-item dd-item" data-id="0" data-price="0" data-name="No, thanks" data-image="placeholder.png">
                        <div class="small text-muted">No, thanks</div>
                        <div class="ms-auto small text-muted">₹0</div>
                      </a>
                    </li>

                    <?php foreach ($items as $it):
                      $p = floatval($it['price']) - floatval($it['discount']);
                      $safeName = htmlspecialchars($it['product_name'], ENT_QUOTES);
                      $img = htmlspecialchars($it['image'], ENT_QUOTES);
                    ?>
                      <li>
                        <a href="#" class="dropdown-item dd-item"
                          data-id="<?= (int)$it['product_id'] ?>"
                          data-price="<?= $p ?>"
                          data-name="<?= $safeName ?>"
                          data-image="<?= $img ?>">
                          <div class="dd-thumb me-2">
                            <img src="../assets/images/<?= e($it['image']) ?>" alt="">
                          </div>
                          <div class="flex-grow-1 small text-truncate"><?= e($it['product_name']) ?></div>
                          <div class="ms-2 small text-muted"><?= $p > 0 ? formatPrice($p) : '₹0' ?></div>
                        </a>
                      </li>
                    <?php endforeach; ?>
                  </ul>

                  <input type="hidden" id="input_<?= e($slug) ?>" name="part[<?= e($slug) ?>]" value="0">
                </div>
              </div>
            <?php endforeach; ?>

            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
              <div>
                <div class="small text-muted">Total</div>
                <div id="totalPrice" class="h4 text-danger">₹0</div>
              </div>
              <div>
                <button type="button" id="previewBtn" class="btn btn-outline-secondary me-2">Preview Build</button>
                <button type="button" class="btn btn-primary" disabled>Checkout (disabled)</button>
              </div>
            </div>
          </form>

        </div>
      </div>
    </div>
  </main>

  <!-- preview modal (small summary) -->
  <div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Build Preview</h5><button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="previewList" class="row gy-3"></div>
          <hr>
          <div class="d-flex justify-content-between">
            <div class="fw-semibold">Estimated Total</div>
            <div id="previewTotal" class="fw-bold text-danger">₹0</div>
          </div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
      </div>
    </div>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>

  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const currency = new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR'
      });

      document.querySelectorAll('.dd-item').forEach(item => {
        item.addEventListener('click', function(e) {
          e.preventDefault();
          const id = this.dataset.id;
          const price = parseFloat(this.dataset.price || 0);
          const name = this.dataset.name || '';
          const image = this.dataset.image || 'placeholder.png';

          const menu = this.closest('.dropdown-menu');
          const btn = menu.previousElementSibling;
          const key = btn.id.replace('ddBtn_', '');

          document.getElementById('label_' + key).textContent = name;
          document.getElementById('thumb_' + key).src = '../assets/images/' + image;
          document.getElementById('price_' + key).textContent = currency.format(price);
          document.getElementById('input_' + key).value = id;

          const dd = bootstrap.Dropdown.getInstance(btn);
          if (dd) dd.hide();
          recalcTotal();
        });
      });

      function recalcTotal() {
        let total = 0;
        document.querySelectorAll('input[id^="input_"]').forEach(h => {
          const val = h.value;
          if (!val || val === '0') return;
          const item = document.querySelector('.dd-item[data-id="' + val + '"]');
          if (item) total += parseFloat(item.dataset.price || 0);
        });
        document.getElementById('totalPrice').textContent = currency.format(total);
        document.getElementById('previewTotal').textContent = currency.format(total);
        return total;
      }

      document.getElementById('previewBtn').addEventListener('click', function() {
        const list = document.getElementById('previewList');
        list.innerHTML = '';
        document.querySelectorAll('input[id^="input_"]').forEach(h => {
          const val = h.value;
          if (!val || val === '0') return;
          const item = document.querySelector('.dd-item[data-id="' + val + '"]');
          if (!item) return;
          const name = item.dataset.name || item.textContent.trim();
          const price = parseFloat(item.dataset.price || 0);
          const img = item.dataset.image || 'placeholder.png';
          const row = document.createElement('div');
          row.className = 'col-12 d-flex gap-3 align-items-center';
          row.innerHTML = `
            <div style="width:72px;height:54px;flex-shrink:0;"><img src="../assets/images/${img}" class="img-fluid rounded object-fit-cover w-100 h-100"></div>
            <div class="flex-grow-1">
              <div class="fw-semibold">${escapeHtml(name)}</div>
              <div class="text-muted small">${currency.format(price)}</div>
            </div>
          `;
          list.appendChild(row);
        });
        new bootstrap.Modal(document.getElementById('previewModal')).show();
      });

      function escapeHtml(s) {
        return String(s).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
      }

      // init labels and thumbs
      document.querySelectorAll('.dd-btn').forEach(btn => {
        const key = btn.id.replace('ddBtn_', '');
        document.getElementById('label_' + key).textContent = 'No, thanks';
        document.getElementById('thumb_' + key).src = '../assets/images/placeholder.png';
        document.getElementById('price_' + key).textContent = currency.format(0);
      });

      recalcTotal();
    });
  </script>
</body>

</html>