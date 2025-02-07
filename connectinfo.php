<?php
// Load environment variables
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection
$servername = $_ENV['DB_HOST']; // Corrected
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME']; // Corrected

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve and sanitize form data
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$contact = filter_var($_POST['contact'], FILTER_SANITIZE_STRING);
$address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
$payment_method = filter_var($_POST['payment_method'], FILTER_SANITIZE_STRING);
$total_amount = 54.99; // Fixed total amount for now

// Handle payment details based on method
$cc_number = isset($_POST['cc-number']) ? filter_var($_POST['cc-number'], FILTER_SANITIZE_STRING) : null;
$cc_name = isset($_POST['cc-name']) ? filter_var($_POST['cc-name'], FILTER_SANITIZE_STRING) : null;
$cc_expiry = isset($_POST['cc-expiry']) ? filter_var($_POST['cc-expiry'], FILTER_SANITIZE_STRING) : null;
$cc_cvv = isset($_POST['cc-cvv']) ? filter_var($_POST['cc-cvv'], FILTER_SANITIZE_STRING) : null;
$bank_name = isset($_POST['bank-name']) ? filter_var($_POST['bank-name'], FILTER_SANITIZE_STRING) : null;

// Use a prepared statement
$sql = "INSERT INTO payments (email, contact, address, payment_method, cc_number, cc_name, cc_expiry, cc_cvv, bank_name, total_amount) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssd", $email, $contact, $address, $payment_method, $cc_number, $cc_name, $cc_expiry, $cc_cvv, $bank_name, $total_amount);

// Execute the statement
if ($stmt->execute()) {
    echo "Payment successful!";
} else {
    echo "Error: " . $stmt->error;
}

// Close connection
$stmt->close();
$conn->close();
?>
