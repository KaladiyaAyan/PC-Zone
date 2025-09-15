<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// admin auth
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ./login.php');
    exit;
}

if (isset($_GET['product'])) {
    if (!isset($_GET['product']) || !is_numeric($_GET['product'])) {
        header("Location: product.php?delete=invalid");
        exit;
    }

    $product_id = (int) $_GET['product'];

    /* Fetch filenames (if product exists) */
    $stmt = mysqli_prepare($conn, "SELECT main_image, image_1, image_2, image_3 FROM products WHERE product_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $product_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$row) {
        header("Location: product.php?delete=invalid");
        exit;
    }

    /* Collect non-empty filenames */
    $files = [];
    foreach (['main_image', 'image_1', 'image_2', 'image_3'] as $c) {
        if (!empty($row[$c])) $files[] = $row[$c];
    }

    /* Delete product specs (optional cleanup) */
    $ds = mysqli_prepare($conn, "DELETE FROM product_specs WHERE product_id = ?");
    mysqli_stmt_bind_param($ds, 'i', $product_id);
    mysqli_stmt_execute($ds);
    mysqli_stmt_close($ds);

    /* Delete product row */
    $dp = mysqli_prepare($conn, "DELETE FROM products WHERE product_id = ?");
    mysqli_stmt_bind_param($dp, 'i', $product_id);
    mysqli_stmt_execute($dp);
    $affected = mysqli_stmt_affected_rows($dp);
    mysqli_stmt_close($dp);

    if ($affected > 0) {
        // remove files (uploads preferred, then assets)
        foreach ($files as $fname) {
            $p1 = __DIR__ . '/../uploads/' . $fname;
            $p2 = __DIR__ . '/../assets/images/products/' . $fname;
            if (file_exists($p1)) @unlink($p1);
            elseif (file_exists($p2)) @unlink($p2);
        }
        header("Location: product.php?delete=success");
        exit;
    } else {
        header("Location: product.php?delete=failed");
        exit;
    }
} else if (isset($_GET['category'])) {
    $id = (int) $_GET['category'];

    if (!$id) {
        header("Location: categories.php?delete=not_found");
        exit;
    }

    $product_check = mysqli_query($conn, "SELECT product_id FROM products WHERE category_id = $id LIMIT 1");

    if (mysqli_num_rows($product_check) > 0) {
        $updSql = "UPDATE products SET is_active = 0 WHERE category_id = $id";
        mysqli_query($conn, $updSql);
    }

    $delSql = "DELETE FROM categories WHERE category_id = $id";
    mysqli_query($conn, $delSql);

    header("Location: categories.php?success=Successfully deleted category and deactivated products");
    exit;
} else if (isset($_GET['brand'])) {

    $id = intval($_GET['brand']);

    // Check if any products use this brand before deleting
    $product_check = mysqli_query($conn, "SELECT product_id FROM products WHERE brand_id = $id LIMIT 1");
    if (mysqli_num_rows($product_check) > 0) {
        // Deactivate products in this specific category
        $updSql = "UPDATE products SET is_active = 0 WHERE brand_id = $id";
        mysqli_query($conn, $updSql);
    }

    $delete = mysqli_query($conn, "DELETE FROM brands WHERE brand_id = $id");
    if ($delete) {
        header("Location: brands.php?success=deleted");
        exit;
    } else {
        header("Location: brands.php?error=delete_failed");
        exit;
    }
}



// If no action, redirect to admin dashboard
header('Location: index.php');
exit;
