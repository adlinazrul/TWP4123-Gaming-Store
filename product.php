<?php
$host = "localhost"; // Change if needed
$user = "root";      // Default user for XAMPP/WAMP
$pass = "";          // Default is empty for XAMPP
$db = "gaming_store";  // The database name

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
