forgot  password  new
<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

// Database connection
$conn = new mysqli("localhost", "root", "", "gaming_store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// When user submits the email
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_code'])) {
    $email = strtolower(trim($_POST['email']));
    
    $stmt = $conn->prepare("SELECT * FROM customers WHERE LOWER(email) = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $code = rand(100000, 999999);
        $expires_at = date("Y-m-d H:i:s", strtotime('+10 minutes'));

        // Update customer's reset_code and expiry in DB
        $update = $conn->prepare("UPDATE customers SET reset_code = ?, reset_expiry = ? WHERE email = ?");
        $update->bind_param("sss", $code, $expires_at, $email);
        $update->execute();

        $_SESSION['reset_email'] = $email;

        // Send the code via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'adlina.mlk@gmail.com';
            $mail->Password = 'ewpuqoqxjlksvgaf'; // App-specific password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];

            $mail->setFrom('adlina.mlk@gmail.com', 'NEXUS Gaming Store');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Your Password Reset Code';
            $mail->Body = "
                <div style='font-family: Rubik, sans-serif; color: #0d0221;'>
                    <h1 style='color: #ff0000; font-family: Orbitron, sans-serif;'>NEXUS Password Reset</h1>
                    <p>Here is your password reset code:</p>
                    <div style='font-size: 24px; font-weight: bold; color: #ff0000; letter-spacing: 2px; margin: 20px 0;'>$code</div>
                    <p>This code will expire in 10 minutes.</p>
                    <p>If you didn't request this, please ignore this email.</p>
                </div>
            ";

            $mail->send();
            echo "<script>alert('Verification code sent to your email.'); window.location.href = 'verify_codecust.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Email sending failed. Please try again later.');</script>";
        }
    } else {
        echo "<script>alert('Email not found in our system.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS | Password Reset</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rubik:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ff0000;
            --secondary: #d10000;
            --dark: #0d0221;
            --light: #ffffff;
            --accent: #ff3333;
        }
        
        body {
            font-family: 'Rubik', sans-serif;
            background-color: var(--dark);
            color: var(--light);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        header {
            background: var(--dark);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(255, 0, 0, 0.3);
        }
        
        .nav-menu {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
        }
        
        .logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            text-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
        }
        
        .nav-links a {
            color: var(--light);
            text-decoration: none;
            font-family: 'Orbitron', sans-serif;
            font-weight: 400;
            transition: all 0.3s ease;
        }
        
        .nav-links a:hover {
            color: var(--primary);
        }
        
        .icons-left, .icons-right {
            display: flex;
            gap: 25px;
        }
        
        .icons-left i, .icons-right i {
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--light);
        }
        
        .icons-left i:hover, .icons-right i:hover {
            color: var(--primary);
        }
        
        .reset-container {
            max-width: 500px;
            margin: 80px auto;
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(255, 0, 0, 0.2);
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .reset-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            color: var(--primary);
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
        }
        
        .reset-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.3);
            border-radius: 5px;
            color: var(--light);
            font-family: 'Rubik', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(255, 0, 0, 0.3);
        }
        
        .btn-reset {
            background: var(--primary);
            color: var(--light);
            border: none;
            padding: 15px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-reset:hover {
            background: var(--accent);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 0, 0, 0.4);
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-to-login a {
            color: var(--primary);
            text-decoration: none;
            font-family: 'Orbitron', sans-serif;
            transition: all 0.3s ease;
        }
        
        .back-to-login a:hover {
            text-decoration: underline;
        }
        
        footer {
            background: #0a0118;
            padding: 50px 30px 20px;
            text-align: center;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .footer-links a {
            color: var(--light);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--primary);
        }
        
        .social-icons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .social-icons a {
            color: var(--light);
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .social-icons a:hover {
            color: var(--primary);
        }
        
        .copyright {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 0, 0, 0.1);
        }
        
        /* Mobile menu styles */
        #menuOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            z-index: 2000;
        }
        
        #menuContainer {
            position: fixed;
            top: 0;
            left: -400px;
            width: 400px;
            height: 100%;
            background: var(--dark);
            padding: 40px;
            transition: left 0.4s ease;
            z-index: 2001;
            border-right: 1px solid var(--primary);
        }
        
        #closeMenu {
            font-size: 2rem;
            color: var(--primary);
            cursor: pointer;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        
        #menuOverlay.active {
            display: block;
        }
        
        #menuOverlay.active #menuContainer {
            left: 0;
        }
        
        .menu-item {
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 0, 0, 0.1);
        }
        
        .menu-item a {
            color: var(--light);
            text-decoration: none;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            display: block;
        }
        
        .menu-item a:hover {
            color: var(--primary);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .reset-container {
                padding: 30px 20px;
                margin: 50px 20px;
            }
            
            .reset-title {
                font-size: 1.8rem;
            }
            
            .nav-links {
                display: none;
            }
            
            .logo {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="nav-menu">
            <div class="icons-left">
                <i class="fas fa-search"></i>
                <i class="fas fa-bars" id="menuIcon"></i>
            </div>
            
            <div class="logo">NEXUS</div>
            
            <div class="nav-links">
                <a href="#nintendo">NINTENDO</a>
                <a href="#playstation">PLAYSTATION</a>
                <a href="#xbox">XBOX</a>
                <a href="PRODUCTLIST.html">ACCESSORIES</a>
                <a href="#vr">VR</a>
            </div>
            
            <div class="icons-right">
                <a href="custlogin.html">
                    <i class="fas fa-user"></i>
                </a>
                <i class="fas fa-shopping-cart"></i>
            </div>
        </nav>
    </header>

    <!-- Mobile Menu Overlay -->
    <div id="menuOverlay">
        <div id="menuContainer">
            <span id="closeMenu">&times;</span>
            <div id="menuContent">
                <div class="menu-item"><a href="ORDERHISTORY.html">ORDER</a></div>
                <div class="menu-item"><a href="custservice.html">HELP</a></div>
                <div class="menu-item"><a href="login_admin.php">LOGIN ADMIN</a></div>
            </div>
        </div>
    </div>

    <!-- Password Reset Form -->
    <div class="reset-container">
        <h1 class="reset-title">FORGOT PASSWORD</h1>
        <form method="POST" class="reset-form">
            <div class="form-group">
                <label for="email">ENTER YOUR EMAIL</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <button type="submit" name="send_code" class="btn-reset">
                SEND VERIFICATION CODE
            </button>
            
            <div class="back-to-login">
                <a href="custlogin.html">← Back to Login</a>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-links">
            <a href="#about">ABOUT US</a>
            <a href="#contact">CONTACT</a>
           
        </div>
        
        <div class="social-icons">
            <a href="#facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#twitter"><i class="fab fa-twitter"></i></a>
            <a href="#instagram"><i class="fab fa-instagram"></i></a>
            <a href="#youtube"><i class="fab fa-youtube"></i></a>
            <a href="#twitch"><i class="fab fa-twitch"></i></a>
        </div>
        
        <div class="copyright">
            &copy; 2025 NEXUS GAMING STORE. ALL RIGHTS RESERVED.
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let menuOverlay = document.getElementById("menuOverlay");
            let menuContainer = document.getElementById("menuContainer");
            let menuIcon = document.getElementById("menuIcon");
            let closeMenu = document.getElementById("closeMenu");

            // Open menu
            menuIcon.addEventListener("click", function () {
                menuOverlay.style.display = "block";
                setTimeout(() => {
                    menuOverlay.classList.add("active");
                }, 10);
            });

            // Close menu when clicking "X"
            closeMenu.addEventListener("click", function (e) {
                e.stopPropagation();
                menuOverlay.classList.remove("active");
                setTimeout(() => {
                    menuOverlay.style.display = "none";
                }, 300);
            });

            // Close menu when clicking outside of menu container
            menuOverlay.addEventListener("click", function (e) {
                if (e.target === menuOverlay) {
                    menuOverlay.classList.remove("active");
                    setTimeout(() => {
                        menuOverlay.style.display = "none";
                    }, 300);
                }
            });
        });
    </script>
</body>
</html>