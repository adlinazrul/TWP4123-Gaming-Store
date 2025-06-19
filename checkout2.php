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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) && is_numeric($_POST['quantity']) && $_POST['quantity'] > 0 ? intval($_POST['quantity']) : 1;

    // Fetch product with stock information
    $product_query = $conn->prepare("SELECT *, product_quantity as stock FROM products WHERE id = ?");
    $product_query->bind_param("i", $product_id);
    $product_query->execute();
    $product_result = $product_query->get_result();
    
    if ($product_result->num_rows === 0) {
        header("Location: view_product_user.php?error=invalid_product");
        exit();
    }

    $product = $product_result->fetch_assoc();
    
    // Check stock availability
    if ($product['stock'] < $quantity) {
        header("Location: VIEWPRODUCT.php?id=$product_id&error=out_of_stock&available=".$product['stock']);
        exit();
    }

    $product_name = $product['product_name'];
    $price_per_item = floatval($product['product_price']);
    $product_image = $product['product_image'];
    $product_stock = $product['stock'];

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
        /* Your existing CSS remains unchanged */
        /* Only adding new styles for stock indicators */
        
        .stock-status {
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .in-stock {
            color: #28a745;
        }
        .low-stock {
            color: #ffc107;
            font-weight: bold;
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
                <div class="stock-status <?= $product_stock <= 5 ? 'low-stock' : 'in-stock' ?>">
                    <?php 
                    if ($product_stock <= 5) {
                        echo "Only $product_stock left in stock!";
                    } else {
                        echo "In Stock";
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="total-row"><span>Subtotal:</span><span>RM<?= number_format($subtotal, 2) ?></span></div>
        <div class="total-row"><span>Shipping:</span><span>FREE</span></div>
        <div class="total-row"><span>Tax (6%):</span><span>RM<?= number_format($tax, 2) ?></span></div>
        <div class="total-row grand-total"><span>Total:</span><span>RM<?= number_format($grand_total, 2) ?></span></div>
    </section>

    <!-- The rest of your form remains EXACTLY THE SAME -->
    <section class="payment-section">
        <h2>PAYMENT DETAILS</h2>
        <form method="POST" action="process_checkout.php" class="needs-validation" novalidate>
            <!-- All your existing hidden inputs and form fields remain unchanged -->
            <!-- ... -->
        </form>
    </section>
</main>

<!-- Footer and JavaScript remain exactly the same -->
</body>
</html>