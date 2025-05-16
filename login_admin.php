<?php
session_start();
require_once 'db_config.php';  // include your DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['uname'];
    $password = $_POST['psw'];
    $selectedRole = $_POST['role'];  // value from radio button

    if (empty($username) || empty($password) || empty($selectedRole)) {
        echo "Please fill in all fields and select a role.";
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM admin_list WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($password === $user['password']) {  // you can switch to password_verify() if using hashed passwords
            // Compare selected role with user_type in DB
            if ($selectedRole === strtolower($user['user_type'])) {
                // Set session variables
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_email'] = $user['email'];

                // Redirect based on role
                if ($selectedRole === 'admin') {
                    header("Location: dashboard.php");
                    exit();
                } elseif ($selectedRole === 'superadmin') {
                    header("Location: admindashboard.php");
                    exit();
                } else {
                    echo "Invalid role.";
                }
            } else {
                echo "Role mismatch. You selected the wrong role.";
            }
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "User not found.";
    }

    $stmt->close();
    $conn->close();
}
?>
