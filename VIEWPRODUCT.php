<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$product = null;

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
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
    <title><?= htmlspecialchars($product['product_name']) ?> - Product Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial, sans-serif';
            background-color: white;
            color: black;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #d03b3b;
            color: white;
            padding: 20px 0;
        }
        .nav-menu {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .nav-links {
            display: flex;
            gap: 20px;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
        }
        .nav-links a.active {
            font-weight: bold;
            text-decoration: underline;
        }
        .icons-left, .icons-right {
            display: flex;
            gap: 20px;
        }
        .icons-left i, .icons-right i {
            font-size: 24px;
            cursor: pointer;
        }
        .product-details {
            max-width: 1200px;
            margin: 40px auto;
            padding: 40px;
            border: 1px solid black;
            background-color: white;
            display: flex;
            gap: 40px;
        }
        .product-image {
            flex: 1;
        }
        .product-image img {
            max-width: 100%;
            height: auto;
        }
        .product-info {
            flex: 2;
        }
        .product-info h2 {
            margin-bottom: 20px;
        }
        .product-info .price {
            font-weight: bold;
            color: #d03b3b;
            margin-bottom: 20px;
        }
        .product-info .quantity {
            margin-bottom: 20px;
        }
        .product-info label {
            margin-right: 10px;
        }
        .product-info input[type="number"] {
            padding: 10px;
        }
        .product-info .buttons {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .product-info button {
            padding: 10px 20px;
            cursor: pointer;
            border: none;
            color: white;
        }
        .add-to-cart {
            background-color: #d03b3b;
        }
        .buy-now {
            background-color: #000;
        }
        .description {
            margin-top: 20px;
        }
        footer {
            background-color: #d03b3b;
            color: white;
            text-align: center;
            padding: 20px 0;
        }
    </style>
</head>
<body>

<header>
    <nav class="nav-menu">
        <div class="icons-left">
            <i class="fas fa-bars"></i>
            <i class="fas fa-search"></i>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="nintendo.php">Nintendo</a>
            <a href="consoles.php">Consoles</a>
            <a href="xbox.php">Xbox</a>
            <a href="accessories.php">Accessories</a>
            <a href="vr.php">VR</a>
        </div>
        <div class="icons-right">
            <i class="fas fa-user"></i>
            <i class="fas fa-shopping-cart"></i>
        </div>
    </nav>
</header>

<section class="product-details">
    <div class="product-image">
        <img src="uploads/<?= htmlspecialchars($product['product_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
    </div>
    <div class="product-info">
        <h2><?= htmlspecialchars($product['product_name']) ?></h2>
        <p class="price">RM <?= number_format($product['product_price'], 2) ?></p>

        <div class="quantity">
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $product['product_quantity'] ?>">
        </div>

        <div class="buttons">
            <button class="add-to-cart">Add to Cart</button>
            <button class="buy-now">Buy Now</button>
        </div>

        <div class="description">
            <p><?= nl2br(htmlspecialchars($product['product_description'])) ?></p>
        </div>
    </div>
</section>

<footer>
    <p>&copy; 2025 Online Gaming Store. All rights reserved.</p>
</footer>

</body>
</html>

<?php $conn->close(); ?>
