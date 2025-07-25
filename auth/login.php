<?php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $pass  = $_POST['password'];

  $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && password_verify($pass, $user['password_hash'])) {
    session_start();
    $_SESSION['user'] = $user;
    header('Location: ../pages/home.php');
    exit;
  } else {
    $error = 'Invalid credentials';
  }
}
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
  <h2>Login</h2>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>
  <form method="post" class="w-50">
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button class="btn btn-primary">Login</button>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>