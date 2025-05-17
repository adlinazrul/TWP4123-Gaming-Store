<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gaming_store");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if reset_email is set in session
if (!isset($_SESSION['reset_email'])) {
    die("⚠️ Session expired. Go back to <a href='forgotpass.php'>Forgot Password</a>.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        echo "<script>alert('❌ Passwords do not match!');</script>";
    } elseif (strlen($newPassword) < 6) {
        echo "<script>alert('🔐 Password should be at least 6 characters.');</script>";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $email = $_SESSION['reset_email'];

        $stmt = $conn->prepare("UPDATE customers SET password = ?, reset_code = NULL, reset_expiry = NULL WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);

        if ($stmt->execute()) {
            // Clear session
            unset($_SESSION['reset_email']);
            echo "<script>alert('✅ Password updated successfully. Please log in.'); window.location.href='custlogin.html';</script>";
        } else {
            echo "❌ Error updating password: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<h2>🔐 Reset Your Password</h2>
<form method="POST">
    <label>New Password:</label><br>
    <input type="password" name="new_password" required><br><br>

    <label>Confirm New Password:</label><br>
    <input type="password" name="confirm_password" required><br><br>

    <button type="submit">Update Password</button>
</form>
