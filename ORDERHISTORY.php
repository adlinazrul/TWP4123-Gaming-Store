<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: custlogin.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch orders
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();

$orders = [];
while ($order = $orders_result->fetch_assoc()) {
    // Fetch items for each order
    $order_id = $order['id'];
    $items_sql = "SELECT * FROM items_ordered WHERE order_id = ?";
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();

    $order['items'] = [];
    while ($item = $items_result->fetch_assoc()) {
        $order['items'][] = $item;
    }

    $orders[] = $order;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS | Order History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        <?php include 'ORDERHISTORY_styles.css'; // Optional: move the CSS here ?>
    </style>
</head>
<body>
    <header>
        <nav class="nav-menu">
            <div class="icons"><i class="fas fa-bars"></i></div>
            <div class="logo">NEXUS</div>
            <div class="nav-links">
                <a href="index.html">HOME</a>
                <a href="NINTENDO.html">NINTENDO</a>
                <a href="XBOX.html">CONSOLES</a>
            </div>
            <div class="icons">
                <a href="custlogin.html"><i class="fas fa-user"></i></a>
                <a href="ADDTOCART.html"><i class="fas fa-shopping-cart"></i></a>
            </div>
        </nav>
    </header>

    <div class="order-history-container">
        <h1 class="section-title">ORDER HISTORY</h1>
        <a href="index.html" class="back-button"><i class="fas fa-arrow-left"></i> BACK</a>

        <?php if (empty($orders)): ?>
            <p>No orders found.</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-item">
                    <div class="order-header">
                        <div>
                            <span class="order-id">ORDER #NEX-<?= $order['id'] ?></span>
                        </div>
                        <span class="order-status"><?= htmlspecialchars($order['status']) ?></span>
                    </div>

                    <?php foreach ($order['items'] as $item): ?>
                        <div class="product-details" style="margin-top: 10px;">
                            <img src="<?= htmlspecialchars($item['image_items']) ?>" class="product-image">
                            <div>
                                <h3><?= htmlspecialchars($item['product_name']) ?></h3>
                                <p>RM<?= number_format($item['price_items'], 2) ?></p>
                                <p>Quantity: <?= $item['quantity_items'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="order-actions">
                        <button class="buy-again" onclick="location.href='VIEWPRODUCT.php?id=<?= $order['items'][0]['id'] ?>'">BUY AGAIN</button>
                    </div>

                    <div class="order-total">
                        TOTAL: RM<?= number_format($order['total_amount'], 2) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <footer>
        <div class="footer-links">
            <a href="#about">ABOUT</a>
            <a href="#contact">CONTACT</a>
            <a href="TOS.html">TERMS</a>
        </div>
        <div class="copyright">
            &copy; 2025 NEXUS GAMING STORE
        </div>
    </footer>
</body>
</html>
