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
    $allowed_statuses = ['Pending', 'Completed']; // Removed Processing & Cancelled

    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE orders SET status_order = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        if ($stmt->execute()) {
            header("Location: order_details.php?order_id=$order_id");
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
    .divider {
        height: 1px;
        background: linear-gradient(to right, transparent, #ddd, transparent);
        margin: 25px 0;
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
    echo "<div class='detail-item'><strong>Customer Name</strong><p>" . htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) . "</p></div>";
    echo "<div class='detail-item'><strong>Email</strong><p>" . htmlspecialchars($order['email']) . "</p></div>";
    echo "<div class='detail-item'><strong>Phone Number</strong><p>" . htmlspecialchars($order['phone_number']) . "</p></div>";
    echo "</div>";

    echo "<div class='detail-card'>";
    echo "<div class='detail-item'><strong>Street Address</strong><p>" . htmlspecialchars($order['street_address']) . "</p></div>";
    echo "<div class='detail-item'><strong>City</strong><p>" . htmlspecialchars($order['city']) . "</p></div>";
    echo "<div class='detail-item'><strong>State</strong><p>" . htmlspecialchars($order['state']) . "</p></div>";
    echo "<div class='detail-item'><strong>Postcode</strong><p>" . htmlspecialchars($order['postcode']) . "</p></div>";
    echo "<div class='detail-item'><strong>Country</strong><p>" . htmlspecialchars($order['country']) . "</p></div>";
    echo "</div>";

    echo "<div class='detail-card'>";
    echo "<div class='detail-item'><strong>Order Date</strong><p>" . htmlspecialchars($order['date']) . "</p></div>";
    echo "<div class='detail-item'><strong>Total</strong><p>RM " . number_format($order['total_price'], 2) . "</p></div>";
    echo "<div class='detail-item'><strong>Status</strong><p><span style='padding: 4px 8px; border-radius: 12px; background: #e0e0e0;'>" . htmlspecialchars($order['status_order']) . "</span></p></div>";
    echo "</div>";

    // Status update form
    echo "<div class='status-form'>";
    echo "<form method='post' action='order_details.php?order_id=" . $order_id . "'>";
    echo "<label for='status_order' style='margin-right: 10px;'>Update Order Status:</label>";
    echo "<select name='status_order' id='status_order'>";
    $statuses = ['Pending', 'Completed']; // Only these two statuses
    foreach ($statuses as $status) {
        $selected = $order['status_order'] == $status ? 'selected' : '';
        echo "<option value='$status' $selected>$status</option>";
    }
    echo "</select>";
    echo "<button type='submit' name='update_status'>💾 Save Status</button>";
    echo "</form>";
    echo "</div>";

    echo "<div class='divider'></div>";
    echo "<a href='order.php' style='display: inline-block; padding: 10px 20px; background-color: #4a6fa5; color: white; border-radius: 5px; text-decoration: none;'>← Back to Orders</a>";
    echo "</div>";
} else {
    echo "<div class='error-banner'>Order not found.</div>";
}
$conn->close();
?>
