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
$quantity = intval($data['quantity']);

if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
    exit;
}

// Check product stock
$stmt = $conn->prepare("SELECT product_price, product_quantity FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}
$product = $result->fetch_assoc();

if ($quantity > $product['product_quantity']) {
    echo json_encode(['success' => false, 'message' => "Only {$product['product_quantity']} items available in stock."]);
    exit;
}

// Update cart
$stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE email = ? AND product_id = ?");
$stmt->bind_param("isi", $quantity, $email, $product_id);
$stmt->execute();

echo json_encode([
    'success' => true,
    'price' => $product['product_price']
]);
exit;
?>
