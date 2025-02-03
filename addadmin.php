<?php
$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Change if needed
$dbname = "gaming_store"; // Ensure database name is correct

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from the frontend
$staff_id = $_POST['emp_id'];
$name = $_POST['name'];
$position = $_POST['position'];
$salary = $_POST['salary'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash password for security
$image = ""; // Placeholder for image path

// Handle image upload
if (!empty($_FILES['image']['name'])) {
    $target_dir = "uploads/"; // Folder where images will be stored
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image = $target_file; // Save the image path to store in the database
    }
}

// Insert data into `admin_users` table
$sql = "INSERT INTO admin_users (id, name, position, salary, password, image) 
        VALUES ('$staff_id', '$name', '$position', '$salary', '$password', '$image')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["message" => "Staff added successfully"]);
} else {
    echo json_encode(["error" => "Error: " . $conn->error]);
}

$conn->close();
?>
