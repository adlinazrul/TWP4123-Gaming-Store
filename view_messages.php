<?php
include 'db_connectsupport.php';

$sql = "SELECT * FROM messages ORDER BY created_at DESC";
$result = $conn->query($sql);

echo "<h2>Customer Messages</h2>";
echo "<table border='1' cellpadding='10'>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Subject</th>
<th>Message</th>
<th>Date</th>
</tr>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
        <td>{$row['id']}</td>
        <td>{$row['name']}</td>
        <td>{$row['email']}</td>
        <td>{$row['subject']}</td>
        <td>{$row['message']}</td>
        <td>{$row['created_at']}</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No messages yet.</td></tr>";
}

echo "</table>";
$conn->close();
?>
