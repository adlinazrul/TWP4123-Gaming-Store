<?php
$servername = "localhost";
$username = "root";  // Default user for XAMPP
$password = "";  // Default password is empty
$dbname = "customer_support";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
