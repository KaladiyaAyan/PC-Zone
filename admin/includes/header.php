<!-- <!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PC Parts Admin</title>

  <link rel="stylesheet" href="./../assets/vendor/fontawesome/css/all.min.css">

  <link rel="stylesheet" href="./../assets/css/style.css">
</head>

<body> -->
<!-- <header class="admin-header">
  <div class="left-section">
    <button id="hamburger" class="hamburger">
      <i class="fas fa-bars"></i>
    </button>
    <a href="/admin/index.php">PC Parts Admin</a>
  </div>

  <div class="navbar-custom-menu">
    <ul class="nav navbar-nav">


      <li class="dropdown user user-menu">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
          <img src="dist/img/img-ad.jpg" class="user-image" alt="User Image">
          <span class="hidden-xs">Saitama</span>
        </a>
        <ul class="dropdown-menu">
          User image
          <li class="user-header">
            <img src="dist/img/img-ad.jpg" class="img-circle" alt="User Image">

            <p>
              Saitama - Web Developer
            </p>

          </li>

          <li class="user-footer">

            <div class="pull-left">
              <a href="profile.php" class="btn btn-default btn-flat">Profile</a>
              <a href="change-password.php" class="btn btn-default btn-flat">Change Password</a>
            </div>


            <div class="pull-right">
              <a href="logout.php" class="btn btn-default btn-flat"><i class="fa fa-power-off" style="color:red;"></i></a>
            </div>

          </li>
        </ul>
      </li>
      <li>
        <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
      </li>
    </ul>
  </div>
</header> -->

<header class="admin-header ">
  <div class="left-section">
    <button id="hamburger" class="hamburger">
      <i class="fas fa-bars"></i>
    </button>
    <a href="./dashboard.php">PC Parts Admin</a>
  </div>

  <div class=" dropdown">
    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
      <img src="./assets/images/admin.jpg" alt="User Image" width="36" height="36" class="rounded-circle me-2">
      <span class="fw-semibold">Saitama</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end text-center" aria-labelledby="userDropdown">
      <li class="p-3">
        <img src="./assets/images/admin.jpg" alt="User Image" width="70" height="70" class="rounded-circle mb-2">
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