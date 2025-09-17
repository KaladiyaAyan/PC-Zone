<?php
// admin/login.php
session_start();
require('../includes/db_connect.php');
require('../includes/functions.php');


if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
  header('Location: index.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if (!$email || !$password) {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'All fields are required');
    header('Location: login.php');
    exit;
  }

  $sql = "SELECT user_id, username, email, password, role, status FROM users WHERE email = ? LIMIT 1";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "s", $email);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);

  if (!$res || mysqli_num_rows($res) === 0) {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Admin account not found');
    header('Location: login.php');
    exit;
  }

  $row = mysqli_fetch_assoc($res);

  if ($row['status'] !== 'active') {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Admin account is disabled');
  } elseif ($row['role'] !== 'admin') {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'You are not an admin');
  } elseif (!password_verify($password, $row['password'])) {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Invalid admin credentials');
  } else {

    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_user'] = [
      'id' => (int)$row['user_id'],
      'username' => $row['username'] ?? '',
      'email' => $row['email']
    ];
    $_SESSION['role'] = 'admin';
    mysqli_stmt_close($stmt);

    message('popup-success', '<i class="ri-check-line"></i>', 'Welcome ' . $_SESSION['admin_user']['username']);
    header('Location: index.php');
    exit;
  }

  mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PC ZONE — Admin Login</title>
  <link rel="stylesheet" href="./assets/css/style.css">
  <link rel="stylesheet" href="../fonts/remixicon.css">
</head>

<body>
  <?php include('./includes/alert.php') ?>

  <div class="login-main-container">
    <div class="login-container">
      <h2>Admin Panel Login</h2>

      <form action="" method="POST" autocomplete="off" novalidate>
        <label for="email">Email:</label>
        <input id="email" type="email" name="email" required autofocus>

        <label for="password">Password:</label>
        <input id="password" type="password" name="password" required>

        <button type="submit" name="admin_login">Login</button>
      </form>

      <div style="margin-top:12px; color:#fff;">
        <a href="/login.php" style="color:#cfcfcf;">Back to site login</a>
      </div>
    </div>
  </div>

  <?php include('./includes/footer-link.php') ?>
</body>

</html>