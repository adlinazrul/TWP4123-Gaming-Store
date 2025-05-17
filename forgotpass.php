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
    $res = $conn->query("SELECT email FROM customers");
    while ($row = $res->fetch_assoc()) {
        $emails[] = "📧 " . $row['email'];
    }
    echo "<br><b>Existing Emails in Database:</b><br>" . implode("<br>", $emails) . "<br>";
}

if (isset($_POST['send_code'])) {
    $email = strtolower(trim($_POST['email']));

    // DEBUG: Show the raw SQL being executed
    echo "DEBUG SQL: SELECT * FROM admin_list WHERE LOWER(email) = '$email'<br>";

    $stmt = $conn->prepare("SELECT * FROM customers WHERE LOWER(email) = ?");
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
            $mail->Password = 'ewpuqoqxjlksvgaf'; // Your Gmail App Password
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
            echo "✅ Verification code sent. <a href='verify_code.php'>Click here to verify</a>";
        } catch (Exception $e) {
            echo "❌ Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "❌ Email not found in our system.";
    }

    // Show all existing emails for debugging
    debugEmails($conn);
}
?>

<form method="POST">
    <label>Enter your email:</label><br>
    <input type="email" name="email" required><br><br>
    <button type="submit" name="send_code">Send Verification Code</button>
</form>
