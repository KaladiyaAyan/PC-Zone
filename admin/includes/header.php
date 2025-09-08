<?php
$userName = trim(($_SESSION['full_name'] ?? '')) ?: ($_SESSION['username'] ?? 'Admin');
$avatarSrc = '';
?>
<header class="admin-header">
  <div class="left-section">
    <button id="hamburger" class="hamburger" type="button" aria-label="Toggle sidebar">
      <i class="fas fa-bars"></i>
    </button>

    <a href="dashboard.php" class="brand-link">
      <span style="color:#ff9900; font-weight:700;">PC</span>ZONE
    </a>
  </div>

  <div class="right-section">
    <!-- Theme toggle (small icon button) -->
    <button id="themeToggle" class="admin-button" type="button" aria-label="Toggle theme">
      <i id="themeIcon" class="fas fa-moon"></i>
      <!-- <span id="themeText">Dark Mode</span> -->
    </button>

    <div class="dropdown">
      <button class="user-btn d-flex align-items-center gap-2" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="./assets/images/admin.jpg" alt="avatar" class="admin-avatar">
        <span class="d-none d-sm-inline"><?= htmlspecialchars($userName) ?></span>
        <i class="fas fa-caret-down ms-1"></i>
      </button>

      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
        <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
        <li>
          <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
      </ul>
    </div>
  </div>
</header>

<script>
  // Sidebar toggle functionality
  document.addEventListener("DOMContentLoaded", () => {
    const hamburger = document.getElementById("hamburger");
    const sidebar = document.getElementById("sidebar");

    if (!hamburger || !sidebar) return;

    // === Desktop Sidebar State ===
    const savedState = localStorage.getItem("sidebarCollapsed") === "true";
    if (savedState) sidebar.classList.add("collapsed");

    hamburger.addEventListener("click", (e) => {
      if (window.innerWidth > 768) {
        // Desktop toggle
        sidebar.classList.toggle("collapsed");
        localStorage.setItem("sidebarCollapsed", sidebar.classList.contains("collapsed"));
      } else {
        // Mobile toggle
        e.preventDefault();
        sidebar.classList.toggle("show");
      }
    });

    // === Close sidebar on outside click (mobile only) ===
    document.addEventListener("click", (e) => {
      if (
        window.innerWidth <= 768 &&
        sidebar.classList.contains("show") &&
        !sidebar.contains(e.target) &&
        !hamburger.contains(e.target)
      ) {
        sidebar.classList.remove("show");
      }
    });
  });


  (function() {
    const storageKey = 'pczoneTheme';
    const btn = document.getElementById('themeToggle');
    const icon = document.getElementById('themeIcon');
    const text = document.getElementById('themeText');

    function applyTheme(theme) {
      if (theme === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
        text.textContent = 'Light Mode';
      } else {
        document.documentElement.removeAttribute('data-theme');
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
        text.textContent = 'Dark Mode';
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      const saved = localStorage.getItem(storageKey);
      applyTheme(saved === 'light' ? 'light' : 'dark');

      // Update status indicator colors
      const statusElements = document.querySelectorAll('.status');
      statusElements.forEach(el => {
        if (saved === 'light') {
          el.style.color = '#00d4aa';
        } else {
          el.style.color = '#00d4aa';
        }
      });
    });

    if (btn) {
      btn.addEventListener('click', function() {
        const current = document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
        const next = current === 'light' ? 'dark' : 'light';
        localStorage.setItem(storageKey, next === 'light' ? 'light' : 'dark');
        applyTheme(next);

        // Update status indicator colors
        const statusElements = document.querySelectorAll('.status');
        statusElements.forEach(el => {
          if (next === 'light') {
            el.style.color = '#00d4aa';
          } else {
            el.style.color = '#00d4aa';
          }
        });
      });
    }
  })();
</script>