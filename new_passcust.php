<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gaming_store");

$message = '';

if (isset($_POST['reset'])) {
    $new_pass = $_POST['new_password']; // No encryption
    $email = $_SESSION['reset_email'] ?? '';

    if (!$email) {
        $message = "<div class='error-message'><svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-alert-circle'><circle cx='12' cy='12' r='10'></circle><line x1='12' y1='8' x2='12' y2='12'></line><line x1='12' y1='16' x2='12' y2='16'></line></svg> Session expired or invalid. Please restart the password reset process.</div>";
    } else {
        $update = $conn->query("UPDATE customers SET password='$new_pass' WHERE email='$email'");
        if ($update) {
            session_destroy();
            header("Location: custlogin.html");
            exit();
        } else {
            $message = "<div class='error-message'><svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-x-circle'><circle cx='12' cy='12' r='10'></circle><line x1='15' y1='9' x2='9' y2='15'></line><line x1='9' y1='9' x2='15' y2='15'></line></svg> Error updating password. Please try again.</div>";
        }
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
