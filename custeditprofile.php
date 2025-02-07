<?php
include "db_connect1.php"; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $profile_pic = $_FILES["profilePicture"]["name"];
    $target_dir = "uploads/";

    // Ensure the uploads directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Handle profile picture upload
    if (!empty($profile_pic)) {
        $target_file = $target_dir . basename($profile_pic);
        move_uploaded_file($_FILES["profilePicture"]["tmp_name"], $target_file);
    } else {
        // If no new image is uploaded, keep the old one
        $sql = "SELECT profile_pic FROM customers WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $target_file = $row["profile_pic"];
        $stmt->close();
    }

    // Fetch the current profile information
    $sql = "SELECT * FROM customers WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_profile = $result->fetch_assoc();
    $stmt->close();

    // Update profile information in the database
    $sql = "UPDATE customers SET username = ?, phone = ?, profile_pic = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $phone, $target_file, $email);

    if ($stmt->execute()) {
        echo "Profile updated successfully!";

        // Log changes in the profile_edits table
        $fields = ['username' => $name, 'phone' => $phone, 'profile_pic' => $target_file];

        foreach ($fields as $field => $new_value) {
            if ($current_profile[$field] != $new_value) {
                $sql = "INSERT INTO profile_edits (customer_id, field_changed, old_value, new_value) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isss", $current_profile['id'], $field, $current_profile[$field], $new_value);
                $stmt->execute();
            }
        }

        echo "<script>alert('Profile updated successfully!'); window.location.href='edit_profile.html';</script>";
    } else {
        echo "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
