<?php
$servername = "localhost";  // Database server
$username = "root";         // Database username (usually 'root' in local development)
$password = "";             // Database password (empty by default in XAMPP)
$dbname = "gaming_store";   // Your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
