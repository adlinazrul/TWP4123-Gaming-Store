<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "db_connection.php"; // your DB connection

    $email = $_POST["email"];
    $code = rand(100000, 999999);
    $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    $stmt = $conn->prepare("UPDATE admin_list SET reset_code = ?, reset_expiry = ? WHERE email = ?");
    $stmt->bind_param("sss", $code, $expiry, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Send email
        $to = $email;
        $subject = "Password Reset Verification Code";
        $message = "Your verification code is: $code";
        $headers = "From: no-reply@gamingstore.com";

        mail($to, $subject, $message, $headers);

        echo "Verification code sent to your email.";
    } else {
        echo "Email not found.";
    }
}
?>

<form method="POST">
    <input type="email" name="email" required placeholder="Enter your email">
    <button type="submit">Send Verification Code</button>
</form>
