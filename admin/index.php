<?php
session_start();

// 1️⃣ Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
  header("Location: pages/dashboard.php");
  exit;
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Admin Login</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <div class="login-main-container">
    <div class="login-container">
      <h2>Admin Panel Login</h2>

      <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form method="POST" action="">
        <label>Username:</label>
        <input type="text" name="username" required autofocus>

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
      </form>
    </div>
  </div>
</body>

</html>