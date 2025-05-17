<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gaming_store");

$message = '';

if (isset($_POST['reset'])) {
    $new_pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $email = $_SESSION['reset_email'] ?? '';

    if (!$email) {
        $message = 'Session expired or invalid. Please restart the password reset process.';
    } else {
        $update = $conn->query("UPDATE admin_list SET password='$new_pass' WHERE email='$email'");
        if ($update) {
            session_destroy();
            $message = "✅ Password updated successfully. <a href='login.php'>Login here</a>";
        } else {
            $message = "❌ Error updating password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Reset Password - Gaming Store</title>
<style>
    body {
        background: linear-gradient(135deg, #667eea, #764ba2);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .container {
        background: #fff;
        padding: 2rem 3rem;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        max-width: 400px;
        width: 100%;
        text-align: center;
    }
    h2 {
        margin-bottom: 1.5rem;
        color: #4a148c;
    }
    input[type="password"] {
        width: 100%;
        padding: 12px 15px;
        margin: 1rem 0 1.5rem 0;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }
    input[type="password"]:focus {
        border-color: #764ba2;
        outline: none;
    }
    button {
        background-color: #764ba2;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s ease;
        width: 100%;
    }
    button:hover {
        background-color: #5a3571;
    }
    .message {
        margin-top: 1rem;
        font-size: 0.95rem;
        color: #e53935;
    }
    .message a {
        color: #764ba2;
        text-decoration: none;
        font-weight: 600;
    }
    .message a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
    <div class="container">
        <h2>Reset Your Password</h2>

        <?php if ($message) : ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <?php if (!$message || strpos($message, 'Password updated successfully') === false): ?>
        <form method="POST" novalidate>
            <input type="password" name="new_password" placeholder="Enter new password" required minlength="6" />
            <button type="submit" name="reset">Reset Password</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
