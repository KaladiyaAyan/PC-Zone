<?php
session_start();
include('../includes/db_connect.php');

// if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
//   header("Location: pages/dashboard.php");
//   exit;
// }
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PC ZONE Admin</title>

  <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

  <div class="login-main-container">
    <div class="login-container">
      <h2>Admin Panel Login</h2>

      <form action="../verify.php" method="POST">
        <label>Email:</label>
        <input type="text" name="email" autocomplete="off" required autofocus>

        <label>Password:</label>
        <input type="password" name="password" autocomplete="off" required>


        <button type="submit" name="login">Login</button>
        <!-- <div class="text-center text-white">
          <a href="./login.php">I forgot my password</a>
        </div> -->
      </form>
    </div>
  </div>
</body>

</html>