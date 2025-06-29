<?php
session_start();
require 'db_connect.php';

// Check if user is logged in by email
if (!isset($_SESSION['email'])) {
    header("Location: custlogin.php");
    exit;
}

$customer_email = $_SESSION['email'];

// First get customer_id from email
$stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
$stmt->bind_param("s", $customer_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Customer not found (shouldn't happen if session is valid)
    header("Location: custlogin.php");
    exit;
}

$customer = $result->fetch_assoc();
$customer_id = $customer['id'];

// Fetch cart items with product info and stock status
$stmt = $conn->prepare("
    SELECT 
        ci.product_id, 
        ci.quantity, 
        p.product_name, 
        p.product_price, 
        p.product_image, 
        p.stock,
        CASE 
            WHEN p.stock <= 0 THEN 'Out Of Stock' 
            WHEN p.stock < ci.quantity THEN 'Low Stock' 
            ELSE 'In Stock' 
        END as stock_status
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.email = ?
");
$stmt->bind_param("s", $customer_email);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$cart_total = 0;
$can_checkout = true;
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $cart_total += $row['product_price'] * $row['quantity'];
    
    // Check if any item is out of stock or has insufficient stock
    if ($row['stock'] <= 0 || $row['stock'] < $row['quantity']) {
        $can_checkout = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS | Your Cart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rubik:wght@300;400;600&display=swap" rel="stylesheet">
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
            cursor: pointer;
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
        
        .icons-left, .icons-right {
            display: flex;
            gap: 25px;
            align-items: center;
        }
        
        .icons-left i, .icons-right i {
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--light);
        }
        
        .icons-left i:hover, .icons-right i:hover {
            color: var(--primary);
            transform: scale(1.1);
        }
        
        .cart-count {
            background: var(--primary);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 0.7rem;
            position: absolute;
            top: -5px;
            right: -5px;
            font-family: 'Rubik', sans-serif;
        }
        
        .cart-icon-container {
            position: relative;
        }
        
        .cart-page {
            max-width: 1400px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            border: 1px solid rgba(255, 0, 0, 0.1);
            box-shadow: 0 10px 30px rgba(255, 0, 0, 0.1);
        }
        
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 0, 0, 0.2);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .cart-header h2 {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            font-size: 2rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .continue-shopping {
            color: var(--light);
            text-decoration: none;
            font-family: 'Orbitron', sans-serif;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .continue-shopping:hover {
            color: var(--primary);
        }
        
        .cart-items {
            margin-top: 20px;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 0, 0, 0.1);
            padding: 20px 0;
            transition: all 0.3s ease;
        }
        
        .cart-item:hover {
            background: rgba(255, 0, 0, 0.05);
        }
        
        .cart-item img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid rgba(255, 0, 0, 0.2);
        }
        
        .cart-item-details {
            flex-grow: 1;
            margin-left: 30px;
        }
        
        .cart-item-details h3 {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.2rem;
        }
        
        .cart-item-details p {
            color: var(--gray);
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .cart-item-price {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 15px;
            min-width: 200px;
        }
        
        .cart-item-price span {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .cart-item-controls {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .remove-item {
            color: var(--gray);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .remove-item:hover {
            color: var(--primary);
            transform: scale(1.1);
        }
        
        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .cart-item-quantity button {
            padding: 5px 12px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Orbitron', sans-serif;
            transition: all 0.3s ease;
        }
        
        .cart-item-quantity button:hover {
            background: var(--accent);
            transform: translateY(-2px);
        }
        
        .quantity-input {
            width: 60px;
            height: 40px;
            text-align: center;
            font-size: 1.1rem;
            background: rgba(255, 255, 255, 0.1);
            color: var(--light);
            border: 1px solid rgba(255, 0, 0, 0.3);
            border-radius: 5px;
        }
        
        .quantity-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(255, 0, 0, 0.3);
        }
        
        .update-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Rubik', sans-serif;
            transition: all 0.3s ease;
        }
        
        .update-btn:hover {
            background: var(--accent);
            transform: translateY(-2px);
        }
        
        .cart-summary {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 0, 0, 0.2);
            text-align: right;
        }
        
        .cart-total {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .checkout-button {
            display: inline-block;
            padding: 15px 40px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-family: 'Orbitron', sans-serif;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
        }
        
        .checkout-button:hover {
            background: var(--accent);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 0, 0, 0.3);
        }
        
        .checkout-button:disabled {
            background: var(--gray);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        footer {
            background: #0a0118;
            padding: 50px 30px 20px;
            text-align: center;
            position: relative;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .footer-links a {
            color: var(--light);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            padding: 5px 0;
        }
        
        .footer-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: var(--primary);
            bottom: 0;
            left: 0;
            transition: width 0.3s ease;
        }
        
        .footer-links a:hover::after {
            width: 100%;
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
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            text-decoration: none;
        }
        
        .social-icons a:hover {
            color: var(--primary);
            transform: translateY(-3px);
            background: rgba(255, 0, 0, 0.2);
        }
        
        .copyright {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 0, 0, 0.1);
        }
        
        /* Mobile menu styles */
        #menuOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            z-index: 2000;
        }
        
        #menuContainer {
            position: fixed;
            top: 0;
            left: -400px;
            width: 400px;
            height: 100%;
            background: var(--dark);
            padding: 40px;
            transition: left 0.4s ease;
            z-index: 2001;
            border-right: 1px solid var(--primary);
        }
        
        #closeMenu {
            font-size: 2rem;
            color: var(--primary);
            cursor: pointer;
            position: absolute;
            top: 20px;
            right: 20px;
            transition: transform 0.3s ease;
        }
        
        #closeMenu:hover {
            transform: rotate(90deg);
        }
        
        #menuOverlay.active {
            display: block;
        }
        
        #menuOverlay.active #menuContainer {
            left: 0;
        }
        
        .menu-item {
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 0, 0, 0.1);
        }
        
        .menu-item a {
            color: var(--light);
            text-decoration: none;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            display: block;
        }
        
        .menu-item a:hover {
            color: var(--primary);
            padding-left: 10px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .nav-links {
                gap: 15px;
            }
            
            .cart-item {
                flex-wrap: wrap;
            }
            
            .cart-item-price {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                width: 100%;
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid rgba(255, 0, 0, 0.1);
            }
        }
        
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .logo {
                font-size: 1.5rem;
            }
            
            .cart-item img {
                width: 80px;
                height: 80px;
            }
            
            .cart-item-details {
                margin-left: 15px;
            }
            
            #menuContainer {
                width: 100%;
                max-width: 320px;
            }
            
            .footer-links {
                gap: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .cart-page {
                padding: 20px;
            }
            
            .cart-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .cart-item-details h3 {
                font-size: 1rem;
            }
            
            .cart-item-details p {
                font-size: 0.8rem;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 10px;
            }
        }
        
        /* Stock status styles */
        .stock-status {
            font-family: 'Orbitron', sans-serif;
            font-size: 0.9rem;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-left: 10px;
        }
        
        .stock-status.in-stock {
            background-color: rgba(0, 170, 0, 0.2);
            color: #00aa00;
        }
        
        .stock-status.low-stock {
            background-color: rgba(255, 153, 0, 0.2);
            color: #ff9900;
        }
        
        .stock-status.out-of-stock {
            background-color: rgba(255, 0, 0, 0.2);
            color: #ff0000;
        }
        
        .stock-warning {
            color: #ff0000;
            font-size: 0.9rem;
            margin-top: 5px;
            display: block;
        }
    </style>
