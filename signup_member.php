<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $membership = $_POST['membership'];
    $fee = $_POST['fee'];
    $bank = $_POST['bank'];

    // SQL query to insert data into the members table
    $sql = "INSERT INTO members (username, email, membership, fee, bank) 
            VALUES ('$username', '$email', '$membership', '$fee', '$bank')";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        echo "Your data is saved successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close(); // Close the database connection
?>
