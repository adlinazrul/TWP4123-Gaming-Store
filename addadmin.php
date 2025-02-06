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

// Debug: Print received POST data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "<pre>";
    print_r($_POST); // Check if position and salary are received
    echo "</pre>";

    $username = $_POST['username'] ?? null;
    $email = $_POST['email'] ?? null;
    $position = $_POST['position'] ?? null;
    $salary = $_POST['salary'] ?? null;
    $password = $_POST['password'] ?? null;

    // Check for missing values
    if (!$username || !$email || !$position || !$salary || !$password) {
        die("Missing required fields.");
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Handle file upload
    $target_dir = "uploads/";
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

    // Insert into database
    $sql = "INSERT INTO admin_users (username, email, position, salary, password, image)
            VALUES ('$username', '$email', '$position', '$salary', '$hashed_password', '$image_name')";

    if ($conn->query($sql) === TRUE) {
        echo "Admin added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>
