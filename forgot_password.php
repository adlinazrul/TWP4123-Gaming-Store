<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

// Connect to database
$conn = new mysqli("localhost", "root", "", "gaming_store");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function debugEmails($conn) {
    $emails = [];
    $res = $conn->query("SELECT email FROM admin_list");
    while ($row = $res->fetch_assoc()) {
        $emails[] = "📧 " . $row['email'];
    }
    return "<div class='debug-info'><b>Existing Emails in Database:</b><br>" . implode("<br>", $emails) . "</div>";
}

if (isset($_POST['send_code'])) {
    $email = strtolower(trim($_POST['email']));

    $stmt = $conn->prepare("SELECT * FROM admin_list WHERE LOWER(email) = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email exists
        $code = rand(100000, 999999);
        $_SESSION['verification_code'] = $code;
        $_SESSION['reset_email'] = $email;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'adlina.mlk@gmail.com';
            $mail->Password = 'ewpuqoqxjlksvgaf';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->setFrom('adlina.mlk@gmail.com', 'Gaming Store');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Code';
            $mail->Body = "Your verification code is <b>$code</b>";

            $mail->send();
            $message = "<div class='success-message'>✅ Verification code sent. <a href='verify_code.php'>Click here to verify</a></div>";
        } catch (Exception $e) {
            $message = "<div class='error-message'>❌ Message could not be sent. Mailer Error: {$mail->ErrorInfo}</div>";
        }
    } else {
        $message = "<div class='error-message'>❌ Email not found in our system.</div>";
    }

    $debug_info = debugEmails($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - Gaming Store</title>
    <style>
        :root {
            --primary: #ef4444;
            --primary-dark: #dc2626;
            --secondary: #fca5a5;
            --dark: #1e293b;
            --light: #f8fafc;
            --success: #10b981;
            --error: #b91c1c;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: var(--light);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(239, 68, 68, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(239, 68, 68, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
            z-index: -1;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            color: var(--primary);
            text-shadow: 0 0 10px rgba(239, 68, 68, 0.5);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--secondary);
        }
        
        input[type="email"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid rgba(239, 68, 68, 0.3);
            background: rgba(15, 23, 42, 0.5);
            border-radius: 8px;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        input[type="email"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.3);
            background: rgba(15, 23, 42, 0.7);
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.6);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .success-message {
            margin-top: 20px;
            padding: 15px;
            background: rgba(16, 185, 129, 0.2);
            border-left: 4px solid var(--success);
            border-radius: 4px;
        }
        
        .error-message {
            margin-top: 20px;
            padding: 15px;
            background: rgba(185, 28, 28, 0.2);
            border-left: 4px solid var(--error);
            border-radius: 4px;
        }
        
        .debug-info {
            margin-top: 20px;
            padding: 15px;
            background: rgba(30, 41, 59, 0.8);
            border-radius: 4px;
            font-size: 14px;
            color: #cbd5e1;
        }
        
        .gaming-icon {
            text-align: center;
            font-size: 50px;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
            color: var(--primary);
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--secondary);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: var(--primary);
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="gaming-icon">🎮</div>
        <h1>Reset Your Password</h1>
        
        <?php if(isset($message)) echo $message; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Enter your email address</label>
                <input type="email" name="email" id="email" required placeholder="your@email.com">
            </div>
            
            <button type="submit" name="send_code">
                Send Verification Code
            </button>
        </form>
        
        <a href="login.php" class="back-link">Back to Login</a>
        
        <?php if(isset($debug_info)) echo $debug_info; ?>
    </div>
</body>
</html>