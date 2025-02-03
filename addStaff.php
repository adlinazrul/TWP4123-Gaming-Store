<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST["emp_id"]) || !isset($_POST["username"]) || !isset($_POST["email"]) || 
        !isset($_POST["position"]) || !isset($_POST["salary"]) || !isset($_POST["password"])) {
        echo json_encode(["success" => false, "message" => "Missing form data"]);
        exit();
    }

    $emp_id = $_POST["emp_id"];
    $username = $_POST["username"];
    $email = $_POST["email"];
    $position = $_POST["position"];
    $salary = $_POST["salary"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

    // Handle Image Upload
    $imagePath = "";
    if (!empty($_FILES["image"]["name"])) {
        $imageName = basename($_FILES["image"]["name"]);
        $imagePath = "uploads/" . $imageName;
        move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath);
    }

    // Insert Data into Database
    $sql = "INSERT INTO admin_users (emp_id, username, email, position, salary, password, image) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssiss", $emp_id, $username, $email, $position, $salary, $password, $imagePath);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>
