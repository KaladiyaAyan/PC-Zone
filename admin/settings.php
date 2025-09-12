<?php
include '../includes/db_connect.php';
include './includes/functions.php';

session_start();
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: ./login1.php');
  exit;
}


function h($s)
{
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// helper: get setting
function get_setting($conn, $key, $default = '')
{
  $stmt = mysqli_prepare($conn, "SELECT meta_value FROM settings WHERE meta_key = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, 's', $key);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);
  return $row ? $row['meta_value'] : $default;
}

// helper: set setting (insert or update)
function set_setting($conn, $key, $value)
{
  $stmt = mysqli_prepare($conn, "INSERT INTO settings (meta_key, meta_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)");
  mysqli_stmt_bind_param($stmt, 'ss', $key, $value);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
}

$current_page = 'settings';
$errors = [];
success:;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // basic site settings
  $site_name = trim($_POST['site_name'] ?? 'PCZone');
  $site_email = trim($_POST['site_email'] ?? '');
  $currency = trim($_POST['currency'] ?? 'INR');
  $tax_rate = trim($_POST['tax_rate'] ?? '0');
  $maintenance = isset($_POST['maintenance']) ? '1' : '0';

  // payment toggles
  $pay_cod = isset($_POST['pay_cod']) ? '1' : '0';
  $pay_card = isset($_POST['pay_card']) ? '1' : '0';
  $pay_upi = isset($_POST['pay_upi']) ? '1' : '0';

  // smtp
  $smtp_host = trim($_POST['smtp_host'] ?? '');
  $smtp_port = trim($_POST['smtp_port'] ?? '');
  $smtp_user = trim($_POST['smtp_user'] ?? '');
  $smtp_pass = trim($_POST['smtp_pass'] ?? '');
  $smtp_secure = trim($_POST['smtp_secure'] ?? '');

  // logo upload
  if (!empty($_FILES['site_logo']['name'])) {
    $f = $_FILES['site_logo'];
    if ($f['error'] === UPLOAD_ERR_OK) {
      $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
      $dstName = 'logo_' . time() . '.' . $ext;
      $dstPath = __DIR__ . '/../uploads/' . $dstName;
      if (!move_uploaded_file($f['tmp_name'], $dstPath)) {
        $errors[] = 'Failed to save logo upload.';
      } else {
        // save filename
        set_setting($conn, 'site_logo', $dstName);
      }
    } else {
      $errors[] = 'Logo upload error.';
    }
  }

  // persist settings
  set_setting($conn, 'site_name', $site_name);
  set_setting($conn, 'site_email', $site_email);
  set_setting($conn, 'currency', $currency);
  set_setting($conn, 'tax_rate', $tax_rate);
  set_setting($conn, 'maintenance_mode', $maintenance);

  set_setting($conn, 'pay_cod', $pay_cod);
  set_setting($conn, 'pay_card', $pay_card);
  set_setting($conn, 'pay_upi', $pay_upi);

  set_setting($conn, 'smtp_host', $smtp_host);
  set_setting($conn, 'smtp_port', $smtp_port);
  set_setting($conn, 'smtp_user', $smtp_user);
  set_setting($conn, 'smtp_pass', $smtp_pass);
  set_setting($conn, 'smtp_secure', $smtp_secure);

  // simple success marker
  $saved = true;
}

// load current values
$site_name = get_setting($conn, 'site_name', 'PCZone');
$site_email = get_setting($conn, 'site_email', '');
$currency = get_setting($conn, 'currency', 'INR');
$tax_rate = get_setting($conn, 'tax_rate', '0');
$maintenance = get_setting($conn, 'maintenance_mode', '0');

$pay_cod = get_setting($conn, 'pay_cod', '1');
$pay_card = get_setting($conn, 'pay_card', '1');
$pay_upi = get_setting($conn, 'pay_upi', '1');

$smtp_host = get_setting($conn, 'smtp_host', '');
$smtp_port = get_setting($conn, 'smtp_port', '');
$smtp_user = get_setting($conn, 'smtp_user', '');
$smtp_pass = get_setting($conn, 'smtp_pass', '');
$smtp_secure = get_setting($conn, 'smtp_secure', '');

$site_logo = get_setting($conn, 'site_logo', '');

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Settings • PCZone Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .small-muted {
      color: var(--text-muted);
      font-size: 13px
    }
  </style>
</head>

<body>
  <?php include '../includes/header.php';
  include '../includes/sidebar.php'; ?>
  <main class="main-content pt-5 mt-4">
    <div class="container-fluid mt-2">
      <div class="form-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h3 class="mb-0">Settings</h3>
          <div class="small-muted">Configure site, payments, SMTP and store defaults</div>
        </div>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <?php foreach ($errors as $err) echo '<div>' . h($err) . '</div>'; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($saved)): ?>
          <div class="alert alert-success">Settings saved.</div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="row g-3">
          <div class="col-12 col-lg-6">
            <div class="theme-card p-3">
              <h5>Site</h5>
              <div class="mb-2">
                <label class="form-label">Site name</label>
                <input name="site_name" class="form-control" value="<?= h($site_name) ?>">
              </div>
              <div class="mb-2">
                <label class="form-label">Email (from)</label>
                <input name="site_email" class="form-control" value="<?= h($site_email) ?>">
              </div>
              <div class="mb-2">
                <label class="form-label">Logo</label>
                <?php if ($site_logo): ?>
                  <div class="mb-2"><img src="../uploads/<?= h($site_logo) ?>" alt="logo" style="max-height:60px;border:1px solid var(--border-color);padding:6px;background:var(--bg-section)"></div>
                <?php endif; ?>
                <input type="file" name="site_logo" accept="image/*" class="form-control">
              </div>
              <div class="mb-2">
                <label class="form-label">Currency</label>
                <input name="currency" class="form-control" value="<?= h($currency) ?>">
              </div>
              <div class="mb-2">
                <label class="form-label">Tax rate (%)</label>
                <input name="tax_rate" type="number" step="0.01" class="form-control" value="<?= h($tax_rate) ?>">
              </div>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" id="maintenance" name="maintenance" <?= $maintenance === '1' ? 'checked' : '' ?>>
                <label class="form-check-label" for="maintenance">Maintenance mode</label>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="theme-card p-3 mb-3">
              <h5>Payments</h5>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="pay_cod" name="pay_cod" <?= $pay_cod === '1' ? 'checked' : '' ?>>
                <label class="form-check-label" for="pay_cod">Cash on delivery</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="pay_card" name="pay_card" <?= $pay_card === '1' ? 'checked' : '' ?>>
                <label class="form-check-label" for="pay_card">Card payments</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="pay_upi" name="pay_upi" <?= $pay_upi === '1' ? 'checked' : '' ?>>
                <label class="form-check-label" for="pay_upi">UPI</label>
              </div>
            </div>

            <div class="theme-card p-3">
              <h5>SMTP / Email</h5>
              <div class="mb-2">
                <label class="form-label">Host</label>
                <input name="smtp_host" class="form-control" value="<?= h($smtp_host) ?>">
              </div>
              <div class="mb-2">
                <label class="form-label">Port</label>
                <input name="smtp_port" class="form-control" value="<?= h($smtp_port) ?>">
              </div>
              <div class="mb-2">
                <label class="form-label">User</label>
                <input name="smtp_user" class="form-control" value="<?= h($smtp_user) ?>">
              </div>
              <div class="mb-2">
                <label class="form-label">Password</label>
                <input name="smtp_pass" class="form-control" value="<?= h($smtp_pass) ?>">
              </div>
              <div class="mb-2">
                <label class="form-label">Encryption (tls/ssl)</label>
                <input name="smtp_secure" class="form-control" value="<?= h($smtp_secure) ?>">
              </div>
            </div>
          </div>

          <div class="col-12 text-end">
            <button class="btn btn-primary">Save settings</button>
          </div>
        </form>
      </div>
    </div>
  </main>
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>