<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gaming_store");

$message = '';

// Ensure no output before header()
ob_start();

if (isset($_POST['reset'])) {
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';
    $email = $_SESSION['reset_email'] ?? '';

    if (!$email) {
        $message = "<div class='error-message'>⚠️ Session expired or invalid. Please restart the password reset process.</div>";
    } elseif ($new_pass !== $confirm_pass) {
        $message = "<div class='error-message'>❗Passwords do not match. Please try again.</div>";
    } else {
        $hashed_pass = password_hash($new_pass, PASSWORD_BCRYPT);
        $update = $conn->prepare("UPDATE customers SET password=? WHERE email=?");
        $update->bind_param("ss", $hashed_pass, $email);

        if ($update->execute()) {
            session_destroy();
            header("Location: custlogin.html");
            exit(); // Ensure redirect
        } else {
            $message = "<div class='error-message'>❌ Error updating password. Please try again.</div>";
        }
    }
}
?>

<!-- HTML -->
<h2>🔐 Reset Your Password</h2>
<?php if (!empty($message)) echo $message; ?>
<form method="POST">
    <label>New Password:</label><br>
    <input type="password" name="new_password" class="form-control"  pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
            title="Must contain at least one number, one lowercase letter, one uppercase letter, one special character, and be at least 8 characters long" required>
                <span onclick="togglePassword('new_password')" style="position:absolute; top:68%; right:10px; transform:translateY(-50%); cursor:pointer;">👁️</span> required><br><br>

    <label>Confirm New Password:</label><br>
    <input type="password" name="confirm_password" class="form-control"  pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
            title="Must contain at least one number, one lowercase letter, one uppercase letter, one special character, and be at least 8 characters long" required>
                <span onclick="togglePassword('new_password')" style="position:absolute; top:68%; right:10px; transform:translateY(-50%); cursor:pointer;">👁️</span> required><br><br>

    <button type="submit" name="reset">Update Password</button>
</form>

