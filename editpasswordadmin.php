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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            max-width: 500px;
            width: 90%;
            margin: 20px;
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transform: scale(0.98);
            opacity: 0;
            animation: fadeIn 0.5s forwards;
        }

        @keyframes fadeIn {
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        h2 {
            text-align: center;
            color: #ef4444;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 10px;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: #ef4444;
            border-radius: 3px;
        }

        .form-group {
            position: relative;
            margin-bottom: 25px;
        }

        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            transition: all 0.3s ease;
        }

        form input[type="password"] {
            width: 100%;
            padding: 14px 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            padding-right: 50px;
        }

        form input[type="password"]:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
            outline: none;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 40px;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 14px;
            padding: 5px;
        }

        .submit-btn {
            margin-top: 30px;
            background-color: #ef4444;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
            position: relative;
            overflow: hidden;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(239, 68, 68, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .submit-btn:hover::after {
            opacity: 1;
        }

        .back-link {
            display: inline-block;
            margin-top: 25px;
            text-align: center;
            text-decoration: none;
            color: #3b82f6;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            padding-bottom: 3px;
            width: 100%;
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

        .back-link:hover {
            color: #2563eb;
        }

        .message {
            margin: 25px 0;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            animation: slideIn 0.5s ease-out;
            transform-origin: top;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
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

        .message-icon {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Password</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>">
                <?php if (strpos($message, 'successfully') !== false): ?>
                    <span class="message-icon">✓</span>
                <?php else: ?>
                    <span class="message-icon">!</span>
                <?php endif; ?>
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="passwordForm">
            <div class="form-group">
                <label for="old_password">Old Password</label>
                <input type="password" name="old_password" id="old_password" required>
                <button type="button" class="toggle-password" onclick="togglePassword('old_password')">👁️</button>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" required>
                <button type="button" class="toggle-password" onclick="togglePassword('new_password')">👁️</button>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
                <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">👁️</button>
            </div>

            <button type="submit" class="submit-btn">Update Password</button>
        </form>

        <a href="profile_admin.php" class="back-link">← Back to Profile</a>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(id) {
            const input = document.getElementById(id);
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }

        // Form submission animation
        const form = document.getElementById('passwordForm');
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('.submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Updating... <span class="spinner">↻</span>';
        });
    </script>
</body>
</html>