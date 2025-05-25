<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['order_id'])) {
    echo "<p style='color:red;'>Invalid order ID.</p>";
    exit();
}

$order_id = intval($_GET['order_id']);

// Handle status update first
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status_order'];
    $allowed_statuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];

    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE orders SET status_order = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        if ($stmt->execute()) {
            // Redirect to avoid resubmission and reflect the update
            header("Location: order_details.php?order_id=$order_id");
            exit();
        } else {
            echo "<p>Error updating status.</p>";
        }
        $stmt->close();
    } else {
        echo "<p>Invalid status selected.</p>";
    }
}

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

    // Show current status
    echo "<p><strong>Current Status:</strong> " . htmlspecialchars($order['status_order']) . "</p>";

    // Status update form
    ?>
    <form method="post" action="order_details.php?order_id=<?= $order_id ?>">
      <label for="status_order">Change Status:</label>
      <select name="status_order" id="status_order">
        <option value="Pending" <?= ($order['status_order'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
        <option value="Processing" <?= ($order['status_order'] == 'Processing') ? 'selected' : '' ?>>Processing</option>
        <option value="Completed" <?= ($order['status_order'] == 'Completed') ? 'selected' : '' ?>>Completed</option>
        <option value="Cancelled" <?= ($order['status_order'] == 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
      </select>
      <button type="submit" name="update_status">Update Status</button>
    </form>
    <?php

    echo "<p><strong>Total Price:</strong> RM " . number_format($order['total_price'], 2) . "</p>";
    echo "<hr>";

    // Fetch items from order_items table
    $items_query = "SELECT product_name, price_items, quantity_items, image_items FROM items_ordered WHERE order_id = ?";
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
    echo "<p style='color:red;'>Order not found.</p>";
}

$conn->close();
?>
