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
            $message = "<div class='success-message'><svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-check-circle'><path d='M22 11.08V12a10 10 0 1 1-5.93-9.14'></path><polyline points='22 4 12 14.01 9 11.01'></polyline></svg> Verification code sent. <a href='verify_code.php'>Click here to verify</a></div>";
        } catch (Exception $e) {
            $message = "<div class='error-message'><svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-alert-circle'><circle cx='12' cy='12' r='10'></circle><line x1='12' y1='8' x2='12' y2='12'></line><line x1='12' y1='16' x2='12' y2='16'></line></svg> Message could not be sent. Mailer Error: {$mail->ErrorInfo}</div>";
        }
    } else {
        $message = "<div class='error-message'><svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-x-circle'><circle cx='12' cy='12' r='10'></circle><line x1='15' y1='9' x2='9' y2='15'></line><line x1='9' y1='9' x2='15' y2='15'></line></svg> Email not found in our system.</div>";
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
            transition: all 0.3s ease;
        }
        
        input[type="email"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            background: #ffffff;
            border-radius: 8px;
            color: var(--text-dark);
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        input[type="email"]:hover {
            border-color: var(--secondary);
        }
        
        input[type="email"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
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
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
            position: relative;
            overflow: hidden;
        }
        
        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s ease;
        }
        
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.6);
        }
        
        button:hover::before {
            left: 100%;
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .success-message, .error-message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.5s ease;
        }
        
        .success-message {
            background: rgba(16, 185, 129, 0.1);
            border-left: 4px solid var(--success);
            color: var(--success);
        }
        
        .error-message {
            background: rgba(185, 28, 28, 0.1);
            border-left: 4px solid var(--error);
            color: var(--error);
        }
        
        .debug-info {
            margin-top: 20px;
            padding: 15px;
            background: rgba(241, 245, 249, 0.8);
            border-radius: 8px;
            font-size: 14px;
            color: #64748b;
            border: 1px dashed #cbd5e1;
        }
        
        .gaming-icon {
            text-align: center;
            font-size: 60px;
            margin-bottom: 20px;
            animation: pulse 2s infinite, float 4s ease-in-out infinite;
            filter: drop-shadow(0 5px 10px rgba(239, 68, 68, 0.3));
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            display: inline-block;
        }
        
        .back-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
        }
        
        .back-link:hover {
            color: var(--primary-dark);
        }
        
        .back-link:hover::after {
            width: 100%;
        }
        
        .feather {
            vertical-align: middle;
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
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="login_admin.php" class="back-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to Login
            </a>
        </div>
        
        <?php if(isset($debug_info)) echo $debug_info; ?>
    </div>
</body>
</html>