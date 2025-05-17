<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gaming store");

if (isset($_POST['reset'])) {
    $new_pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $email = $_SESSION['reset_email'];

    $update = $conn->query("UPDATE admin_list SET password='$new_pass' WHERE email='$email'");
    if ($update) {
        session_destroy();
        echo "Password updated successfully. <a href='login.php'>Login</a>";
    } else {
        echo "Error updating password.";
    }
}
?>

<form method="POST">
    <label>Enter new password:</label>
    <input type="password" name="new_password" required>
    <button type="submit" name="reset">Reset Password</button>
</form>
