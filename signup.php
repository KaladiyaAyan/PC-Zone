<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PC ZONE — Signup</title>
  <?php include('./includes/header-link.php'); ?>
  <link rel="stylesheet" href="./assets/css/auth.css">
</head>

<body>
  <?php include('./includes/alert.php') ?>

  <div class="auth-container">
    <h2>Create Account</h2>
    <form action="verify.php" method="POST" autocomplete="off" novalidate>
      <div class="auth-group">
        <label for="username"><i class="ri-user-line"></i> Username</label>
        <input type="text" id="username" name="username" placeholder="Choose a username" autofocus required>
      </div>
      <div class="auth-group">
        <label for="email"><i class="ri-mail-line"></i> Email</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required>
      </div>
      <div class="auth-group">
        <label for="password"><i class="ri-lock-line"></i> Password</label>
        <div class="password-field">
          <input type="password" id="password" name="password" placeholder="Enter password" required>
          <div class="show-hide-password"><i class="ri-eye-line"></i></div>
        </div>
      </div>
      <div class="auth-group">
        <label for="cpassword"><i class="ri-lock-2-line"></i> Confirm Password</label>
        <div class="password-field">
          <input type="password" id="cpassword" name="cpassword" placeholder="Confirm password" required>
          <div class="show-hide-cpassword"><i class="ri-eye-line"></i></div>
        </div>
      </div>
      <button type="submit" name="signup" class="btn btn-gradient w-100">Signup</button>
    </form>
    <div class="auth-link">
      Already have an account? <a href="login.php">Login</a>
    </div>
  </div>

  <script>
    (function() {
      // toggle main password
      const pw = document.getElementById('password');
      const togglePw = document.querySelector('.show-hide-password');
      if (togglePw && pw) {
        togglePw.addEventListener('click', () => {
          const icon = togglePw.querySelector('i');
          if (pw.type === 'password') {
            pw.type = 'text';
            icon.classList.replace('ri-eye-line', 'ri-eye-off-line');
          } else {
            pw.type = 'password';
            icon.classList.replace('ri-eye-off-line', 'ri-eye-line');
          }
        });
      }

      // toggle confirm password
      const cpw = document.getElementById('cpassword');
      const toggleCpw = document.querySelector('.show-hide-cpassword');
      if (toggleCpw && cpw) {
        toggleCpw.addEventListener('click', () => {
          const icon = toggleCpw.querySelector('i');
          if (cpw.type === 'password') {
            cpw.type = 'text';
            icon.classList.replace('ri-eye-line', 'ri-eye-off-line');
          } else {
            cpw.type = 'password';
            icon.classList.replace('ri-eye-off-line', 'ri-eye-line');
          }
        });
      }
    })();
  </script>

  <?php include('./includes/footer-link.php'); ?>
</body>

</html>