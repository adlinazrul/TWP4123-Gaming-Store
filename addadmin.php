<?php
$servername = "localhost";
$username = "root"; // Change if using different DB credentials
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
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hashing password

    // Image upload handling
    $target_dir = "uploads/";
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

    // Insert into database
    $sql = "INSERT INTO admin_users (username, email, position, salary, password, image)
            VALUES ('$username', '$email', '$position', '$salary', '$password', '$image_name')";

    if ($conn->query($sql) === TRUE) {
        echo "Admin added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>
