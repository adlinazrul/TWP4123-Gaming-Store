<?php
// Database configuration
$servername = "localhost"; // Your database host (usually localhost)
$username = "root"; // Your database username (default is 'root' for XAMPP)
$password = ""; // Your database password (default is empty for XAMPP)
$dbname = "gaming_store"; // Your database name (change it to your actual database name)

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    // If there is an error connecting, display it
    die("Error: " . $e->getMessage());
}
?>
