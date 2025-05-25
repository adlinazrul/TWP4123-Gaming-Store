<?php
// order_items.php

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    echo "<p>Invalid order ID.</p>";
    exit;
}

$order_id = intval($_GET['order_id']);

// DB connection
$conn = new mysqli("localhost", "root", "", "gaming_store");
if ($conn->connect_error) {
    echo "<p>Database connection failed.</p>";
    exit;
}

// Get all items for the order
$sql = "SELECT product_name, price_items, quantity_items, image_items, status_order 
        FROM order_items 
        WHERE order_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    echo '<table border="1" cellpadding="8" cellspacing="0" style="width:100%;">';
    echo '<thead><tr>
            <th>Product</th>
            <th>Image</th>
            <th>Price (RM)</th>
            <th>Quantity</th>
            <th>Status</th>
          </tr></thead><tbody>';

    while ($item = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($item['product_name']) . '</td>';
        echo '<td>';
        if (!empty($item['image_items'])) {
            // Assuming images are stored in 'uploads/' folder
            $imgPath = 'uploads/' . htmlspecialchars($item['image_items']);
            echo '<img src="' . $imgPath . '" alt="Product Image" style="width:60px;height:auto;">';
        } else {
            echo 'No image';
        }
        echo '</td>';
        echo '<td>' . number_format($item['price_items'], 2) . '</td>';
        echo '<td>' . intval($item['quantity_items']) . '</td>';
        echo '<td>' . htmlspecialchars($item['status_order']) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
} else {
    echo "<p>No items found for this order.</p>";
}

$stmt->close();
$conn->close();
?>
