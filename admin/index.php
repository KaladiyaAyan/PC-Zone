<?php
session_start();

// 1️⃣ Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
  header("Location: pages/dashboard.php");
  exit;
}

// 2️⃣ Include your DB connection
//    Make sure this path is correct from the location of login.php
include  './includes/db_connect.php';

$error = '';

// 3️⃣ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->bind_result($id, $hash, $role);
  // echo $stmt . $id . $hash . $role;
  if ($stmt->fetch()) {

    if (password_verify($password, $hash) && $role === 'admin') {
      // ✅ Successful login
      $_SESSION['user_id']   = $id;
      $_SESSION['username']  = $username;
      $_SESSION['user_role'] = $role;
      header("Location: pages/dashboard.php");
      exit;
    } else {
      $error = 'Incorrect password or you are not an admin.';
    }
  } else {
    $error = 'Username not found.';
  }

  $stmt->close();
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