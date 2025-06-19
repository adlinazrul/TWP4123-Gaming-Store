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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Nexus Gaming Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/nexus-styles.css">
    <style>
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        .auth-container {
            max-width: 500px;
            margin: 5rem auto;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            background-color: #1a1a2e;
            color: #fff;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
            color: #00ffff;
        }
        .btn-nexus {
            background-color: #00ffff;
            color: #1a1a2e;
            border: none;
            font-weight: bold;
            width: 100%;
            padding: 10px;
            margin-top: 1rem;
        }
        .btn-nexus:hover {
            background-color:rgb(179, 0, 0);
            color: #fff;
        }
        .form-label {
            color: #00ffff;
        }
    </style>
</head>
<body class="bg-dark">
    <?php include('includes/header.php'); ?>
    
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h2><i class="fas fa-key"></i> Reset Your Password</h2>
                <p class="text-muted">Please enter your new password below</p>
            </div>
            
            <?php if (!empty($message)) echo $message; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <div class="password-container">
                        <input type="password" class="form-control bg-dark text-light" id="new_password" name="new_password" 
                               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required>
                        <span class="password-toggle" onclick="togglePassword('new_password')">
                            <i class="far fa-eye"></i>
                        </span>
                    </div>
                    <div class="form-text text-muted">
                        Must contain: 8+ characters, 1 uppercase, 1 lowercase, 1 number
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <div class="password-container">
                        <input type="password" class="form-control bg-dark text-light" id="confirm_password" name="confirm_password" required>
                        <span class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="far fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <button type="submit" name="reset" class="btn btn-nexus">
                    <i class="fas fa-sync-alt"></i> Update Password
                </button>
            </form>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
