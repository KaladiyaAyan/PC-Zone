<?php
require_once '../config/config.php';
$pageTitle = 'Register - ' . SITE_NAME;

if (isLoggedIn()) {
  redirect('../index.php');
}

$error = '';
$success = '';

if ($_POST) {
  $name = sanitizeInput($_POST['name']);
  $email = sanitizeInput($_POST['email']);
  $password = $_POST['password'];
  $confirmPassword = $_POST['confirm_password'];
  $phone = sanitizeInput($_POST['phone']);

  if ($password !== $confirmPassword) {
    $error = 'Passwords do not match.';
  } else {
    if (registerUser($name, $email, $password, $phone)) {
      $success = 'Registration successful! You can now login.';
    } else {
      $error = 'Registration failed. Email might already exist.';
    }
  }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h3>Register</h3>
        </div>
        <div class="card-body">
          <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
          <?php endif; ?>
          <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
          <?php endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label for="name" class="form-label">Full Name</label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
              <label for="phone" class="form-label">Phone</label>
              <input type="tel" class="form-control" id="phone" name="phone">
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
              <label for="confirm_password" class="form-label">Confirm Password</label>
              <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
            <a href="login.php" class="btn btn-link">Already have an account? Login</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>