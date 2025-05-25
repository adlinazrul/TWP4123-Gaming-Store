<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);

    // Fetch order main details
    $order_query = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_result = $stmt->get_result();
    $order = $order_result->fetch_assoc();
    $stmt->close();

    if ($order) {
        echo "<p><strong>Order ID:</strong> " . htmlspecialchars($order['id']) . "</p>";
        echo "<p><strong>Customer:</strong> " . htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) . "</p>";
        echo "<p><strong>Date:</strong> " . htmlspecialchars($order['date']) . "</p>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($order['status_order']) . "</p>";
        echo "<p><strong>Total Price:</strong> RM " . number_format($order['total_price'], 2) . "</p>";
        echo "<hr>";
    }

    // Fetch items from order_items table
    $items_query = "SELECT  product_name, price_items, quantity_items, image_items FROM items_ordered WHERE order_id = ?";
    

    $stmt = $conn->prepare($items_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items_result = $stmt->get_result();

    if ($items_result->num_rows > 0) {
        echo "<h3>Items</h3>";
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr><th style='border-bottom: 1px solid #ccc;'>Product</th><th style='border-bottom: 1px solid #ccc;'>Quantity</th><th style='border-bottom: 1px solid #ccc;'>Price</th></tr>";
        while ($item = $items_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($item['product_name']) . "</td>";
            echo "<td>" . htmlspecialchars($item['quantity_items']) . "</td>";
            echo "<td>RM " . number_format($item['price_items'], 2) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No items found for this order.</p>";
    }

    $stmt->close();
} else {
    echo "<p style='color:red;'>Invalid order ID.</p>";
}

$conn->close();
?>
