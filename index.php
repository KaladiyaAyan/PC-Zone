<?php
require_once __DIR__ . '../config/config.php';
require_once __DIR__ . '../includes/db_connect.php';
require_once __DIR__ . '../includes/functions.php';

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PC ZONE - Home</title>
  <meta name="description" content="PC ZONE - pre-built PCs, custom builds and premium components.">
  <link rel="stylesheet" href="./assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="./assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="./assets/css/style.css">

  <style>
    /* Custom PC build cards */
    .build-card-modern {
      border: 1px solid var(--bs-border-color);
      border-radius: 1rem;
      overflow: hidden;
      transition: transform .18s ease, box-shadow .18s ease;
      background: #fff;
    }

    .build-card-modern:hover {
      transform: translateY(-2px);
      box-shadow: 0 1rem 2rem rgba(0, 0, 0, .08);
    }

    .build-card-modern .brand-badge {
      position: absolute;
      top: .75rem;
      left: .75rem;
      backdrop-filter: blur(6px);
    }

    .build-card-modern .price {
      font-weight: 700;
    }

    .build-card-modern .specs li {
      display: flex;
      align-items: center;
      gap: .5rem;
      margin: .25rem 0;
      font-size: .95rem;
    }

    .build-card-modern .custom-build-img {
      aspect-ratio: 16/9;
      padding: 12px 40px;
    }
  </style>
</head>

<body>


  <?php
  require_once __DIR__ . '../includes/navbar.php';
  include './pages/home.php';
  ?>


  <script src="./assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>