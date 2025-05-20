<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "gaming_store";

// Connect to database
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Validate order ID
if ($order_id <= 0) {
    echo "Invalid order ID.";
    exit;
}

// Fetch order details from orders table
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if order exists
if ($result->num_rows == 0) {
    echo "Order not found with ID: $order_id";
    exit;
}

$order = $result->fetch_assoc();

// Fetch items related to the order
$stmt_items = $conn->prepare("SELECT * FROM items_ordered WHERE order_id = ?");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f4f4f4;
        }
        h2 {
            color: #333;
        }
        table {
            width: 90%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 14px;
            text-align: center;
        }
        th {
            background-color: #555;
            color: #fff;
        }
    </style>
</head>
<body>
    <h2>Order Details for Order #<?= htmlspecialchars($order_id) ?></h2>

    <p><strong>Name:</strong> <?= htmlspecialchars($order['name_cust']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($order['num_tel_cust']) ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($order['address_cust']) ?></p>
    <p><strong>Order Date:</strong> <?= htmlspecialchars($order['date']) ?></p>

    <h3>Items Ordered:</h3>
    <table>
        <tr>
            <th>Product Image</th>
            <th>Name</th>
            <th>Quantity</th>
            <th>Price (RM)</th>
            <th>Status</th>
        </tr>
        <?php while ($item = $result_items->fetch_assoc()) { ?>
        <tr>
            <td><img src="<?= htmlspecialchars($item['image_items']) ?>" width="100" alt=""></td>
            <td><?= htmlspecialchars($item['product_name']) ?></td>
            <td><?= intval($item['quantity_items']) ?></td>
            <td>RM <?= number_format($item['price_items'], 2) ?></td>
            <td><?= htmlspecialchars($item['status_order']) ?></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>

<?php
// Close connections
$stmt->close();
$stmt_items->close();
$conn->close();
?>
