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
    $position = $_POST['position'];
    $salary = $_POST['salary'];

    if (!empty($_FILES["image"]["name"])) {
        $image_name = basename($_FILES["image"]["name"]);
        $target_file = "uploads/" . $image_name;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        $sql = "UPDATE admin_list SET username=?, email=?, position=?, salary=?, image=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $username, $email, $position, $salary, $image_name, $id);
    } else {
        $sql = "UPDATE admin_list SET username=?, email=?, position=?, salary=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $username, $email, $position, $salary, $id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Admin updated.'); window.location.href='manageadmin.php';</script>";
    } else {
        echo "Update failed.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Edit Admin</title></head>
<body>
<h2>Edit Admin</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $row['id'] ?>">
    Username: <input type="text" name="username" value="<?= $row['username'] ?>" required><br>
    Email: <input type="email" name="email" value="<?= $row['email'] ?>" required><br>
    Position: <input type="text" name="position" value="<?= $row['position'] ?>" required><br>
    Salary: <input type="number" name="salary" value="<?= $row['salary'] ?>" required><br>
    Profile Image: <input type="file" name="image"><br>
    <button type="submit">Update</button>
</form>
</body>
</html>
