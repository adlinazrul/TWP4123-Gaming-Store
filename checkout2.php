<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['email'])) {
    header("Location: custlogin.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch customer info
$user_query = $conn->prepare("SELECT * FROM customers WHERE email = ?");
$user_query->bind_param("s", $email);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Ensure Buy Now access
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_now'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    $product_query = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $product_query->bind_param("i", $product_id);
    $product_query->execute();
    $product_result = $product_query->get_result();
    if ($product_result->num_rows === 0) die("Product not found.");

    $product = $product_result->fetch_assoc();
    $product_name = $product['product_name'];
    $price_per_item = floatval($product['product_price']);
    $product_image = $product['product_image'];

    $subtotal = $price_per_item * $quantity;
    $tax = $subtotal * 0.06;
    $grand_total = $subtotal + $tax;
} else {
    die("Invalid access.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | NEXUS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #ff0000;
            --secondary: #d10000;
            --dark: #0d0221;
            --light: #ffffff;
            --accent: #ff3333;
            --gray: #7a7a7a;
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
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 0, 0, 0.2);
        }
        
        .logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
            cursor: pointer;
        }
        
        .cancel-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Rubik', sans-serif;
            transition: all 0.3s ease;
        }
        
        .cancel-btn:hover {
            background: var(--accent);
            transform: translateY(-2px);
        }
        
        .checkout-container {
            display: flex;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 30px auto;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(255, 0, 0, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.1);
        }
        
        .order-summary, .payment-section {
            flex: 1 1 45%;
            margin: 15px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
        }
        
        h2 {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            border-bottom: 2px solid rgba(255, 0, 0, 0.2);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .order-item {
            display: flex;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 0, 0, 0.1);
        }
        
        .order-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid rgba(255, 0, 0, 0.2);
            margin-right: 15px;
        }
        
        .item-name {
            font-weight: bold;
            color: var(--light);
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .item-price, .item-quantity {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 0, 0, 0.1);
        }
        
        .grand-total {
            font-size: 1.2rem;
            color: var(--primary);
            font-weight: bold;
            border-top: 1px solid rgba(255, 0, 0, 0.3);
            padding-top: 15px;
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: var(--light);
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.3);
            border-radius: 5px;
            color: var(--light);
            font-family: 'Rubik', sans-serif;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(255, 0, 0, 0.3);
        }
        
        .pay-now-btn {
            background: var(--primary);
            color: white;
            padding: 15px;
            border: none;
            font-size: 1.1rem;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
            font-family: 'Orbitron', sans-serif;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .pay-now-btn:hover {
            background: var(--accent);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 0, 0, 0.3);
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: var(--gray);
            font-size: 0.9rem;
            border-top: 1px solid rgba(255, 0, 0, 0.1);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .checkout-container {
                flex-direction: column;
                padding: 20px;
            }
            
            .order-summary, .payment-section {
                margin: 10px 0;
            }
        }
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
        <div class="order-item">
            <img src="uploads/<?= htmlspecialchars($product_image) ?>" alt="<?= htmlspecialchars($product_name) ?>">
            <div>
                <div class="item-name"><?= htmlspecialchars($product_name) ?></div>
                <div class="item-price">RM<?= number_format($price_per_item, 2) ?></div>
                <div class="item-quantity">Quantity: <?= $quantity ?></div>
            </div>
        </div>
        <div class="total-row"><span>Subtotal:</span><span>RM<?= number_format($subtotal, 2) ?></span></div>
        <div class="total-row"><span>Shipping:</span><span>FREE</span></div>
        <div class="total-row"><span>Tax (6%):</span><span>RM<?= number_format($tax, 2) ?></span></div>
        <div class="total-row grand-total"><span>Total:</span><span>RM<?= number_format($grand_total, 2) ?></span></div>
    </section>

    <section class="payment-section">
        <h2>PAYMENT DETAILS</h2>
        <form method="POST" action="process_checkout.php" class="needs-validation" novalidate>
            <input type="hidden" name="product_id" value="<?= $product_id ?>">
            <input type="hidden" name="quantity" value="<?= $quantity ?>">
            <input type="hidden" name="order_type" value="buy_now">
            <input type="hidden" name="total_price" value="<?= $grand_total ?>">
            <input type="hidden" name="tax_fee" value="<?= $tax ?>">

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone_number" value="<?= htmlspecialchars($user['phone']) ?>" required>
            </div>

            <div class="form-group">
                <label>Street Address</label>
                <input type="text" name="street_address" value="<?= htmlspecialchars($user['address']) ?>" required>
            </div>

            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>State</label>
                        <input type="text" name="state" value="<?= htmlspecialchars($user['state'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Postcode</label>
                        <input type="text" name="postcode" value="<?= htmlspecialchars($user['postcode'] ?? '') ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Country</label>
                <input type="text" name="country" value="Malaysia" required>
            </div>

            <hr style="border-color: rgba(255,0,0,0.1); margin: 25px 0;">

            <h2 style="margin-bottom: 20px;">PAYMENT METHOD</h2>

            <div class="form-group">
                <label>Cardholder Name</label>
                <input type="text" name="cardholder_name" required>
            </div>

            <div class="form-group">
                <label>Card Number</label>
                <input type="text" class="card-number" name="card_number" maxlength="19" placeholder="1234 5678 9101 1121" required>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="text" name="expiry_date" placeholder="MM/YY" maxlength="5" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" name="cvv" placeholder="123" pattern="\d{3}" maxlength="3" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="pay-now-btn">PAY RM<?= number_format($grand_total, 2) ?></button>
        </form>
    </section>
</main>

<footer>
    <div>&copy; 2025 NEXUS GAMING STORE</div>
    <div>NEXUS is not affiliated with Nintendo or any other game publishers.</div>
</footer>

<script>
document.getElementById("cancelCheckout").addEventListener("click", function () {
    if (confirm("Cancel checkout?")) {
        window.location.href = "index.php";
    }
});

// Card formatting
document.querySelector('input.card-number').addEventListener('input', function (e) {
    let val = e.target.value.replace(/\D/g, '');
    val = val.replace(/(.{4})/g, '$1 ').trim();
    e.target.value = val;
});

document.querySelector('input[name="expiry_date"]').addEventListener('input', function (e) {
    let val = e.target.value.replace(/\D/g, '').slice(0, 4);
    if (val.length > 2) val = val.slice(0, 2) + '/' + val.slice(2);
    e.target.value = val;
});
</script>
</body>
</html>