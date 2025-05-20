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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | NEXUS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rubik:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ff0000;
            --secondary: #d10000;
            --dark: #0d0221;
            --light: #ffffff;
            --accent: #ff3333;
        }
        
        body {
            font-family: 'Rubik', sans-serif;
            background-color: var(--dark);
            color: var(--light);
            margin: 0;
            padding: 0;
        }
        
        header {
            background: var(--dark);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(255, 0, 0, 0.3);
            border-bottom: 1px solid var(--primary);
        }
        
        .nav-menu {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
        }
        
        .logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            text-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
        }
        
        .cancel-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Orbitron', sans-serif;
            transition: all 0.3s ease;
        }
        
        .cancel-btn:hover {
            background: var(--accent);
            transform: translateY(-2px);
        }
        
        .checkout-container {
            display: flex;
            flex-wrap: wrap;
            max-width: 1400px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 15px 30px rgba(255, 0, 0, 0.1);
            gap: 30px;
        }
        
        .order-summary, .payment-section {
            flex: 1;
            min-width: 300px;
        }
        
        h2 {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .order-item {
            display: flex;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .order-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
            border: 1px solid rgba(255, 0, 0, 0.2);
        }
        
        .item-name {
            font-weight: bold;
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .item-price {
            color: var(--light);
            margin-bottom: 5px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .grand-total {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary);
            border-top: 2px solid var(--primary);
            padding-top: 15px;
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--light);
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            color: var(--light);
            font-family: 'Rubik', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(255, 0, 0, 0.2);
        }
        
        .pay-now-btn {
            background: var(--primary);
            color: white;
            padding: 15px;
            border: none;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .pay-now-btn:hover {
            background: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 0, 0, 0.3);
        }
        
        footer {
            background: #0a0118;
            padding: 30px;
            text-align: center;
            margin-top: 50px;
            border-top: 1px solid rgba(255, 0, 0, 0.1);
        }
        
        .copyright {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .checkout-container {
                flex-direction: column;
                margin: 20px;
                padding: 20px;
            }
            
            .order-summary, .payment-section {
                width: 100%;
            }
            
            .logo {
                font-size: 1.5rem;
            }
            
            .cancel-btn {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="nav-menu">
            <div class="logo">NEXUS</div>
            <button class="cancel-btn" id="cancelCheckout"><i class="fas fa-times"></i> Cancel</button>
        </nav>
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
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" required>
                </div>
                <div class="form-group">
                    <label>Street Address</label>
                    <input type="text" name="address" required>
                </div>
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" required>
                </div>
                <div class="form-group">
                    <label>State</label>
                    <input type="text" name="state" required>
                </div>
                <div class="form-group">
                    <label>Postcode</label>
                    <input type="text" name="postcode" required>
                </div>
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Card Number</label>
                    <input type="text" name="card_number" required>
                </div>
                <div class="form-group">
                    <label>Cardholder Name</label>
                    <input type="text" name="card_name" required>
                </div>
                <div class="form-group">
                    <label>Expiry Date</label>
                    <input type="text" name="expiry_date" placeholder="MM/YY" required>
                </div>
                <div class="form-group">
                    <label>CVV</label>
                    <input type="text" name="cvv" required>
                </div>

                <button type="submit" name="checkout" class="pay-now-btn">PAY RM<?= number_format($grand_total, 2) ?></button>
            </form>
        </section>
    </main>

    <footer>
        <div class="copyright">&copy; 2025 NEXUS GAMING STORE. ALL RIGHTS RESERVED.</div>
    </footer>

    <script>
        document.getElementById("cancelCheckout").addEventListener("click", function () {
            if (confirm("Are you sure you want to cancel checkout?")) {
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

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders 
        (first_name, last_name, email, phone_number, street_address, city, state, postcode, country, card_number, cardholder_name, expiry_date, cvv, total_price, tax_fee, date) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    $stmt->bind_param(
        "ssssssssssssddss",
        $first_name,
        $last_name,
        $email,
        $phone,
        $address,
        $city,
        $state,
        $postcode,
        $country,
        $card_number,
        $card_name,
        $expiry_date,
        $cvv,
        $grand_total,
        $tax,
        $date
    );

    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;

        // Insert each ordered item into items_ordered table
        $stmt2 = $conn->prepare("INSERT INTO items_ordered (order_id, product_name, price_items, quantity_items, image_items) VALUES (?, ?, ?, ?, ?)");

        foreach ($products as $item) {
            $stmt2->bind_param(
                "isdis",
                $order_id,
                $item['name'],
                $item['price'],
                $item['quantity'],
                $item['image']
            );
            $stmt2->execute();
        }
        $stmt2->close();

        // Clear session data
        unset($_SESSION['checkout_source']);
        unset($_SESSION['single_product']);
        unset($_SESSION['cart_products']);

        echo "<script>alert('Order placed successfully!'); window.location.href='ORDERHISTORY.php';</script>";
    } else {
        echo "<script>alert('Error placing order. Please try again.');</script>";
    }

    $stmt->close();
}
?>