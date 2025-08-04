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
  <link rel="stylesheet" href="./assets/fontawesome/css/all.min.css">
  <link href="./assets/css/custom.css" rel="stylesheet">

  <style>
    /* Nested dropdown support */
    .dropdown-submenu {
      position: absolute;
      left: 100%;
      top: 0;
      margin-top: -0.5rem;
      min-width: 200px;
      border-radius: 0.375rem;
      display: none;
    }

    .dropdown:hover>.dropdown-submenu {
      display: block;
    }

    /* Mobile menu indentation */
    .offcanvas-body .list-unstyled .list-unstyled {
      padding-left: 1rem;
      border-left: 1px solid #dee2e6;
    }

    /* Ensure category icons are same size */
    .dropdown-menu img {
      width: 100px;
      height: 100px;
      object-fit: contain;
    }
  </style>

</head>

<body>


  <?php include 'navbar.php'; ?>