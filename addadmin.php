<?php
$servername = "localhost";
$username = "root"; // Change if needed
$password = "";
$dbname = "gaming_store";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $salary = $_POST['salary'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Secure password

    // Handle file upload
    $target_dir = "uploads/";
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

    // Insert into database
    $sql = "INSERT INTO admin_users (username, email, position, salary, password, image) 
            VALUES ('$username', '$email', '$position', '$salary', '$password', '$image_name')";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Admin added successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}

// Fetch existing admin users
$sql = "SELECT * FROM admin_users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <button onclick="window.location.href='admindashboard.php'">Back to Dashboard</button>

    <h2>Add Admin</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Username:</label>
        <input type="text" name="username" required><br>

        <label>Email:</label>
        <input type="email" name="email" required><br>

        <label>Position:</label>
        <input type="text" name="position" required><br>

        <label>Salary:</label>
        <input type="number" name="salary" required><br>

        <label>Password:</label>
        <input type="password" name="password" required><br>

        <label>Image:</label>
        <input type="file" name="image" accept="image/*" required><br>

        <button type="submit">Add Admin</button>
    </form>

    <h2>Admin List</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Position</th>
            <th>Salary</th>
            <th>Image</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['username'] ?></td>
                <td><?= $row['email'] ?></td>
                <td><?= $row['position'] ?></td>
                <td>RM <?= number_format($row['salary'], 2) ?></td>
                <td><img src="uploads/<?= $row['image'] ?>" width="50"></td>
            </tr>
        <?php } ?>
    </table>

</body>
</html>

<?php
$conn->close();
?>
