<?php
session_start();
include('./functions/message.php');
include('./includes/db_connect.php');



if (isset($_POST['signup'])) {
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);
  $cpassword = mysqli_real_escape_string($conn, $_POST['cpassword']);


  if ($cpassword == $password) {
    $password = password_hash($password, PASSWORD_BCRYPT);

    $check_email = "SELECT * FROM users WHERE email = '$email'";
    $check_email_run = mysqli_query($conn, $check_email);


    if (mysqli_num_rows($check_email_run) == 0) {

      $insert = "INSERT INTO `users` (username, email, password) VALUES ('$username', '$email', '$password')";
      $insert_run = mysqli_query($conn, $insert);

      if ($insert_run) {
        message('popup-success', '<i class="ri-check-line"></i>', 'Account created successfully');
        header("Location: login.php");
      } else {
        message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Sign Up Unsuccessfull.');
        header('Location: signup.php');
      }
    } else {
      message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Account already registered.');
      header('Location: login.php');
    }
  } else {
    message('popup-error', '<i class="ri-close-line"></i>', 'Password Not Matched');
    header('Location: signup.php');
  }
} else if (isset($_POST['login'])) {

  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);

  $check_email = "SELECT * FROM users WHERE email = '$email'";
  $check_email_run = mysqli_query($conn, $check_email);

  if (mysqli_num_rows($check_email_run) == 1) {
    $row = mysqli_fetch_array($check_email_run);

    $db_password = $row['password'];
    $password_chack = password_verify($password, $db_password);
    if ($password_chack == 1) {
      if ($row['role'] == 'admin') {
        $_SESSION['user_role'] = 'admin';
        $_SESSION['admin'] = [
          "id" => $row['user_id'],
          "username" => $row['username'],
          "email" => $row['email'],
        ];
        message('popup-success', '<i class="ri-check-line"></i>', 'Login Successfully');
        header("Location: admin/index.php");
      } else {
        $_SESSION['user'] = [
          "id" => $row['user_id'],
          "username" => $row['username'],
          "email" => $row['email'],
        ];
        message('popup-success', '<i class="ri-check-line"></i>', 'Login Successfully');
        header("Location: index.php");
      }
    } else {
      message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Incorrect Password');
      header('Location: login.php');
    }
  } else {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Incorrect Email Address');
    header('Location: login1.php');
  }
} else if (isset($_POST['user-account'])) {

  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);
  $number = mysqli_real_escape_string($conn, $_POST['number']);

  if ($password == '') {
    $update_user = "UPDATE users SET username='$username', number='$number' WHERE email='$email'";
  } else {
    $password = password_hash($password, PASSWORD_BCRYPT);
    $update_user = "UPDATE users SET username='$username', password='$password', number='$number' WHERE email='$email'";
  }
  $update_user_run = mysqli_query($conn, $update_user);


  if ($update_user_run) {
    message('popup-success', '<i class="ri-check-line"></i>', 'Profile Updated Successfully');
    header("Location: index.php");
  } else {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Profile Update Failed');
    header("Location: user_profile.php");
  }

  $stname = mysqli_real_escape_string($conn, $_POST['stname']);
  $city = mysqli_real_escape_string($conn, $_POST['city']);
  $state = mysqli_real_escape_string($conn, $_POST['state']);
  $country = mysqli_real_escape_string($conn, $_POST['country']);
  $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
  $email = $_SESSION['user']['email'];

  $select_address = "SELECT * FROM user_address WHERE email='$email'";
  $select_address_run = mysqli_query($conn, $select_address);

  if (mysqli_num_rows($select_address_run) == 0) {
    $insert_address = "INSERT INTO user_address (email, street, city, state, country, pincode) VALUES ('$email', '$stname', '$city', '$state', '$country', '$pincode')";
    $insert_address_run = mysqli_query($conn, $insert_address);

    if ($insert_address_run) {
      message('popup-success', '<i class="ri-check-line"></i>', 'Address Updated Successfully');
      header("Location: profile.php");
    } else {
      message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Address Update Failed');
      header("Location: profile.php");
    }
  } else {
    $update_address = "UPDATE user_address SET street='$stname', city='$city', state='$state', country='$country', pincode='$pincode' WHERE email='$email'";
    $update_address_run = mysqli_query($conn, $update_address);

    if ($update_address_run) {
      message('popup-success', '<i class="ri-check-line"></i>', 'Address Updated Successfully');
      header("Location: profile.php");
    } else {
      message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Address Update Failed');
      header("Location: profile.php");
    }
  }
} else if (isset($_POST['checkout-address'])) {
  $number = mysqli_real_escape_string($conn, $_POST['number']);
  $email = $_SESSION['user']['email'];

  $update_user = "UPDATE users SET number='$number' WHERE email='$email'";
  $update_user_run = mysqli_query($conn, $update_user);

  if ($update_user_run) {
    message('popup-success', '<i class="ri-check-line"></i>', 'Profile Updated Successfully');
    header("Location: checkout.php");
  } else {
    message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Profile Update Failed');
    header("Location: checkout.php");
  }

  $stname = mysqli_real_escape_string($conn, $_POST['stname']);
  $city = mysqli_real_escape_string($conn, $_POST['city']);
  $state = mysqli_real_escape_string($conn, $_POST['state']);
  $country = mysqli_real_escape_string($conn, $_POST['country']);
  $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
  $email = $_SESSION['user']['email'];

  $select_address = "SELECT * FROM user_address WHERE email='$email'";
  $select_address_run = mysqli_query($conn, $select_address);

  if (mysqli_num_rows($select_address_run) == 0) {
    $insert_address = "INSERT INTO user_address (email, street, city, state, country, pincode) VALUES ('$email', '$stname', '$city', '$state', '$country', '$pincode')";
    $insert_address_run = mysqli_query($conn, $insert_address);

    if ($insert_address_run) {
      message('popup-success', '<i class="ri-check-line"></i>', 'Address Updated Successfully');
      header("Location: checkout.php");
    } else {
      message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Address Update Failed');
      header("Location: checkout.php");
    }
  } else {
    $update_address = "UPDATE user_address SET street='$stname', city='$city', state='$state', country='$country', pincode='$pincode' WHERE email='$email'";
    $update_address_run = mysqli_query($conn, $update_address);

    if ($update_address_run) {
      message('popup-success', '<i class="ri-check-line"></i>', 'Address Updated Successfully');
      header("Location: checkout.php");
    } else {
      message('popup-warning', '<i class="ri-error-warning-line"></i>', 'Address Update Failed');
      header("Location: checkout.php");
    }
  }
}
