<?php
include 'db_connect.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['psw'];
    $passwordRepeat = $_POST['psw-repeat'];

    // Validation (you can add more here)
    if ($password == $passwordRepeat) {
        // Here you would connect to your database and save the user data
        // Example:
        // $conn = new mysqli('localhost', 'username', 'password', 'database');
        // if ($conn->connect_error) {
        //     die("Connection failed: " . $conn->connect_error);
        // }
        // $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // $sql = "INSERT INTO users (email, username, password) VALUES ('$email', '$username', '$hashedPassword')";
        // if ($conn->query($sql) === TRUE) {
        //     echo "New record created successfully";
        // } else {
        //     echo "Error: " . $sql . "<br>" . $conn->error;
        // }
        // $conn->close();

        // After successful sign up, redirect to the admin dashboard
        header("Location: admindashboard.html");
        exit(); // Always call exit after header redirect to stop further execution
    } else {
        echo "Passwords do not match!";
    }
}
?>
