<?php
session_start();
include 'db_connection.php'; // your DB connection file

// Check login
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// 1. Retrieve customer info
$customer_sql = $conn->prepare("SELECT * FROM customers WHERE email = ?");
$customer_sql->bind_param("s", $email);
$customer_sql->execute();
$customer_result = $customer_sql->get_result();
$customer = $customer_result->fetch_assoc();

if (!$customer) {
    die("Customer not found.");
}

// 2. Get POSTed form data
$card_number = $_POST['card_number'];
$cardholder_name = $_POST['cardholder_name'];
$expiry_date = $_POST['expiry_date'];
$cvv = $_POST['cvv'];
$street_address = $_POST['street_address'];
$city = $_POST['city'];
$state = $_POST['state'];
$postcode = $_POST['postcode'];
$country = $_POST['country'];

// 3. Validate credit card
$card_check = $conn->prepare("SELECT * FROM credit_card WHERE card_number = ? AND cardholder_name = ? AND expiry_date = ? AND cvv = ?");
$card_check->bind_param("ssss", $card_number, $cardholder_name, $expiry_date, $cvv);
$card_check->execute();
$card_result = $card_check->get_result();

if ($card_result->num_rows === 0) {
    die("Invalid credit card details.");
}

// 4. Get cart items
$cart_sql = $conn->prepare("SELECT c.*, p.product_name, p.product_price, p.product_image FROM cart_items c JOIN products p ON c.product_id = p.id WHERE c.email = ?");
$cart_sql->bind_param("s", $email);
$cart_sql->execute();
$cart_items = $cart_sql->get_result();

if ($cart_items->num_rows === 0) {
    die("Cart is empty.");
}

// 5. Calculate total and tax
$total_price = 0;
$tax_rate = 0.06; // 6%
while ($item = $cart_items->fetch_assoc()) {
    $total_price += $item['product_price'] * $item['quantity'];
}
$tax_fee = $total_price * $tax_rate;
$final_price = $total_price + $tax_fee;

// 6. Insert into orders
$order_sql = $conn->prepare("INSERT INTO orders (user_id, first_name, last_name, email, phone_number, street_address, city, state, postcode, country, card_number, cardholder_name, expiry_date, cvv, total_price, tax_fee, date, status_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Processing')");
$order_sql->bind_param("isssssssssssssdd",
    $customer['id'], $customer['first_name'], $customer['last_name'], $email, $customer['phone'],
    $street_address, $city, $state, $postcode, $country,
    $card_number, $cardholder_name, $expiry_date, $cvv,
    $total_price, $tax_fee
);
$order_sql->execute();
$order_id = $conn->insert_id;

// 7. Re-fetch cart items (for inserting to items_ordered)
$cart_sql->execute();
$cart_items = $cart_sql->get_result();

while ($item = $cart_items->fetch_assoc()) {
    $insert_item = $conn->prepare("INSERT INTO items_ordered (order_id, product_name, price_items, quantity_items, image_items, status_order, name_cust, num_tel_cust, address_cust, date) VALUES (?, ?, ?, ?, ?, 'Processing', ?, ?, ?, NOW())");
    $full_name = $customer['first_name'] . ' ' . $customer['last_name'];
    $full_address = $street_address . ', ' . $city . ', ' . $state . ', ' . $postcode . ', ' . $country;
    $insert_item->bind_param("isdisss",
        $order_id, $item['product_name'], $item['product_price'], $item['quantity'], $item['product_image'],
        $full_name, $customer['phone'], $full_address
    );
    $insert_item->execute();
}

// 8. Delete from cart_items
$delete_cart = $conn->prepare("DELETE FROM cart_items WHERE email = ?");
$delete_cart->bind_param("s", $email);
$delete_cart->execute();

// 9. Redirect to Order History
header("Location: ORDERHISTORY.php");
exit();
?>
