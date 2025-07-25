<?php
// File: pcparts-admin-panel/index.php
session_start();

// If not logged in, send to login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: login.php");
  exit;
}

// Already logged in as admin: go to dashboard
header("Location: pages/dashboard.php");
exit;
?>

<?php $current = basename($_SERVER['PHP_SELF']); ?>
<?php include __DIR__ . '/includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
  <h1>Dashboard</h1>
  <p>Here you’ll view and process customer orders.</p>
</main>

<script src="./assets/js/script.js"></script>
</body>

</html>