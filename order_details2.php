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
    echo "<div class='error-banner'>Invalid order ID.</div>";
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
            header("Location: order_details2.php?order_id=$order_id");
            exit();
        } else {
            echo "<div class='notification'>Error updating status.</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='notification'>Invalid status selected.</div>";
    }
}

echo "<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f5f5;
        margin: 0;
        padding: 20px;
    }
    .order-container {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        padding: 25px;
        max-width: 900px;
        margin: 0 auto;
    }
    .order-header {
        border-bottom: 2px dashed #e0e0e0;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    .order-header h2 {
        color: #333;
        margin-top: 0;
        font-size: 24px;
    }
    .detail-card {
        background: #f9f9f9;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    .detail-item {
        flex: 1;
        min-width: 200px;
    }
    .detail-item strong {
        display: block;
        margin-bottom: 5px;
        color: #555;
        font-size: 14px;
    }
    .detail-item p {
        margin: 0;
        font-size: 16px;
        color: #222;
    }
    .status-form {
        background: #f0f7ff;
        padding: 15px;
        border-radius: 8px;
        margin: 20px 0;
    }
    .status-form select, .status-form button {
        padding: 8px 12px;
        border-radius: 5px;
        border: 1px solid #ddd;
        font-size: 14px;
    }
    .status-form button {
        background: #4a6fa5;
        color: white;
        border: none;
        cursor: pointer;
        transition: background 0.3s;
        margin-left: 10px;
    }
    .status-form button:hover {
        background: #3a5a8a;
    }
    .items-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 20px;
    }
    .items-table th {
        background: #4a6fa5;
        color: white;
        padding: 12px;
        text-align: left;
    }
    .items-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }
    .items-table tr:nth-child(even) {
        background: #f9f9f9;
    }
    .items-table tr:hover {
        background: #f0f0f0;
    }
    .error-banner, .notification {
        padding: 15px;
        border-radius: 5px;
        margin: 20px auto;
        max-width: 900px;
        text-align: center;
    }
    .error-banner {
        background: #ffebee;
        border-left: 4px solid #f44336;
    }
    .notification {
        background: #fff8e1;
        border-left: 4px solid #ffc107;
    }
    .divider {
        height: 1px;
        background: linear-gradient(to right, transparent, #ddd, transparent);
        margin: 25px 0;
    }
</style>";

// Fetch order main details
$order_query = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();
$stmt->close();

if ($order) {
    echo "<div class='order-container'>";
    echo "<div class='order-header'>";
    echo "<h2>🎮 Order #" . htmlspecialchars($order['id']) . " Details</h2>";
    echo "</div>";
    
    echo "<div class='detail-card'>";
    echo "<div class='detail-item'>";
    echo "<strong>Customer</strong>";
    echo "<p>" . htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) . "</p>";
    echo "</div>";
    
    echo "<div class='detail-item'>";
    echo "<strong>Order Date</strong>";
    echo "<p>" . htmlspecialchars($order['date']) . "</p>";
    echo "</div>";
    
    echo "<div class='detail-item'>";
    echo "<strong>Order Total</strong>";
    echo "<p>RM " . number_format($order['total_price'], 2) . "</p>";
    echo "</div>";
    
    echo "<div class='detail-item'>";
    echo "<strong>Current Status</strong>";
    echo "<p><span style='padding: 4px 8px; border-radius: 12px; background: #e0e0e0;'>" . htmlspecialchars($order['status_order']) . "</span></p>";
    echo "</div>";
    echo "</div>"; // close detail-card

    // Status update form
    echo "<div class='status-form'>";
    echo "<form method='post' action='order_details2.php?order_id=" . $order_id . "'>";
    echo "<label for='status_order' style='margin-right: 10px;'>Update Order Status:</label>";
    echo "<select name='status_order' id='status_order'>";
    echo "<option value='Pending' " . ($order['status_order'] == 'Pending' ? 'selected' : '') . ">Pending</option>";
    echo "<option value='Processing' " . ($order['status_order'] == 'Processing' ? 'selected' : '') . ">Processing</option>";
    echo "<option value='Completed' " . ($order['status_order'] == 'Completed' ? 'selected' : '') . ">Completed</option>";
    echo "<option value='Cancelled' " . ($order['status_order'] == 'Cancelled' ? 'selected' : '') . ">Cancelled</option>";
    echo "</select>";
    echo "<button type='submit' name='update_status'>💾 Save Status</button>";

    echo "</form>";
    echo "</div>";

    echo "<div class='divider'></div>";
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='order_admin.php' style='display: inline-block; padding: 10px 20px; background-color: #4a6fa5; color: white; border-radius: 5px; text-decoration: none; font-weight: bold;'>← Back to Orders</a>";
    echo "</div>";
    // Fetch items from order_items table
    $items_query = "SELECT product_name, price_items, quantity_items, image_items FROM items_ordered WHERE order_id = ?";
    $stmt = $conn->prepare($items_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items_result = $stmt->get_result();

    if ($items_result->num_rows > 0) {
        echo "<h3 style='color: #333;'>🛒 Order Items</h3>";
        echo "<table class='items-table'>";
        echo "<tr><th>Product</th><th>Quantity</th><th>Price</th></tr>";
        while ($item = $items_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($item['product_name']) . "</td>";
            echo "<td>" . htmlspecialchars($item['quantity_items']) . "</td>";
            echo "<td>RM " . number_format($item['price_items'], 2) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='notification'>No items found for this order.</div>";
    }

    $stmt->close();
    echo "</div>"; // close order-container

} else {
    echo "<div class='error-banner'>Order not found.</div>";
}

$conn->close();
?>