<!-- ======= HEADER ======= -->
<header class="sticky-top bg-white shadow-sm">
  <div class="container px-3 px-lg-4">

    <!-- Top bar -->
    <div class="d-flex align-items-center py-3 gap-3">

      <!-- Brand -->
      <a class="d-flex align-items-center text-decoration-none" href="/">
        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
          style="width:48px;height:48px">
          <i class="fa-solid fa-computer text-white fs-5"></i>
        </div>
        <span class="fs-5 fw-bold text-primary ms-2 d-none d-sm-inline">PC Builder Pro</span>
      </a>

      <!-- Search (flex-fill makes it grow) -->
      <form class="search-form flex-fill mx-sm-3" role="search">
        <div class="input-group">
          <input class="form-control border-primary-subtle"
            type="search" placeholder="Search product…" aria-label="Search">
          <button class="btn btn-primary" type="submit">
            <i class="fa-solid fa-magnifying-glass"></i>
          </button>
        </div>
      </form>

      <!-- Icons -->
      <div class="d-flex align-items-center gap-2">
        <a href="#" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center"
          style="width:40px;height:40px" aria-label="User">
          <i class="fa-solid fa-user text-white"></i>
        </a>

        <a href="#" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center"
          style="width:40px;height:40px" aria-label="Cart">
          <i class="fa-solid fa-cart-shopping text-white"></i>
        </a>

        <span class="fw-semibold d-none d-md-inline">₹10,000</span>
      </div>
    </div>

    <!-- ======= NAVBAR ======= -->
    <nav class="navbar navbar-expand-lg navbar-light border-top">
      <div class="container-fluid px-0">
        <button class="navbar-toggler ms-auto" type="button"
          data-bs-toggle="offcanvas" data-bs-target="#navDrawer"
          aria-controls="navDrawer" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Desktop / large menu -->
        <div class="collapse navbar-collapse">
          <ul class="navbar-nav align-items-center gap-1">
            <!-- Mega-dropdown -->
            <li class="nav-item dropdown dropdown-mega">
              <a class="nav-link dropdown-toggle fw-semibold text-primary"
                href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-bars me-1"></i> All Categories
              </a>
              <div class="dropdown-menu shadow-lg border-0 p-3">
                <div class="row g-2">
                  <!-- Processor -->
                  <div class="col-6 col-md-4 col-lg-2">
                    <a class="d-block text-center text-decoration-none" href="#">
                      <img src="./assets/images/Processor-Icon-300x300.webp"
                        class="img-fluid rounded mb-1" alt="Processor">
                      <span class="small text-dark">Processor</span>
                    </a>
                  </div>

                  <!-- Motherboard -->
                  <div class="col-6 col-md-4 col-lg-2">
                    <a class="d-block text-center text-decoration-none" href="#">
                      <img src="./assets/images/motherboard-icon-300x300.webp"
                        class="img-fluid rounded mb-1" alt="Motherboard">
                      <span class="small text-dark">Motherboard</span>
                    </a>
                  </div>

                  <!-- CPU Cooler -->
                  <div class="col-6 col-md-4 col-lg-2">
                    <a class="d-block text-center text-decoration-none" href="#">
                      <img src="./assets/images/liquid-cooler-icon-300x300.webp"
                        class="img-fluid rounded mb-1" alt="Cooler">
                      <span class="small text-dark">CPU Cooler</span>
                    </a>
                  </div>

                  <!-- RAM -->
                  <div class="col-6 col-md-4 col-lg-2">
                    <a class="d-block text-center text-decoration-none" href="#">
                      <img src="./assets/images/RAM-icon-300x300.webp"
                        class="img-fluid rounded mb-1" alt="RAM">
                      <span class="small text-dark">RAM</span>
                    </a>
                  </div>

                  <!-- Graphics Card -->
                  <div class="col-6 col-md-4 col-lg-2">
                    <a class="d-block text-center text-decoration-none" href="#">
                      <img src="./assets/images/graphics-card-icon-300x300.webp"
                        class="img-fluid rounded mb-1" alt="GPU">
                      <span class="small text-dark">Graphics Card</span>
                    </a>
                  </div>

                  <!-- SSD -->
                  <div class="col-6 col-md-4 col-lg-2">
                    <a class="d-block text-center text-decoration-none" href="#">
                      <img src="./assets/images/ssd-icon-300x300.webp"
                        class="img-fluid rounded mb-1" alt="SSD">
                      <span class="small text-dark">SSD</span>
                    </a>
                  </div>

                  <!-- Repeat the pattern for remaining categories -->
                </div>
              </div>
            </li>

            <li class="nav-item"><a class="nav-link fw-semibold" href="#">Custom PC</a></li>
            <li class="nav-item"><a class="nav-link fw-semibold" href="#">Pre-built PC</a></li>
            <li class="nav-item"><a class="nav-link fw-semibold" href="#">Contact Us</a></li>
            <li class="nav-item"><a class="nav-link fw-semibold" href="#">About Us</a></li>
          </ul>
        </div>

        <!-- Off-canvas for mobile -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="navDrawer" aria-labelledby="navDrawerLabel">
          <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="navDrawerLabel">Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <ul class="nav flex-column gap-2">
              <li class="nav-item border-bottom pb-2">
                <a class="nav-link fw-semibold" data-bs-toggle="collapse" href="#catCollapse" role="button">
                  <i class="fa-solid fa-bars me-1"></i> All Categories
                </a>
                <div class="collapse mt-2" id="catCollapse">
                  <!-- Insert same category list again, simplified -->
                  <ul class="list-unstyled ps-3">
                    <li><a href="#" class="text-decoration-none small">Processor</a></li>
                    <li><a href="#" class="text-decoration-none small">Motherboard</a></li>
                    <li><a href="#" class="text-decoration-none small">CPU Cooler</a></li>
                    <li><a href="#" class="text-decoration-none small">RAM</a></li>
                    <li><a href="#" class="text-decoration-none small">Graphics Card</a></li>
                    <li><a href="#" class="text-decoration-none small">SSD</a></li>
                  </ul>
                </div>
              </li>

              <li class="nav-item"><a class="nav-link fw-semibold" href="#">Custom PC</a></li>
              <li class="nav-item"><a class="nav-link fw-semibold" href="#">Pre-built PC</a></li>
              <li class="nav-item"><a class="nav-link fw-semibold" href="#">Contact Us</a></li>
              <li class="nav-item"><a class="nav-link fw-semibold" href="#">About Us</a></li>
            </ul>
          </div>
        </div>
      </div>
    </nav>
  </div>
</header>