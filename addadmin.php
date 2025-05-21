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

// Delete functionality
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    // Fetch image to delete from folder
    $stmt = $conn->prepare("SELECT image FROM admin_list WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->bind_result($image_name);
    $stmt->fetch();
    $stmt->close();

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM admin_list WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Remove image from folder
    if (!empty($image_name) && file_exists("uploads/" . $image_name)) {
        unlink("uploads/" . $image_name);
    }

    echo "<script>alert('Admin deleted successfully!'); window.location.href='addadmin.php';</script>";
    exit;
}

// Add admin functionality
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $salary = $_POST['salary'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $user_type = $_POST['user_type'];

    $target_dir = "uploads/";
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

    $check_email = "SELECT * FROM admin_list WHERE email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Error: Email already exists!'); window.location.href='addadmin.php';</script>";
    } else {
        $sql = "INSERT INTO admin_list (username, email, position, salary, password, image, user_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $username, $email, $position, $salary, $password, $image_name, $user_type);

        if ($stmt->execute()) {
            echo "<script>alert('Admin added successfully!'); window.location.href='addadmin.php';</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
    }
    $stmt->close();
}

// Fetch admin list
$sql = "SELECT * FROM admin_list";
$result = $conn->query($sql);

// Profile picture of logged-in admin
$query = "SELECT image FROM admin_list WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($image);
if ($stmt->fetch() && !empty($image)) {
    $profile_image = 'uploads/' . $image;
} else {
    $profile_image = 'image/default_profile.jpg';
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Management</title>
    <style>
        body { font-family: Arial; }
        .container { width: 80%; margin: auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background-color: #f44336; color: white; }
        img { width: 60px; height: auto; border-radius: 5px; }
        button, .delete-btn {
            padding: 5px 10px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover, .delete-btn:hover {
            background-color: #d32f2f;
        }
        input, select {
            padding: 8px;
            width: 100%;
            margin-bottom: 10px;
        }
        label { font-weight: bold; }
    </style>
    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this admin?")) {
                window.location.href = 'addadmin.php?delete=' + id;
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Add Admin</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Username:</label>
            <input type="text" name="username" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Position:</label>
            <input type="text" name="position" required>

            <label>Salary:</label>
            <input type="number" name="salary" step="0.01" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <label>Profile Image:</label>
            <input type="file" name="image" accept="image/*" required>

            <label>Role:</label>
            <select name="user_type" required>
                <option value="Admin">Admin</option>
                <option value="Super Admin">Super Admin</option>
            </select>

            <button type="submit" name="submit">Add Admin</button>
        </form>

        <h2>Admin List</h2>
        <table>
            <tr>
                <th>Image</th>
                <th>Username</th>
                <th>Email</th>
                <th>Position</th>
                <th>Salary</th>
                <th>User Type</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr>
                    <td><img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Admin Image"></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['position']); ?></td>
                    <td>RM <?php echo number_format($row['salary'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['user_type']); ?></td>
                    <td>
                        <button class="delete-btn" onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
