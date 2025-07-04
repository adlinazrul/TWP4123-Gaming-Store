<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$product = null;
$average_rating = 0;
$reviews = [];

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $_SESSION['checkout_source'] = 'single';
        $_SESSION['single_product'] = [
            'name' => $product['product_name'],
            'price' => $product['product_price'],
            'quantity' => 1,
            'image' => $product['product_image']
        ];

        // Get average rating and reviews
        $rating_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM rating WHERE product_id = $id";
        $rating_result = $conn->query($rating_sql);
        if ($rating_result->num_rows > 0) {
            $rating_data = $rating_result->fetch_assoc();
            $average_rating = round($rating_data['avg_rating'], 1);
        }

        // Get individual reviews
        $reviews_sql = "SELECT r.*, c.username FROM rating r 
                        JOIN customers c ON r.customer_id = c.id 
                        WHERE r.product_id = $id 
                        ORDER BY r.created_at DESC";
        $reviews_result = $conn->query($reviews_sql);
        if ($reviews_result->num_rows > 0) {
            while ($row = $reviews_result->fetch_assoc()) {
                $reviews[] = $row;
            }
        }
    } else {
        die("Product not found.");
    }
} else {
    die("No product ID specified.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['product_name']) ?> | NEXUS</title>
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
        
        .product-detail-container {
            max-width: 1400px;
            margin: 50px auto;
            padding: 0 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 50px;
        }
        
        .product-gallery {
            flex: 1;
            min-width: 300px;
        }
        
        .main-image {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        
        .main-image:hover {
            transform: scale(1.02);
        }
        
        .thumbnail-container {
            display: flex;
            gap: 15px;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            border-radius: 5px;
            cursor: pointer;
            object-fit: cover;
            border: 1px solid rgba(255, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .thumbnail:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
        }
        
        .thumbnail.active {
            border: 2px solid var(--primary);
        }
        
        .product-info {
            flex: 1;
            min-width: 300px;
        }
        
        .product-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
            text-shadow: 0 0 10px rgba(255, 0, 0, 0.3);
        }
        
        .product-rating {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .product-rating i {
            color: gold;
        }
        
        .product-rating span {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .product-price {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
            color: var(--primary);
            margin: 20px 0;
        }
        
        .stock-status {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            font-size: 1rem;
        }
        
        .stock-status.in-stock {
            color: #4CAF50;
        }
        
        .stock-status.low-stock {
            color: #FFC107;
        }
        
        .stock-status.out-of-stock {
            color: #F44336;
        }
        
        .product-description {
            line-height: 1.6;
            margin-bottom: 30px;
            color: var(--gray);
        }
        
        .product-features {
            margin-bottom: 30px;
            padding-left: 20px;
        }
        
        .product-features li {
            margin-bottom: 10px;
            color: var(--light);
        }
        
        .product-features li::marker {
            color: var(--primary);
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            gap: 10px;
        }
        
        .quantity-btn {
            background: var(--primary);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .quantity-btn:hover {
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
        
        .action-buttons {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .add-to-cart-lg {
            background: var(--primary);
            color: white;
            border: none;
            padding: 15px 40px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .add-to-cart-lg:hover {
            background: var(--accent);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 0, 0, 0.3);
        }

        .login-btn {
            background: var(--primary);
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-family: 'Orbitron', sans-serif;
            transition: all 0.3s ease;
        }
        
        .login-btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }
        
        .add-to-cart-lg:disabled {
            background: var(--gray);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .buy-now {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 15px 40px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .buy-now:hover {
            background: var(--primary);
            color: var(--dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 0, 0, 0.3);
        }
        
        .buy-now:disabled {
            color: var(--gray);
            border-color: var(--gray);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .buy-now:disabled:hover {
            background: transparent;
            color: var(--gray);
        }
        
        .product-specs {
            margin-top: 50px;
            width: 100%;
        }
        
        .product-specs h2 {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            font-size: 2rem;
            margin-bottom: 20px;
            position: relative;
        }
        
        .product-specs h2::after {
            content: '';
            position: absolute;
            width: 100px;
            height: 3px;
            background: var(--primary);
            bottom: -10px;
            left: 0;
            border-radius: 3px;
        }
        
        .specs-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .specs-table tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .specs-table th, .specs-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .specs-table th {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            width: 30%;
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


        
        @media (max-width: 1024px) {
            .nav-links {
                gap: 15px;
            }
            
            .product-title {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .logo {
                font-size: 1.5rem;
            }
            
            .product-detail-container {
                flex-direction: column;
                gap: 30px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .add-to-cart-lg, .buy-now {
                width: 100%;
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
            .product-title {
                font-size: 1.8rem;
            }
            
            .product-price {
                font-size: 1.5rem;
            }
            
            .thumbnail-container {
                justify-content: center;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 10px;
            }
        }
        /* Add these new styles for the review section */
        .reviews-section {
            margin-top: 50px;
            width: 100%;
        }
        
        .reviews-section h2 {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            font-size: 2rem;
            margin-bottom: 20px;
            position: relative;
        }
        
        .reviews-section h2::after {
            content: '';
            position: absolute;
            width: 100px;
            height: 3px;
            background: var(--primary);
            bottom: -10px;
            left: 0;
            border-radius: 3px;
        }
        
        .average-rating {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 10px;
        }
        
        .average-rating-value {
            font-size: 3rem;
            font-weight: bold;
            margin-right: 20px;
            color: var(--primary);
            font-family: 'Orbitron', sans-serif;
        }
        
        .average-rating-stars {
            display: flex;
            flex-direction: column;
        }
        
        .stars {
            color: gold;
            font-size: 1.5rem;
            letter-spacing: 3px;
        }
        
        .rating-count {
            color: var(--gray);
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .review-list {
            margin-top: 30px;
        }
        
        .review-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px 0;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .reviewer-name {
            font-weight: bold;
            color: var(--light);
        }
        
        .review-date {
            color: var(--gray);
            font-size: 0.8rem;
        }
        
        .review-rating {
            color: gold;
            margin-bottom: 10px;
        }
        
        .review-content {
            color: var(--light);
            line-height: 1.6;
        }
        
        .no-reviews {
            color: var(--gray);
            font-style: italic;
            padding: 20px 0;
        }
        
        @media (max-width: 768px) {
            .average-rating {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .average-rating-value {
                margin-bottom: 10px;
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
                <a href="index.html">HOME</a>
                <a href="NINTENDO.php">NINTENDO</a>
                <a href="XBOX.php" class="active">CONSOLES</a>
                <a href="ACCESSORIES.php">ACCESSORIES</a>
                <a href="VR.php">VR</a>
            </div>
            
            <div class="icons-right">
                <a href="custlogin.html" class="login-btn">LOGIN</a>
            </div>
        </nav>
    </header>

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

    <div class="product-detail-container">
        <div class="product-gallery">
            <img src="<?= htmlspecialchars($product['product_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" class="main-image" id="mainImage">
        </div>

        <div class="product-info">
            <h1 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h1>
            
            <!-- Add product rating display -->
            <div class="product-rating">
                <?php
                $full_stars = floor($average_rating);
                $half_star = ($average_rating - $full_stars) >= 0.5;
                $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                
                for ($i = 0; $i < $full_stars; $i++) {
                    echo '<i class="fas fa-star"></i>';
                }
                if ($half_star) {
                    echo '<i class="fas fa-star-half-alt"></i>';
                }
                for ($i = 0; $i < $empty_stars; $i++) {
                    echo '<i class="far fa-star"></i>';
                }
                ?>
                <span>(<?= count($reviews) ?> reviews)</span>
            </div>

            <div class="product-price">RM <?= number_format($product['product_price'], 2) ?></div>
            
            <div class="stock-status <?= $product['product_quantity'] > $product['min_stock_threshold'] ? 'in-stock' : ($product['product_quantity'] > 0 ? 'low-stock' : 'out-of-stock') ?>">
                <i class="fas <?= $product['product_quantity'] > $product['min_stock_threshold'] ? 'fa-check-circle' : ($product['product_quantity'] > 0 ? 'fa-exclamation-circle' : 'fa-times-circle') ?>"></i>
                <span>
                    <?= $product['product_quantity'] > $product['min_stock_threshold'] ? "In Stock ({$product['product_quantity']} available)" : 
                       ($product['product_quantity'] > 0 ? "Low Stock (Only {$product['product_quantity']} left!)" : "Out of Stock") ?>
                </span>
            </div>

            <p class="product-description">
                <?= nl2br(htmlspecialchars($product['product_description'])) ?>
            </p>

            <div class="quantity-selector">
                <button type="button" class="quantity-btn minus" onclick="updateQuantity(-1)">-</button>
                <input type="number" value="1" min="1" max="<?= $product['product_quantity'] ?>" class="quantity-input" id="quantityInput" onchange="validateQuantity()">
                <button type="button" class="quantity-btn plus" onclick="updateQuantity(1)">+</button>
            </div>

            <div class="action-buttons">
                <form method="POST" action="custlogin.html" style="display: contents;">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>">
                    <input type="hidden" name="product_price" value="<?= $product['product_price'] ?>">
                    <input type="hidden" name="product_image" value="<?= $product['product_image'] ?>">
                    <input type="hidden" name="quantity" id="formQuantity" value="1">
                    <button type="submit" class="add-to-cart-lg" <?= $product['product_quantity'] <= 0 ? 'disabled' : '' ?>>ADD TO CART</button>
                </form>
                <form action="custlogin.html" method="post" style="display: contents;">
                    <input type="hidden" name="checkout_source" value="single">
                    <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>">
                    <input type="hidden" name="price" value="<?= $product['product_price'] ?>">
                    <input type="hidden" name="image" value="<?= $product['product_image'] ?>">
                    <input type="hidden" name="quantity" id="buyNowQuantity" value="1">
                    <button type="submit" name="buy_now" class="buy-now" <?= $product['product_quantity'] <= 0 ? 'disabled' : '' ?>>BUY NOW</button>
                </form>
            </div>
        </div>

        <div class="product-specs">
            <h2>SUMMARY OF PRODUCT</h2>
            <table class="specs-table">
                <tr><th>Model</th><td><?= htmlspecialchars($product['product_name']) ?></td></tr>
                <tr><th>Category</th><td><?= htmlspecialchars($product['product_category']) ?></td></tr>
                <tr><th>Stock</th><td><?= $product['product_quantity'] ?> units</td></tr>
                <tr><th>Price</th><td>RM <?= number_format($product['product_price'], 2) ?></td></tr>
            </table>
        </div>

        <!-- Add the new reviews section -->
        <div class="reviews-section">
            <h2>CUSTOMER REVIEWS</h2>
            
            <div class="average-rating">
                <div class="average-rating-value"><?= $average_rating ?>/5</div>
                <div class="average-rating-stars">
                    <div class="stars">
                        <?php
                        $full_stars = floor($average_rating);
                        $half_star = ($average_rating - $full_stars) >= 0.5;
                        $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                        
                        for ($i = 0; $i < $full_stars; $i++) {
                            echo '<i class="fas fa-star"></i>';
                        }
                        if ($half_star) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        }
                        for ($i = 0; $i < $empty_stars; $i++) {
                            echo '<i class="far fa-star"></i>';
                        }
                        ?>
                    </div>
                    <div class="rating-count">
                        <?php 
                        $review_count = count($reviews);
                        echo $review_count . ' review' . ($review_count != 1 ? 's' : '');
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="review-list">
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="reviewer-name"><?= htmlspecialchars($review['username']) ?></div>
                                <div class="review-date"><?= date('F j, Y', strtotime($review['created_at'])) ?></div>
                            </div>
                            <div class="review-rating">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $review['rating']) {
                                        echo '<i class="fas fa-star"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <div class="review-content">
                                <?= nl2br(htmlspecialchars($review['review'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-reviews">No reviews yet. Be the first to review this product!</div>
                <?php endif; ?>
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
            <a href="#instagram"><i class="fab fa-instagram"></i></a>
        </div>
        
        <div class="copyright">
            &copy; 2025 NEXUS GAMING STORE. ALL RIGHTS RESERVED.<br>
            NEXUS is not affiliated with Nintendo or any other game publishers.
        </div>
    </footer>

    <script>
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

        function updateQuantity(change) {
            const quantityInput = document.getElementById('quantityInput');
            const formQuantity = document.getElementById('formQuantity');
            const buyNowQuantity = document.getElementById('buyNowQuantity');
            let newValue = parseInt(quantityInput.value) + change;
            
            if (newValue < 1) newValue = 1;
            if (newValue > <?= $product['product_quantity'] ?>) {
                newValue = <?= $product['product_quantity'] ?>;
                alert(`Only <?= $product['product_quantity'] ?> items available in stock!`);
            }
            
            quantityInput.value = newValue;
            formQuantity.value = newValue;
            buyNowQuantity.value = newValue;
        }

        function validateQuantity() {
            const quantityInput = document.getElementById('quantityInput');
            const formQuantity = document.getElementById('formQuantity');
            const buyNowQuantity = document.getElementById('buyNowQuantity');
            let value = parseInt(quantityInput.value);
            
            if (isNaN(value) || value < 1) {
                value = 1;
            } else if (value > <?= $product['product_quantity'] ?>) {
                value = <?= $product['product_quantity'] ?>;
                alert(`Only <?= $product['product_quantity'] ?> items available in stock!`);
            }
            
            quantityInput.value = value;
            formQuantity.value = value;
            buyNowQuantity.value = value;
        }

        function changeImage(thumbnail) {
            const mainImage = document.getElementById('mainImage');
            mainImage.src = thumbnail.src;
            
            document.querySelectorAll('.thumbnail').forEach(img => {
                img.classList.remove('active');
            });
            thumbnail.classList.add('active');
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>