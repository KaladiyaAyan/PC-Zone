<?php
session_start();
include('includes/functions.php');
include('includes/db_connect.php');

if (isset($_POST['signup'])) {
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);
  $cpassword = mysqli_real_escape_string($conn, $_POST['cpassword']);

  if (empty($username) || empty($email) || empty($password) || empty($cpassword)) {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'All fields are required');
    header('Location: signup.php');
    exit;
  }

  if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$/", $email)) {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Incorrect email or password');
    header('Location: signup.php');
    exit;
  }

  if ($cpassword != $password) {
    message('popup-error', '<i class="ri-close-line"></i>', 'Password Not Matched');
    header('Location: signup.php');
    exit;
  }

  $password = password_hash($password, PASSWORD_BCRYPT);

  $check_email = "SELECT * FROM users WHERE email = '$email'";
  $check_email_run = mysqli_query($conn, $check_email);

  if (mysqli_num_rows($check_email_run) == 0) {
    // enforce user role on signup
    $insert = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', 'user')";
    $insert_run = mysqli_query($conn, $insert);

    if ($insert_run) {
      message('popup-success', '<i class="ri-check-line"></i>', 'Account created successfully');
      header("Location: login.php");
      exit;
    } else {
      message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Sign Up Unsuccessful.');
      header('Location: signup.php');
      exit;
    }
  } else {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Account already registered.');
    header('Location: signup.php');
    exit;
  }
} else if (isset($_POST['login'])) {
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);

  if (empty($email) || empty($password)) {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'All fields are required');
    header('Location: login.php');
    exit;
  }

  if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$/", $email)) {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Incorrect email or password');
    header('Location: login.php');
    exit;
  }

  $check_email_run = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' AND status='active' LIMIT 1");

  if (!$check_email_run) {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Incorrect Email or Password');
    header('Location: login.php');
    exit;
  }

  $row = mysqli_fetch_assoc($check_email_run);
  $db_password = $row['password'];
  $password_check = password_verify($password, $db_password);

  if (!$password_check) {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Incorrect Email or Password');
    header('Location: login.php');
    exit;
  }

  $_SESSION['user_id'] = (int)$row['user_id'];
  $_SESSION['user'] = [
    "id" => $row['user_id'],
    "username" => $row['username'] ?? '',
    "email" => $row['email']
  ];
  $_SESSION['role'] = $row['role'];

  if ($row['role'] === 'admin') {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_user'] = [
      'id' => (int)$row['user_id'],
      'username' => $row['username'] ?? '',
      'email' => $row['email']
    ];
    $_SESSION['role'] = 'admin';

    message('popup-success', '<i class="ri-check-line"></i>', 'Welcome ' . $_SESSION['admin_user']['username']);
    header("Location: admin/index.php");
    exit;
  } else {
    message('popup-success', '<i class="ri-check-line"></i>', 'Welcome ' . $_SESSION['user']['username']);
    header("Location: ./index.php");
    exit;
  }
} else if (isset($_POST['update_profile'])) {
  $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
  $dob      = mysqli_real_escape_string($conn, $_POST['dob'] ?? '');
  $gender   = mysqli_real_escape_string($conn, $_POST['gender'] ?? '');
  $password = mysqli_real_escape_string($conn, $_POST['password'] ?? '');

  $user_id = $_SESSION['user_id'];

  if ($password === '') {
    $update_user = "
      UPDATE users
      SET username='$username',
          date_of_birth='$dob',
          gender='$gender'
      WHERE user_id=$user_id
    ";
  } else {
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $update_user = "
      UPDATE users
      SET username='$username',
          password='$hashed',
          date_of_birth='$dob',
          gender='$gender'
      WHERE user_id=$user_id
    ";
  }

  $ok = mysqli_query($conn, $update_user);

  if ($ok) {
    message('popup-success', '<i class="ri-check-line"></i>', 'Profile Updated Successfully');
    header("Location: account.php");
    exit;
  } else {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Profile Update Failed');
    header("Location: account.php");
    exit;
  }
} else if (isset($_POST['update_address'])) {
  $user_id = $_SESSION['user_id'];

  $full_name     = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
  $address_phone = mysqli_real_escape_string($conn, $_POST['address_phone'] ?? '');
  $address1      = mysqli_real_escape_string($conn, $_POST['address1'] ?? '');
  $address2      = mysqli_real_escape_string($conn, $_POST['address2'] ?? '');
  $city          = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
  $state         = mysqli_real_escape_string($conn, $_POST['state'] ?? '');
  $zip           = mysqli_real_escape_string($conn, $_POST['zip'] ?? '');
  $country       = mysqli_real_escape_string($conn, $_POST['country'] ?? '');

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

  header("Location: account.php");
  exit();
}
