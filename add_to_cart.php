<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to add items to cart',
        'redirect' => 'custlogin.php'  // Suggest redirect URL
    ]);
    exit;
}

// Validate input data
if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit;
}

$email = $_SESSION['email'];
$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);

// Validate quantity
if ($quantity <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Quantity must be at least 1'
    ]);
    exit;
}

// Begin transaction for atomic operations
$conn->begin_transaction();

try {
    // Get product details with FOR UPDATE to lock the row
    $stmt = $conn->prepare("SELECT id, product_name, product_price, product_quantity FROM products WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        throw new Exception("Product not found");
    }

    // Check stock availability
    if ($product['product_quantity'] < $quantity) {
        throw new Exception("Only {$product['product_quantity']} items available in stock");
    }

    // Check if item exists in cart
    $stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE email = ? AND product_id = ? FOR UPDATE");
    $stmt->bind_param("si", $email, $product_id);
    $stmt->execute();
    $cart_item = $stmt->get_result()->fetch_assoc();

    if ($cart_item) {
        // Update existing cart item
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        // Verify stock again with new quantity
        if ($new_quantity > $product['product_quantity']) {
            throw new Exception("Cannot add more than {$product['product_quantity']} items to cart");
        }

        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE email = ? AND product_id = ?");
        $stmt->bind_param("isi", $new_quantity, $email, $product_id);
        $stmt->execute();
    } else {
        // Insert new cart item
        $stmt = $conn->prepare("INSERT INTO cart_items (email, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $email, $product_id, $quantity);
        $stmt->execute();
    }

    // Get updated cart count
    $stmt = $conn->prepare("SELECT SUM(quantity) as cart_count FROM cart_items WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $cart_count = $stmt->get_result()->fetch_assoc()['cart_count'] ?? 0;

    // Update session
    $_SESSION['cart_count'] = $cart_count;

    // Commit transaction
    $conn->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => "{$product['product_name']} (×{$quantity}) added to cart successfully!",
        'cart_count' => $cart_count,
        'product' => [
            'id' => $product['id'],
            'name' => $product['product_name'],
            'price' => $product['product_price'],
            'quantity' => $quantity,
            'image' => $product['product_image'] ?? 'default.jpg'
        ]
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>