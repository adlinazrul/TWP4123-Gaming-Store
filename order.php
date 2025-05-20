<?php
session_start();
require_once "db_connect1.php";

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Invalid order ID.");
}

$order_id = (int)$_GET['order_id'];

// Fetch the order details
$order_sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($order_sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    die("Order not found.");
}

$order = $order_result->fetch_assoc();

// Fetch the items for this order
$items_sql = "SELECT * FROM items_ordered WHERE order_id = ?";
$stmt_items = $conn->prepare($items_sql);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();

function formatAddress($order) {
    $parts = [];
    if (!empty($order['street_address'])) $parts[] = $order['street_address'];
    if (!empty($order['city'])) $parts[] = $order['city'];
    if (!empty($order['state'])) $parts[] = $order['state'];
    if (!empty($order['postcode'])) $parts[] = $order['postcode'];
    if (!empty($order['country'])) $parts[] = $order['country'];
    return implode(", ", $parts);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Order Details - NEXUS</title>
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
      padding: 20px;
    }
    header {
      background: #0a0118;
      padding: 15px;
      max-width: 900px;
      margin: 0 auto 20px;
      border-radius: 6px;
    }
    h1, h2 {
      color: var(--primary);
    }
    .order-info, .items {
      background: rgba(255,255,255,0.05);
      padding: 20px;
      margin-bottom: 30px;
      border-radius: 8px;
      max-width: 900px;
      margin-left: auto;
      margin-right: auto;
    }
    .item {
      display: flex;
      gap: 15px;
      margin-bottom: 15px;
      align-items: center;
      border-bottom: 1px solid #333;
      padding-bottom: 10px;
    }
    .item:last-child {
      border-bottom: none;
    }
    .item img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 5px;
      border: 1px solid #444;
    }
    .item-details {
      flex-grow: 1;
    }
    .item-details div {
      margin-bottom: 5px;
    }
    .total, .tax, .grand-total {
      text-align: right;
      font-weight: bold;
      max-width: 900px;
      margin-left: auto;
      margin-right: auto;
    }
    a.back-link {
      display: inline-block;
      margin: 10px auto 30px;
      color: var(--primary);
      text-decoration: none;
      font-weight: bold;
      max-width: 900px;
      margin-left: auto;
      margin-right: auto;
    }
    a.back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<header>
  <h1>Order Details</h1>
  <a href="orderhistory.php" class="back-link">&larr; Back to Order History</a>
</header>

<section class="order-info">
  <h2>Order #<?= htmlspecialchars($order['id']) ?></h2>
  <p><strong>Customer Name:</strong> <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></p>
  <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
  <p><strong>Contact Number:</strong> <?= htmlspecialchars($order['phone_number']) ?></p>
  <p><strong>Shipping Address:</strong> <?= nl2br(htmlspecialchars(formatAddress($order))) ?></p>
  <p><strong>Order Date:</strong> <?= date('d M Y, h:i A', strtotime($order['date'])) ?></p>
  <p><strong>Status:</strong> <?= htmlspecialchars($order['status_order']) ?></p>
</section>

<section class="items">
  <h2>Items Ordered</h2>
  <?php if ($items_result->num_rows > 0): ?>
    <?php while ($item = $items_result->fetch_assoc()): ?>
      <div class="item">
        <img src="<?= htmlspecialchars($item['image_items']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" />
        <div class="item-details">
          <div><strong>Product:</strong> <?= htmlspecialchars($item['product_name']) ?></div>
          <div><strong>Quantity:</strong> <?= $item['quantity_items'] ?></div>
          <div><strong>Price per item:</strong> RM<?= number_format($item['price_items'], 2) ?></div>
          <div><strong>Subtotal:</strong> RM<?= number_format($item['price_items'] * $item['quantity_items'], 2) ?></div>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No items found for this order.</p>
  <?php endif; ?>
</section>

<div class="total">Total Price: RM<?= number_format($order['total_price'], 2) ?></div>
<div class="tax">Tax Fee: RM<?= number_format($order['tax_fee'], 2) ?></div>
<div class="grand-total">Grand Total: RM<?= number_format($order['total_price'] + $order['tax_fee'], 2) ?></div>

</body>
</html>
