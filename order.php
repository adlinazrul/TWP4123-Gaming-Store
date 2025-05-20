<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id']) || intval($_GET['order_id']) <= 0) {
    die("Invalid order ID.");
}
$order_id = intval($_GET['order_id']);

// Check if order exists in orders table
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
if ($order_result->num_rows == 0) {
    die("Order not found.");
}
$order = $order_result->fetch_assoc();
$stmt->close();

// Get ordered items from items_ordered table
$stmt = $conn->prepare("SELECT * FROM items_ordered WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
if ($items_result->num_rows == 0) {
    die("No items found for this order.");
}
$items = [];
while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

// Get admin profile image
$admin_id = $_SESSION['admin_id'];
$img_stmt = $conn->prepare("SELECT image FROM admin_list WHERE id = ?");
$img_stmt->bind_param("i", $admin_id);
$img_stmt->execute();
$img_stmt->bind_result($image);
$profile_image = ($img_stmt->fetch() && !empty($image)) ? 'image/' . $image : 'image/default_profile.jpg';
$img_stmt->close();

$conn->close();
?>
<!-- Your HTML output here... -->

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Order Details</title>
<!-- Your CSS and styles here -->
</head>
<body>
<!-- Sidebar and nav here -->

<main>
    <h1>Order Details for Order #<?= htmlspecialchars($order_id) ?></h1>
    
    <p><strong>Order Date:</strong> <?= htmlspecialchars($order['date']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($order['status_order']) ?></p>
    <p><strong>Total Price:</strong> RM <?= number_format($order['total_price'], 2) ?></p>

    <table border="1" cellpadding="10" cellspacing="0" style="width:100%;border-collapse:collapse;">
        <thead>
            <tr>
                <th>Image</th>
                <th>Product Name</th>
                <th>Price (RM)</th>
                <th>Quantity</th>
                <th>Total (RM)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            foreach ($items as $item):
                $item_total = $item['price_items'] * $item['quantity_items'];
            ?>
            <tr>
                <td><img src="<?= htmlspecialchars($item['image_items']) ?>" alt="Product Image" style="width:60px; height:60px; object-fit:cover;"></td>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= number_format($item['price_items'], 2) ?></td>
                <td><?= intval($item['quantity_items']) ?></td>
                <td><?= number_format($item_total, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</main>

</body>
</html>
