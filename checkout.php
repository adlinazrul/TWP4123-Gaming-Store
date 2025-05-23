<?php
session_start();
include 'db_connection.php';

$orderPlaced = false;
$error = "";
$taxRate = 0.06;
$cart_items = $_SESSION['cart'] ?? [];

function calculateSubtotal($cart) {
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += $item['product_price'] * $item['quantity'];
    }
    return $subtotal;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    // Retrieve form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $street_address = $_POST['street_address'];
    $postcode = $_POST['postcode'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $country = 'Malaysia';
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $card_number = $_POST['card_number'];
    $cardholder_name = $_POST['cardholder_name'];
    $expiry_date = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];

    $subtotal = calculateSubtotal($cart_items);
    $tax_fee = $subtotal * $taxRate;
    $total_price = $subtotal + $tax_fee;

    // Validate card
    $stmt = $conn->prepare("SELECT * FROM credit_card WHERE card_number = ? AND cardholder_name = ? AND expiry_date = ? AND cvv = ?");
    $stmt->bind_param("ssss", $card_number, $cardholder_name, $expiry_date, $cvv);
    $stmt->execute();
    $card_result = $stmt->get_result();

    if ($card_result->num_rows > 0) {
        // Insert into orders
        $insert_order = $conn->prepare("INSERT INTO orders (first_name, last_name, email, phone_number, street_address, city, state, postcode, country, card_number, cardholder_name, expiry_date, cvv, total_price, tax_fee, status_order, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Paid', NOW())");
        $insert_order->bind_param("ssssssssssssssd", $first_name, $last_name, $email, $phone, $street_address, $city, $state, $postcode, $country, $card_number, $cardholder_name, $expiry_date, $cvv, $total_price, $tax_fee);
        $insert_order->execute();
        $order_id = $insert_order->insert_id;

        // Insert each item
        foreach ($cart_items as $item) {
            $name = $item['product_name'];
            $price = $item['product_price'];
            $quantity = $item['quantity'];
            $image = $item['product_image'];
            $status = "Paid";
            $cust_name = "$first_name $last_name";
            $full_address = "$street_address, $postcode, $city, $state, $country";

            $insert_item = $conn->prepare("INSERT INTO items_ordered (order_id, product_name, price_items, quantity_items, image_items, status_order, name_cust, num_tel_cust, address_cust, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $insert_item->bind_param("isdisssss", $order_id, $name, $price, $quantity, $image, $status, $cust_name, $phone, $full_address);
            $insert_item->execute();
        }

        unset($_SESSION['cart']);
        $orderPlaced = true;
    } else {
        $error = "Invalid credit card. Please enter a valid card from our database.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <style>
        body { font-family: Arial; margin: 40px; }
        .section { margin-bottom: 40px; }
        .product { display: flex; align-items: center; margin-bottom: 10px; }
        .product img { width: 60px; height: 60px; object-fit: cover; margin-right: 10px; }
        input, select { padding: 8px; width: 100%; margin: 5px 0; }
        form { max-width: 600px; margin: auto; }
        .summary { background: #f9f9f9; padding: 20px; border-radius: 10px; }
        .totals { font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .success { color: green; font-weight: bold; text-align: center; }
    </style>
</head>
<body>

<?php if ($orderPlaced): ?>
    <p class="success">Order placed successfully!</p>
<?php else: ?>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="section summary">
        <h2>Order Summary</h2>
        <?php $subtotal = 0; ?>
        <?php foreach ($cart_items as $item): ?>
            <div class="product">
                <img src="<?= htmlspecialchars($item['product_image']) ?>" alt="Product Image">
                <div>
                    <div><strong><?= htmlspecialchars($item['product_name']) ?></strong></div>
                    <div>Quantity: <?= $item['quantity'] ?></div>
                    <div>Price: RM <?= number_format($item['product_price'], 2) ?></div>
                    <div>Subtotal: RM <?= number_format($item['product_price'] * $item['quantity'], 2) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php
            $subtotal = calculateSubtotal($cart_items);
            $tax = $subtotal * $taxRate;
            $total = $subtotal + $tax;
        ?>
        <p class="totals">Subtotal: RM <?= number_format($subtotal, 2) ?></p>
        <p class="totals">Shipping: Free</p>
        <p class="totals">Tax (6%): RM <?= number_format($tax, 2) ?></p>
        <p class="totals">Total: RM <?= number_format($total, 2) ?></p>
    </div>

    <form method="POST">
        <div class="section">
            <h2>Customer Info</h2>
            <input type="text" name="first_name" placeholder="First Name" required value="<?= $_SESSION['first_name'] ?? '' ?>">
            <input type="text" name="last_name" placeholder="Last Name" required value="<?= $_SESSION['last_name'] ?? '' ?>">
            <input type="email" name="email" placeholder="Email" required value="<?= $_SESSION['email'] ?? '' ?>">
            <input type="text" name="phone" placeholder="Phone Number" required value="<?= $_SESSION['phone'] ?? '' ?>">
            <input type="text" name="street_address" placeholder="Street Address" required value="<?= $_SESSION['address'] ?? '' ?>">
            <input type="text" name="postcode" placeholder="Postcode" required>
            <input type="text" name="city" placeholder="City" required>
            <input type="text" name="state" placeholder="State" required>
            <input type="text" name="country" value="Malaysia" readonly>
        </div>

        <div class="section">
            <h2>Credit Card Info</h2>
            <input type="text" name="card_number" placeholder="Card Number" required>
            <input type="text" name="cardholder_name" placeholder="Cardholder Name" required>
            <input type="date" name="expiry_date" placeholder="Expiry Date (YYYY-MM-DD)" required>
            <input type="text" name="cvv" placeholder="CVV" required>
        </div>

        <button type="submit" name="submit_order">Place Order</button>
    </form>
<?php endif; ?>

</body>
</html>
