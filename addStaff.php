<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "gaming_store";
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = $_POST['username'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $salary = $_POST['salary'];
    $password = $_POST['password'];

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $imageName = $_FILES['image']['name'];
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageSize = $_FILES['image']['size'];
        $imageError = $_FILES['image']['error'];
        $imageType = $_FILES['image']['type'];

        // Ensure valid image type (optional check)
        $allowed = array("jpg", "jpeg", "png", "gif");
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if (in_array($imageExt, $allowed)) {
            $imageNewName = uniqid('', true) . "." . $imageExt;
            $imageUploadPath = 'uploads/' . $imageNewName;

            if (move_uploaded_file($imageTmpName, $imageUploadPath)) {
                echo "Image uploaded successfully.<br>";
            } else {
                echo "Error uploading image.<br>";
            }
        } else {
            echo "Invalid image type.<br>";
        }
    } else {
        echo "No image uploaded or there was an error with the upload.<br>";
    }

    // Insert data into the database
    $sql = "INSERT INTO admin_staff (username, email, position, salary, password, image) 
            VALUES ('$username', '$email', '$position', '$salary', '$password', '$imageNewName')";

    if ($conn->query($sql) === TRUE) {
        echo "New staff added successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
