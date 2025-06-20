<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['email'])) {
    header("Location: custlogin.php");
    exit();
}

$email = $_SESSION['email'];

// Get customer ID
$customer_query = $conn->prepare("SELECT id FROM customers WHERE email = ?");
$customer_query->bind_param("s", $email);
$customer_query->execute();
$customer_result = $customer_query->get_result();
$customer = $customer_result->fetch_assoc();
$customer_id = $customer['id'];

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_rating'])) {
    $order_id = intval($_POST['order_id']);
    $product_id = intval($_POST['product_id']);
    $rating = intval($_POST['rating']);
    $review = $conn->real_escape_string($_POST['review']);

    $verify_query = $conn->prepare("SELECT i.id FROM items_ordered i JOIN orders o ON i.order_id = o.id WHERE i.order_id = ? AND o.user_id = ? AND i.status_order = 'Delivered'");
    $verify_query->bind_param("ii", $order_id, $customer_id);
    $verify_query->execute();
    $verify_result = $verify_query->get_result();

    if ($verify_result->num_rows > 0) {
        $rating_query = $conn->prepare("INSERT INTO rating (order_id, product_id, customer_id, rating, review) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating), review = VALUES(review)");
        $rating_query->bind_param("iiiis", $order_id, $product_id, $customer_id, $rating, $review);
        $rating_query->execute();
    }

    header("Location: ORDERHISTORY.php?rating_success=1");
    exit();
}

