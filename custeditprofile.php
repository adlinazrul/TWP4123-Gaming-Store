<?php
include "db_connect1.php"; // Make sure this file contains your database connection details

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $profile_pic = $_FILES["profile-pic"]["name"];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($profile_pic);

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES["profile-pic"]["tmp_name"], $target_file)) {
        // Update the database with the new profile information
        $sql = "UPDATE customers SET username = ?, email = ?, phone = ?, profile_pic = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $email, $phone, $target_file, $email);

        if ($stmt->execute()) {
            echo "Profile updated successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error uploading profile picture.";
    }

    $conn->close();
}
?>