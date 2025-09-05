<?php
// header.php
// $conn (mysqli)
// require_once BASE_URL . 'config/config.php';
// require_once __DIR__ . './includes/db_connect.php';
// require_once __DIR__ . './includes/functions.php';
// fetch categories
$categories = [];
$sql = "SELECT category_id, category_name, parent_id, slug, icon_image
        FROM categories
        WHERE status = 'active'
        ORDER BY COALESCE(sort_order,9999), category_name";
if ($res = mysqli_query($conn, $sql)) {
  while ($r = mysqli_fetch_assoc($res)) {
    if (empty($r['slug'])) $r['slug'] = strtolower(preg_replace('/[^a-z0-9\-]+/i', '-', trim($r['category_name'])));
    $categories[] = $r;
  }
  mysqli_free_result($res);
}

// build maps
$parents = [];
$children = [];
foreach ($categories as $c) {
  $pid = $c['parent_id'] === null ? null : (int)$c['parent_id'];
  if ($pid === null) $parents[] = $c;
  else $children[$pid][] = $c;
}
?>
<header class="sticky-top bg-white shadow-sm" style="z-index:1100;">
  <div class="container px-3 px-lg-4">

    <!-- Top bar -->
    <div class="d-flex align-items-center py-3 gap-3">
      <a class="d-flex align-items-center text-decoration-none" href="/">
        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px">
          <i class="fa-solid fa-computer text-white fs-5"></i>
        </div>
        <span class="fs-5 fw-bold text-primary ms-2 d-none d-sm-inline">PC Builder Pro</span>
      </a>

      <!-- Search -->
      <form class="search-form flex-fill mx-sm-3" role="search" action="/search.php" method="get">
        <div class="input-group">
          <input class="form-control border-primary-subtle" name="q" type="search" placeholder="Search product…" aria-label="Search">
          <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </div>
      </form>

      <!-- Icons -->
      <div class="d-flex align-items-center gap-2">
        <a href="/account.php" class="btn btn-primary rounded-circle" style="width:40px;height:40px"><i class="fa-solid fa-user text-white"></i></a>
        <a href="/cart.php" class="btn btn-primary rounded-circle" style="width:40px;height:40px"><i class="fa-solid fa-cart-shopping text-white"></i></a>
        <span class="fw-semibold d-none d-md-inline">₹10,000</span>
      </div>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light border-top p-0">
      <div class="container-fluid px-0">
        <!-- toggler opens offcanvas on small screens -->
        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="offcanvas" data-bs-target="#navDrawer" aria-controls="navDrawer" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse">
          <ul class="navbar-nav align-items-center gap-1">

            <!-- Vertical categories dropdown -->
            <li class="nav-item dropdown dropdown-cats-vertical">
              <a class="nav-link dropdown-toggle fw-semibold text-primary d-flex align-items-center" href="#" id="catsToggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-bars me-1"></i> All Categories
              </a>

              <div class="dropdown-menu dropdown-cats-menu shadow border-0 p-0" aria-labelledby="catsToggle">
                <div class="d-flex">
                  <!-- parent column -->
                  <div class="parent-column">
                    <?php if (!empty($parents)): ?>
                      <?php foreach ($parents as $cat):
                        $catId = (int)$cat['category_id'];
                        $name  = htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8');
                        $slug  = rawurlencode($cat['slug']);
                        $icon  = !empty($cat['icon_image']) ? htmlspecialchars($cat['icon_image'], ENT_QUOTES, 'UTF-8') : 'placeholder-category.png';

                        // Use BASE_URL instead of relative path
                        $img   = ASSETS_URL . "images/category_icons/{$icon}";
                        $hasSub = !empty($children[$catId]);
                      ?>

                        <div class="parent-item d-flex align-items-center" data-cat="<?php echo $catId; ?>">
                          <a class="parent-link d-flex align-items-center w-100 text-decoration-none" href="/category.php?slug=<?php echo $slug; ?>">
                            <img src="<?php echo $img; ?>" alt="<?php echo $name; ?>" class="parent-icon">
                            <span class="parent-title"><?php echo $name; ?></span>
                          </a>
                          <?php if ($hasSub): ?>
                            <span class="chev">›</span>
                            <div class="submenu">
                              <?php foreach ($children[$catId] as $s):
                                $sName = htmlspecialchars($s['category_name'], ENT_QUOTES, 'UTF-8');
                                $sSlug = rawurlencode($s['slug']);
                              ?>
                                <a class="submenu-link d-block" href="/category.php?slug=<?php echo $sSlug; ?>"><?php echo $sName; ?></a>
                              <?php endforeach; ?>
                            </div>
                          <?php endif; ?>
                        </div>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <div class="parent-item"><a class="parent-link" href="/category.php?slug=processor">Processor</a></div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </li>

            <li class="nav-item"><a class="nav-link fw-semibold" href="./pages/custom-pc.php">Custom PC</a></li>
            <li class="nav-item"><a class="nav-link fw-semibold" href="/prebuilt-pc.php">Pre-built PC</a></li>
            <li class="nav-item"><a class="nav-link fw-semibold" href="/contact.php">Contact Us</a></li>
            <li class="nav-item"><a class="nav-link fw-semibold" href="/about.php">About Us</a></li>
          </ul>
        </div>

        <!-- Offcanvas (mobile) -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="navDrawer" aria-labelledby="navDrawerLabel">
          <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="navDrawerLabel">Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <ul class="nav flex-column">
              <li class="nav-item mb-2">
                <a class="nav-link fw-semibold" data-bs-toggle="collapse" href="#catCollapse" role="button"><i class="fa-solid fa-bars me-1"></i> All Categories</a>
                <div class="collapse mt-2" id="catCollapse">
                  <ul class="list-unstyled ps-3">
                    <?php if (!empty($parents)): foreach ($parents as $cat): $sub = $children[(int)$cat['category_id']] ?? []; ?>
                        <li class="mb-1">
                          <a href="/category.php?slug=<?php echo rawurlencode($cat['slug']); ?>" class="fw-semibold d-block py-1"><?php echo htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8'); ?></a>
                          <?php if (!empty($sub)): ?>
                            <ul class="list-unstyled ps-3">
                              <?php foreach ($sub as $s): ?>
                                <li><a class="d-block py-1" href="/category.php?slug=<?php echo rawurlencode($s['slug']); ?>"><?php echo htmlspecialchars($s['category_name'], ENT_QUOTES, 'UTF-8'); ?></a></li>
                              <?php endforeach; ?>
                            </ul>
                          <?php endif; ?>
                        </li>
                      <?php endforeach;
                    else: ?>
                      <li><a href="/category.php?slug=processor">Processor</a></li>
                    <?php endif; ?>
                  </ul>
                </div>
              </li>
              <li class="nav-item"><a class="nav-link fw-semibold" href="/custom-pc.php">Custom PC</a></li>
              <li class="nav-item"><a class="nav-link fw-semibold" href="/prebuilt-pc.php">Pre-built PC</a></li>
              <li class="nav-item"><a class="nav-link fw-semibold" href="/contact.php">Contact Us</a></li>
              <li class="nav-item"><a class="nav-link fw-semibold" href="/about.php">About Us</a></li>
            </ul>
          </div>
        </div>
      </div>
    </nav>
  </div>

  <!-- REPLACE dropdown styles -->
  <style>
    .dropdown-cats-menu {
      min-width: 300px;
      max-width: calc(100vw - 40px);
      /* never exceed viewport width */
      border-radius: 8px;
      overflow: visible;
    }

    .parent-column {
      width: min(340px, 36vw);
      max-height: calc(100vh - 140px);
      overflow-y: auto;
      overflow-x: hidden;
      /* prevent inner horizontal scroll */
      background: #fff;
    }

    .parent-item {
      position: relative;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      border-bottom: 1px solid rgba(0, 0, 0, 0.04);
    }

    .parent-link {
      display: flex;
      align-items: center;
      gap: 10px;
      color: #222;
      text-decoration: none;
      width: 100%;
    }

    .parent-icon {
      width: 36px;
      height: 36px;
      object-fit: contain;
    }

    .parent-title {
      font-size: 14px;
    }

    .chev {
      margin-left: 6px;
      color: #9aa;
      font-size: 14px;
    }

    /* submenu flyout */
    .submenu {
      display: none;
      position: absolute;
      left: 100%;
      top: 50%;
      transform: translateY(-50%);
      min-width: 220px;
      max-width: calc(100vw - 380px);
      /* keep submenu fitting viewport */
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
      padding: 8px 0;
      z-index: 1320;
      max-height: calc(100vh - 140px);
      overflow: auto;
      white-space: nowrap;
    }

    .submenu-link {
      display: block;
      padding: 10px 14px;
      color: #333;
      text-decoration: none;
    }

    .submenu-link:hover {
      background: rgba(0, 0, 0, 0.03);
    }

    /* flipped submenu (opens to the left) */
    .parent-item.flip .submenu {
      left: auto;
      right: 100%;
      transform: translateY(-50%);
    }

    @media (min-width:992px) {
      .parent-item:hover .submenu {
        display: block;
      }
    }

    @media (max-width:991.98px) {
      .submenu {
        position: static;
        transform: none;
        box-shadow: none;
        border-radius: 0;
        padding-left: 12px;
        display: block;
      }
    }

    /* small niceties */
    .parent-column::-webkit-scrollbar {
      width: 10px;
    }

    .parent-column::-webkit-scrollbar-thumb {
      background: rgba(0, 0, 0, 0.12);
      border-radius: 6px;
    }
  </style>

  <!-- REPLACE hover script with flip logic -->
  <script>
    (function() {
      const mq = () => window.matchMedia('(min-width:992px)').matches;
      const dropdown = document.querySelector('.dropdown-cats-vertical');
      if (!dropdown) return;

      // show/close dropdown on hover (existing)
      function open() {
        dropdown.querySelector('.dropdown-toggle').classList.add('show');
        dropdown.querySelector('.dropdown-menu').classList.add('show');
      }

      function close() {
        dropdown.querySelector('.dropdown-toggle').classList.remove('show');
        dropdown.querySelector('.dropdown-menu').classList.remove('show');
      }

      function bindDropdown() {
        dropdown.removeEventListener('mouseenter', open);
        dropdown.removeEventListener('mouseleave', close);
        if (!mq()) {
          close();
          return;
        }
        dropdown.addEventListener('mouseenter', open);
        dropdown.addEventListener('mouseleave', close);
      }

      // flip submenu if it would overflow viewport
      function updateFlip(el) {
        const submenu = el.querySelector('.submenu');
        if (!submenu) return el.classList.remove('flip');
        submenu.style.display = 'block'; // temporarily show to measure
        const rect = submenu.getBoundingClientRect();
        const vw = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
        submenu.style.display = ''; // restore
        if (rect.right > vw - 8) {
          el.classList.add('flip');
        } else {
          el.classList.remove('flip');
        }
      }

      // attach listeners to all parent-items
      function attachParentHandlers() {
        const parentItems = dropdown.querySelectorAll('.parent-item');
        parentItems.forEach(pi => {
          // update on mouseenter and on focus for accessibility
          pi.addEventListener('mouseenter', () => updateFlip(pi));
          pi.addEventListener('focusin', () => updateFlip(pi));
          // handle window resize
          window.addEventListener('resize', () => updateFlip(pi));
        });
      }

      // init
      document.addEventListener('DOMContentLoaded', () => {
        bindDropdown();
        attachParentHandlers();
      });
      window.addEventListener('resize', bindDropdown);
    })();
  </script>

</header>