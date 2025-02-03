<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Change if needed
$dbname = "gaming_store"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Debugging: Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['name'], $_POST['position'], $_POST['salary'], $_POST['password'])) {
        die("Error: Missing form data");
    }

    $name = $_POST['name'];
    $position = $_POST['position'];
    $salary = $_POST['salary'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encrypt password

    // Handle image upload
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $targetDir = "uploads/";
        $imageName = basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $imageName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            // Insert into database
            $sql = "INSERT INTO admin_users (name, position, salary, password, image) 
                    VALUES ('$name', '$position', '$salary', '$password', '$imageName')";

            if ($conn->query($sql) === TRUE) {
                echo "New staff added successfully!";
            } else {
                echo "Error: " . $conn->error;
            }
        } else {
            echo "Error uploading image.";
        }
    } else {
        echo "No image uploaded or file error.";
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
