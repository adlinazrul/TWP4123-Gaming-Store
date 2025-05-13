<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission to update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $salary = $_POST['salary'];
    $password = $_POST['password'];

    // Optional: update image
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target = "image/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);

        $sql = "UPDATE admin_list SET username=?, email=?, position=?, salary=?, password=?, image=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $username, $email, $position, $salary, $password, $image, $admin_id);
    } else {
        $sql = "UPDATE admin_list SET username=?, email=?, position=?, salary=?, password=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $username, $email, $position, $salary, $password, $admin_id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='profile_admin.php';</script>";
    } else {
        echo "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch admin data
$query = "SELECT * FROM admin_list WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Profile</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 60px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #ef4444;
            margin-bottom: 30px;
        }

        form label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #333;
        }

        form input[type="text"],
        form input[type="email"],
        form input[type="number"],
        form input[type="password"],
        form input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .password-container {
            position: relative;
        }

        .password-container input[type="password"],
        .password-container input[type="text"] {
            padding-right: 50px;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 35%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #555;
            font-size: 14px;
        }

        form img {
            margin-top: 10px;
            width: 120px;
            border-radius: 10px;
        }

        form input[type="submit"] {
            margin-top: 20px;
            background-color: #ef4444;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        form input[type="submit"]:hover {
            background-color: #dc2626;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>My Profile</h2>
    <form method="post" enctype="multipart/form-data">
        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>

        <label>Position:</label>
        <input type="text" name="position" value="<?= htmlspecialchars($admin['position']) ?>" required>

        <label>Salary (RM):</label>
        <input type="number" name="salary" step="0.01" value="<?= htmlspecialchars($admin['salary']) ?>" required>

        <label>Password:</label>
        <div class="password-container">
            <input type="password" name="password" id="passwordField" value="<?= htmlspecialchars($admin['password']) ?>" required>
            <span class="toggle-password" onclick="togglePassword()">Show</span>
        </div>

        <label>Profile Image:</label>
        <?php if (!empty($admin['image'])): ?>
            <br><img src="image/<?= htmlspecialchars($admin['image']) ?>" alt="Profile Image"><br>
        <?php endif; ?>
        <input type="file" name="image">

        <input type="submit" value="Update Profile">
    </form>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById("passwordField");
    const toggleBtn = document.querySelector(".toggle-password");

    if (passwordField.type === "password") {
        passwordField.type = "text";
        toggleBtn.textContent = "Hide";
    } else {
        passwordField.type = "password";
        toggleBtn.textContent = "Show";
    }
}
</script>

</body>
</html>
