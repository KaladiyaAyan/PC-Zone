<?php
session_start();
require('../includes/db_connect.php');
require('../includes/functions.php');

$user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
if ($user_id <= 0) {
  header("Location: ../login.php");
  exit();
}

/* ---------- Update Profile ---------- */
if (isset($_POST['update_profile'])) {
  $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $dob = mysqli_real_escape_string($conn, $_POST['dob'] ?? '');
  $gender = mysqli_real_escape_string($conn, $_POST['gender'] ?? '');

  $sets = [];
  $sets[] = "username='$username'";

  if ($dob !== '') {
    $sets[] = "date_of_birth='$dob'";
  } else {
    $sets[] = "date_of_birth=NULL";
  }

  if ($gender !== '') {
    $sets[] = "gender='$gender'";
  } else {
    $sets[] = "gender=NULL";
  }

  if (!empty($password)) {
    if (function_exists('password_hash')) {
      $pw = password_hash($password, PASSWORD_DEFAULT);
    } else {
      $pw = md5($password);
    }
    $pw = mysqli_real_escape_string($conn, $pw);
    $sets[] = "password='$pw'";
  }

  // Profile image handling: save as assets/images/admin.<ext>
  if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_image'];
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 2 * 1024 * 1024; // 2MB

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file['size'] <= $max_size && in_array($ext, $allowed_ext)) {
      $assets_dir = __DIR__ . './assets/images/';
      if (!is_dir($assets_dir)) {
        mkdir($assets_dir, 0755, true);
      }

      // target name admin.<ext>
      $target_name = 'admin.' . $ext;
      $target_full = $assets_dir . $target_name;

      // if existing admin.* present (any extension) rename it to admin_old_TIMESTAMP.ext
      foreach (glob($assets_dir . 'admin.*') as $existing) {
        if (is_file($existing)) {
          $existing_ext = pathinfo($existing, PATHINFO_EXTENSION);
          $backup_name = 'admin_old_' . time() . '.' . $existing_ext;
          @rename($existing, $assets_dir . $backup_name);
        }
      }

      if (move_uploaded_file($file['tmp_name'], $target_full)) {
        $db_path = "./assets/images/" . $target_name; // path usable from admin pages
        $db_path = mysqli_real_escape_string($conn, $db_path);
        $sets[] = "profile_image='$db_path'";
      } else {
        message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Profile image upload failed.');
      }
    } else {
      message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Invalid profile image. Use JPG/PNG/GIF under 2MB.');
    }
  }

  $set_sql = implode(", ", $sets);
  $sql = "UPDATE users SET $set_sql WHERE user_id = $user_id LIMIT 1";
  $run = mysqli_query($conn, $sql);

  if ($run) {
    message('popup-success', '<i class="ri-check-line"></i>', 'Profile updated successfully');
  } else {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Profile update failed');
  }

  header("Location: settings.php");
  exit();
}

/* ---------- Update Address (existing code) ---------- */
if (isset($_POST['update_address'])) {
  $full_name = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
  $address_phone = mysqli_real_escape_string($conn, $_POST['address_phone'] ?? '');
  $address1 = mysqli_real_escape_string($conn, $_POST['address1'] ?? '');
  $address2 = mysqli_real_escape_string($conn, $_POST['address2'] ?? '');
  $city = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
  $state = mysqli_real_escape_string($conn, $_POST['state'] ?? '');
  $zip = mysqli_real_escape_string($conn, $_POST['zip'] ?? '');
  $country = mysqli_real_escape_string($conn, $_POST['country'] ?? '');

  $select_address = "SELECT * FROM user_address WHERE user_id=$user_id LIMIT 1";
  $res = mysqli_query($conn, $select_address);

  if (mysqli_num_rows($res) == 0) {
    $insert_address = "
      INSERT INTO user_address
      (user_id, full_name, phone, address_line1, address_line2, city, state, zip, country, is_default)
      VALUES
      ($user_id, '$full_name', '$address_phone', '$address1', '$address2', '$city', '$state', '$zip', '$country', 1)
      ";
    $run = mysqli_query($conn, $insert_address);

    if ($run) {
      message('popup-success', '<i class="ri-check-line"></i>', 'Address Saved Successfully');
    } else {
      message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Address Save Failed');
    }
  } else {
    $update_address = "
      UPDATE user_address
      SET full_name='$full_name',
      phone='$address_phone',
      address_line1='$address1',
      address_line2='$address2',
      city='$city',
      state='$state',
      zip='$zip',
      country='$country'
      WHERE user_id=$user_id
      ";
    $run = mysqli_query($conn, $update_address);

    if ($run) {
      message('popup-success', '<i class="ri-check-line"></i>', 'Address Updated Successfully');
    } else {
      message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Address Update Failed');
    }
  }

  header("Location: settings.php");
  exit();
}
