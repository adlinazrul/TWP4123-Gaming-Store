<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

// Connect to MySQL
$conn = new mysqli("localhost", "root", "", "gaming_store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if (isset($_POST['send_code'])) {
    // Clean input
    $email = strtolower(trim($_POST['email']));
    $escaped_email = $conn->real_escape_string($email);

    // Debug: Show SQL
    $sql = "SELECT * FROM admin_list WHERE LOWER(email) = '$escaped_email'";
    echo "<b>DEBUG SQL:</b> $sql<br>";

    // Execute query
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // Email exists
        $code = rand(100000, 999999);
        $_SESSION['verification_code'] = $code;
        $_SESSION['reset_email'] = $email;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'adlina.mlk@gmail.com'; // ✅ Your Gmail
            $mail->Password = 'ewpuqoqxjlksvgaf';     // ✅ Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Bypass SSL verification (if needed)
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            // Send email
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
        // Email not found
        echo "❌ Email not found in our system.<br>";

        // Debug: show all existing emails
        echo "<hr><b>Existing Emails in Database:</b><br>";
        $all = $conn->query("SELECT email FROM admin_list");
        while ($row = $all->fetch_assoc()) {
            echo "📧 " . $row['email'] . "<br>";
        }
    }
}
?>

<!-- HTML Form -->
<form method="POST">
    <label>Enter your email:</label><br>
    <input type="email" name="email" required><br><br>
    <button type="submit" name="send_code">Send Verification Code</button>
</form>
