<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "staff_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, name, position, salary, image FROM staff";
$result = $conn->query($sql);

$staffArray = [];
while ($row = $result->fetch_assoc()) {
    $staffArray[] = $row;
}

echo json_encode($staffArray);
$conn->close();
?>
