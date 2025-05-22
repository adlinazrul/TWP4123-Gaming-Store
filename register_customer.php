<?php
include 'db_connect1.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = sanitize($_POST["first_name"] ?? '');
    $last_name = sanitize($_POST["last_name"] ?? '');
    $username = sanitize($_POST["username"] ?? '');
    $email = sanitize($_POST["email"] ?? '');
    $phone = sanitize($_POST["phone"] ?? '');
    $address = sanitize($_POST["address"] ?? '');
    $city = sanitize($_POST["city"] ?? '');
    $state = sanitize($_POST["state"] ?? '');
    $postcode = sanitize($_POST["postcode"] ?? '');
    $country = sanitize($_POST["country"] ?? '');
   
    $password_raw = $_POST["password"] ?? '';
    $confirm_password = $_POST["confirm-password"] ?? '';

    if ($password_raw !== $confirm_password) {
        echo "<script>alert('Passwords do not match. Please try again.'); window.history.back();</script>";
        exit();
    }

    // Hash the password before storing it
    $hashed_password = password_hash($password_raw, PASSWORD_DEFAULT);

    // Check for duplicate email or username
    $check_sql = "SELECT * FROM customers WHERE email = ? OR username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $email, $username);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email or username already exists. Please choose another.'); window.history.back();</script>";
        exit();
    }
    $check_stmt->close();

    // Insert into database
    $sql = "INSERT INTO customers 
        (first_name, last_name, username, email, phone, address, city, state, postcode, country, password) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        echo "<script>alert('System error. Try again later.');</script>";
        exit();
    }

    $stmt->bind_param(
        "sssssssssss",
        $first_name, $last_name, $username, $email, $phone,
        $address, $city, $state, $postcode, $country, $hashed_password
    );

    if ($stmt->execute()) {
        echo "<script>alert('New customer registered successfully'); window.location.href='login.php';</script>";
    } else {
        error_log("Execute failed: " . $stmt->error);
        echo "<script>alert('Registration failed. Try again.');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
