<?php
session_start();
require_once "db_connect1.php";

// Fetch all orders
$orders_sql = "SELECT * FROM orders ORDER BY date DESC";
$orders_result = $conn->query($orders_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>NEXUS | Order History</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root {
      --primary: #ff0000;
      --dark: #0d0221;
      --light: #ffffff;
    }

    body {
      font-family: sans-serif;
      background: var(--dark);
      color: var(--light);
      margin: 0;
    }

    header, footer {
      background: #0a0118;
      padding: 15px;
    }

    .nav-menu, .footer-links {
      display: flex;
      justify-content: space-between;
      max-width: 1000px;
      margin: 0 auto;
    }

    .logo {
      color: var(--primary);
      font-weight: bold;
      font-size: 1.5rem;
    }

    .order-history {
      max-width: 900px;
      margin: 30px auto;
      padding: 0 20px;
    }

    .order {
      background: rgba(255,255,255,0.05);
      padding: 20px;
      margin-bottom: 30px;
      border-left: 3px solid var(--primary);
      border-radius: 8px;
    }

    .order-header {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      font-weight: bold;
    }

    .product {
      display: flex;
      gap: 15px;
      margin-bottom: 10px;
      align-items: center;
    }

    .product img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 5px;
      border: 1px solid #444;
    }

    .product-info {
      flex-grow: 1;
    }

    .total {
      font-weight: bold;
      text-align: right;
      margin-top: 10px;
    }

    .no-orders {
      text-align: center;
      font-size: 18px;
      padding: 40px;
    }
  </style>
</head>
<body>

<header>
  <div class="nav-menu">
    <div class="logo">NEXUS</div>
  </div>
</header>

<main class="order-history">
  <h2>Your Order History</h2>
  <?php if ($orders_result->num_rows > 0): ?>
    <?php while ($order = $orders_result->fetch_assoc()): ?>
      <div class="order">
        <div class="order-header">
          <div>Order ID: <?= $order['id'] ?> | <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></div>
          <div><?= date('d M Y, h:i A', strtotime($order['date'])) ?></div>
        </div>
        <?php
          $order_id = $order['id'];
          $items_sql = "SELECT * FROM items_ordered WHERE order_id = $order_id";
          $items_result = $conn->query($items_sql);
        ?>
        <?php while ($item = $items_result->fetch_assoc()): ?>
          <div class="product">
            <img src="<?= htmlspecialchars($item['image_items']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
            <div class="product-info">
              <div><?= htmlspecialchars($item['product_name']) ?></div>
              <div>Quantity: <?= $item['quantity_items'] ?> | RM<?= number_format($item['price_items'], 2) ?></div>
            </div>
          </div>
        <?php endwhile; ?>
        <div class="total">Total: RM<?= number_format($order['total_price'], 2) ?> (Incl. RM<?= number_format($order['tax_fee'], 2) ?> Tax)</div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="no-orders">No orders found.</div>
  <?php endif; ?>
</main>

<footer>
  <div class="footer-links">
    <div>&copy; 2025 NEXUS GAMING STORE</div>
  </div>
</footer>

</body>
</html>
