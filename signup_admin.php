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
                // Hash the password for security before saving to database
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // SQL query to insert data into the admin_users table
                $sql = "INSERT INTO admin_users (email, username, password) VALUES ('$email', '$username', '$hashedPassword')";
        
                // Execute the query
                if ($conn->query($sql) === TRUE) {
                    // After successful sign up, redirect to the admin dashboard
                    header("Location: admindashboard.html");
                    exit(); // Stop further execution after the redirect
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            } else {
                echo "Passwords do not match!";
            }
        }
        
        $conn->close();  // Close the database connection after the operation
        ?>
        
