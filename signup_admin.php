<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['psw'];
    $password_repeat = $_POST['psw-repeat'];

    // Check if passwords match
    if ($password !== $password_repeat) {
        die("Passwords do not match.");
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL query
    $stmt = $conn->prepare("INSERT INTO admins (email, username, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Error in preparing SQL: " . $conn->error);
    }
    $stmt->bind_param("sss", $email, $username, $hashed_password);

    if ($stmt->execute()) {
        // Redirect to admin dashboard if signup is successful
        header("Location: admin_dashboard.html");
        exit(); // Ensure script stops executing after redirection
    } else {
        die("Error executing query: " . $stmt->error); // Display SQL error if any
    }

    $stmt->close();
    $conn->close();
}
?>
