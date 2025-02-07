<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $username = isset($_POST['username']) ? $conn->real_escape_string($_POST['username']) : '';
    $email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
    $membership = isset($_POST['membership']) ? $conn->real_escape_string($_POST['membership']) : '';
    $fee = isset($_POST['fee']) ? $conn->real_escape_string($_POST['fee']) : '';
    $bank = isset($_POST['bank']) ? $conn->real_escape_string($_POST['bank']) : '';

    // Validate required fields
    if (empty($username) || empty($email) || empty($membership) || empty($fee) || empty($bank)) {
        die("Error: All fields are required!");
    }

    // Insert data into database
    $sql = "INSERT INTO members (username, email, membership, fee, bank) 
            VALUES ('$username', '$email', '$membership', '$fee', '$bank')";

    if ($conn->query($sql) === TRUE) {
        echo "Your data is saved successfully!";
    } else {
        echo "Error: " . $conn->error; // Show the actual error
    }
}

$conn->close();
?>
