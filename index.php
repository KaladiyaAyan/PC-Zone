<?php
// if () {
//   header("Location: pages/Home.php");
//   exit;
// }
include './includes/db_connect.php';
// include './includes/functions.php';

?>

<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PC ZONE</title>

  <link rel="stylesheet" href="./assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="./assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
  <?php
  include './includes/header.php';
  include './includes/sidebar.php';
  ?>

  <main>
    <h1>Home</h1>

    <!-- Hero Section -->
    <section class="hero-section">
      <div class="container">
        <div class="hero-content">
          <div class="row">
            <div class="col-lg-8 mx-auto">
              <h2 class="hero-title">BUILD YOUR DREAM PC</h2>
              <p class="hero-subtitle">Premium PC components from top brands. Intel, AMD, NVIDIA, ASUS, MSI & more. Fast delivery across India.</p>
              <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="/pc-builder" class="btn btn-primary-custom">
                  <i class="bi bi-tools me-2"></i>PC Builder Tool
                </a>
                <a href="/gaming-pcs" class="btn btn-outline-light">
                  <i class="bi bi-controller me-2"></i>Pre-Built Gaming PCs
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Categories -->
    <section class="featured-categories">
      <div class="container">
        <div class="row mb-5">
          <div class="col-12 text-center">
            <h3 class="display-5 fw-bold mb-3" style="color: var(--primary-color);">PC COMPONENTS</h3>
            <p class="lead text-light opacity-75">Build your perfect gaming rig with premium components</p>
          </div>
        </div>
        <div class="row g-4">
          <div class="col-lg-3 col-md-6">
            <div class="category-card">
              <i class="bi bi-cpu"></i>
              <h5>Processors (CPU)</h5>
              <p>Intel Core i5, i7, i9 & AMD Ryzen processors for ultimate performance</p>
              <div class="mt-3">
                <small class="text-muted">Starting from ₹8,999</small>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="category-card">
              <i class="bi bi-gpu-card"></i>
              <h5>Graphics Cards</h5>
              <p>NVIDIA RTX & AMD Radeon graphics cards for gaming and content creation</p>
              <div class="mt-3">
                <small class="text-muted">Starting from ₹15,999</small>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="category-card">
              <i class="bi bi-motherboard"></i>
              <h5>Motherboards</h5>
              <p>ASUS, MSI, Gigabyte motherboards with latest chipsets and features</p>
              <div class="mt-3">
                <small class="text-muted">Starting from ₹6,499</small>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="category-card">
              <i class="bi bi-memory"></i>
              <h5>Memory (RAM)</h5>
              <p>High-speed DDR4 & DDR5 RAM from Corsair, G.Skill, Kingston</p>
              <div class="mt-3">
                <small class="text-muted">Starting from ₹2,999</small>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="category-card">
              <i class="bi bi-device-hdd"></i>
              <h5>Storage</h5>
              <p>Fast NVMe SSDs and high-capacity HDDs for all your storage needs</p>
              <div class="mt-3">
                <small class="text-muted">Starting from ₹1,999</small>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="category-card">
              <i class="bi bi-fan"></i>
              <h5>Cooling Systems</h5>
              <p>Air coolers, AIO liquid coolers, and thermal solutions</p>
              <div class="mt-3">
                <small class="text-muted">Starting from ₹1,499</small>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="category-card">
              <i class="bi bi-lightning"></i>
              <h5>Power Supply</h5>
              <p>80+ certified PSUs from Corsair, Seasonic, Cooler Master</p>
              <div class="mt-3">
                <small class="text-muted">Starting from ₹3,499</small>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="category-card">
              <i class="bi bi-pc-display"></i>
              <h5>PC Cases</h5>
              <p>Mid-tower, full-tower cases with RGB lighting and cable management</p>
              <div class="mt-3">
                <small class="text-muted">Starting from ₹2,799</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-5" style="background: var(--card-bg);">
      <div class="container">
        <div class="row g-4">
          <div class="col-md-3 text-center">
            <div class="p-4">
              <i class="bi bi-truck display-4 text-primary mb-3"></i>
              <h6 class="text-light fw-bold">FREE SHIPPING</h6>
              <p class="text-muted small mb-0">On orders above ₹5,999</p>
            </div>
          </div>
          <div class="col-md-3 text-center">
            <div class="p-4">
              <i class="bi bi-shield-check display-4 text-success mb-3"></i>
              <h6 class="text-light fw-bold">WARRANTY</h6>
              <p class="text-muted small mb-0">2-3 years on all components</p>
            </div>
          </div>
          <div class="col-md-3 text-center">
            <div class="p-4">
              <i class="bi bi-headset display-4 text-warning mb-3"></i>
              <h6 class="text-light fw-bold">TECH SUPPORT</h6>
              <p class="text-muted small mb-0">Expert assistance for builds</p>
            </div>
          </div>
          <div class="col-md-3 text-center">
            <div class="p-4">
              <i class="bi bi-arrow-repeat display-4 text-info mb-3"></i>
              <h6 class="text-light fw-bold">EASY RETURNS</h6>
              <p class="text-muted small mb-0">7-day return policy</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Popular Brands -->
    <section class="py-5">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5">
            <h3 class="display-6 fw-bold text-light mb-3">POPULAR BRANDS</h3>
            <p class="text-muted">We stock components from the world's leading manufacturers</p>
          </div>
        </div>
        <div class="row g-4 text-center">
          <div class="col-lg-2 col-md-3 col-4">
            <div class="p-3 rounded" style="background: var(--card-bg); border: 1px solid rgba(0,212,255,0.2);">
              <h6 class="text-light mb-0">INTEL</h6>
            </div>
          </div>
          <div class="col-lg-2 col-md-3 col-4">
            <div class="p-3 rounded" style="background: var(--card-bg); border: 1px solid rgba(255,107,53,0.2);">
              <h6 class="text-light mb-0">AMD</h6>
            </div>
          </div>
          <div class="col-lg-2 col-md-3 col-4">
            <div class="p-3 rounded" style="background: var(--card-bg); border: 1px solid rgba(0,255,136,0.2);">
              <h6 class="text-light mb-0">NVIDIA</h6>
            </div>
          </div>
          <div class="col-lg-2 col-md-3 col-4">
            <div class="p-3 rounded" style="background: var(--card-bg); border: 1px solid rgba(0,212,255,0.2);">
              <h6 class="text-light mb-0">ASUS</h6>
            </div>
          </div>
          <div class="col-lg-2 col-md-3 col-4">
            <div class="p-3 rounded" style="background: var(--card-bg); border: 1px solid rgba(255,107,53,0.2);">
              <h6 class="text-light mb-0">MSI</h6>
            </div>
          </div>
          <div class="col-lg-2 col-md-3 col-4">
            <div class="p-3 rounded" style="background: var(--card-bg); border: 1px solid rgba(0,255,136,0.2);">
              <h6 class="text-light mb-0">CORSAIR</h6>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>


  <script src="./assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <script>
    // Mobile Search Toggle
    function toggleMobileSearch() {
      const mobileSearch = document.getElementById('mobileSearch');
      mobileSearch.classList.toggle('show');
    }

    // Search Functionality
    document.addEventListener('DOMContentLoaded', function() {
      const searchInputs = document.querySelectorAll('.search-input');
      searchInputs.forEach(input => {
        input.addEventListener('input', function(e) {
          const searchTerm = e.target.value.toLowerCase();
          console.log('Searching for PC components:', searchTerm);
          // Implement your search logic here
          // You can make AJAX calls to your PHP backend
          // Example: searchProducts(searchTerm);
        });
      });

      // Search form submission
      const searchButtons = document.querySelectorAll('.search-btn');
      searchButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const searchInput = this.parentElement.querySelector('.search-input');
          const categorySelect = this.parentElement.querySelector('.category-select');
          const searchTerm = searchInput.value.trim();
          const category = categorySelect ? categorySelect.value : '';

          if (searchTerm) {
            let url = `/search?q=${encodeURIComponent(searchTerm)}`;
            if (category) {
              url += `&category=${encodeURIComponent(category)}`;
            }
            window.location.href = url;
          }
        });
      });

      // Enter key search
      searchInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
          if (e.key === 'Enter') {
            const btn = this.parentElement.querySelector('.search-btn');
            btn.click();
          }
        });
      });
    });

    // Category selection
    document.querySelector('.category-select')?.addEventListener('change', function(e) {
      const selectedCategory = e.target.value;
      console.log('Category selected:', selectedCategory);
      // Update search placeholder based on category
      const searchInput = document.querySelector('.search-input');
      const placeholders = {
        'cpu': 'Search processors... (Intel i5, i7, AMD Ryzen)',
        'gpu': 'Search graphics cards... (RTX 4070, RX 7800 XT)',
        'motherboard': 'Search motherboards... (ASUS, MSI, Gigabyte)',
        'ram': 'Search memory... (DDR4, DDR5, 16GB, 32GB)',
        'storage': 'Search storage... (NVMe SSD, SATA SSD, HDD)',
        'cooling': 'Search cooling... (Air cooler, AIO, Thermal paste)',
        'psu': 'Search power supply... (650W, 750W, Modular PSU)',
        'case': 'Search PC cases... (Mid tower, Full tower, RGB)'
      };

      if (searchInput && placeholders[selectedCategory]) {
        searchInput.placeholder = placeholders[selectedCategory];
      } else if (searchInput) {
        searchInput.placeholder = 'Search for processors, graphics cards, motherboards...';
      }
    });

    // Enhanced cart functionality for PC components
    function addToCart(productId, productType = 'component') {
      // Make AJAX call to your PHP backend
      fetch('/ajax/add-to-cart.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            product_id: productId,
            product_type: productType,
            quantity: 1
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            updateCartCount(data.cart_count);
            showNotification('Component added to cart!', 'success');

            // Show compatibility suggestions if it's a major component
            if (['cpu', 'motherboard', 'ram'].includes(productType)) {
              showCompatibilityModal(productId, productType);
            }
          } else {
            showNotification('Error adding component to cart', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showNotification('Error adding component to cart', 'error');
        });
    }

    // PC Builder compatibility checker
    function showCompatibilityModal(productId, productType) {
      // This would show suggestions for compatible components
      console.log(`Checking compatibility for ${productType} with ID: ${productId}`);
      // Implementation would depend on your compatibility data
    }

    // Notification system
    function showNotification(message, type = 'info') {
      // Create notification element
      const notification = document.createElement('div');
      notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
      notification.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
      notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;

      document.body.appendChild(notification);

      // Auto remove after 5 seconds
      setTimeout(() => {
        notification.remove();
      }, 5000);
    }

    // Update counters
    function updateCartCount(count) {
      document.querySelectorAll('.badge-count').forEach(badge => {
        if (badge.parentElement.href && badge.parentElement.href.includes('cart')) {
          badge.textContent = count;
        }
      });
    }

    function updateWishlistCount(count) {
      document.querySelectorAll('.badge-count').forEach(badge => {
        if (badge.parentElement.href && badge.parentElement.href.includes('wishlist')) {
          badge.textContent = count;
        }
      });
    }

    // Sticky navigation with scroll effects
    window.addEventListener('scroll', function() {
      const header = document.querySelector('.main-header');
      if (window.scrollY > 100) {
        header.style.boxShadow = '0 4px 30px rgba(0, 212, 255, 0.3)';
      } else {
        header.style.boxShadow = '0 4px 30px rgba(0, 212, 255, 0.2)';
      }
    });

    // Initialize tooltips (if using Bootstrap tooltips)
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Price formatter for Indian currency
    function formatPrice(price) {
      return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR'
      }).format(price);
    }

    // Component specification helper
    function showSpecifications(productId) {
      // Fetch and display detailed specifications
      fetch(`/ajax/get-specifications.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            displaySpecificationModal(data.specifications);
          }
        });
    }

    function displaySpecificationModal(specs) {
      // Create and show modal with specifications
      console.log('Displaying specifications:', specs);
    }
  </script>
</body>

</html>