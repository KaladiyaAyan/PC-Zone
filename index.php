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
    /* Custom PC Build Section */
    .custom-build-section {
      background: linear-gradient(135deg, #f8f9ff 0%, #e8f0ff 100%);
      border-radius: 20px;
      padding: 3rem 2rem;
      margin: 3rem 0;
    }

    .build-card {
      background: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      height: 100%;
    }

    .build-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .build-card img {
      width: 100%;
      height: 250px;
      object-fit: cover;
    }

    .build-info {
      padding: 2rem;
    }

    .brand-logo {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 1.2rem;
      color: white;
      margin-bottom: 1rem;
    }

    .intel-brand {
      background: linear-gradient(45deg, #0071c5, #0096d6);
    }

    .amd-brand {
      background: linear-gradient(45deg, #ed1c24, #ff6b35);
    }

    .price-tag {
      background: linear-gradient(45deg, #28a745, #20c997);
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 25px;
      font-weight: bold;
      display: inline-block;
      margin: 1rem 0;
    }

    /* Categories Section */
    .categories-section {
      padding: 4rem 0;
      background: #fff;
    }

    .category-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 12px;
      padding: 2rem 1.5rem;
      text-align: center;
      transition: all 0.3s ease;
      height: 100%;
      text-decoration: none;
      color: inherit;
    }

    .category-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      border-color: #007bff;
      text-decoration: none;
      color: inherit;
    }

    .category-icon {
      width: 80px;
      height: 80px;
      margin: 0 auto 1rem;
      background: linear-gradient(45deg, #667eea, #764ba2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      color: white;
    }

    .category-name {
      font-weight: 600;
      font-size: 1.1rem;
      margin-bottom: 0.5rem;
    }

    .product-count {
      color: #6c757d;
      font-size: 0.9rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .custom-build-section {
        padding: 2rem 1rem;
        margin: 2rem 0;
      }

      .build-info {
        padding: 1.5rem;
      }

      .build-card img {
        height: 200px;
      }
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