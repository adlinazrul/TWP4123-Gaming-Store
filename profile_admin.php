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
        echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
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
    <style>
        form {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }

        form input[type="text"],
        form input[type="email"],
        form input[type="number"],
        form input[type="password"],
        form input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
        }

        form img {
            width: 100px;
            margin-top: 10px;
        }

        form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }

        form input[type="submit"]:hover {
            background-color: #45a049;
        }

        h2 {
            text-align: center;
        }
    </style>
</head>
<body>

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
    <input type="password" name="password" value="<?= htmlspecialchars($admin['password']) ?>" required>

    <label>Profile Image:</label><br>
    <img src="image/<?= htmlspecialchars($admin['image']) ?>" alt="Profile Image"><br>
    <input type="file" name="image">

    <input type="submit" value="Update Profile">
</form>

</body>
</html>
