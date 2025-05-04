<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Change if needed
$password = "";     // Change if needed
$dbname = "gaming_store"; // Your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get login input
$uname = $_POST['uname'];
$psw = $_POST['psw'];

// Validate login
$sql = "SELECT * FROM addadmin WHERE username = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $uname, $psw);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $row = $result->fetch_assoc();
  $_SESSION['admin_id'] = $row['id'];
  $_SESSION['admin_username'] = $row['username'];
  echo "<script>alert('Login successful!'); window.location.href='admin_dashboard.php';</script>";
} else {
  echo "<script>alert('Invalid username or password'); window.location.href='loginadmin.php';</script>";
}

$stmt->close();
$conn->close();
?>