// Fetch order history
$orders_query = $conn->prepare("SELECT o.id as order_id, o.date, i.*, p.product_name, p.product_image FROM orders o JOIN items_ordered i ON o.id = i.order_id JOIN products p ON i.product_name = p.product_name WHERE o.user_id = ? ORDER BY o.date DESC");
$orders_query->bind_param("i", $customer_id);
$orders_query->execute();
$orders_result = $orders_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS | Order History</title>
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
        
        .order-container {
            max-width: 1400px;
            margin: 50px auto;
            padding: 0 30px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .order-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            color: var(--primary);
            text-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
        }
        
        .order-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid var(--primary);
        }
        
        .order-card-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 0, 0, 0.2);
        }
        
        .order-id {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        .order-date {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .order-status {
            background: var(--primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .order-item {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .order-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .order-item-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .order-item-details {
            flex: 1;
        }
        
        .order-item-name {
            font-size: 1.2rem;
            margin-bottom: 5px;
            color: var(--light);
        }
        
        .order-item-meta {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        .order-item-price {
            font-weight: bold;
            color: var(--light);
        }
        
        .rating-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px dashed rgba(255, 255, 255, 0.2);
        }
        
        .rating-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .rating-stars {
            margin-bottom: 10px;
        }
        
        .rating-stars i {
            color: var(--primary);
            font-size: 1.5rem;
            cursor: pointer;
            margin-right: 5px;
        }
        
        .rating-textarea {
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 5px;
            padding: 10px;
            color: white;
            margin-bottom: 10px;
            resize: vertical;
        }
        
        .rating-submit {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .rating-submit:hover {
            background: var(--accent);
        }
        
        .no-orders {
            text-align: center;
            padding: 50px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .btn-primary {
            background: var(--primary);
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: var(--accent);
            color: white;
        }
        
        footer {
            background: #0a0118;
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
        
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .order-item {
                flex-direction: column;
            }
            
            .order-item-img {
                width: 100%;
                height: auto;
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

    <div class="order-container">
        <?php if (isset($_GET['rating_success'])): ?>
            <div class="alert alert-success" style="background: rgba(0, 200, 0, 0.2); color: #0f0; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #0f0;">
                Your rating has been submitted!
            </div>
        <?php endif; ?>

        <div class="order-header">
            <h1 class="order-title">ORDER HISTORY</h1>
            <a href="index.php" class="continue-shopping">
                <i class="fas fa-arrow-left"></i> CONTINUE SHOPPING
            </a>
        </div>

        <?php if ($orders_result->num_rows > 0): ?>
            <?php while ($order = $orders_result->fetch_assoc()):
                $product_name = $order['product_name'];
                $product_id = null;
                $prod_id_stmt = $conn->prepare("SELECT id FROM products WHERE product_name = ?");
                $prod_id_stmt->bind_param("s", $product_name);
                $prod_id_stmt->execute();
                $prod_id_result = $prod_id_stmt->get_result();
                if ($prod_row = $prod_id_result->fetch_assoc()) {
                    $product_id = $prod_row['id'];
                }
                $prod_id_stmt->close();

                $rated_query = $conn->prepare("SELECT rating, review FROM rating WHERE order_id = ? AND product_id = ? AND customer_id = ?");
                $rated_query->bind_param("iii", $order['order_id'], $product_id, $customer_id);
                $rated_query->execute();
                $rated_result = $rated_query->get_result();
                $rated = $rated_result->fetch_assoc();
                $rated_query->close();
            ?>
                <div class="order-card">
                    <div class="order-card-header">
                        <div>
                            <span class="order-id">ORDER #<?= $order['order_id'] ?></span>
                            <span class="order-date"><?= date('d M Y, h:i A', strtotime($order['date'])) ?></span>
                        </div>
                        <span class="order-status"><?= $order['status_order'] ?></span>
                    </div>
                    
                    <div class="order-item">
                        <img src="uploads/<?= htmlspecialchars($order['product_image']) ?>" 
                             onerror="this.src='uploads/default.png';"
                             alt="<?= htmlspecialchars($order['product_name']) ?>" 
                             class="order-item-img">
                        <div class="order-item-details">
                            <h3 class="order-item-name"><?= htmlspecialchars($order['product_name']) ?></h3>
                            <div class="order-item-meta">
                                <span>Quantity: <?= $order['quantity_items'] ?></span> | 
                                <span>Price: RM <?= number_format($order['price_items'], 2) ?></span> | 
                                <span class="order-item-price">Total: RM <?= number_format($order['price_items'] * $order['quantity_items'], 2) ?></span>
                            </div>
                            
                            <?php if ($order['status_order'] == 'Delivered' && $product_id !== null): ?>
                                <div class="rating-section">
                                    <h4 class="rating-title">RATE THIS PRODUCT</h4>
                                    <form method="POST" action="">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                        <input type="hidden" name="rating" id="rating_input_<?= $order['order_id'] ?>_<?= $product_id ?>" value="<?= $rated ? $rated['rating'] : 0 ?>">

                                        <div class="rating-stars" data-input-id="rating_input_<?= $order['order_id'] ?>_<?= $product_id ?>">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="<?= ($rated && $i <= $rated['rating']) ? 'fas' : 'far' ?> fa-star" data-rating="<?= $i ?>"></i>
                                            <?php endfor; ?>
                                        </div>

                                        <textarea name="review" class="rating-textarea" placeholder="Your review (optional)"><?= $rated ? htmlspecialchars($rated['review']) : '' ?></textarea>
                                        
                                        <button type="submit" name="submit_rating" class="rating-submit">
                                            <?= $rated ? 'UPDATE RATING' : 'SUBMIT RATING' ?>
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-orders">
                <h3>YOU HAVEN'T PLACED ANY ORDERS YET</h3>
                <p>Start shopping to see your orders here</p>
                <a href="all_product_user.php" class="btn-primary">SHOP NOW</a>
            </div>
        <?php endif; ?>
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
        document.addEventListener("DOMContentLoaded", function () {
            // Star rating logic
            document.querySelectorAll('.rating-stars').forEach(container => {
                const inputId = container.dataset.inputId;
                const hiddenInput = document.getElementById(inputId);
                const stars = container.querySelectorAll('i');

                stars.forEach(star => {
                    star.addEventListener('click', () => {
                        const rating = parseInt(star.dataset.rating);
                        hiddenInput.value = rating;

                        stars.forEach((s, i) => {
                            s.classList.toggle('fas', i < rating);
                            s.classList.toggle('far', i >= rating);
                        });
                    });
                });
            });

            // Mobile menu toggle (same as index.php)
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