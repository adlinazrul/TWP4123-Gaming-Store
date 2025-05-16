<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "db_connection.php";

    $email = $_POST["email"];
    $code = $_POST["code"];

    $stmt = $conn->prepare("SELECT reset_code, reset_expiry FROM admin_list WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($reset_code, $reset_expiry);
    $stmt->fetch();
    $stmt->close();

    if ($code == $reset_code && strtotime($reset_expiry) > time()) {
        header("Location: new_password.php?email=" . urlencode($email));
        exit;
    } else {
        echo "Invalid or expired code.";
    }
}
?>

<form method="POST">
    <input type="email" name="email" required placeholder="Enter your email">
    <input type="text" name="code" required placeholder="Enter verification code">
    <button type="submit">Verify</button>
</form>
