<?php
// Start session to store logged-in status
session_start();

include 'db_connect.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $username = $_POST['uname'];
    $password = $_POST['psw'];

    // SQL query to fetch the user with the entered username
    $sql = "SELECT * FROM admin_list WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username); // "s" means the parameter is a string
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists
    if ($result->num_rows > 0) {
        // User exists, fetch the data
        $user = $result->fetch_assoc();

        // Verify the entered password with the stored hashed password
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session and redirect to dashboard
            $_SESSION['username'] = $username; // Store username in session
            header("Location: admindashboard.html"); // Redirect to admin dashboard
            exit(); // Stop further execution
        } else {
            // Incorrect password
            echo "Invalid password. Please try again.";
        }
    } else {
        // Username not found
        echo "No user found with this username.";
    }

    $stmt->close(); // Close the prepared statement
}

$conn->close(); // Close the database connection
?>
