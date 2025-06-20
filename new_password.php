<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gaming_store");

$message = '';

if (isset($_POST['reset'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'] ?? '';

    if (!$email) {
        $message = "<div class='error-message'><svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-alert-circle'><circle cx='12' cy='12' r='10'></circle><line x1='12' y1='8' x2='12' y2='12'></line><line x1='12' y1='16' x2='12' y2='16'></line></svg> Session expired or invalid. Please restart the password reset process.</div>";
    } elseif ($new_pass !== $confirm_pass) {
        $message = "<div class='error-message'><svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-x-circle'><circle cx='12' cy='12' r='10'></circle><line x1='15' y1='9' x2='9' y2='15'></line><line x1='9' y1='9' x2='15' y2='15'></line></svg> Passwords do not match.</div>";
    } elseif (strlen($new_pass) < 12) {
        $message = "<div class='error-message'><svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-x-circle'><circle cx='12' cy='12' r='10'></circle><line x1='15' y1='9' x2='9' y2='15'></line><line x1='9' y1='9' x2='15' y2='15'></line></svg> Password must be at least 12 characters long.</div>";
    } elseif (!preg_match('/[A-Z]/', $new_pass)) {
        $message = "<div class='error-message'><svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-x-circle'><circle cx='12' cy='12' r='10'></circle><line x1='15' y1='9' x2='9' y2='15'></line><line x1='9' y1='9' x2='15' y2='15'></line></svg> Password must contain at least one uppercase letter.</div>";
    } elseif (!preg_match('/[a-z]/', $new_pass)) {
        $message = "<div class='error-message'><svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-x-circle'><circle cx='12' cy='12' r='10'></circle><line x1='15' y1='9' x2='9' y2='15'></line><line x1='9' y1='9' x2='15' y2='15'></line></svg> Password must contain at least one lowercase letter.</div>";
    } elseif (!preg_match('/[0-9]/', $new_pass)) {
        $message = "<div class='error-message'><svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-x-circle'><circle cx='12' cy='12' r='10'></circle><line x1='15' y1='9' x2='9' y2='15'></line><line x1='9' y1='9' x2='15' y2='15'></line></svg> Password must contain at least one number.</div>";
    } elseif (!preg_match('/[^A-Za-z0-9]/', $new_pass)) {
        $message = "<div class='error-message'><svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-x-circle'><circle cx='12' cy='12' r='10'></circle><line x1='15' y1='9' x2='9' y2='15'></line><line x1='9' y1='9' x2='15' y2='15'></line></svg> Password must contain at least one special character.</div>";
    } else {
        $update = $conn->query("UPDATE admin_list SET password='$new_pass' WHERE email='$email'");
        if ($update) {
            session_destroy();
            header("Location: login_admin.php");
            exit();
        } else {
            $message = "<div class='error-message'><svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-x-circle'><circle cx='12' cy='12' r='10'></circle><line x1='15' y1='9' x2='9' y2='15'></line><line x1='9' y1='9' x2='15' y2='15'></line></svg> Error updating password. Please try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Gaming Store</title>
    <style>
        :root {
            --primary: #ef4444;
            --primary-dark: #dc2626;
            --primary-light: #fee2e2;
            --secondary: #fca5a5;
            --dark: #1e293b;
            --light: #f8fafc;
            --container-bg: #ffffff;
            --success: #10b981;
            --error: #b91c1c;
            --text-dark: #1e293b;
            --text-light: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            background: var(--container-bg);
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            position: relative;
            display: inline-block;
            width: 100%;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--text-dark);
        }

        .password-input-group {
            position: relative;
        }

        .password-input-group input {
            width: 100%;
            padding: 15px 45px 15px 15px;
            border: 2px solid #e2e8f0;
            background: #ffffff;
            border-radius: 8px;
            color: var(--text-dark);
            font-size: 16px;
        }

        .password-input-group input:hover {
            border-color: var(--secondary);
        }

        .password-input-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            font-size: 14px;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .password-toggle:hover {
            background-color: #f0f0f0;
            color: #333;
        }

        .password-requirements {
            margin: 15px 0;
            padding: 12px;
            background-color: #f8fafc;
            border-radius: 8px;
            font-size: 14px;
            color: #4b5563;
            border-left: 4px solid #3b82f6;
        }

        .password-requirements ul {
            margin: 8px 0 0 20px;
            padding: 0;
        }

        .password-requirements li {
            margin-bottom: 5px;
        }

        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.6);
        }

        .error-message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(185, 28, 28, 0.1);
            border-left: 4px solid var(--error);
            color: var(--error);
        }

        .feather {
            vertical-align: middle;
        }

        .gaming-icon {
            text-align: center;
            font-size: 60px;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="gaming-icon">🔑</div>
        <h1>Reset Your Password</h1>

        <?php if ($message) : ?>
            <?= $message ?>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="password-input-group">
                    <input type="password" name="new_password" id="new_password" required placeholder="Enter your new password">
                    <button type="button" class="password-toggle" onclick="togglePassword('new_password', this)">
                        <span class="toggle-text">Show</span>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="password-input-group">
                    <input type="password" name="confirm_password" id="confirm_password" required placeholder="Confirm your new password">
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                        <span class="toggle-text">Show</span>
                    </button>
                </div>
            </div>

            <div class="password-requirements">
                <strong>Password Requirements:</strong>
                <ul>
                    <li>Minimum 12 characters</li>
                    <li>At least one uppercase letter (A-Z)</li>
                    <li>At least one lowercase letter (a-z)</li>
                    <li>At least one number (0-9)</li>
                    <li>At least one special character (!@#$%^&*, etc.)</li>
                </ul>
            </div>

            <button type="submit" name="reset">Reset Password</button>
        </form>
    </div>

    <script>
        function togglePassword(fieldId, button) {
            const passwordField = document.getElementById(fieldId);
            const toggleText = button.querySelector('.toggle-text');
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleText.textContent = "Hide";
            } else {
                passwordField.type = "password";
                toggleText.textContent = "Show";
            }
        }
    </script>
</body>
</html>