<?php
// File: pcparts-admin-panel/index.php
// session_start();

// If not logged in, send to login
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
//   header("Location: login.php");
//   exit;
// }

// Already logged in as admin: go to dashboard
// header("Location: pages/dashboard.php");
// exit;
?>


<?php
session_start();
include('./includes/db_connect.php');

// ini_set('display_errors', 1);
// error_reporting(E_ALL);
error_reporting(0);

$page = 'login';
$msg = '';
if (isset($_POST['login'])) {
  $adminuser = mysqli_real_escape_string($conn, $_POST['username']);
  $password = $_POST['password'];

  $query = mysqli_query($conn, "SELECT id, password, role FROM users WHERE role = 'admin' AND username = '$adminuser'");
  $user = mysqli_fetch_assoc($query);

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    header('Location: ./dashboard.php');
    exit;
  } else {
    $msg = "Invalid username or password.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PC ZONE Admin</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="./assets/vendor/bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="./assets/vendor/fontawesome/css/all.min.css">

  <style>
    body {
      background-color: #f8f9fa;
    }

    .login-box {
      max-width: 400px;
      margin: 60px auto;
      padding: 30px;
      border-radius: 8px;
      background: #ffffff;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    .login-logo a {
      font-size: 1.8rem;
      font-weight: bold;
      color: #1565c0;
      text-decoration: none;
    }

    .login-box-msg {
      font-size: 1.1rem;
      margin-bottom: 1.5rem;
      text-align: center;
    }

    .login-btn {
      color: #f8f9fa;
      background-color: #1565c0;
      border-color: #1565c0;
    }

    .login-btn:hover {
      color: #f8f9fa;
      background-color: #0d47a1;
    }
  </style>
</head>

<!-- <body class="hold-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <a href=""><b>PC ZONE</b> Administrator</a>
    </div>
<div class="login-box-body">
  <p class="login-box-msg">Log In to start your session</p>

  <form method="POST">

    <?php if ($msg) {
      // echo "<div class='alert alert-danger alert-dismissible'>
      //           <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
      //           <h4><i class='icon fa fa-info-circle'></i> Alert!</h4>
      //           $msg
      //         </div>";
    }  ?>

    <div class="form-group has-feedback">
      <input type="text" class="form-control" name="username" placeholder="Username">
      <span class="glyphicon glyphicon-user form-control-feedback"></span>
    </div>
    <div class="form-group has-feedback">
      <input type="password" class="form-control" name="password" placeholder="Password">
      <span class="glyphicon glyphicon-lock form-control-feedback"></span>
    </div>
    <div class="row">

      <div class="col-xs-4">
        <button type="submit" name="login" class="btn btn-success btn-block btn-flat">Login</button>
      </div>
    </div>
  </form>
  <a href="forgotpw.php">I forgot my password</a><br>

</div>
</div>

jQuery 3
<script src="./assets/vendor/jquery/jquery-3.7.1.min.js"></script>
Bootstrap 5
<script src="./assets/vendor/bootstrap/js/bootstrap.min.js"></script>
</body> -->

<body>

  <div class="login-box">
    <div class="login-logo text-center mb-3">
      <a href="#"><b>PC ZONE</b> Administrator</a>
    </div>

    <p class="login-box-msg">Log in to start your session</p>

    <form method="POST">

      <?php if ($msg): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong>Alert!</strong> <?= $msg ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <div class="mb-3">
        <input type="text" name="username" class="form-control" placeholder="Username" required>
      </div>

      <div class="mb-3">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
      </div>

      <div class="d-grid mb-3">
        <button type="submit" name="login" class="btn login-btn">Login</button>
      </div>

      <div class="text-center">
        <a href="forgotpw.php">I forgot my password</a>
      </div>

    </form>
  </div>

  <script src="./assets/vendor/jquery/jquery-3.7.1.min.js"></script>
  <script src="./assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
// if (isset($_POST['login'])) {
//   echo "<pre>";
//   print_r($_POST);
//   print_r($_SESSION);
//   echo "</pre>";
// }
?>