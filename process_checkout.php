<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['email'])) {
    header("Location: custlogin.php");
    exit();
}

$email = $_SESSION['email'];

// Get user ID from customers table
$user_query = $conn->prepare("SELECT id FROM customers WHERE email = ?");
$user_query->bind_param("s", $email);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
$user_id = $user['id'];

// Sanitize POST data
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$phone_number = $_POST['phone_number'];
$street_address = $_POST['street_address'];
$city = $_POST['city'];
$state = $_POST['state'];
$postcode = $_POST['postcode'];
$country = $_POST['country'];
$cardholder_name = $_POST['cardholder_name'];
$card_number = str_replace(' ', '', $_POST['card_number']); // remove spaces
$expiry_date = $_POST['expiry_date'];
$cvv = $_POST['cvv'];
$total_price = $_POST['total_price'];
$tax_fee = $_POST['tax_fee'];
$order_date = date("Y-m-d H:i:s");
$status_order = "Pending";

// Determine order type
$order_type = $_POST['order_type'] ?? 'cart'; // default to cart if not set

// Validate credit card against dummy table
$card_query = $conn->prepare("SELECT * FROM credit_card WHERE card_number = ? AND cardholder_name = ? AND expiry_date = ? AND cvv = ?");
$card_query->bind_param("ssss", $card_number, $cardholder_name, $expiry_date, $cvv);
$card_query->execute();
$card_result = $card_query->get_result();

if ($card_result->num_rows === 0) {
    echo "<script>alert('Invalid credit card information.'); window.location.href='checkout.php';</script>";
    exit();
}

// Insert into orders table
$order_stmt = $conn->prepare("INSERT INTO orders (user_id, first_name, last_name, email, phone_number, street_address, city, state, postcode, country, card_number, cardholder_name, expiry_date, cvv, total_price, tax_fee, date, status_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$order_stmt->bind_param(
    "isssssssssssssddss",
    $user_id, $first_name, $last_name, $email, $phone_number,
    $street_address, $city, $state, $postcode, $country,
    $card_number, $cardholder_name, $expiry_date, $cvv,
    $total_price, $tax_fee, $order_date, $status_order
);

if (!$order_stmt->execute()) {
    die("Order insertion failed: " . $order_stmt->error);
}

$order_id = $order_stmt->insert_id;

// Unified full address and name
$full_name = $first_name . " " . $last_name;
$full_address = $street_address . ", " . $city . ", " . $state . " " . $postcode . ", " . $country;

// ✅ If "Buy Now" from checkout2.php
if ($order_type === 'buy_now') {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Get product details
    $product_stmt = $conn->prepare("SELECT product_name, product_price, product_image FROM products WHERE id = ?");
    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();

    if ($product_result->num_rows > 0) {
        $product = $product_result->fetch_assoc();

        $name = $product['product_name'];
        $price = $product['product_price'];
        $image = $product['product_image'];

        // Insert item into items_ordered
        $item_stmt = $conn->prepare("INSERT INTO items_ordered (order_id, product_name, price_items, quantity_items, image_items, status_order, name_cust, num_tel_cust, address_cust, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $item_stmt->bind_param(
            "isdissssss",
            $order_id,
            $name,
            $price,
            $quantity,
            $image,
            $status_order,
            $full_name,
            $phone_number,
            $full_address,
            $order_date
        );
        $item_stmt->execute();
    }
}
// ✅ If from Cart
else if (isset($_POST['cart']) && is_array($_POST['cart'])) {
    foreach ($_POST['cart'] as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];

        $product_stmt = $conn->prepare("SELECT product_name, product_price, product_image FROM products WHERE id = ?");
        $product_stmt->bind_param("i", $product_id);
        $product_stmt->execute();
        $product_result = $product_stmt->get_result();
        $product = $product_result->fetch_assoc();

        $name = $product['product_name'];
        $price = $product['product_price'];
        $image = $product['product_image'];

        $item_stmt = $conn->prepare("INSERT INTO items_ordered (order_id, product_name, price_items, quantity_items, image_items, status_order, name_cust, num_tel_cust, address_cust, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $item_stmt->bind_param(
            "isdissssss",
            $order_id,
            $name,
            $price,
            $quantity,
            $image,
            $status_order,
            $full_name,
            $phone_number,
            $full_address,
            $order_date
        );
        $item_stmt->execute();
    }

    // Clear cart after order
    $clear_cart = $conn->prepare("DELETE FROM cart_items WHERE email = ?");
    $clear_cart->bind_param("s", $email);
    $clear_cart->execute();
}

echo "<script>alert('Order placed successfully!'); window.location.href='ORDERHISTORY.php';</script>";
?>
