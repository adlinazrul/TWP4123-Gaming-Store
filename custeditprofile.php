<?php
include "db_connect1.php"; // Make sure this file contains your database connection details

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $profile_pic = $_FILES["profile-pic"]["name"];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($profile_pic);

    // Fetch the current profile information
    $sql = "SELECT * FROM customers WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_profile = $result->fetch_assoc();

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES["profile-pic"]["tmp_name"], $target_file)) {
        // Update the database with the new profile information
        $sql = "UPDATE customers SET username = ?, phone = ?, profile_pic = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $phone, $target_file, $email);

        if ($stmt->execute()) {
            echo "Profile updated successfully!";

            // Log changes to the profile_edits table
            $fields = ['username', 'phone', 'profile_pic'];
            foreach ($fields as $field) {
                if ($current_profile[$field] != $$field) {
                    $sql = "INSERT INTO profile_edits (customer_id, field_changed, old_value, new_value) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isss", $current_profile['id'], $field, $current_profile[$field], $$field);
                    $stmt->execute();
                }
            }
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