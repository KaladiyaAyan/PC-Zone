<?php
session_start();
require('./includes/db_connect.php');
require('./includes/functions.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - PCZone</title>
  <?php require('./includes/header-link.php') ?>
  <link rel="stylesheet" href="assets/css/about-us.css">
</head>

<body>
  <?php
  require('./includes/navbar.php');
  ?>
  <header class="page-header">
    <h1>About PCZone</h1>
    <p>Your trusted destination for PC components, accessories, and custom builds.</p>
  </header>

  <main class="content-section">
    <h2>Who We Are</h2>
    <p>
      PCZone is dedicated to providing high-quality computer components and accessories.
      From processors and graphics cards to storage, memory, and peripherals, we ensure
      that every product meets the performance and reliability needs of modern computing.
    </p>

    <h2>Our Mission</h2>
    <p>
      Our mission is to make PC building and upgrading accessible, affordable, and enjoyable.
      We focus on delivering authentic products, transparent pricing, and excellent customer
      support to help you get the most out of your system.
    </p>

    <h2>Why Choose Us</h2>
    <p>
      At PCZone, we offer a wide range of trusted brands, detailed product specifications,
      and an easy-to-use platform for browsing and purchasing. Whether you're a gamer,
      content creator, or casual user, PCZone is here to power your computing journey.
    </p>
  </main>

  <?php include './includes/footer.php'; ?>
  <?php include './includes/footer-link.php'; ?>
</body>

</html>