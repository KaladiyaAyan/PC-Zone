<?php
if (!isset($pageTitle)) $pageTitle = SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $pageTitle; ?></title>
  <link href="./assets/css/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="./assets/css/custom.css" rel="stylesheet">
  <link rel="stylesheet" href="./assets/fonts/fontawesome/css/all.min.css">
</head>

<body>


  <?php include 'navbar.php'; ?>