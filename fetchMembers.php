<?php
$conn = new mysqli("localhost", "root", "", "your_database");
$result = $conn->query("SELECT * FROM members");
$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}
echo json_encode($members);
?>
