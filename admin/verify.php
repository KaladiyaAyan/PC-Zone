<?php
session_start();
require('../includes/db_connect.php');
require('../includes/functions.php');

if (isset($_POST['update_address'])) {
  $user_id = $_SESSION['user_id'];

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
