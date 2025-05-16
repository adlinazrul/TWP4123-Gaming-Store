<?php
session_start();
require_once 'db_config.php';  // Ensure this points to your DB config

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['uname'];
    $password = $_POST['psw'];
    $selectedRole = $_POST['role'];

    if (empty($username) || empty($password) || empty($selectedRole)) {
        $message = "Please fill in all fields and select a role.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin_list WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if ($password === $user['password']) {
                if (strtolower($selectedRole) === strtolower($user['user_type'])) {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_email'] = $user['email'];

                    if ($selectedRole === 'admin') {
                        header("Location: dashboard.php");
                        exit();
                    } elseif ($selectedRole === 'superadmin') {
                        header("Location: admindashboard.php");
                        exit();
                    } else {
                        $message = "Invalid role selected.";
                    }
                } else {
                    $message = "Role mismatch. Please select the correct role.";
                }
            } else {
                $message = "Incorrect password.";
            }
        } else {
            $message = "No such user found.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('image/backgroundad.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            margin: 0;
        }

        form {
            border: 3px solid #f1f1f1;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            width: 300px;
            padding: 20px;
            margin: 50px auto;
            position: relative;
            z-index: 1;
        }

        input[type=text], input[type=password] {
            width: 100%;
            padding: 12px 20px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        button {
            background-color: #C70039;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            opacity: 0.8;
        }

        .cancelbtn {
            width: auto;
            padding: 10px 18px;
            background-color: #f44336;
        }

        .imgcontainer {
            text-align: center;
            margin: 24px 0 12px 0;
        }

        img.avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }

        .container {
            padding: 16px;
        }

        span.psw {
            float: right;
            padding-top: 16px;
        }

        @media screen and (max-width: 300px) {
            span.psw {
                display: block;
                float: none;
            }

            .cancelbtn {
                width: 100%;
            }
        }

        h1, h2 {
            color: white;
            text-align: center;
        }

        h1 {
            margin-top: 50px;
        }

        h2 {
            margin-top: 10px;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Gaming Store</h1>
    <h2>Login Form</h2>

    <form action="login_admin.php" method="post">
        <div class="imgcontainer">
            <img src="image/admin.jpg" alt="Avatar" class="avatar">
        </div>

        <div class="container">
            <?php if (!empty($message)): ?>
                <p class="error-message"><?php echo $message; ?></p>
            <?php endif; ?>

            <label for="uname"><b>Username</b></label>
            <input type="text" placeholder="Enter Username" name="uname" required>

            <label for="psw"><b>Password</b></label>
            <input type="password" id="password" placeholder="Enter Password" name="psw" required>
            <input type="checkbox" id="showPassword" onclick="togglePassword()"> Show Password

            <p><b>Select Role:</b></p>
            <label><input type="radio" name="role" value="admin" required> Admin</label><br>
            <label><input type="radio" name="role" value="superadmin" required> Super Admin</label><br><br>

            <button type="submit">Login</button>
            <label>
                <input type="checkbox" checked="checked" name="remember"> Remember me
            </label>
        </div>

        <div class="container" style="background-color:#f1f1f1">
            <button type="button" class="cancelbtn">Cancel</button>
            <span class="psw">Forgot <a href="#">password</a></span>
        </div>
    </form>

    <script>
        function togglePassword() {
            var passwordInput = document.getElementById("password");
            passwordInput.type = passwordInput.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>
