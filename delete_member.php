<?php
$conn = new mysqli("localhost", "root", "", "your_database");
$data = json_decode(file_get_contents("php://input"), true);
$conn->query("DELETE FROM members WHERE id={$data['id']}");
?>
