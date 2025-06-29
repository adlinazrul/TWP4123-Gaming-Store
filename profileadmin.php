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

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.'); window.history.back();</script>";
        exit;
    }

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
        echo "<script>alert('Profile updated successfully'); window.location.href='admindashboard.php';</script>";
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 60px auto 80px;
            padding: 30px 30px 40px;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        h2 {
            text-align: center;
            color: #ef4444;
            margin-bottom: 20px;
            font-size: 28px;
            font-weight: 700;
        }

        .profile-image-wrapper {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px auto;
            cursor: pointer;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            border: 4px solid #fff;
        }

        .profile-image-wrapper:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .profile-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
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
            transition: opacity 0.3s ease;
        }

        .profile-image-wrapper:hover .overlay-text {
            opacity: 1;
        }

        .role-badge {
            text-align: center;
            margin: 15px auto 25px auto;
            max-width: 200px;
            font-weight: 700;
            font-size: 16px;
            color: #fff;
            background: linear-gradient(135deg, #ef4444, #f97316);
            padding: 8px 20px;
            border-radius: 30px;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        /* Creative Edit Password Button */
        .edit-password-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin: 20px auto;
            padding: 12px 28px;
            background: linear-gradient(135deg,rgb(246, 106, 59),rgb(241, 104, 99));
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(246, 59, 59, 0.3);
            transition: all 0.3s ease;
            text-decoration: none;
            gap: 10px;
        }

        .edit-password-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(246, 59, 59, 0.4);
            background: linear-gradient(135deg,rgb(241, 118, 99),rgb(246, 93, 59));
        }

        .edit-password-button:active {
            transform: translateY(0);
        }

        .edit-password-button svg {
            transition: all 0.3s ease;
        }

        .edit-password-button:hover svg {
            transform: rotate(-10deg) scale(1.1);
        }

        .button-hover-effect {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0) 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .edit-password-button:hover .button-hover-effect {
            opacity: 1;
        }

        form label {
            display: block;
            margin-top: 18px;
            font-weight: 600;
            color: #333;
            font-size: 15px;
        }

        form input[type="text"],
        form input[type="email"],
        form input[type="password"] {
            width: 100%;
            height: 45px;
            padding: 10px 15px;
            margin-top: 8px;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-sizing: border-box;
            font-size: 15px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        form input[type="text"]:focus,
        form input[type="email"]:focus,
        form input[type="password"]:focus {
            border-color:rgb(246, 59, 59);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            outline: none;
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
            height: 30px;
            padding: 0 14px;
            background-color:rgb(246, 65, 59);
            color: white;
            border: none;
            border-radius: 20px;
            font-size: 13px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .toggle-password:hover {
            background-color:rgb(235, 60, 37);
        }

        input[type="file"] {
            display: none;
        }

        form input[type="submit"] {
            margin-top: 30px;
            background: linear-gradient(135deg, #ef4444, #f97316);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        form input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
            background: linear-gradient(135deg, #f97316, #ef4444);
        }

        form input[type="submit"]:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>

<div class="container">
    <h2>My Profile</h2>

    <form method="post" enctype="multipart/form-data">
        <div class="profile-image-wrapper" id="imageWrapper">
            <?php
            $imgSrc = !empty($admin['image']) ? "image/" . htmlspecialchars($admin['image']) : "image/default_profile.jpg";
            ?>
            <img src="<?= $imgSrc ?>" alt="Profile Image">
            <div class="overlay-text">Change Photo</div>
            <input type="file" name="image" id="imageInput" accept="image/*">
        </div>

        <div class="role-badge"><?= htmlspecialchars($admin['user_type']) ?></div>
        <center>
        <a href="edit_passwordadmin.php" class="edit-password-button">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-key">
                <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
            </svg>
            <span>Edit Password</span>
            <div class="button-hover-effect"></div>
        </a>
        </center>

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>

       

        <input type="submit" value="Update Profile">
    </form>
</div>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    

    // Trigger file input when clicking profile image
    document.getElementById('imageWrapper').addEventListener('click', () => {
        document.getElementById('imageInput').click();
    });

    // Preview image when selected
    document.getElementById('imageInput').addEventListener('change', function(e) {
        const [file] = e.target.files;
        if (file) {
            const img = document.querySelector('.profile-image-wrapper img');
            img.src = URL.createObjectURL(file);
            img.onload = () => URL.revokeObjectURL(img.src);
        }
    });
</script>

</body>
</html>