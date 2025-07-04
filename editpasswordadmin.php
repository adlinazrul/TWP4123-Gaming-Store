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
            // Validate new password strength
            if (strlen($new_password) < 12) {
                $message = "Password must be at least 12 characters long.";
            } elseif (!preg_match('/[A-Z]/', $new_password)) {
                $message = "Password must contain at least one uppercase letter.";
            } elseif (!preg_match('/[a-z]/', $new_password)) {
                $message = "Password must contain at least one lowercase letter.";
            } elseif (!preg_match('/[0-9]/', $new_password)) {
                $message = "Password must contain at least one number.";
            } elseif (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
                $message = "Password must contain at least one special character.";
            } else {
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
            }
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
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
        }

        .container {
            max-width: 500px;
            margin: 80px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #ef4444;
            margin-bottom: 25px;
        }

        form label {
            display: block;
            margin-top: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .password-input-group {
            position: relative;
            margin-bottom: 15px;
        }

        .password-input-group input {
            width: 100%;
            padding: 10px 45px 10px 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            box-sizing: border-box;
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

        form input[type="submit"] {
            margin-top: 25px;
            background-color: #ef4444;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        form input[type="submit"]:hover {
            background-color: #dc2626;
        }

        .message {
            margin: 20px 0;
            padding: 12px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }

        .message.error {
            color: #dc2626;
            background-color: #fee2e2;
        }

        .message.success {
            color: #059669;
            background-color: #d1fae5;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            color: #3b82f6;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #2563eb;
        }
        
        .password-requirements {
            margin: 15px 0;
            padding: 12px;
            background-color: #f8fafc;
            border-radius: 5px;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Password</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label for="old_password">Old Password:</label>
            <div class="password-input-group">
                <input type="password" name="old_password" id="old_password" required>
                <button type="button" class="password-toggle" onclick="togglePassword('old_password', this)">
                    <span class="toggle-text">Show</span>
                </button>
            </div>

            <label for="new_password">New Password:</label>
            <div class="password-input-group">
                <input type="password" name="new_password" id="new_password" required>
                <button type="button" class="password-toggle" onclick="togglePassword('new_password', this)">
                    <span class="toggle-text">Show</span>
                </button>
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

            <label for="confirm_password">Confirm New Password:</label>
            <div class="password-input-group">
                <input type="password" name="confirm_password" id="confirm_password" required>
                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                    <span class="toggle-text">Show</span>
                </button>
            </div>

            <input type="submit" value="Update Password">
        </form>

        <a href="profile_admin.php" class="back-link">← Back to Profile</a>
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