</head>
<body>
    <header>
        <nav class="nav-menu">
            <div class="icons-left">
                <i class="fas fa-search" id="searchIcon"></i>
                <i class="fas fa-bars" id="menuIcon"></i>
            </div>
            
            <div class="logo" onclick="window.location.href='index.php'">NEXUS</div>
            
            <div class="nav-links">
                <a href="index.php">HOME</a>
                <a href="nintendo_user.php">NINTENDO</a>
                <a href="console_user.php">CONSOLES</a>
                <a href="accessories_user.php">ACCESSORIES</a>
                <a href="vr_user.php">VR</a>
            </div>
            
            <div class="icons-right">
                <a href="custeditprofile.php">
                    <i class="fas fa-user"></i>
                </a>
                <div class="cart-icon-container">
                    <a href="cart.php"><i class="fas fa-shopping-cart"></i></a>
                    <div class="cart-count" style="<?= count($cart_items) > 0 ? 'display: flex;' : 'display: none;' ?>">
                        <?= array_reduce($cart_items, function($carry, $item) { return $carry + $item['quantity']; }, 0) ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Mobile Menu Overlay -->
    <div id="menuOverlay">
        <div id="menuContainer">
            <span id="closeMenu">&times;</span>
            <div id="menuContent">
                <div class="menu-item"><a href="ORDERHISTORY.php">ORDER</a></div>
                <div class="menu-item"><a href="custservice.html">HELP</a></div>
                <div class="menu-item"><a href="login_admin.php">LOGIN ADMIN</a></div>
            </div>
        </div>
    </div>

    <!-- Cart Page Content -->
    <section class="cart-page">
        <div class="cart-header">
            <h2><i class="fas fa-shopping-cart"></i> YOUR CART</h2>
            <a href="index.php" class="continue-shopping">
                <i class="fas fa-arrow-left"></i> CONTINUE SHOPPING
            </a>
        </div>
        
        <div class="cart-items">
            <?php if (count($cart_items) > 0): ?>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item" data-product-id="<?= $item['product_id'] ?>">
