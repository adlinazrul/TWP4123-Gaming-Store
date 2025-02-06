<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete admin from database
    $sql = "DELETE FROM admin_users WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Admin deleted successfully!'); window.location.href='add_admin.php';</script>";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

$conn->close();
?>
