<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';
$conn = new mysqli("localhost", "root", "", "gaming_store");

if (isset($_POST['send_code'])) {
    $email = $_POST['email'];
    $result = $conn->query("SELECT * FROM admin_list WHERE email = '$email'");

    if ($result->num_rows > 0) {
        $code = rand(100000, 999999);
        $_SESSION['verification_code'] = $code;
        $_SESSION['reset_email'] = $email;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'yourgmail@gmail.com';
            $mail->Password = 'your_app_password';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('yourgmail@gmail.com', 'Gaming Store');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Code';
            $mail->Body = "Your verification code is <b>$code</b>";

            $mail->send();
            echo "Verification code sent. <a href='verify_code.php'>Click here to verify</a>";
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Email not found.";
    }
}
?>

<form method="POST">
    <label>Enter your email:</label>
    <input type="email" name="email" required>
    <button type="submit" name="send_code">Send Verification Code</button>
</form>
