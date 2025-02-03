<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['admin_logged_in'])) {
    die("Unauthorized access");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_id = $_POST['emp_id'];
    $name = $_POST['name'];
    $position = $_POST['position'];
    $salary = $_POST['salary'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Handle Image Upload
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $image_name = basename($_FILES["image"]["name"]);
    $image_path = $target_dir . $image_name;
    move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);

    // Insert into database
    $sql = "INSERT INTO admin_user (emp_id, name, position, salary, password, image) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $emp_id, $name, $position, $salary, $password, $image_path);

    if ($stmt->execute()) {
        echo "Admin added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
