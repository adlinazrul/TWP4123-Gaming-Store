<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "gaming_store");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_GET['id'])) die("No product ID specified.");

$id = intval($_GET['id']);
$product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
if (!$product) die("Product not found.");

$_SESSION['checkout_source'] = 'single';
$_SESSION['single_product'] = [
    'name' => $product['product_name'],
    'price' => $product['product_price'],
    'quantity' => 1,
    'image' => $product['product_image']
];
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
        :root{--primary:#ff0000;--dark:#0d0221;--light:#fff}
        body{font-family:'Rubik',sans-serif;background:var(--dark);color:var(--light);margin:0}
        header{background:var(--dark);padding:15px 0;position:sticky;top:0;z-index:1000}
        .nav-menu{display:flex;justify-content:space-between;align-items:center;max-width:1400px;margin:0 auto;padding:0 30px}
        .logo{font-family:'Orbitron',sans-serif;font-size:2rem;color:var(--primary);cursor:pointer}
        .nav-links{display:flex;gap:30px}
        .nav-links a{color:var(--light);text-decoration:none;font-family:'Orbitron';transition:all 0.3s}
        .nav-links a:hover{color:var(--primary)}
        .icons-left,.icons-right{display:flex;gap:25px;align-items:center}
        .icons-left i,.icons-right i{font-size:1.5rem;cursor:pointer;color:var(--light)}
        .icons-left i:hover,.icons-right i:hover{color:var(--primary)}
        .cart-count{background:var(--primary);color:white;border-radius:50%;width:20px;height:20px;display:flex;justify-content:center;align-items:center;font-size:0.7rem;position:absolute;top:-5px;right:-5px}
        .cart-icon-container{position:relative}
        .product-detail-container{max-width:1400px;margin:50px auto;padding:0 30px;display:flex;flex-wrap:wrap;gap:50px}
        .product-gallery{flex:1;min-width:300px}
        .main-image{width:100%;border-radius:10px;margin-bottom:20px}
        .thumbnail-container{display:flex;gap:15px}
        .thumbnail{width:80px;height:80px;border-radius:5px;cursor:pointer;object-fit:cover;transition:all 0.3s}
        .thumbnail:hover{border:1px solid var(--primary)}
        .thumbnail.active{border:2px solid var(--primary)}
        .product-info{flex:1;min-width:300px}
        .product-title{font-family:'Orbitron';font-size:2.5rem;color:var(--primary);margin-bottom:15px}
        .product-price{font-family:'Orbitron';font-size:1.8rem;color:var(--primary);margin:20px 0}
        .stock-status{display:flex;align-items:center;gap:8px;margin-bottom:20px}
        .stock-status.in-stock{color:#4CAF50}
        .stock-status.low-stock{color:#FFC107}
        .stock-status.out-of-stock{color:#F44336}
        .product-description{line-height:1.6;margin-bottom:30px}
        .quantity-selector{display:flex;align-items:center;margin-bottom:30px;gap:10px}
        .quantity-input{width:60px;height:40px;text-align:center;font-size:1.1rem;background:rgba(255,255,255,0.1);color:var(--light);border:1px solid rgba(255,0,0,0.3);border-radius:5px}
        .action-buttons{display:flex;gap:20px;flex-wrap:wrap}
        .add-to-cart-lg,.buy-now{padding:15px 40px;font-family:'Orbitron';font-size:1.2rem;border-radius:50px;cursor:pointer;transition:all 0.3s}
        .add-to-cart-lg{background:var(--primary);color:white;border:none}
        .add-to-cart-lg:hover{background:#ff3333}
        .buy-now{background:transparent;color:var(--primary);border:1px solid var(--primary)}
        .buy-now:hover{background:var(--primary);color:var(--dark)}
        .product-specs{margin-top:50px;width:100%}
        footer{background:#0a0118;padding:50px 30px 20px;text-align:center}
        .notification{position:fixed;top:20px;right:20px;background:#4CAF50;color:white;padding:15px 25px;border-radius:5px;box-shadow:0 4px 8px rgba(0,0,0,0.2);z-index:1000;display:none}
        @media (max-width:768px){.nav-links{display:none}.product-detail-container{flex-direction:column}}
    </style>
</head>
<body>
    <div id="successNotification" class="notification"><i class="fas fa-check-circle"></i> Item added to cart!</div>

    <header>
        <nav class="nav-menu">
            <div class="icons-left">
                <i class="fas fa-bars" id="menuIcon"></i>
            </div>
            <div class="logo" onclick="window.location.href='index.php'">NEXUS</div>
            <div class="nav-links">
                <a href="index.php">HOME</a>
                <a href="nintendo_user.php">NINTENDO</a>
                <a href="console_user.php">CONSOLES</a>
                <a href="accessories_user.php">ACCESSORIES</a>
            </div>
            <div class="icons-right">
                <a href="custeditprofile.php"><i class="fas fa-user"></i></a>
                <div class="cart-icon-container">
                    <a href="cart.php"><i class="fas fa-shopping-cart"></i></a>
                    <span class="cart-count"><?= $_SESSION['cart_count'] ?? 0 ?></span>
                </div>
            </div>
        </nav>
    </header>

    <div id="menuOverlay">
        <div id="menuContainer">
            <span id="closeMenu">&times;</span>
            <div id="menuContent">
                <div class="menu-item"><a href="ORDERHISTORY.php">ORDER</a></div>
                <div class="menu-item"><a href="custservice.html">HELP</a></div>
            </div>
        </div>
    </div>

    <div class="product-detail-container">
        <div class="product-gallery">
            <img src="uploads/<?= htmlspecialchars($product['product_image']) ?>" class="main-image" id="mainImage">
            <div class="thumbnail-container">
                <img src="uploads/<?= htmlspecialchars($product['product_image']) ?>" class="thumbnail active" onclick="changeImage(this)">
            </div>
        </div>

        <div class="product-info">
            <h1 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h1>
            <div class="product-price">RM <?= number_format($product['product_price'], 2) ?></div>
            
            <div class="stock-status <?= $product['product_quantity'] > 10 ? 'in-stock' : ($product['product_quantity'] > 0 ? 'low-stock' : 'out-of-stock') ?>">
                <i class="fas <?= $product['product_quantity'] > 10 ? 'fa-check-circle' : ($product['product_quantity'] > 0 ? 'fa-exclamation-circle' : 'fa-times-circle') ?>"></i>
                <span><?= $product['product_quantity'] > 10 ? "In Stock" : ($product['product_quantity'] > 0 ? "Low Stock" : "Out of Stock") ?></span>
            </div>

            <p class="product-description"><?= nl2br(htmlspecialchars($product['product_description'])) ?></p>

            <div class="quantity-selector">
                <label>Quantity:</label>
                <input type="number" value="1" min="1" max="<?= $product['product_quantity'] ?>" class="quantity-input" id="quantityInput" onchange="validateQuantity()">
            </div>

            <div class="action-buttons">
                <form id="addToCartForm">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="quantity" id="formQuantity" value="1">
                    <button type="submit" class="add-to-cart-lg" <?= $product['product_quantity'] <= 0 ? 'disabled' : '' ?>>ADD TO CART</button>
                </form>
                
                <form action="checkout2.php" method="post">
    <input type="hidden" name="checkout_source" value="single">
    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
    <input type="hidden" name="quantity" id="buyNowQuantity" value="1">
    <button type="submit" name="buy_now" class="buy-now" <?= $product['product_quantity'] <= 0 ? 'disabled' : '' ?>>BUY NOW</button>
</form>
            </div>
        </div>

        <div class="product-specs">
            <h2>PRODUCT INFO</h2>
            <table style="width:100%">
                <tr><th>Model</th><td><?= htmlspecialchars($product['product_name']) ?></td></tr>
                <tr><th>Category</th><td><?= htmlspecialchars($product['product_category']) ?></td></tr>
                <tr><th>Price</th><td>RM <?= number_format($product['product_price'], 2) ?></td></tr>
            </table>
        </div>
    </div>

    <footer>
        <div style="margin-bottom:30px">
            <a href="ABOUTUS.html" style="color:var(--light);margin:0 15px">ABOUT US</a>
            <a href="CONTACT.html" style="color:var(--light);margin:0 15px">CONTACT</a>
        </div>
        <div>&copy; 2025 NEXUS GAMING STORE</div>
    </footer>

    <script>
        // Mobile menu
        document.getElementById("menuIcon").addEventListener("click", () => {
            document.getElementById("menuOverlay").style.display = "block";
            setTimeout(() => document.getElementById("menuOverlay").classList.add("active"), 10);
        });

        document.getElementById("closeMenu").addEventListener("click", () => {
            document.getElementById("menuOverlay").classList.remove("active");
            setTimeout(() => document.getElementById("menuOverlay").style.display = "none", 300);
        });

        // Quantity handling
        document.getElementById('quantityInput').addEventListener('change', function() {
            let quantity = Math.max(1, Math.min(this.value, <?= $product['product_quantity'] ?>));
            this.value = quantity;
            document.getElementById('formQuantity').value = quantity;
            document.getElementById('buyNowQuantity').value = quantity;
        });

        // Add to cart
        document.getElementById('addToCartForm').addEventListener('submit', function(e) {
            e.preventDefault();
            fetch('add_to_cart.php', {method:'POST', body:new FormData(this)})
                .then(r=>r.json()).then(data=>{
                    if(data.success) {
                        document.getElementById('successNotification').style.display='block';
                        setTimeout(()=>document.getElementById('successNotification').style.display='none',3000);
                        if(data.cart_count) document.querySelector('.cart-count').textContent=data.cart_count;
                    }
                });
        });

        function changeImage(img) {
            document.getElementById('mainImage').src = img.src;
            document.querySelectorAll('.thumbnail').forEach(t=>t.classList.remove('active'));
            img.classList.add('active');
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>