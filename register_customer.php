<?php
include 'db_connect1.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple sanitization function
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data - note the underscores replacing hyphens
    $first_name = sanitize($_POST["first_name"] ?? '');
    $last_name = sanitize($_POST["last_name"] ?? '');
    $email = sanitize($_POST["email"] ?? '');
    $phone = sanitize($_POST["phone"] ?? '');
    $address = sanitize($_POST["address"] ?? '');
    $city = sanitize($_POST["city"] ?? '');
    $state = sanitize($_POST["state"] ?? '');
    $postcode = sanitize($_POST["postcode"] ?? '');
    $country = sanitize($_POST["country"] ?? '');
    $password = password_hash($_POST["password"] ?? '', PASSWORD_BCRYPT);

    // Insert into database
    $sql = "INSERT INTO customers (first_name, last_name, email, phone, address, city, state, postcode, country, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ssssssssss", $first_name, $last_name, $email, $phone, $address, $city, $state, $postcode, $country, $password);

    if ($stmt->execute()) {
        echo "New customer registered successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>