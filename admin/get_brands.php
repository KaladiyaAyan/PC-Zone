<?php
require_once './includes/db_connect.php';

$categoryId = intval($_GET['category_id']);

$stmt = $conn->prepare("SELECT id, name FROM brands WHERE category_id = ?");
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$result = $stmt->get_result();

$brands = [];
while ($row = $result->fetch_assoc()) {
  $brands[] = $row;
}

echo json_encode($brands);
