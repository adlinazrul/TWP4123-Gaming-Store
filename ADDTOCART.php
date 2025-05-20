<?php
session_start();
include "db_connect1.php";

$cart_items = [];
$total_price = 0;

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT * FROM cart_items WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $total_price += $row['product_price'] * $row['quantity'];
    }
    $stmt->close();
} else {
    if (isset($_SESSION['cart'])) {
        $cart_items = $_SESSION['cart'];
        foreach ($cart_items as $item) {
            $total_price += $item['product_price'] * $item['quantity'];
        }
    }
}

// ✅ Set session for cart-based checkout
$_SESSION['checkout_source'] = 'cart';

// Normalize keys for checkout
$normalized_items = [];
foreach ($cart_items as $item) {
    $normalized_items[] = [
        'name' => $item['product_name'],
        'price' => $item['product_price'],
        'quantity' => $item['quantity'],
        'image' => $item['product_image'] // adjust key name if different
    ];
}
$_SESSION['cart_products'] = $normalized_items;

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS | Shopping Cart</title>
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
        
        .cart-item-checkbox {
            accent-color: var(--primary);
            width: 18px;
            height: 18px;
            cursor: pointer;
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
    </style>
</head>
<body>
    <header>
        <nav class="nav-menu">
            <div class="icons-left">
                <i class="fas fa-search" id="searchIcon"></i>
                <i class="fas fa-bars" id="menuIcon"></i>
            </div>
            
            <div class="logo" onclick="window.location.href='index.html'">NEXUS</div>
            
            <div class="nav-links">
                <a href="index">HOME</a>
                <a href="NINTENDO.php">NINTENDO</a>
                <a href="XBOX.php" class="active">CONSOLES</a>
                <a href="ACCESSORIES.php">ACCESSORIES</a>
                <a href="VR.php">VR</a>
            </div>
            
            <div class="icons-right">
                <a href="custlogin.html">
                    <i class="fas fa-user"></i>
                </a>
                <div class="cart-icon-container">
                    <a href="ADDTOCART.php"><i class="fas fa-shopping-cart"></i></a>
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
                <div class="menu-item"><a href="ORDERHISTORY.html">ORDER</a></div>
                <div class="menu-item"><a href="custservice.html">HELP</a></div>
                <div class="menu-item"><a href="login_admin.php">LOGIN ADMIN</a></div>
            </div>
        </div>
    </div>

    <!-- Cart Page Content -->
    <section class="cart-page">
        <div class="cart-header">
            <h2><i class="fas fa-shopping-cart"></i> YOUR CART</h2>
            <a href="index.html" class="continue-shopping">
                <i class="fas fa-arrow-left"></i> CONTINUE SHOPPING
            </a>
        </div>
        
        <div class="cart-items">
            <?php if (count($cart_items) > 0): ?>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <img src="uploads/<?= htmlspecialchars($item['product_image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                        <div class="cart-item-details">
                            <h3><?= htmlspecialchars($item['product_name']) ?></h3>
                            <p><?= htmlspecialchars($item['product_description'] ?? '') ?></p>
                            <form method="POST" action="update_cart_quantity.php" class="cart-item-quantity">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <button type="button" class="quantity-btn minus" onclick="decreaseQuantity(this, <?= $item['product_id'] ?>)">-</button>
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="quantity-input" id="quantity-<?= $item['product_id'] ?>">
                                <button type="button" class="quantity-btn plus" onclick="increaseQuantity(this, <?= $item['product_id'] ?>)">+</button>
                                <button type="submit" class="update-btn" style="margin-left: 10px;">Update</button>
                            </form>
                        </div>
                        <div class="cart-item-price">
                            <span>RM<?= number_format($item['product_price'], 2) ?></span>
                            <form method="POST" action="remove_cart_item.php" onsubmit="return confirm('Are you sure you want to remove this item?');">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <button type="submit" class="remove-item">
                                    <i class="fas fa-trash-alt"></i> Remove
                                </button>
                            </form>
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
                TOTAL: RM<?= number_format($total_price, 2) ?>
            </div>
            <button class="checkout-button" onclick="window.location.href='NEW_BuyNow.php'" <?= count($cart_items) == 0 ? 'disabled' : '' ?>>PROCEED TO CHECKOUT</button>
        </div>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-links">
            <a href="ABOUTUS.html">ABOUT US</a>
            <a href="CONTACT.html">CONTACT</a>
            <a href="TOS.html">TERMS OF SERVICE</a>
        </div>
        
        <div class="social-icons">
            <a href="#facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#instagram"><i class="fab fa-instagram"></i></a>
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

            // Cart icon click
            document.getElementById("cartIcon").addEventListener("click", function() {
                alert("You're already viewing your cart!");
            });

            // Search icon click
            document.getElementById("searchIcon").addEventListener("click", function() {
                alert("Search functionality would appear here. This is a demo.");
            });
        });

        function increaseQuantity(button, productId) {
            const input = document.getElementById('quantity-' + productId);
            input.value = parseInt(input.value) + 1;
        }

        function decreaseQuantity(button, productId) {
            const input = document.getElementById('quantity-' + productId);
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }

        function updateCartCount() {
            // This would update the cart count in the header
            // Implementation would depend on your cart system
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>