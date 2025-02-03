<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "customer_registration";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$first_name = $_POST['first-name'];
$last_name = $_POST['last-name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

// Insert data into the database
$sql "INSERT INTO customers (first_name, last_name, email, phone, password) VALUES ('$first_name', '$last_name', '$email', '$phone', '$password')";

if ($conn->query($sql) === TRUE) {
    echo "New customer registered successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>