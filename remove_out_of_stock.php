<?php
// remove_out_of_stock.php
require 'db_connect.php';

// Delete all cart items where the product is now out of stock
$sql = "
    DELETE ci FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE p.product_quantity = 0
";

if ($conn->query($sql) === TRUE) {
    echo "Out-of-stock items removed from all carts.";
} else {
    echo "Error removing items: " . $conn->error;
}

$conn->close();
?>
