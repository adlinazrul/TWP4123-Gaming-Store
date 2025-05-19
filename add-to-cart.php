<?php
session_start();
include "db_connect1.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'] ?? 'default.jpg';
    $quantity = $_POST['quantity'];

    if (isset($_SESSION['user_id'])) {
        // Logged-in user: Save to database
        $user_id = $_SESSION['user_id'];

        // Check if item already in cart
        $stmt = $conn->prepare("SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update quantity
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $quantity, $user_id, $product_id);
        } else {
            // Insert new
            $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, product_name, product_price, product_image, quantity) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisdsi", $user_id, $product_id, $product_name, $product_price, $product_image, $quantity);
        }

        $stmt->execute();
    } else {
        // Guest: Save to session
        $item = [
            'product_id' => $product_id,
            'product_name' => $product_name,
            'product_price' => $product_price,
            'product_image' => $product_image,
            'quantity' => $quantity
        ];

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if item already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$cart_item) {
            if ($cart_item['product_id'] == $product_id) {
                $cart_item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = $item;
        }
    }

    // Redirect back to cart
    header("Location: ADDTOCART.php");
    exit();
}
?>
