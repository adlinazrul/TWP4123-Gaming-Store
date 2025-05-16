<?php
session_start();
include "db_connect1.php";

// Ensure the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
$email = $_SESSION['email'];

// Fetch user info
$sql = "SELECT * FROM customers WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Stop if user not found
if (!$user) {
    die("User not found.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST["first_name"];
    $lastName = $_POST["last_name"];
    $phone = $_POST["phone"];
    $username = $_POST["username"];
    $birthdate = $_POST["birthdate"];
    $bio = $_POST["bio"];
    $profile_pic = $_FILES["profile_pic"]["name"];
    $newPassword = $_POST["new_password"];
    $confirmPassword = $_POST["confirm_password"];
    $target_dir = "uploads/";

    // Handle uploads
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (!empty($profile_pic)) {
        $target_file = $target_dir . basename($profile_pic);
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file);
    } else {
        $target_file = $user["profile_pic"] ?? "";
    }

    // 🔐 Handle password update if provided
    $updatePassword = false;
    if (!empty($newPassword)) {
        if ($newPassword === $confirmPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updatePassword = true;
        } else {
            echo "<script>alert('New password and confirm password do not match.'); window.history.back();</script>";
            exit();
        }
    }

    // Build SQL and bind params accordingly
    if ($updatePassword) {
        $sql = "UPDATE customers SET first_name=?, last_name=?, phone=?, username=?, birthdate=?, bio=?, profile_pic=?, password=? WHERE email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $firstName, $lastName, $phone, $username, $birthdate, $bio, $target_file, $hashedPassword, $email);
    } else {
        $sql = "UPDATE customers SET first_name=?, last_name=?, phone=?, username=?, birthdate=?, bio=?, profile_pic=? WHERE email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $firstName, $lastName, $phone, $username, $birthdate, $bio, $target_file, $email);
    }

    if ($stmt->execute()) {
        // Log changes
        $fields = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'username' => $username,
            'birthdate' => $birthdate,
            'bio' => $bio,
            'profile_pic' => $target_file
        ];

        if ($updatePassword) {
            $fields['password'] = '[HIDDEN]';
        }

        foreach ($fields as $field => $new_value) {
            if (!isset($user[$field]) || $user[$field] != $new_value) {
                $old_value = ($field === 'password') ? '[HIDDEN]' : $user[$field];
                $sql_log = "INSERT INTO profile_edits (customer_id, field_changed, old_value, new_value) VALUES (?, ?, ?, ?)";
                $stmt_log = $conn->prepare($sql_log);
                $stmt_log->bind_param("isss", $user['id'], $field, $old_value, $new_value);
                $stmt_log->execute();
                $stmt_log->close();
            }
        }

        echo "<script>alert('Profile updated successfully!'); window.location.href='custeditprofile.php';</script>";
    } else {
        echo "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>


<!-- HTML Section Starts -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | Gamers Hideout</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 60px auto;
            padding: 40px;
            background-color: #ffffff;
            border: 1px solid #ffb3b3;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(255, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #b30000;
            margin-bottom: 30px;
            font-size: 28px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #cc0000;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="date"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 20px;
            border: 1px solid #ff9999;
            border-radius: 5px;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
        }

        .profile-image {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .profile-image img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 2px solid #ff6666;
            object-fit: cover;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
        }

        .btn {
            padding: 10px 25px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-cancel {
            background-color: #fff0f0;
            color: #b30000;
            border: 1px solid #ff9999;
        }

        .btn-cancel:hover {
            background-color: #ffe6e6;
        }

        .btn-save {
            background-color: #e60000;
            color: white;
        }

        .btn-save:hover {
            background-color: #cc0000;
        }
    </style>
</head>
<body class="bg-red-50 font-sans">
    <div class="container mx-auto max-w-3xl p-8 bg-white shadow-lg mt-8 rounded-xl border">
        <h2 class="text-3xl font-bold text-red-800 mb-6">My Profile</h2>
 <div>
                
               
               <?php if (!empty($user['profile_pic'])): ?>
    <div class="flex justify-center mb-6">
        <img src="<?= $user['profile_pic'] ?>" alt="Current Picture" class="w-32 h-32 rounded-full border-4 border-red-300 shadow-md object-cover">
    </div>
    <label class="block text-red-700">Profile Picture</label>
     <input type="file" name="profile_pic" class="form-input w-full rounded border-red-300">
<?php endif; ?>
            </div>



        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-red-700">First Name</label>
                    <input type="text" name="first_name" class="form-input w-full rounded border-red-300" value="<?= htmlspecialchars($user['first_name']) ?>">
                </div>
                <div>
                    <label class="block text-red-700">Last Name</label>
                    <input type="text" name="last_name" class="form-input w-full rounded border-red-300" value="<?= htmlspecialchars($user['last_name']) ?>">
                </div>
                <div>
                    <label class="block text-red-700">Email (read-only)</label>
                    <input type="email" name="email" class="form-input w-full rounded border-red-300 bg-gray-100" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                </div>
                <div>
                    <label class="block text-red-700">Phone</label>
                    <input type="tel" name="phone" class="form-input w-full rounded border-red-300" value="<?= htmlspecialchars($user['phone']) ?>">
                </div>
                <div>
                    <label class="block text-red-700">Username</label>
                    <input type="text" name="username" class="form-input w-full rounded border-red-300" value="<?= htmlspecialchars($user['username']) ?>">
                </div>
                <div>
                    <label class="block text-red-700">Date of Birth</label>
                    <input type="date" name="birthdate" class="form-input w-full rounded border-red-300" value="<?= htmlspecialchars($user['birthdate']) ?>">
                </div>
            </div>
            <div>
                <label class="block text-red-700">Bio</label>
                <textarea name="bio" rows="3" class="form-input w-full rounded border-red-300"><?= htmlspecialchars($user['bio']) ?></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block text-red-700">New Password</label>
        <input type="password" name="new_password" class="form-input w-full rounded border-red-300">
    </div>
    <div>
        <label class="block text-red-700">Confirm New Password</label>
        <input type="password" name="confirm_password" class="form-input w-full rounded border-red-300">
    </div>
</div>
           
            <div class="flex justify-end gap-4 pt-4">
                <a href="index.php" class="px-4 py-2 border border-red-300 text-red-700 rounded hover:bg-red-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Save Changes</button>
            </div>
        </form>
    </div>
</body>
</html>
