<?php
session_start();
include('./includes/db_connect.php');

// Disable error reporting for production
error_reporting(0);

$msg = '';

// Handle login form submission
if (isset($_POST['login'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];

  // Simple query to get admin user
  $query = "SELECT id, password, role FROM users WHERE role = 'admin' AND username = '$username'";
  $result = mysqli_query($conn, $query);

  if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);

    // Verify password
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_role'] = $user['role'];
      header('Location: ./dashboard.php');
      exit;
    } else {
      $msg = "Invalid username or password.";
    }
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
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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