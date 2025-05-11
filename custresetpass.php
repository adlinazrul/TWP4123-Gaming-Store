<?php
// Include database connection
include 'db_connect1.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $old_password = $_POST['old-password'];
    $new_password = $_POST['new-password'];
    $confirm_password = $_POST['confirm-password'];

    // Fetch the current password from the database
    $query = "SELECT password FROM customers WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($current_password);
    $stmt->fetch();
    $stmt->close();

    // Verify the old password
    if (password_verify($old_password, $current_password)) {
        // Check if new password and confirmation match
        if ($new_password === $confirm_password) {
            // Hash the new password
            $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $update_query = "UPDATE customers SET password = ? WHERE email = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ss", $new_password_hashed, $email);
            $update_stmt->execute();
            $update_stmt->close();

            echo "Password updated successfully!";
        } else {
            echo "New password and confirmation do not match.";
        }
    } else {
        echo "Old password is incorrect.";
    }
}
?>
