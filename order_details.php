<?php
include('db_connection.php');

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Invalid order ID.");
}

$order_id = (int)$_GET['order_id'];

// Get order and user info
$sql_order = "SELECT orders.id, users.username, orders.order_date, orders.total_amount
              FROM orders 
              JOIN users ON orders.user_id = users.id 
              WHERE orders.id = $order_id";

$result_order = mysqli_query($conn, $sql_order);

if (!$result_order || mysqli_num_rows($result_order) === 0) {
    die("Order not found.");
}

$order = mysqli_fetch_assoc($result_order);

// Get order items
$sql_items = "SELECT order_items.quantity, order_items.price, products.name AS product_name
              FROM order_items 
              JOIN products ON order_items.product_id = products.id 
              WHERE order_items.order_id = $order_id";

$result_items = mysqli_query($conn, $sql_items);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 60%; margin-top: 20px; }
        th, td { border: 1px solid #aaa; padding: 8px; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>Order Details</h1>

    <p><strong>Order ID:</strong> <?= $order['id'] ?></p>
    <p><strong>Customer:</strong> <?= htmlspecialchars($order['username']) ?></p>
    <p><strong>Order Date:</strong> <?= $order['order_date'] ?></p>
    <p><strong>Total Amount:</strong> RM <?= number_format($order['total_amount'], 2) ?></p>

    <h2>Items</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price (RM)</th>
                <th>Subtotal (RM)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grand_total = 0;
            while ($item = mysqli_fetch_assoc($result_items)): 
                $subtotal = $item['quantity'] * $item['price'];
                $grand_total += $subtotal;
            ?>
            <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item['price'], 2) ?></td>
                <td><?= number_format($subtotal, 2) ?></td>
            </tr>
            <?php endwhile; ?>
            <tr>
                <td colspan="3" align="right"><strong>Grand Total:</strong></td>
                <td><strong>RM <?= number_format($grand_total, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>

    <br>
    <a href="order.php">← Back to Orders</a>
</body>
</html>
