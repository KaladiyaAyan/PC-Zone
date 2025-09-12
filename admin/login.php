<?php
// admin/login.php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// If already authenticated for admin, go to admin dashboard
if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
  header('Location: index.php');
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($email === '' || $password === '') {
    $error = 'Fill required fields';
  } else {
    // Prepared statement to fetch user
    $sql = "SELECT user_id, username, email, password, role, status FROM users WHERE email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($res && mysqli_num_rows($res) === 1) {
      $row = mysqli_fetch_assoc($res);

      if ($row['status'] !== 'active') {
        $error = 'Account is inactive';
      } elseif ($row['role'] !== 'admin') {
        $error = 'Not an admin account';
      } elseif (!password_verify($password, $row['password'])) {
        $error = 'Invalid admin credentials';
      } else {
        // Successful admin authentication
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = [
          'id' => (int)$row['user_id'],
          'username' => $row['username'] ?? '',
          'email' => $row['email']
        ];
        // Keep role consistent across site
        $_SESSION['role'] = 'admin';
        // (Optional) also set site-level user_id if you want unified session
        $_SESSION['user_id'] = (int)$row['user_id'];

        mysqli_stmt_close($stmt);
        header('Location: index.php'); // admin/index.php
        exit;
      }
    } else {
      $error = 'Account not found';
    }
    mysqli_stmt_close($stmt);
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PC ZONE — Admin Login</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <div class="login-main-container">
    <div class="login-container">
      <h2>Admin Panel Login</h2>

      <?php if ($error): ?>
        <div class="alert alert-danger" role="alert" style="margin-bottom:1rem;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

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
</body>

</html>