<?php
session_start();
require_once "db_connect1.php";

$products = [];
$total = 0;
$tax = 0;
$grand_total = 0;

// Detect checkout source
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_now'])) {
    $_SESSION['checkout_source'] = 'single';

    $product_name = $_POST['product_name'];
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $image = $_POST['image'];

    $_SESSION['single_product'] = [
        'name' => $product_name,
        'price' => $price,
        'quantity' => $quantity,
        'image' => $image
    ];

    $product = $_SESSION['single_product'];
    $product['subtotal'] = $product['price'] * $product['quantity'];
    $products[] = $product;
} elseif (isset($_SESSION['checkout_source']) && $_SESSION['checkout_source'] === 'single') {
    $product = $_SESSION['single_product'];
    $product['subtotal'] = $product['price'] * $product['quantity'];
    $products[] = $product;
} elseif (isset($_SESSION['checkout_source']) && $_SESSION['checkout_source'] === 'cart') {
    $products = $_SESSION['cart_products'];
}

foreach ($products as $prod) {
    $total += $prod['price'] * $prod['quantity'];
}

$tax_rate = 0.06;
$tax = round($total * $tax_rate, 2);
$grand_total = round($total + $tax, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | NEXUS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        header { background: #000; color: #fff; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 24px; font-weight: bold; }
        .cancel-btn { background: red; color: white; border: none; padding: 10px 15px; cursor: pointer; font-size: 14px; }
        .checkout-container { display: flex; flex-wrap: wrap; max-width: 1000px; margin: 20px auto; background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .order-summary, .payment-section { flex: 1 1 45%; margin: 10px; }
        h2 { border-bottom: 2px solid #ddd; padding-bottom: 5px; }
        .order-item { display: flex; margin-bottom: 15px; }
        .order-item img { width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc; margin-right: 10px; }
        .item-name { font-weight: bold; }
        .total-row { display: flex; justify-content: space-between; margin: 10px 0; font-weight: 500; }
        .grand-total { font-size: 18px; color: #2d2d2d; font-weight: bold; border-top: 1px solid #ccc; padding-top: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .pay-now-btn { background: #28a745; color: white; padding: 12px 20px; border: none; font-size: 16px; cursor: pointer; border-radius: 5px; width: 100%; }
        footer { text-align: center; margin-top: 20px; color: #888; }
    </style>
</head>
<body>
<header>
    <div class="logo">NEXUS</div>
    <button class="cancel-btn" id="cancelCheckout"><i class="fas fa-times"></i> Cancel</button>
</header>

<main class="checkout-container">
    <section class="order-summary">
        <h2>ORDER SUMMARY</h2>
        <?php foreach ($products as $item): ?>
        <div class="order-item">
            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
            <div>
                <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                <div class="item-price">RM<?= number_format($item['price'], 2) ?></div>
                <div>Quantity: <?= $item['quantity'] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="total-row"><span>Subtotal:</span><span>RM<?= number_format($total, 2) ?></span></div>
        <div class="total-row"><span>Shipping:</span><span>FREE</span></div>
        <div class="total-row"><span>Tax (6%):</span><span>RM<?= number_format($tax, 2) ?></span></div>
        <div class="total-row grand-total"><span>Total:</span><span>RM<?= number_format($grand_total, 2) ?></span></div>
    </section>

    <section class="payment-section">
        <h2>PAYMENT DETAILS</h2>
        <form method="POST">
            <div class="form-group"><label>First Name</label><input type="text" name="first_name" required></div>
            <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required></div>
            <div class="form-group"><label>Street Address</label><input type="text" name="address" required></div>
            <div class="form-group"><label>City</label><input type="text" name="city" required></div>
            <div class="form-group"><label>State</label><input type="text" name="state" required></div>
            <div class="form-group"><label>Postcode</label><input type="text" name="postcode" required></div>
            <div class="form-group"><label>Country</label><input type="text" name="country" required></div>
            <div class="form-group"><label>Phone Number</label><input type="text" name="phone" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Card Number</label><input type="text" name="card_number" required></div>
            <div class="form-group"><label>Cardholder Name</label><input type="text" name="card_name" required></div>
            <div class="form-group"><label>Expiry Date</label><input type="text" name="expiry_date" required></div>
            <div class="form-group"><label>CVV</label><input type="text" name="cvv" required></div>

            <button type="submit" name="checkout" class="pay-now-btn">PAY RM<?= number_format($grand_total, 2) ?></button>
        </form>
    </section>
</main>

<footer><div>&copy; 2025 NEXUS GAMING STORE</div></footer>

<script>
document.getElementById("cancelCheckout").addEventListener("click", function () {
    if (confirm("Cancel checkout?")) {
        window.location.href = "index.php";
    }
});
</script>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $postcode = $_POST['postcode'];
    $country = $_POST['country'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $card_number = $_POST['card_number'];
    $card_name = $_POST['card_name'];
    $expiry_date = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];
    $date = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO orders (first_name, last_name, address, city, state, postcode, country, phone, email, card_number, card_name, expiry_date, cvv, total, tax, date) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssssssssssds", $first_name, $last_name, $address, $city, $state, $postcode, $country, $phone, $email, $card_number, $card_name, $expiry_date, $cvv, $grand_total, $tax, $date);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    foreach ($products as $item) {
        $stmt2 = $conn->prepare("INSERT INTO items_ordered (order_id, product_name, price_items, quantity_items, image_items, name_cust, num_tel_cust, address_cust, date) VALUES (?,?,?,?,?,?,?,?,?)");
        $full_name = $first_name . " " . $last_name;
        $stmt2->bind_param("isdisssss", $order_id, $item['name'], $item['price'], $item['quantity'], $item['image'], $full_name, $phone, $address, $date);
        $stmt2->execute();
    }

    // Clear session
    unset($_SESSION['checkout_source']);
    unset($_SESSION['single_product']);
    unset($_SESSION['cart_products']);

    echo "<script>alert('Order placed successfully!'); window.location.href='ORDERHISTORY.html';</script>";
}
?>
