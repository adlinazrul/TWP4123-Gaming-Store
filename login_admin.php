<?php
session_start();
require_once 'db_config.php';  // Make sure this path is correct

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['uname'];
    $password = $_POST['psw'];
    $selectedRole = strtolower($_POST['role']);

    if (empty($username) || empty($password) || empty($selectedRole)) {
        $message = "Please fill in all fields and select a role.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin_list WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $dbPassword = $user['password'];
            $dbRole = strtolower($user['user_type']);

            if ($password === $dbPassword) {
                if ($selectedRole === $dbRole) {
                    // Set session
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_email'] = $user['email'];

                    // Redirect based on role
                    if ($dbRole === 'admin') {
                        header("Location: dashboard.php");
                        exit();
                    } elseif ($dbRole === 'superadmin') {
                        header("Location: admindashboard.php");
                        exit();
                    } else {
                        $message = "Unknown role. Cannot redirect.";
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
            --transition-speed: 0.3s;
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
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: auto;
        }
        
        body::before {
            content: '';
            position: fixed;
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
            transition: all var(--transition-speed) ease;
            margin: 40px 0;
        }
        
        .login-container:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            transform: translateY(-5px);
        }
        
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
            transition: all var(--transition-speed) ease;
        }
        
        .header:hover {
            background-color: #a80030;
        }
        
        .header h1 {
            font-size: 28px;
            margin: 0;
            letter-spacing: 1px;
            transition: all var(--transition-speed) ease;
        }
        
        .header:hover h1 {
            letter-spacing: 1.5px;
        }
        
        .header h2 {
            font-size: 16px;
            margin-top: 5px;
            font-weight: normal;
            opacity: 0.9;
            transition: all var(--transition-speed) ease;
        }
        
        .header:hover h2 {
            opacity: 1;
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
            transition: all var(--transition-speed) ease;
        }
        
        .avatar:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        
        .form-container {
            padding: 25px 30px;
        }
        
        .input-group {
            position: relative;
            margin-bottom: 20px;
            transition: all var(--transition-speed) ease;
        }
        
        .input-group:hover {
            transform: translateX(5px);
        }
        
        .input-group i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #777;
            transition: all var(--transition-speed) ease;
        }
        
        .input-group:hover i {
            color: var(--primary-color);
            transform: translateY(-50%) scale(1.1);
        }
        
        .input-group input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: all var(--transition-speed) ease;
        }
        
        .input-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(199, 0, 57, 0.2);
            outline: none;
            transform: translateX(3px);
        }
        
        .role-selection {
            margin: 20px 0;
            transition: all var(--transition-speed) ease;
        }
        
        .role-selection:hover {
            transform: translateY(-3px);
        }
        
        .role-selection p {
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
            transition: all var(--transition-speed) ease;
        }
        
        .role-selection:hover p {
            color: var(--primary-color);
        }
        
        .role-options {
            display: flex;
            justify-content: space-between;
            transition: all var(--transition-speed) ease;
        }
        
        .role-option {
            flex: 1;
            margin-right: 10px;
            transition: all var(--transition-speed) ease;
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
            transition: all var(--transition-speed) ease;
        }
        
        .role-option label:hover {
            background-color: #e1e1e1;
            transform: translateY(-3px);
        }
        
        .role-option input[type="radio"]:checked + label {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 15px 0;
            font-size: 14px;
            transition: all var(--transition-speed) ease;
        }
        
        .remember-forgot:hover {
            transform: translateX(3px);
        }
        
        .remember-me {
            display: flex;
            align-items: center;
        }
        
        .remember-me input {
            margin-right: 5px;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
        }
        
        .remember-me input:hover {
            transform: scale(1.1);
        }
        
        .remember-me label {
            cursor: pointer;
            transition: all var(--transition-speed) ease;
        }
        
        .remember-me label:hover {
            color: var(--primary-color);
        }
        
        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
            transition: all var(--transition-speed) ease;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
            color: #a80030;
            transform: translateX(3px);
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
            transition: all var(--transition-speed) ease;
            font-weight: bold;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }
        
        .login-btn:hover {
            background-color: #a80030;
            transform: translateY(-3px);
            box-shadow: 0 7px 14px rgba(0, 0, 0, 0.2);
        }
        
        .login-btn:active {
            transform: translateY(-1px);
        }
        
        .login-btn::after {
            content: "";
            display: block;
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            background-image: radial-gradient(circle, #fff 10%, transparent 10.01%);
            background-repeat: no-repeat;
            background-position: 50%;
            transform: scale(10, 10);
            opacity: 0;
            transition: transform 0.5s, opacity 1s;
        }
        
        .login-btn:active::after {
            transform: scale(0, 0);
            opacity: 0.3;
            transition: 0s;
        }
        
        .error-message {
            color: #d9534f;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
            transition: all var(--transition-speed) ease;
        }
        
        .error-message:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(217, 83, 79, 0.2);
        }
        
        .show-password {
            display: flex;
            align-items: center;
            margin: 10px 0;
            font-size: 14px;
            transition: all var(--transition-speed) ease;
        }
        
        .show-password:hover {
            transform: translateX(5px);
        }
        
        .show-password input {
            margin-right: 5px;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
        }
        
        .show-password input:hover {
            transform: scale(1.1);
        }
        
        .show-password label {
            cursor: pointer;
            transition: all var(--transition-speed) ease;
        }
        
        .show-password label:hover {
            color: var(--primary-color);
        }
        
        .footer {
            text-align: center;
            padding: 15px;
            background-color: #f1f1f1;
            font-size: 12px;
            color: #777;
            transition: all var(--transition-speed) ease;
        }
        
        .footer:hover {
            background-color: #e1e1e1;
        }
        
        .cancel-btn {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            position: relative;
            overflow: hidden;
        }
        
        .cancel-btn:hover {
            background-color: #d32f2f;
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }
        
        .cancel-btn:active {
            transform: translateY(-1px);
        }
        
        .footer p {
            margin-top: 10px;
            transition: all var(--transition-speed) ease;
        }
        
        .footer:hover p {
            color: #333;
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
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a80030;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                width: 90%;
                margin: 20px 0;
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
        <br>
        <br>
        <br>
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
                        <a href="forgot_password.php"><i class="fas fa-key"></i> Forgot password ?</a>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> LOGIN
                </button>
            </div>
        </form>
        
        <div class="footer">
            <a href="index.html" class="back-link">Cancel</a>   
        </div>
    </div>

    <script>
        function togglePassword() {
            var passwordInput = document.getElementById("password");
            passwordInput.type = passwordInput.type === "password" ? "text" : "password";
        }
        
        // Add ripple effect to buttons
        document.querySelectorAll('.login-btn, .cancel-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove any existing ripples
                const existingRipples = this.querySelectorAll('.ripple');
                existingRipples.forEach(ripple => ripple.remove());
                
                // Create new ripple
                const ripple = document.createElement('span');
                ripple.classList.add('ripple');
                
                // Position the ripple
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size/2;
                const y = e.clientY - rect.top - size/2;
                
                ripple.style.width = ripple.style.height = `${size}px`;
                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;
                
                this.appendChild(ripple);
                
                // Remove ripple after animation
                setTimeout(() => {
                    ripple.remove();
                }, 600);
                
                // For form submission
                if(this.classList.contains('login-btn')) {
                    setTimeout(() => {
                        this.closest('form').submit();
                    }, 300);
                }
            });
        });
        
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