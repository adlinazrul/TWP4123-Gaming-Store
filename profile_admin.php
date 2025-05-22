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
    $password = $_POST['password'];

    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target = "image/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);

        $sql = "UPDATE admin_list SET username=?, email=?, password=?, image=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $username, $email, $password, $image, $admin_id);
    } else {
        $sql = "UPDATE admin_list SET username=?, email=?, password=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $username, $email, $password, $admin_id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='admindashboard.php';</script>";
    } else {
        echo "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
}

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
            margin: 60px auto 80px;
            padding: 30px 30px 40px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        h2 {
            text-align: center;
            color: #ef4444;
            margin-bottom: 20px;
        }

        .profile-image-wrapper {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 10px auto;
            cursor: pointer;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            transition: box-shadow 0.3s ease;
        }

        .profile-image-wrapper:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.25);
        }

        .profile-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 50%;
            transition: transform 0.3s ease;
        }

        .profile-image-wrapper:hover img {
            transform: scale(1.05);
        }

        .overlay-text {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(239, 68, 68, 0.7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            font-weight: 600;
            font-size: 16px;
            border-radius: 50%;
            transition: opacity 0.3s ease;
        }

        .profile-image-wrapper:hover .overlay-text {
            opacity: 1;
        }

        .role-badge {
            text-align: center;
            margin: 10px auto 20px auto;
            max-width: 200px;
            font-weight: 700;
            font-size: 16px;
            color: #fff;
            background: linear-gradient(135deg, #ef4444, #f97316);
            padding: 8px 20px;
            border-radius: 30px;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.6);
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .edit-password-button {
            display: block;
            margin: 0 auto 30px;
            background-color: #f59e0b;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .edit-password-button:hover {
            background-color: #d97706;
        }

        form label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #333;
        }

        form input[type="text"],
        form input[type="email"],
        form input[type="password"] {
            width: 100%;
            height: 40px;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-container input[type="password"],
        .password-container input[type="text"] {
            flex: 1;
            padding-right: 90px;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            height: 28px;
            padding: 0 14px;
            background-color: #ef4444;
            color: white;
            border: none;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
        }

        .toggle-password:hover {
            background-color: #dc2626;
        }

        input[type="file"] {
            display: none;
        }

        form input[type="submit"] {
            margin-top: 25px;
            background-color: #ef4444;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
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
        <div class="profile-image-wrapper" tabindex="0">
            <?php
            $imgSrc = !empty($admin['image']) ? "image/" . htmlspecialchars($admin['image']) : "image/default_profile.jpg";
            ?>
            <img src="<?= $imgSrc ?>" alt="Profile Image">
            <div class="overlay-text">Change Image Profile</div>
            <input type="file" name="image" id="imageInput" accept="image/*">
        </div>

        <div class="role-badge"><?= htmlspecialchars($admin['user_type']) ?></div>

        <button type="button" class="edit-password-button" onclick="document.getElementById('password').focus();">Edit Password</button>

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>

        <label for="password">Password:</label>
        <div class="password-container">
            <input type="password" id="password" name="password" value="<?= htmlspecialchars($admin['password']) ?>" required>
            <button type="button" id="togglePassword" class="toggle-password">Show</button>
        </div>

        <input type="submit" value="Update Profile">
    </form>
</div>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePassword.addEventListener('click', () => {
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            togglePassword.textContent = "Hide";
        } else {
            passwordInput.type = "password";
            togglePassword.textContent = "Show";
        }
    });

    // Trigger file input when clicking profile image
    document.getElementById('imageWrapper')?.addEventListener('click', () => {
        document.getElementById('imageInput')?.click();
    });
</script>

</body>
</html>
