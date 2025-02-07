<?php
$conn = new mysqli("localhost", "root", "", "gaming_store");
$data = json_decode(file_get_contents("php://input"), true);
$conn->query("UPDATE members SET email='{$data['email']}' WHERE id={$data['id']}");
?>
