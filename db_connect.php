<?php
$servername = "localhost"; // Change if needed
$username = "root"; // Default username for XAMPP
$password = ""; // Default password for XAMPP is empty
$database = "gaming_store"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
