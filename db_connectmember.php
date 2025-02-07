<?php
$servername = "localhost"; // Change to your server (usually 'localhost' for local development)
$username = "root"; // MySQL username (default is 'root' in many cases)
$password = ""; // MySQL password (default is empty for local development)
$dbname = "gaming_store"; // The name of your database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
