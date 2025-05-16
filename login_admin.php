<?php
session_start();
require_once 'db_config.php';  // Include your database configuration file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['uname'];
    $password = $_POST['psw'];
    $role = $_POST['role'];  // Get the selected role

    // Check if the fields are empty
    if (empty($username) || empty($password) || empty($role)) {
        echo "Please fill in all fields and select a role.";
        exit();
    }

    // Prepare SQL query to check if the user exists in the admin_list table
    $stmt = $conn->prepare("SELECT * FROM admin_list WHERE username=?");
    $stmt->bind_param("s", $username);  // Bind the username to the query
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the user data
        $user = $result->fetch_assoc();

        // Verify the password (plain text check)
        if ($password === $user['password']) {
            // Set session variables
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_email'] = $user['email'];

            // Redirect based on selected role
            if ($role === "admin") {
                header("Location: dashboard.php");
                exit();
            } elseif ($role === "superadmin") {
                header("Location: admindashboard.php");
                exit();
            } else {
                echo "Invalid role selected.";
            }
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "No such user found.";
    }

    $stmt->close();
    $conn->close();
}
?>
