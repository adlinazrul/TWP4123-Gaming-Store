<?php
include 'db_connect.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['psw'];  // Assuming plain text password
    $password_repeat = $_POST['psw-repeat'];

    // Check if passwords match
    if ($password !== $password_repeat) {
        die("Passwords do not match.");
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // SQL query to insert the data into the database
    $sql = "INSERT INTO admins (email, username, password) VALUES ('$email', '$username', '$hashed_password')";

    if ($conn->query($sql) === TRUE) {
        // Redirect to admin dashboard
        header("Location: admindashboard.html");
        exit();  // Make sure to stop further script execution
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
