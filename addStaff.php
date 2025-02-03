<?php
include 'db_connection.php';  // Ensure your DB connection is correct

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required fields are set
    if (!isset($_POST["emp_id"], $_POST["name"], $_POST["email"], $_POST["position"], $_POST["salary"], $_POST["password"])) {
        echo json_encode(["success" => false, "message" => "Missing form data"]);
        exit();
    }

    // Retrieve form data
    $emp_id = $_POST["emp_id"];
    $name = $_POST["name"];
    $email = $_POST["email"];
    $position = $_POST["position"];
    $salary = $_POST["salary"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

    // Debugging: Print received data
    error_log("Received data: emp_id=$emp_id, name=$name, email=$email, position=$position, salary=$salary");

    // Handle image upload
    if (!empty($_FILES["image"]["name"])) {
        $imagePath = "uploads/" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath);
    } else {
        $imagePath = "default.jpg";  // Set default if no image uploaded
    }

    // Debugging: Check image path
    error_log("Image path: $imagePath");

    // Insert data into database
    $sql = "INSERT INTO admin_users (emp_id, name, email, position, salary, password, image) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssiss", $emp_id, $name, $email, $position, $salary, $password, $imagePath);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
    }

    // Debugging: Check if query executed
    error_log("SQL Error: " . $stmt->error);

    $stmt->close();
    $conn->close();
}
?>
