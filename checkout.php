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

// Fetch cart items with stock and threshold information
$cart_query = $conn->prepare("
    SELECT ci.product_id, ci.quantity, p.product_name, p.product_price, 
           p.product_image, p.product_quantity as stock, p.min_stock_threshold
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.email = ?
");
$cart_query->bind_param("s", $email);
$cart_query->execute();
$cart_result = $cart_query->get_result();

$cart_items = [];
$subtotal = 0;
$item_count = 0;
$out_of_stock_items = [];

while ($item = $cart_result->fetch_assoc()) {
    // Check stock availability
    if ($item['stock'] < $item['quantity']) {
        $out_of_stock_items[] = $item;
        continue;
    }
    
    $cart_items[] = $item;
    $subtotal += $item['product_price'] * $item['quantity'];
    $item_count += $item['quantity'];
}

if ($item_count === 0) {
    $_SESSION['out_of_stock'] = $out_of_stock_items;
    header("Location: cart.php?error=out_of_stock");
    exit();
}

$tax = $subtotal * 0.06;
$shipping = 0.00;
$grand_total = $subtotal + $tax;

if (!empty($out_of_stock_items)) {
    $_SESSION['partial_out_of_stock'] = $out_of_stock_items;
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
        .product-img-thumbnail { max-height: 60px; }
        .out-of-stock-badge { 
            background-color: #dc3545;
            font-size: 0.75rem;
            margin-left: 5px;
        }
        .low-stock-badge {
            background-color: #ffc107;
            color: #000;
            font-size: 0.75rem;
            margin-left: 5px;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <?php if (!empty($out_of_stock_items)): ?>
        <div class="alert alert-warning">
            <strong>Note:</strong> Some items in your cart are out of stock or have insufficient quantity. 
            They have been removed from this order but remain in your cart.
        </div>
    <?php endif; ?>
    
    <h2 class="mb-4 text-center">Checkout</h2>
    <div class="row">
        <div class="col-md-5 order-md-2 mb-4">
            <h4 class="d-flex justify-content-between align-items-center mb-3">
                <span>Your Order</span>
                <span class="badge bg-primary rounded-pill"><?= $item_count ?></span>
            </h4>
            <ul class="list-group mb-3">
                <?php foreach ($cart_items as $item): ?>
                    <li class="list-group-item d-flex justify-content-between lh-sm">
                        <div class="d-flex align-items-center">
                            <img src="uploads/<?= htmlspecialchars($item['product_image']) ?>" 
                                 alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                 class="img-thumbnail product-img-thumbnail me-2">
                            <div>
                                <h6 class="my-0"><?= htmlspecialchars($item['product_name']) ?> (x<?= $item['quantity'] ?>)</h6>
                                <small class="text-muted">RM <?= number_format($item['product_price'], 2) ?></small>
                                <?php if ($item['stock'] <= $item['min_stock_threshold'] && $item['stock'] > 0): ?>
                                    <span class="badge low-stock-badge">Low Stock (<?= $item['stock'] ?> left)</span>
                                <?php elseif ($item['stock'] <= 0): ?>
                                    <span class="badge out-of-stock-badge">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="text-muted">RM <?= number_format($item['product_price'] * $item['quantity'], 2) ?></span>
                    </li>
                <?php endforeach; ?>
                
                <?php if (!empty($out_of_stock_items)): ?>
                    <li class="list-group-item bg-light">
                        <h6 class="text-danger">Removed Out-of-Stock Items:</h6>
                        <?php foreach ($out_of_stock_items as $item): ?>
                            <div class="text-muted small">
                                <?= htmlspecialchars($item['product_name']) ?> 
                                (Available: <?= $item['stock'] ?>, Requested: <?= $item['quantity'] ?>)
                            </div>
                        <?php endforeach; ?>
                    </li>
                <?php endif; ?>
                
                <li class="list-group-item d-flex justify-content-between"><span>Subtotal</span><strong>RM <?= number_format($subtotal, 2) ?></strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Tax (6%)</span><strong>RM <?= number_format($tax, 2) ?></strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Shipping</span><strong>FREE</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Total</span><strong>RM <?= number_format($grand_total, 2) ?></strong></li>
            </ul>
        </div>

        <div class="col-md-7 order-md-1">
            <h4 class="mb-3">Shipping Address</h4>
            <form method="POST" action="process_checkout.php" class="needs-validation" novalidate>
                <input type="hidden" name="order_type" value="cart">
                <input type="hidden" name="total_price" value="<?= $grand_total ?>">
                <input type="hidden" name="tax_fee" value="<?= $tax ?>">

                <?php foreach ($cart_items as $index => $item): ?>
                    <input type="hidden" name="cart[<?= $index ?>][product_id]" value="<?= $item['product_id'] ?>">
                    <input type="hidden" name="cart[<?= $index ?>][quantity]" value="<?= $item['quantity'] ?>">
                <?php endforeach; ?>

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
                        <input type="text" class="form-control" id="cc-cvv" name="cvv" placeholder="123" pattern="\d{3}" maxlength="3" required>
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