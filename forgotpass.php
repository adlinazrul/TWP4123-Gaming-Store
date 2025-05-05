<?php
// Include database connection
include 'db_connect1.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $query = "SELECT * FROM customers WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email exists, redirect to custresetpass.html
        $_SESSION['email'] = $email; // Store email in session for use in custresetpass.php
        header("Location: custresetpass.html");
        exit();
    } else {
        // Email does not exist, show an error message
        echo "Email not found. Please try again.";
    }

    $stmt->close();
}
?>
