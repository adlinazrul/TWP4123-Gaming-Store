<?php
$servername = "localhost"; // Change to your server, usually 'localhost' if you're running on your local machine
$username = "root"; // Your MySQL username (default is 'root')
$password = ""; // Your MySQL password (default is empty)
$dbname = "gaming_store"; // Name of your database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
?>
