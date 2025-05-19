<?php
session_start();
include "db_connect1.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['quantity'])) {
    $product_id = (int) $_POST['product_id'];
    $new_quantity = max(1, (int) $_POST['quantity']);

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
        $stmt->execute();
        $stmt->close();
    } else {
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['product_id'] == $product_id) {
                    $item['quantity'] = $new_quantity;
                    break;
                }
            }
        }
    }
}

header("Location: ADDTOCART.php");
exit;
