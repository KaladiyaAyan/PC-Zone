<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>

  <!-- Remix Icon CDN -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">

  <style>
    <?php include('./assets/css/login_signup.css'); ?>
  </style>
</head>

<body>

  <div class="signup-container">
    <h2>Create Account</h2>

    <form action="verify.php" method="POST">
      <div class="input-group">
        <label for="username"><i class="ri-user-line"></i> Username</label>
        <input type="text" id="username" name="username" placeholder="Enter Your Username" required>
      </div>

      <div class="input-group">
        <label for="email"><i class="ri-mail-line"></i> Email</label>
        <input type="email" id="email" name="email" placeholder="Enter Email Address" required>
      </div>

      <div class="input-group">
        <label for="password"><i class="ri-lock-line"></i> Password</label>
        <div class="password-field">
          <input type="password" id="password" name="password" placeholder="Enter Your Password" required>
          <div class="show-hide-password"><i class="ri-eye-line"></i></div>
        </div>
      </div>

      <div class="input-group">
        <label for="cpassword"><i class="ri-lock-line"></i> Confirm Password</label>
        <input type="password" id="cpassword" name="cpassword" placeholder="Confirm Your Password" required>
      </div>

      <button type="submit" name="signup">Sign Up</button>
    </form>

    <div class="have-account">
      Already have an account? <a href="login.php">Login</a>
    </div>
  </div>

  <script>
    const passwordInput = document.getElementById('password');
    const showHidePassword = document.querySelector('.show-hide-password');

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
  </script>
</body>

</html>