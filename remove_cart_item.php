<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$email = $_SESSION['email'];
$product_id = intval($data['product_id']);

// Delete from cart
$stmt = $conn->prepare("DELETE FROM cart_items WHERE email = ? AND product_id = ?");
$stmt->bind_param("si", $email, $product_id);
$stmt->execute();

echo json_encode(['success' => true]);
exit;
?>
