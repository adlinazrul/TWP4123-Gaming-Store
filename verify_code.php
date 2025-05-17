<?php
session_start();
if (isset($_POST['verify'])) {
    $entered_code = $_POST['code'];
    if ($entered_code == $_SESSION['verification_code']) {
        header("Location: new_password.php");
        exit();
    } else {
        echo "Invalid code. Try again.";
    }
}
?>

<form method="POST">
    <label>Enter the verification code sent to your email:</label>
    <input type="text" name="code" required>
    <button type="submit" name="verify">Verify Code</button>
</form>
