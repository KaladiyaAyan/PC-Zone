<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PC ZONE — Login</title>
  <?php include('./includes/header-link.php'); ?>
  <link rel="stylesheet" href="./assets/css/auth.css">
</head>

<body>
  <?php include('./includes/alert.php') ?>

  <div class="auth-container">
    <h2>Login</h2>
    <form action="verify.php" method="POST" autocomplete="off" novalidate>
      <div class="auth-group">
        <label for="email"><i class="ri-mail-line"></i> Email</label>
        <input type="email" id="email" name="email" placeholder="Enter Your Email" required autofocus>
      </div>
      <div class="auth-group">
        <label for="password"><i class="ri-lock-line"></i> Password</label>
        <div class="password-field">
          <input type="password" id="password" name="password" placeholder="Enter Your Password" required>
          <div class="show-hide-password"><i class="ri-eye-line"></i></div>
        </div>
      </div>
      <button type="submit" name="login" class="btn btn-gradient w-100">Login</button>
    </form>
    <div class="auth-link">
      Don't have an account? <a href="signup.php">Signup</a>
    </div>
  </div>

  <script>
    (function() {
      const passwordInput = document.getElementById('password');
      const showHidePassword = document.querySelector('.show-hide-password');
      if (showHidePassword && passwordInput) {
        showHidePassword.addEventListener('click', () => {
          const icon = showHidePassword.querySelector('i');
          if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.replace('ri-eye-line', 'ri-eye-off-line');
          } else {
            passwordInput.type = 'password';
            icon.classList.replace('ri-eye-off-line', 'ri-eye-line');
          }
        });
      }
    })();
  </script>

  <?php include('./includes/footer-link.php'); ?>
</body>

</html>