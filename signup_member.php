<?php
include 'db_connectmember.php'; // Make sure this is correct, including connection details.

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch form data
    $username = isset($_POST['username']) ? $conn->real_escape_string($_POST['username']) : '';
    $email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
    $membership = isset($_POST['membership']) ? $conn->real_escape_string($_POST['membership']) : '';
    $fee = isset($_POST['fee']) ? $_POST['fee'] : '';
    $bank = isset($_POST['bank']) ? $conn->real_escape_string($_POST['bank']) : '';

    // Validate fields
    if (empty($username) || empty($email) || empty($membership) || empty($fee) || empty($bank)) {
        die("Error: All fields are required!");
    }

    // Prepare the SQL query to insert data into the members table
    $sql = "INSERT INTO members (username, email, membership, fee, bank) 
            VALUES ('$username', '$email', '$membership', '$fee', '$bank')";

    // Check if query was successful
    if ($conn->query($sql) === TRUE) {
        echo "Member added successfully!";
    } else {
        echo "Error: " . $conn->error; // Show any error that occurred
    }

    $conn->close();
}
?>
