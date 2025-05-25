<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$email = $_SESSION['email'];
$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);

if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
    exit;
}

// Get product info and stock
$stmt = $conn->prepare("SELECT product_name, product_price, product_image, product_quantity FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$product = $result->fetch_assoc();

// Check if item already in cart
$stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE email = ? AND product_id = ?");
$stmt->bind_param("si", $email, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update quantity but check stock limit
    $row = $result->fetch_assoc();
    $new_quantity = $row['quantity'] + $quantity;

    if ($new_quantity > $product['product_quantity']) {
        echo json_encode([
            'success' => false,
            'message' => "Only {$product['product_quantity']} items available in stock."
        ]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE email = ? AND product_id = ?");
    $stmt->bind_param("isi", $new_quantity, $email, $product_id);
    $stmt->execute();
} else {
    if ($quantity > $product['product_quantity']) {
        echo json_encode([
            'success' => false,
            'message' => "Only {$product['product_quantity']} items available in stock."
        ]);
        exit;
    }

    // Insert new row
    $stmt = $conn->prepare("INSERT INTO cart_items (email, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $email, $product_id, $quantity);
    $stmt->execute();
}

// Get updated cart count for the notification
$stmt = $conn->prepare("SELECT SUM(quantity) as cart_count FROM cart_items WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$cart_data = $result->fetch_assoc();

// Update session cart count
$_SESSION['cart_count'] = $cart_data['cart_count'] ?? 0;

// Return success response with all needed data
echo json_encode([
    'success' => true,
    'message' => "{$product['product_name']} added to cart",
    'cart_count' => $_SESSION['cart_count'],
    'product' => [
        'name' => $product['product_name'],
        'price' => $product['product_price'],
        'quantity' => $quantity
    ]
]);
exit;
?>