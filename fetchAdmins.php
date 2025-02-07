<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM admin_list";
$result = $conn->query($sql);

$admins = [];

while ($row = $result->fetch_assoc()) {
    $admins[] = $row;
}

echo json_encode($admins);

$conn->close();
?>
