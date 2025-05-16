<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "db_connection.php";

    $email = $_POST["email"];
    $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE admin_list SET password = ?, reset_code = NULL, reset_expiry = NULL WHERE email = ?");
    $stmt->bind_param("ss", $new_password, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Password successfully updated.";
    } else {
        echo "Failed to update password.";
    }
}
?>

<form method="POST">
    <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email']) ?>">
    <input type="password" name="new_password" required placeholder="New Password">
    <button type="submit">Set New Password</button>
</form>
