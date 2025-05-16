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
    $position = $_POST['position'];  // will not be changed because readonly in form
    $salary = $_POST['salary'];
    $password = $_POST['password'];

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

        /* Profile Image Container */
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

        /* Overlay text on hover */
        .overlay-text {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(239, 68, 68, 0.7); /* red with transparency */
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            font-weight: 600;
            font-size: 16px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            letter-spacing: 0.05em;
            text-shadow: 0 1px 2px rgba(0,0,0,0.4);
            border-radius: 50%;
            transition: opacity 0.3s ease;
            user-select: none;
            text-align: center;
            padding: 0 10px; /* some padding for longer text */
        }

        .profile-image-wrapper:hover .overlay-text {
            opacity: 1;
        }

        /* Role Badge */
        .role-badge {
            text-align: center;
            margin: 10px auto 30px auto;
            max-width: 200px;
            font-weight: 700;
            font-size: 16px;
            color: #fff;
            background: linear-gradient(135deg, #ef4444, #f97316);
            padding: 8px 20px;
            border-radius: 30px;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.6);
            letter-spacing: 0.1em;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            user-select: none;
            transition: transform 0.3s ease;
            cursor: default;
            text-transform: uppercase;
        }

        .role-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.8);
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
        form input[type="password"] {
            width: 100%;
            height: 40px;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        /* Readonly style for position input */
        input[readonly] {
            background: #eee;
            cursor: not-allowed;
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
            transition: background-color 0.3s ease;
        }

        .toggle-password:hover {
            background-color: #dc2626;
        }

        /* Hide actual file input */
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

    <form method="post" enctype="multipart/form-data" id="profileForm">
        <!-- Profile Image with overlay -->
        <div class="profile-image-wrapper" id="imageWrapper" tabindex="0" aria-label="Change Profile Image">
            <?php
            $imgSrc = !empty($admin['image']) ? "image/" . htmlspecialchars($admin['image']) : "image/default_profile.jpg";
            ?>
            <img src="<?= $imgSrc ?>" alt="Profile Image" id="profileImage">
            <div class="overlay-text">Change Image Profile</div>
            <input type="file" name="image" id="imageInput" accept="image/*">
        </div>

        <!-- Role Badge under profile image -->
        <div class="role-badge" aria-label="User Role">
            <?= htmlspecialchars($admin['position']) ?>
        </div>

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>

        <label for="position">Position:</label>
        <input type="text" id="position" name="position" value="<?= htmlspecialchars($admin['position']) ?>" readonly>

        <label for="salary">Salary:</label>
        <input type="number" id="salary" name="salary" value="<?= htmlspecialchars($admin['salary']) ?>" min="0" required>

        <label for="password">Password:</label>
        <div class="password-container">
            <input type="password" id="password" name="password" value="<?= htmlspecialchars($admin['password']) ?>" required>
            <button type="button" id="togglePassword" class="toggle-password" aria-label="Show or hide password">Show</button>
        </div>

        <input type="submit" value="Update Profile">
    </form>
</div>

<script>
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePassword.addEventListener('click', () => {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            togglePassword.textContent = 'Hide';
        } else {
            passwordInput.type = 'password';
            togglePassword.textContent = 'Show';
        }
    });

    // Clicking on the profile image wrapper triggers file input
    const imageWrapper = document.getElementById('imageWrapper');
    const imageInput = document.getElementById('imageInput');

    imageWrapper.addEventListener('click', () => {
        imageInput.click();
    });

    // Preview new profile image when selected
    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profileImage').src = e.target.result;
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Allow keyboard access: press Enter or Space on image wrapper to open file dialog
    imageWrapper.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            imageInput.click();
        }
    });
</script>

</body>
</html>
