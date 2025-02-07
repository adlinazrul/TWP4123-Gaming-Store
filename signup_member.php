<?php
// Include the database connection file
include 'db_connectmember.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch the form data
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $membership = isset($_POST['membership']) ? $_POST['membership'] : '';
    $fee = isset($_POST['fee']) ? $_POST['fee'] : '';
    $bank = isset($_POST['bank']) ? $_POST['bank'] : '';

    // Validate fields
    if (empty($username) || empty($email) || empty($membership) || empty($fee) || empty($bank)) {
        echo "Error: All fields are required!";
        exit;
    }

    // Prepare and bind the SQL query
    $stmt = $conn->prepare("INSERT INTO member (username, email, membership, fee, bank) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $membership, $fee, $bank);

    // Execute the query
    if ($stmt->execute()) {
        echo "Member added successfully!";
    } else {
        echo "Error: " . $stmt->error; // Show any error that occurred
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
