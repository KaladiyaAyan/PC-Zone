<?php
$userName = trim(($_SESSION['full_name'] ?? '')) ?: ($_SESSION['username'] ?? 'Admin');
?>

<link rel="stylesheet" href="./assets/css/style.css">
<link rel="stylesheet" href="./assets/css/header.css">

<header class="admin-header">
  <div class="left-section">
    <button id="hamburger" class="hamburger" type="button" aria-label="Toggle sidebar">
      <i class="fas fa-bars"></i>
    </button>
    <a href="index.php" class="brand-link">
      <span>PC</span>ZONE
    </a>
  </div>

  <div class="right-section">
    <button id="themeToggle" class="admin-button" type="button" aria-label="Toggle theme">
      <i id="themeIcon" class="fas fa-moon"></i>
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
  document.addEventListener("DOMContentLoaded", () => {

    // --- Sidebar Toggle Logic ---
    const hamburger = document.getElementById("hamburger");
    const sidebar = document.getElementById("sidebar"); // Assumes sidebar has id="sidebar"

    if (hamburger && sidebar) {
      // Restore desktop sidebar state on page load
      if (window.innerWidth > 768 && localStorage.getItem("sidebarCollapsed") === "true") {
        sidebar.classList.add("collapsed");
      }

      // Toggle sidebar on click
      hamburger.addEventListener("click", () => {
        if (window.innerWidth > 768) { // Desktop
          sidebar.classList.toggle("collapsed");
          localStorage.setItem("sidebarCollapsed", sidebar.classList.contains("collapsed"));
        } else { // Mobile
          sidebar.classList.toggle("show");
        }
      });
    }

    // --- Theme Toggle Logic (Simplified) ---
    const themeToggle = document.getElementById("themeToggle");
    const themeIcon = document.getElementById("themeIcon");
    const storageKey = 'pczoneTheme';

    const applyTheme = (theme) => {
      if (theme === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
        themeIcon.classList.replace('fa-moon', 'fa-sun');
        localStorage.setItem(storageKey, 'light');
      } else {
        document.documentElement.removeAttribute('data-theme');
        themeIcon.classList.replace('fa-sun', 'fa-moon');
        localStorage.setItem(storageKey, 'dark');
      }
    };

    // Apply saved theme on load
    applyTheme(localStorage.getItem(storageKey));

    // Handle button click
    themeToggle.addEventListener('click', () => {
      const currentTheme = document.documentElement.hasAttribute('data-theme') ? 'light' : 'dark';
      applyTheme(currentTheme === 'light' ? 'dark' : 'light');
    });

  });
</script>