<?php
include 'db_connection.php';

$sql = "SELECT emp_id, username, email, position, salary, image FROM admin_users";
$result = $conn->query($sql);

$staff = [];
while ($row = $result->fetch_assoc()) {
    $staff[] = $row;
}

echo json_encode($staff);
?>
