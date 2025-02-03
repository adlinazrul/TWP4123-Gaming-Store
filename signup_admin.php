<?php
// Include database connection
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from form submission
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['psw'];  // The raw password from the form

    // Hash the password before storing it (security measure)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL query to insert data into the database
    $sql = "INSERT INTO admin_users (email, username, password) VALUES (?, ?, ?)";

    // Initialize prepared statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("sss", $email, $username, $hashed_password);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to admin dashboard after successful signup
            header('Location: admindashboard.html');
            exit();
        } else {
            // Handle error if execution fails
            echo "Error: " . $stmt->error;
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>
