<?php
include 'db_connect1.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST["first-name"];
    $last_name = $_POST["last-name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"] ?? ''; // Optional field
    $address = $_POST["address"]; // Add this line to capture the address
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT); // Hash password for security

    // Insert into database
    $sql = "INSERT INTO customers (first_name, last_name, email, phone, address, password) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $first_name, $last_name, $email, $phone, $address, $password);

    if ($stmt->execute()) {
        echo "New customer registered successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
