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
        /* Your existing CSS (unchanged) */
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

        input[type="password"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            background: #ffffff;
            border-radius: 8px;
            color: var(--text-dark);
            font-size: 16px;
        }

        input[type="password"]:hover {
            border-color: var(--secondary);
        }

        input[type="password"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .password-hint {
            font-size: 12px;
            color: #64748b;
            margin-top: -15px;
            margin-bottom: 20px;
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
                <input type="password" name="new_password" id="new_password" required minlength="6" placeholder="Enter your new password">
                <br>
                <br><div class="password-hint">Password must be at least 6 characters long</div>
            </div>

            <button type="submit" name="reset">Reset Password</button>
        </form>
    </div>
</body>
</html>
