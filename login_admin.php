<?php
session_start();
require_once 'db_config.php';  // Ensure this points to your DB config

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['uname'];
    $password = $_POST['psw'];
    $selectedRole = $_POST['role'];

    if (empty($username) || empty($password) || empty($selectedRole)) {
        $message = "Please fill in all fields and select a role.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin_list WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if ($password === $user['password']) {
                if (strtolower($selectedRole) === strtolower($user['user_type'])) {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_email'] = $user['email'];

                    if ($selectedRole === 'admin') {
                        header("Location: dashboard.php");
                        exit();
                    } elseif ($selectedRole === 'superadmin') {
                        header("Location: admindashboard.php");
                        exit();
                    } else {
                        $message = "Invalid role selected.";
                    }
                } else {
                    $message = "Role mismatch. Please select the correct role.";
                }
            } else {
                $message = "Incorrect password.";
            }
        } else {
            $message = "No such user found.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #C70039;
            --secondary-color: #f44336;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('image/backgroundad.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }
        
        .login-container {
            width: 380px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            position: relative;
            z-index: 1;
            transform-style: preserve-3d;
            transition: all 0.5s ease;
        }
        
        .login-container:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }
        
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }
        
        .header h1 {
            font-size: 28px;
            margin: 0;
            letter-spacing: 1px;
        }
        
        .header h2 {
            font-size: 16px;
            margin-top: 5px;
            font-weight: normal;
            opacity: 0.9;
        }
        
        .avatar-container {
            text-align: center;
            margin-top: -50px;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }
        
        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            object-fit: cover;
            transition: all 0.3s ease;
        }
        
        .avatar:hover {
            transform: scale(1.05);
        }
        
        .form-container {
            padding: 25px 30px;
        }
        
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }
        
        .input-group i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #777;
        }
        
        .input-group input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .input-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(199, 0, 57, 0.2);
            outline: none;
        }
        
        .role-selection {
            margin: 20px 0;
        }
        
        .role-selection p {
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
        }
        
        .role-options {
            display: flex;
            justify-content: space-between;
        }
        
        .role-option {
            flex: 1;
            margin-right: 10px;
        }
        
        .role-option:last-child {
            margin-right: 0;
        }
        
        .role-option input[type="radio"] {
            display: none;
        }
        
        .role-option label {
            display: block;
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .role-option input[type="radio"]:checked + label {
            background-color: var(--primary-color);
            color: white;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 15px 0;
            font-size: 14px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
        }
        
        .remember-me input {
            margin-right: 5px;
        }
        
        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .login-btn {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        .login-btn:hover {
            background-color: #a80030;
            transform: translateY(-2px);
        }
        
        .error-message {
            color: #d9534f;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
        }
        
        .show-password {
            display: flex;
            align-items: center;
            margin: 10px 0;
            font-size: 14px;
        }
        
        .show-password input {
            margin-right: 5px;
        }
        
        .footer {
            text-align: center;
            padding: 15px;
            background-color: #f1f1f1;
            font-size: 12px;
            color: #777;
        }
        
        .cancel-btn {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .cancel-btn:hover {
            background-color: #d32f2f;
        }
        
        /* Animation */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .floating {
            animation: float 3s ease-in-out infinite;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                width: 90%;
            }
            
            .role-options {
                flex-direction: column;
            }
            
            .role-option {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="header">
            <h1>GAMING STORE</h1>
            <h2>Admin Portal</h2>
        </div>
        
        <div class="avatar-container floating">
            <img src="image/admin.jpg" alt="Admin Avatar" class="avatar">
        </div>
        
        <form action="login_admin.php" method="post">
            <div class="form-container">
                <?php if (!empty($message)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="Username" name="uname" required>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" placeholder="Password" name="psw" required>
                </div>
                
                <div class="show-password">
                    <input type="checkbox" id="showPassword" onclick="togglePassword()">
                    <label for="showPassword">Show Password</label>
                </div>
                
                <div class="role-selection">
                    <p>Select Your Role:</p>
                    <div class="role-options">
                        <div class="role-option">
                            <input type="radio" id="admin" name="role" value="admin" required>
                            <label for="admin"><i class="fas fa-user-shield"></i> Admin</label>
                        </div>
                        <div class="role-option">
                            <input type="radio" id="superadmin" name="role" value="superadmin">
                            <label for="superadmin"><i class="fas fa-crown"></i> Super Admin</label>
                        </div>
                    </div>
                </div>
                
                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember" checked>
                        <label for="remember">Remember me</label>
                    </div>
                    <div class="forgot-password">
                        <a href="#"><i class="fas fa-key"></i> Forgot password?</a>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> LOGIN
                </button>
            </div>
        </form>
        
        <div class="footer">
            <button type="button" class="cancel-btn"><i class="fas fa-times"></i> Cancel</button>
            <p>© 2023 Gaming Store. All rights reserved.</p>
        </div>
    </div>

    <script>
        function togglePassword() {
            var passwordInput = document.getElementById("password");
            passwordInput.type = passwordInput.type === "password" ? "text" : "password";
        }
        
        // Add focus effects
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.querySelector('i').style.color = '#C70039';
            });
            
            input.addEventListener('blur', function() {
                this.parentNode.querySelector('i').style.color = '#777';
            });
        });
    </script>
</body>
</html>