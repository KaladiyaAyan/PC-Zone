<?php
session_start();

// Site settings
define('SITE_NAME', 'PC-Zone');
define('SITE_URL', 'http://localhost/project/PCZONE/PC-Zone/');
define('UPLOAD_PATH', 'uploads/');
define('PRODUCTS_PER_PAGE', 12);

// Get the root directory path
$rootPath = dirname(__DIR__); // This gets the parent directory of config folder

// Include database connection
require_once __DIR__ . '/database.php';
require_once $rootPath . '/includes/functions.php';
