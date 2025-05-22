<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

$conn = new mysqli("localhost", "root", "", "gaming_store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Get current password from DB
$current_password = "";
$sql = "SELECT password FROM admin_list WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($current_password);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Compare old password with current password from DB
    if ($old_password === $current_password) {
        if ($new_password === $confirm_password) {
            // Update to new password
            $sql = "UPDATE admin_list SET password=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_password, $admin_id);

            if ($stmt->execute()) {
                $message = "Password updated successfully!";
            } else {
                $message = "Error updating password: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $message = "New password and confirm password do not match.";
        }
    } else {
        $message = "Old password is incorrect.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Password</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
        
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: radial-gradient(circle at 15% 50%, rgba(239, 68, 68, 0.05) 0%, rgba(239, 68, 68, 0.05) 25%,transparent 25%, transparent 100%), radial-gradient(circle at 85% 30%, rgba(239, 68, 68, 0.05) 0%, rgba(239, 68, 68, 0.05) 25%,transparent 25%, transparent 100%);
            background-size: 40px 40px;
            animation: backgroundMovement 20s linear infinite;
        }

        @keyframes backgroundMovement {
            0% { background-position: 0 0, 0 0; }
            100% { background-position: 100px 100px, -100px -100px; }
        }

        .container {
            max-width: 500px;
            width: 90%;
            margin: 20px auto;
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transform: translateY(0);
            opacity: 1;
            transition: all 0.4s ease-out;
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #ef4444, #f97316);
        }

        h2 {
            text-align: center;
            color: #ef4444;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
            position: relative;
            display: inline-block;
            width: 100%;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: #ef4444;
            border-radius: 3px;
        }

        form label {
            display: block;
            margin-top: 25px;
            font-weight: 500;
            color: #333;
            transition: all 0.3s ease;
            transform: translateY(0);
        }

        form label:hover {
            transform: translateY(-2px);
        }

        .password-field {
            position: relative;
            margin-top: 8px;
        }

        form input[type="password"] {
            width: 100%;
            padding: 14px 20px;
            margin-top: 5px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        form input[type="password"]:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
            outline: none;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 14px;
        }

        form input[type="submit"] {
            margin-top: 35px;
            background-color: #ef4444;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        form input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
        }

        form input[type="submit"]:active {
            transform: translateY(0);
        }

        .message {
            margin: 25px 0;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
            animation: fadeIn 0.5s ease-out;
            transform-origin: top;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.error {
            color: #ef4444;
            background-color: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
        }

        .message.success {
            color: #10b981;
            background-color: rgba(16, 185, 129, 0.1);
            border-left: 4px solid #10b981;
        }

        .back-link {
            display: block;
            margin-top: 25px;
            text-align: center;
            text-decoration: none;
            color: #3b82f6;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            padding-bottom: 2px;
        }

        .back-link:hover {
            color: #2563eb;
        }

        .back-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: #3b82f6;
            transition: width 0.3s ease;
        }

        .back-link:hover::after {
            width: 100%;
        }

        /* Password strength meter */
        .password-strength {
            height: 5px;
            background: #eee;
            border-radius: 5px;
            margin-top: 10px;
            overflow: hidden;
            position: relative;
        }

        .strength-meter {
            height: 100%;
            width: 0;
            background: #ef4444;
            transition: all 0.3s ease;
        }

        /* Floating animation */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .security-icon {
            text-align: center;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }

        .security-icon svg {
            width: 80px;
            height: 80px;
            fill: #ef4444;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="security-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11V11.99z"/>
                <path d="M0 0h24v24H0z" fill="none"/>
            </svg>
        </div>
        
        <h2>Edit Password</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="passwordForm">
            <label for="old_password">Old Password:</label>
            <div class="password-field">
                <input type="password" name="old_password" id="old_password" required>
                <button type="button" class="toggle-password" onclick="togglePassword('old_password')">Show</button>
            </div>

            <label for="new_password">New Password:</label>
            <div class="password-field">
                <input type="password" name="new_password" id="new_password" required oninput="checkPasswordStrength(this.value)">
                <button type="button" class="toggle-password" onclick="togglePassword('new_password')">Show</button>
                <div class="password-strength">
                    <div class="strength-meter" id="strengthMeter"></div>
                </div>
            </div>

            <label for="confirm_password">Confirm New Password:</label>
            <div class="password-field">
                <input type="password" name="confirm_password" id="confirm_password" required>
                <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">Show</button>
            </div>

            <input type="submit" value="Update Password">
        </form>

        <a href="profile_admin.php" class="back-link">← Back to Profile</a>
    </div>

    <script>
        // Page load animation
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.querySelector('.container');
            setTimeout(() => {
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
        });

        // Toggle password visibility
        function togglePassword(id) {
            const input = document.getElementById(id);
            const button = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = 'Hide';
            } else {
                input.type = 'password';
                button.textContent = 'Show';
            }
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            const meter = document.getElementById('strengthMeter');
            let strength = 0;
            
            if (password.length > 0) strength += 1;
            if (password.length >= 8) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            const width = strength * 20;
            meter.style.width = width + '%';
            
            // Change color based on strength
            if (strength <= 2) {
                meter.style.backgroundColor = '#ef4444';
            } else if (strength <= 4) {
                meter.style.backgroundColor = '#f59e0b';
            } else {
                meter.style.backgroundColor = '#10b981';
            }
        }

        // Form submission animation
        const form = document.getElementById('passwordForm');
        form.addEventListener('submit', (e) => {
            const submitBtn = form.querySelector('input[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.value = 'Updating...';
        });
    </script>
</body>
</html>