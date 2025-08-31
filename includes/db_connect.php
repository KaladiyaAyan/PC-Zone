<?php
require_once __DIR__ . '/../config/config.php';

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "pczone";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
