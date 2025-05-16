<?php
$conn = new mysqli("localhost", "root", "", "gaming_store");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM admin_list WHERE id=$id");
    $row = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $user_type = $_POST['user_type'];  // role: Admin or Super Admin
    $salary = $_POST['salary'];

    if (!empty($_FILES["image"]["name"])) {
        $image_name = basename($_FILES["image"]["name"]);
        $target_file = "uploads/" . $image_name;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        $sql = "UPDATE admin_list SET username=?, email=?, user_type=?, salary=?, image=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $username, $email, $user_type, $salary, $image_name, $id);
    } else {
        $sql = "UPDATE admin_list SET username=?, email=?, user_type=?, salary=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss si", $username, $email, $user_type, $salary, $id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Admin updated.'); window.location.href='manageadmin.php';</script>";
    } else {
        echo "Update failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            padding: 40px;
        }
        .form-container {
            max-width: 500px;
            background: #fff;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin-top: 15px;
            color: #555;
        }
        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }
        button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color:#d03b3b;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #a72a2a;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: #333;
        }
        .role-buttons {
            margin-top: 5px;
            display: flex;
            gap: 20px; /* space between the radio options */
            align-items: center;
        }
        .role-buttons label {
            margin: 0;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Admin</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
            
            <label>Username:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($row['username']) ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required>

            <label>Role:</label>
            <div class="role-buttons">
                <input type="radio" id="admin" name="user_type" value="Admin" <?= ($row['user_type'] == 'Admin') ? 'checked' : '' ?> required>
                <label for="admin">Admin</label>

                <input type="radio" id="superadmin" name="user_type" value="Super Admin" <?= ($row['user_type'] == 'Super Admin') ? 'checked' : '' ?>>
                <label for="superadmin">Super Admin</label>
            </div>

            <label>Salary (RM):</label>
            <input type="number" name="salary" value="<?= htmlspecialchars($row['salary']) ?>" required>

            <label>Profile Image:</label>
            <input type="file" name="image">

            <button type="submit">Update Admin</button>
        </form>
        <a href="addadmin.php" class="back-link">← Back to Admin List</a>
    </div>
</body>
</html>
