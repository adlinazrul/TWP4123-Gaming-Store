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

if ($item_count === 0 && empty($out_of_stock_items)) { // Added check for empty out_of_stock_items
    $_SESSION['out_of_stock'] = $out_of_stock_items; // Still pass this for potential messaging
    header("Location: cart.php?error=empty_cart"); // Changed error message for clarity
    exit();
}

// Exclude tax calculation
$tax = 0.00; // Set tax to zero
$shipping = 0.00;
$grand_total = $subtotal; // Grand total is now just the subtotal

if (!empty($out_of_stock_items)) {
    $_SESSION['partial_out_of_stock'] = $out_of_stock_items;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS | Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rubik:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ff0000;
            --secondary: #d10000;
            --dark: #0d0221;
            --light: #ffffff;
            --accent: #ff3333;
            --dark-bg: #0a0118;
        }
        
        body {
            font-family: 'Rubik', sans-serif;
            background-color: var(--dark);
            color: var(--light);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        header {
            background: var(--dark);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(255, 0, 0, 0.3);
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
        
        .nav-links {
            display: flex;
            gap: 30px;
        }
        
        .nav-links a {
            color: var(--light);
            text-decoration: none;
            font-family: 'Orbitron', sans-serif;
            font-weight: 400;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: var(--primary);
            bottom: -5px;
            left: 0;
            transition: width 0.3s ease;
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        .nav-links a.active {
            color: var(--primary);
        }
        
        .nav-links a.active::after {
            width: 100%;
        }
        
        .icons-left, .icons-right {
            display: flex;
            gap: 25px;
        }
        
        .icons-left i, .icons-right i {
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--light);
        }
        
        .icons-left i:hover, .icons-right i:hover {
            color: var(--primary);
        }
        
        .checkout-container {
            max-width: 1400px;
            margin: 50px auto;
            padding: 0 30px;
            display: flex;
            gap: 40px;
        }
        
        .checkout-form {
            flex: 2;
            background: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 10px;
        }
        
        .order-summary {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 10px;
            height: fit-content;
        }
        
        .section-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 0, 0, 0.3);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--light);
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            color: var(--light);
            font-family: 'Rubik', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(255, 0, 0, 0.2);
        }
        
        .row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .col {
            flex: 1;
        }
        
        .order-item {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .order-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .order-item-details {
            flex: 1;
        }
        
        .order-item-name {
            font-size: 1rem;
            margin-bottom: 5px;
            color: var(--light);
        }
        
        .order-item-meta {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }
        
        .order-item-price {
            font-weight: bold;
            color: var(--light);
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-left: 5px;
        }
        
        .badge-danger {
            background-color: var(--primary);
            color: white;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .summary-total {
            font-weight: bold;
            font-size: 1.1rem;
            color: var(--primary);
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 5px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .btn-primary:hover {
            background: var(--accent);
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            background: rgba(255, 193, 7, 0.2);
            border-left: 4px solid #ffc107;
            color: #ffc107;
        }
        
        .card-number {
            letter-spacing: 2px;
        }
        
        footer {
            background: var(--dark-bg);
            padding: 50px 30px 20px;
            text-align: center;
            margin-top: 50px;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .footer-links a {
            color: var(--light);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--primary);
        }
        
        .social-icons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .social-icons a {
            color: var(--light);
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .social-icons a:hover {
            color: var(--primary);
        }
        
        .copyright {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 0, 0, 0.1);
        }
        
        @media (max-width: 1024px) {
            .nav-links {
                gap: 15px;
            }
            
            .checkout-container {
                flex-direction: column;
            }
        }
        
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .logo {
                font-size: 1.5rem;
            }
            
            .row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="nav-menu">
            <div class="icons-left">
                <i class="fas fa-search"></i>
                <i class="fas fa-bars" id="menuIcon"></i>
            </div>
            
            <div class="logo">NEXUS</div>
            
            <div class="nav-links">
                <a href="index.php">HOME</a>
                <a href="nintendo_user.php">NINTENDO</a>
                <a href="console_user.php">CONSOLES</a>
                <a href="accessories_user.php">ACCESSORIES</a>
                <a href="vr_user.php">VR</a>
                <a href="other_categories_user.php">OTHERS</a>
            </div>
            
            <div class="icons-right">
                <a href="custeditprofile.php">
                    <i class="fas fa-user"></i>
                </a>
                <a href="cart.php"><i class="fas fa-shopping-cart"></i></a>
            </div>
        </nav>
    </header>

    <div class="checkout-container">
        <div class="checkout-form">
            <?php if (!empty($out_of_stock_items)): ?>
                <div class="alert">
                    <strong>Note:</strong> Some items in your cart are out of stock or have insufficient quantity. 
                    They have been removed from this order but remain in your cart.
                </div>
            <?php endif; ?>
            
            <h2 class="section-title">SHIPPING DETAILS</h2>
            <form method="POST" action="process_checkout.php" class="needs-validation" novalidate>
                <input type="hidden" name="order_type" value="cart">
                <input type="hidden" name="total_price" value="<?= $grand_total ?>">
                <input type="hidden" name="tax_fee" value="<?= $tax ?>">

                <?php foreach ($cart_items as $index => $item): ?>
                    <input type="hidden" name="cart[<?= $index ?>][product_id]" value="<?= $item['product_id'] ?>">
                    <input type="hidden" name="cart[<?= $index ?>][quantity]" value="<?= $item['quantity'] ?>">
                <?php endforeach; ?>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone_number" value="<?= htmlspecialchars($user['phone']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">Street Address</label>
                    <input type="text" class="form-control" id="address" name="street_address" value="<?= htmlspecialchars($user['address']) ?>" required>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state" name="state" value="<?= htmlspecialchars($user['state'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="postcode" class="form-label">Postcode</label>
                            <input type="text" class="form-control" id="postcode" name="postcode" value="<?= htmlspecialchars($user['postcode'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="country" class="form-label">Country</label>
                    <input type="text" class="form-control" id="country" name="country" value="Malaysia" required>
                </div>

                <h2 class="section-title" style="margin-top: 40px;">PAYMENT DETAILS</h2>
                
                <div class="form-group">
                    <label for="cc-name" class="form-label">Cardholder Name</label>
                    <input type="text" class="form-control" id="cc-name" name="cardholder_name" required>
                </div>
                
                <div class="form-group">
                    <label for="cc-number" class="form-label">Card Number</label>
                    <input type="text" class="form-control card-number" id="cc-number" name="card_number" maxlength="19" placeholder="1234 5678 9101 1121" required>
                </div>
                
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="cc-expiration" class="form-label">Expiry Date</label>
                            <input type="text" class="form-control" id="cc-expiration" name="expiry_date" placeholder="MM/YY" maxlength="5" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="cc-cvv" class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cc-cvv" name="cvv" placeholder="123" pattern="\d{3}" maxlength="3" required>
                        </div>
                    </div>
                </div>

                <button class="btn-primary" type="submit">PLACE ORDER</button>
            </form>
        </div>

        <div class="order-summary">
            <h2 class="section-title">YOUR ORDER</h2>
            
            <?php foreach ($cart_items as $item): ?>
                <div class="order-item">
                    <img src="<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="order-item-img">
                    <div class="order-item-details">
                        <div class="order-item-name">
                            <?= htmlspecialchars($item['product_name']) ?> (x<?= $item['quantity'] ?>)
                            <?php if ($item['stock'] <= $item['min_stock_threshold'] && $item['stock'] > 0): ?>
                                <span class="badge badge-warning">Low Stock (<?= $item['stock'] ?> left)</span>
                            <?php elseif ($item['stock'] <= 0): ?>
                                <span class="badge badge-danger">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        <div class="order-item-meta">RM <?= number_format($item['product_price'], 2) ?></div>
                        <div class="order-item-price">RM <?= number_format($item['product_price'] * $item['quantity'], 2) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (!empty($out_of_stock_items)): ?>
                <div style="background: rgba(255, 0, 0, 0.1); padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <h4 style="color: var(--primary); margin-bottom: 10px; font-size: 1rem;">REMOVED ITEMS:</h4>
                    <?php foreach ($out_of_stock_items as $item): ?>
                        <div style="font-size: 0.9rem; color: rgba(255, 255, 255, 0.7); margin-bottom: 5px;">
                            <?= htmlspecialchars($item['product_name']) ?> 
                            (Available: <?= $item['stock'] ?>, Requested: <?= $item['quantity'] ?>)
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="summary-row">
                <span>Subtotal</span>
                <span>RM <?= number_format($subtotal, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping</span>
                <span>FREE</span>
            </div>
            <div class="summary-row summary-total">
                <span>Total</span>
                <span>RM <?= number_format($grand_total, 2) ?></span>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-links">
            <a href="ABOUTUS.html">ABOUT US</a>
            <a href="CONTACT.html">CONTACT</a>
            <a href="TOS.html">TERMS OF SERVICE</a>
        </div>
        
        <div class="social-icons">
            <a href="#facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.instagram.com/sojusprite"><i class="fab fa-instagram"></i></a>
        </div>
        
        <div class="copyright">
            &copy; 2025 NEXUS GAMING STORE. ALL RIGHTS RESERVED.
        </div>
    </footer>

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

        // Mobile menu toggle (same as index.php)
        document.addEventListener("DOMContentLoaded", function () {
            let menuOverlay = document.getElementById("menuOverlay");
            let menuContainer = document.getElementById("menuContainer");
            let menuIcon = document.getElementById("menuIcon");
            let closeMenu = document.getElementById("closeMenu");

            menuIcon.addEventListener("click", function () {
                menuOverlay.style.display = "block";
                setTimeout(() => {
                    menuOverlay.classList.add("active");
                }, 10);
            });

            closeMenu.addEventListener("click", function (e) {
                e.stopPropagation();
                menuOverlay.classList.remove("active");
                setTimeout(() => {
                    menuOverlay.style.display = "none";
                }, 300);
            });

            menuOverlay.addEventListener("click", function (e) {
                if (e.target === menuOverlay) {
                    menuOverlay.classList.remove("active");
                    setTimeout(() => {
                        menuOverlay.style.display = "none";
                    }, 300);
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>