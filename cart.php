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

// Fetch cart items with product info
// Fetch cart items with product info
$stmt = $conn->prepare("
    SELECT ci.product_id, ci.quantity, p.product_name, p.product_price, p.product_image, p.product_quantity
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.email = ?
");
$stmt->bind_param("s", $customer_email);  // ✅ Correct: bind email as string

$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$cart_total = 0;
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $cart_total += $row['product_price'] * $row['quantity'];
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
        /* [All previous CSS styles remain exactly the same] */
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
            </div>
            
            <div class="icons-right">
                <a href="custeditprofile.php">
                    <i class="fas fa-user"></i>
                </a>
                <a href="ADDTOCART.php"><i class="fas fa-shopping-cart"></i></a>
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

    <main class="cart-container">
        <h1 class="section-title">CART</h1>
        
        <div id="cartItemsContainer">
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <p>Your cart is empty.</p>
                    <a href="index.php" class="checkout-btn">Continue Shopping</a>
                </div>
            <?php else: ?>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item" data-product-id="<?= $item['product_id'] ?>">
                        <img src="images/<?= htmlspecialchars($item['product_image']) ?>" 
                             alt="<?= htmlspecialchars($item['product_name']) ?>" 
                             class="cart-item-image" />
                        <div class="cart-item-details">
                            <h3><?= htmlspecialchars($item['product_name']) ?></h3>
                            <p class="cart-item-price">Price: RM<?= number_format($item['product_price'], 2) ?></p>
                            <div class="cart-item-qty">
                                <label for="qty-<?= $item['product_id'] ?>">Quantity:</label>
                                <input type="number" 
                                       id="qty-<?= $item['product_id'] ?>" 
                                       min="1" 
                                       max="<?= $item['product_quantity'] ?>" 
                                       value="<?= $item['quantity'] ?>" />
                            </div>
                            <p class="cart-item-subtotal">Subtotal: RM<span class="subtotal"><?= number_format($item['product_price'] * $item['quantity'], 2) ?></span></p>
                        </div>
                        <div class="cart-item-remove" title="Remove item">✖</div>
                    </div>
                <?php endforeach; ?>
                
                <form action="checkout.php" method="POST">
    <?php foreach ($cart_items as $index => $item): ?>
        <input type="hidden" name="cart[<?= $index ?>][product_id]" value="<?= $item['product_id'] ?>">
        <input type="hidden" name="cart[<?= $index ?>][product_name]" value="<?= htmlspecialchars($item['product_name']) ?>">
        <input type="hidden" name="cart[<?= $index ?>][product_price]" value="<?= $item['product_price'] ?>">
        <input type="hidden" name="cart[<?= $index ?>][quantity]" value="<?= $item['quantity'] ?>">
        <input type="hidden" name="cart[<?= $index ?>][product_image]" value="<?= $item['product_image'] ?>">
    <?php endforeach; ?>

    <div class="cart-summary">
        <h2 class="cart-total">Total: RM<span id="cartTotal"><?= number_format($cart_total, 2) ?></span></h2>
        <button type="submit" id="checkoutBtn" class="checkout-btn">Checkout</button>
    </div>
</form>

            <?php endif; ?>
        </div>
    </main>

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
        document.addEventListener("DOMContentLoaded", function () {
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
            
            // Helper to update total
            function updateTotal() {
                let total = 0;
                document.querySelectorAll('.cart-item').forEach(item => {
                    const subtotalEl = item.querySelector('.subtotal');
                    total += parseFloat(subtotalEl.textContent);
                });
                document.getElementById('cartTotal').textContent = total.toFixed(2);
            }

            // AJAX update quantity
            document.querySelectorAll('.cart-item-qty input').forEach(input => {
                input.addEventListener('change', function() {
                    const qty = parseInt(this.value);
                    const parent = this.closest('.cart-item');
                    const productId = parent.getAttribute('data-product-id');
                    const max = parseInt(this.max);

                    if (isNaN(qty) || qty < 1) {
                        this.value = 1;
                        return;
                    }
                    if (qty > max) {
                        alert(`Only ${max} items available in stock.`);
                        this.value = max;
                        return;
                    }

                    // Send AJAX request to update quantity
                    fetch('update_cart.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ 
                            product_id: productId, 
                            quantity: this.value,
                            customer_email: '<?= $_SESSION['email'] ?>' // Pass email to identify customer
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Update subtotal on UI
                            const price = parseFloat(data.price);
                            parent.querySelector('.subtotal').textContent = (price * qty).toFixed(2);
                            updateTotal();
                        } else {
                            alert(data.message);
                        }
                    });
                });
            });

            // AJAX remove item
            document.querySelectorAll('.cart-item-remove').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (!confirm('Remove this item from cart?')) return;
                    const parent = this.closest('.cart-item');
                    const productId = parent.getAttribute('data-product-id');

                    fetch('remove_cart_item.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ 
                            product_id: productId,
                            customer_email: '<?= $_SESSION['email'] ?>' // Pass email to identify customer
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            parent.remove();
                            updateTotal();
                            if(document.querySelectorAll('.cart-item').length === 0){
                                document.getElementById('cartItemsContainer').innerHTML = `
                                    <div class="empty-cart">
                                        <p>Your cart is empty.</p>
                                        <a href="index.php" class="checkout-btn">Continue Shopping</a>
                                    </div>
                                `;
                            }
                        } else {
                            alert(data.message);
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>