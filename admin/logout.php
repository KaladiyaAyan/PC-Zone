<?php
// session_start();
// session_unset();
// session_destroy();
// header('location: login.php');


session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page
header("Location: index.php");
exit;
