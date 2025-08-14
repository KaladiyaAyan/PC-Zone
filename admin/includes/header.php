<header class="admin-header ">
  <div class="left-section">
    <button id="hamburger" class="hamburger">
      <i class="fas fa-bars"></i>
    </button>
    <a href="./dashboard.php">PC Parts Admin</a>
  </div>

  <div class=" dropdown">
    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
      <img src="../assets/images/admin.jpg" alt="User Image" width="36" height="36" class="rounded-circle me-2">
      <span class="fw-semibold">Saitama</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end text-center" aria-labelledby="userDropdown">
      <li class="p-3">
        <img src="../assets/images/admin.jpg" alt="User Image" width="70" height="70" class="rounded-circle mb-2">
        <p class="mb-0 fw-semibold">Saitama</p>
        <small class="text-muted">Web Developer</small>
      </li>
      <li>
        <hr class="dropdown-divider">
      </li>
      <li><a class="dropdown-item" href="profile.php">Profile</a></li>
      <li><a class="dropdown-item" href="change-password.php">Change Password</a></li>
      <li>
        <a class="dropdown-item text-danger" href="logout.php">
          <i class="fa fa-power-off me-2"></i>Logout
        </a>
      </li>
    </ul>
  </div>
</header>