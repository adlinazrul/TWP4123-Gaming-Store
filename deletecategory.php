<?php
include 'database.php';
$id = $_GET['id'];

$sql = "DELETE FROM product_categories WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: managecategory.php");
exit;
?>