<img src="<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                        <div class="cart-item-details">
                            <h3><?= htmlspecialchars($item['product_name']) ?>
                                <span class="stock-status <?= strtolower(str_replace(' ', '-', $item['stock_status'])) ?>">
                                    <?= $item['stock_status'] ?>
                                </span>
                            </h3>
                            <p>Price: RM<?= number_format($item['product_price'], 2) ?></p>
                            <?php if ($item['stock_status'] == 'Out Of Stock'): ?>
                                <span class="stock-warning">This item is currently unavailable</span>
                            <?php elseif ($item['stock_status'] == 'Low Stock'): ?>
                                <span class="stock-warning">Only <?= $item['stock'] ?> left in stock</span>
                            <?php endif; ?>
                            <div class="cart-item-quantity">
                                <button type="button" class="quantity-btn minus" onclick="decreaseQuantity(this, <?= $item['product_id'] ?>)" <?= $item['stock_status'] == 'Out Of Stock' ? 'disabled' : '' ?>>-</button>
                                <input type="number" 
                                       id="quantity-<?= $item['product_id'] ?>" 
                                       min="1" 
                                       max="<?= $item['stock'] ?>" 
                                       value="<?= $item['quantity'] ?>" 
                                       class="quantity-input"
                                       <?= $item['stock_status'] == 'Out Of Stock' ? 'disabled' : '' ?>>
                                <button type="button" class="quantity-btn plus" onclick="increaseQuantity(this, <?= $item['product_id'] ?>)" <?= $item['stock_status'] == 'Out Of Stock' ? 'disabled' : '' ?>>+</button>
                                <button type="button" class="update-btn" onclick="updateQuantity(<?= $item['product_id'] ?>)" <?= $item['stock_status'] == 'Out Of Stock' ? 'disabled' : '' ?>>Update</button>
                            </div>
                            <p>Subtotal: RM<span class="subtotal"><?= number_format($item['product_price'] * $item['quantity'], 2) ?></span></p>
                        </div>
                        <div class="cart-item-price">
                            <span>RM<?= number_format($item['product_price'] * $item['quantity'], 2) ?></span>
                            <button type="button" class="remove-item" onclick="removeItem(<?= $item['product_id'] ?>)">
                                <i class="fas fa-trash-alt"></i> Remove
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; padding: 40px; color: var(--gray);">Your cart is empty</p>
            <?php endif; ?>
        </div>
        
        <?php if (count($cart_items) > 0): ?>
        <div class="cart-summary">
            <div class="cart-total">
                TOTAL: RM<?= number_format($cart_total, 2) ?>
            </div>
            <?php if (!$can_checkout): ?>
                <p style="color: #ff0000; text-align: center; margin-bottom: 15px;">
                    <i class="fas fa-exclamation-circle"></i> Some items in your cart are out of stock or have insufficient quantity. 
                    Please update your cart before proceeding to checkout.
                </p>
            <?php endif; ?>
            <form action="checkout.php" method="POST">
                <?php foreach ($cart_items as $index => $item): ?>
                    <input type="hidden" name="cart[<?= $index ?>][product_id]" value="<?= $item['product_id'] ?>">
                    <input type="hidden" name="cart[<?= $index ?>][product_name]" value="<?= htmlspecialchars($item['product_name']) ?>">
                    <input type="hidden" name="cart[<?= $index ?>][product_price]" value="<?= $item['product_price'] ?>">
                    <input type="hidden" name="cart[<?= $index ?>][quantity]" value="<?= $item['quantity'] ?>">
                    <input type="hidden" name="cart[<?= $index ?>][product_image]" value="<?= $item['product_image'] ?>">
                <?php endforeach; ?>
                <button type="submit" class="checkout-button" <?= !$can_checkout ? 'disabled' : '' ?>>PROCEED TO CHECKOUT</button>
            </form>
        </div>
        <?php endif; ?>
    </section>

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
            &copy; 2025 NEXUS GAMING STORE. ALL RIGHTS RESERVED.<br>
            NEXUS is not affiliated with Nintendo or any other game publishers.
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Mobile menu functionality
            let menuOverlay = document.getElementById("menuOverlay");
            let menuContainer = document.getElementById("menuContainer");
            let menuIcon = document.getElementById("menuIcon");
            let closeMenu = document.getElementById("closeMenu");

            // Open menu
            menuIcon.addEventListener("click", function () {
                menuOverlay.style.display = "block";
                setTimeout(() => {
                    menuOverlay.classList.add("active");
                }, 10);
            });

            // Close menu when clicking "X"
            closeMenu.addEventListener("click", function (e) {
                e.stopPropagation();
                menuOverlay.classList.remove("active");
                setTimeout(() => {
                    menuOverlay.style.display = "none";
                }, 300);
            });

            // Close menu when clicking outside of menu container
            menuOverlay.addEventListener("click", function (e) {
                if (e.target === menuOverlay) {
                    menuOverlay.classList.remove("active");
                    setTimeout(() => {
                        menuOverlay.style.display = "none";
                    }, 300);
                }
            });
            
            // Search icon click
            document.getElementById("searchIcon").addEventListener("click", function() {
                alert("Search functionality would appear here. This is a demo.");
            });
        });

        function increaseQuantity(button, productId) {
            const input = document.getElementById('quantity-' + productId);
            const max = parseInt(input.getAttribute('max'));
            const currentValue = parseInt(input.value);
            
            if (currentValue < max) {
                input.value = currentValue + 1;
            } else {
                alert(`Only ${max} items available in stock.`);
            }
        }

        function decreaseQuantity(button, productId) {
            const input = document.getElementById('quantity-' + productId);
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }

        function updateQuantity(productId) {
            const input = document.getElementById('quantity-' + productId);
            const qty = parseInt(input.value);
            const max = parseInt(input.max);

            if (isNaN(qty) || qty < 1) {
                input.value = 1;
                return;
            }
            if (qty > max) {
                alert(`Only ${max} items available in stock.`);
                input.value = max;
                return;
            }

            // Send AJAX request to update quantity
            fetch('update_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    product_id: productId, 
                    quantity: input.value,
                    customer_email: '<?= $_SESSION['email'] ?>'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update subtotal on UI
                    const price = parseFloat(data.price);
                    const subtotalEl = document.querySelector(`.cart-item[data-product-id="${productId}"] .subtotal`);
                    subtotalEl.textContent = (price * qty).toFixed(2);
                    
                    // Update stock status
                    const stockStatusEl = document.querySelector(`.cart-item[data-product-id="${productId}"] .stock-status`);
                    const warningEl = document.querySelector(`.cart-item[data-product-id="${productId}"] .stock-warning`);
                    
                    if (data.stock <= 0) {
                        stockStatusEl.textContent = 'Out Of Stock';
                        stockStatusEl.className = 'stock-status out-of-stock';
                        if (warningEl) {
                            warningEl.textContent = 'This item is currently unavailable';
                        } else {
                            const detailsDiv = document.querySelector(`.cart-item[data-product-id="${productId}"] .cart-item-details`);
                            const newWarning = document.createElement('span');
                            newWarning.className = 'stock-warning';
                            newWarning.textContent = 'This item is currently unavailable';
                            detailsDiv.appendChild(newWarning);
                        }
                        // Disable quantity controls
                        input.disabled = true;
                        const plusBtn = input.nextElementSibling;
                        const minusBtn = input.previousElementSibling;
                        const updateBtn = plusBtn.nextElementSibling;
                        plusBtn.disabled = true;
                        minusBtn.disabled = true;
                        updateBtn.disabled = true;
                    } else if (qty > data.stock) {
                        stockStatusEl.textContent = 'Low Stock';
                        stockStatusEl.className = 'stock-status low-stock';
                        if (warningEl) {
                            warningEl.textContent = `Only ${data.stock} left in stock`;
                        } else {
                            const detailsDiv = document.querySelector(`.cart-item[data-product-id="${productId}"] .cart-item-details`);
                            const newWarning = document.createElement('span');
                            newWarning.className = 'stock-warning';
                            newWarning.textContent = `Only ${data.stock} left in stock`;
                            detailsDiv.appendChild(newWarning);
                        }
                    } else {
                        stockStatusEl.textContent = 'In Stock';
                        stockStatusEl.className = 'stock-status in-stock';
                        if (warningEl) {
                            warningEl.remove();
                        }
                    }
                    
                    // Update total
                    updateTotal();
                    
                    // Check if we can enable checkout
                    checkCheckoutAvailability();
                } else {
                    alert(data.message);
                }
            });
        }

        function removeItem(productId) {
            if (!confirm('Remove this item from cart?')) return;

            fetch('remove_cart_item.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    product_id: productId,
                    customer_email: '<?= $_SESSION['email'] ?>'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Remove item from UI
                    const itemEl = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
                    itemEl.remove();
                    
                    // Update total
                    updateTotal();
                    
                    // Check if cart is empty
                    if(document.querySelectorAll('.cart-item').length === 0){
                        document.querySelector('.cart-items').innerHTML = `
                            <p style="text-align: center; padding: 40px; color: var(--gray);">Your cart is empty</p>
                        `;
                        document.querySelector('.cart-summary').style.display = 'none';
                    }
                } else {
                    alert(data.message);
                }
            });
        }

        function updateTotal() {
            let total = 0;
            document.querySelectorAll('.cart-item').forEach(item => {
                const subtotalEl = item.querySelector('.subtotal');
                total += parseFloat(subtotalEl.textContent);
            });
            document.querySelector('.cart-total').textContent = `TOTAL: RM${total.toFixed(2)}`;
        }

        function checkCheckoutAvailability() {
            let canCheckout = true;
            document.querySelectorAll('.cart-item').forEach(item => {
                const stockStatus = item.querySelector('.stock-status').textContent;
                const quantity = parseInt(item.querySelector('.quantity-input').value);
                const max = parseInt(item.querySelector('.quantity-input').max);
                
                if (stockStatus === 'Out Of Stock' || quantity > max) {
                    canCheckout = false;
                }
            });
            
            document.querySelector('.checkout-button').disabled = !canCheckout;
            
            if (!canCheckout) {
                const warningExists = document.querySelector('.cart-summary p[style*="color: #ff0000"]');
                if (!warningExists) {
                    const warning = document.createElement('p');
                    warning.style.color = '#ff0000';
                    warning.style.textAlign = 'center';
                    warning.style.marginBottom = '15px';
                    warning.innerHTML = '<i class="fas fa-exclamation-circle"></i> Some items in your cart are out of stock or have insufficient quantity. Please update your cart before proceeding to checkout.';
                    document.querySelector('.cart-summary').insertBefore(warning, document.querySelector('.cart-summary form'));
                }
            } else {
                const warning = document.querySelector('.cart-summary p[style*="color: #ff0000"]');
                if (warning) {
                    warning.remove();
                }
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>