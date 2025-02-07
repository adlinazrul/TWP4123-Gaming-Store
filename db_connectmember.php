<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store"; // Ensure this is the correct database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
