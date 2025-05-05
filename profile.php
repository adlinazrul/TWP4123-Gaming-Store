<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Include your database connection file
include('db_connection.php'); 

// Get the logged-in user's ID from the session
$user_id = $_SESSION['user_id'];

// Query to fetch the user's profile details
$sql = "SELECT name, position, profile_image FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $name = $user['name'];
    $position = $user['position'];
    $profile_image = $user['profile_image'];
} else {
    // If no user is found, redirect or show an error
    header("Location: login.php");
    exit();
}

$stmt->close();
?>
