<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['email'])) {
    header("Location: custlogin.php");
    exit();
}

$email = $_SESSION['email'];

// Get customer info
$user_query = $conn->prepare("SELECT * FROM customers WHERE email = ?");
$user_query->bind_param("s", $email);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Fetch product data based on POST data
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
    $shipping = 0.00;
    $grand_total = $subtotal + $tax;
} else {
    die("Invalid access.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NEXUS | Checkout</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        input.card-number { letter-spacing: 2px; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4 text-center">Checkout</h2>
    <div class="row">
        <!-- Cart Summary -->
        <div class="col-md-5 order-md-2 mb-4">
            <h4 class="d-flex justify-content-between align-items-center mb-3">
                <span>Your Order</span>
                <span class="badge bg-primary rounded-pill">1</span>
            </h4>
            <ul class="list-group mb-3">
                <li class="list-group-item d-flex justify-content-between lh-sm">
                    <div>
                        <h6 class="my-0"><?= htmlspecialchars($product_name) ?> (x<?= $quantity ?>)</h6>
                        <small class="text-muted">RM <?= number_format($price_per_item, 2) ?></small>
                    </div>
                    <span class="text-muted">RM <?= number_format($price_per_item * $quantity, 2) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between"><span>Subtotal</span><strong>RM <?= number_format($subtotal, 2) ?></strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Tax (6%)</span><strong>RM <?= number_format($tax, 2) ?></strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Shipping</span><strong>FREE</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Total</span><strong>RM <?= number_format($grand_total, 2) ?></strong></li>
            </ul>
            
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title">Product Image</h6>
                    <img src="uploads/<?= htmlspecialchars($product_image) ?>" alt="<?= htmlspecialchars($product_name) ?>" class="img-fluid" style="max-height: 200px;">
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="col-md-7 order-md-1">
            <h4 class="mb-3">Shipping Address</h4>
            <form method="POST" action="process_checkout.php" class="needs-validation" novalidate>
                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                <input type="hidden" name="quantity" value="<?= $quantity ?>">
                <input type="hidden" name="order_type" value="buy_now">
                <input type="hidden" name="total_price" value="<?= $grand_total ?>">
                <input type="hidden" name="tax_fee" value="<?= $tax ?>">

                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <label for="firstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label for="lastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone_number" value="<?= htmlspecialchars($user['phone']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Street Address</label>
                    <input type="text" class="form-control" id="address" name="street_address" value="<?= htmlspecialchars($user['address']) ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="state" class="form-label">State</label>
                        <input type="text" class="form-control" id="state" name="state" value="<?= htmlspecialchars($user['state'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="postcode" class="form-label">Postcode</label>
                        <input type="text" class="form-control" id="postcode" name="postcode" value="<?= htmlspecialchars($user['postcode'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="country" class="form-label">Country</label>
                    <input type="text" class="form-control" id="country" name="country" value="Malaysia" required>
                </div>

                <hr class="my-4">

                <h4 class="mb-3">Payment</h4>
                <div class="row gy-3">
                    <div class="col-md-6">
                        <label for="cc-name" class="form-label">Cardholder Name</label>
                        <input type="text" class="form-control" id="cc-name" name="cardholder_name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="cc-number" class="form-label">Card Number</label>
                        <input type="text" class="form-control card-number" id="cc-number" name="card_number" maxlength="19" placeholder="1234 5678 9101 1121" required>
                    </div>
                    <div class="col-md-3">
                        <label for="cc-expiration" class="form-label">Expiry Date</label>
                        <input type="text" class="form-control" id="cc-expiration" name="expiry_date" placeholder="MM/YY" maxlength="5" required>
                    </div>
                    <div class="col-md-3">
                        <label for="cc-cvv" class="form-label">CVV</label>
                        <input type="text" class="form-control" id="cc-cvv" name="cvv" maxlength="4" required>
                    </div>
                </div>

                <hr class="my-4">
                <button class="w-100 btn btn-primary btn-lg" type="submit">Place Order</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('cc-number').addEventListener('input', function (e) {
    let val = e.target.value.replace(/\D/g, '');
    val = val.replace(/(.{4})/g, '$1 ').trim();
    e.target.value = val;
});

document.getElementById('cc-expiration').addEventListener('input', function (e) {
    let val = e.target.value.replace(/\D/g, '').slice(0, 4);
    if (val.length > 2) val = val.slice(0, 2) + '/' + val.slice(2);
    e.target.value = val;
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>