<?php
session_start();

// Check login session
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM admin_list WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "🎉 Admin deleted successfully!";
        header("Location: addadmin.php");
        exit;
    } else {
        $_SESSION['error_message'] = "❌ Error deleting admin: " . $stmt->error;
        header("Location: addadmin.php");
        exit;
    }
    $stmt->close();
}

// Fetch admins
$sql = "SELECT * FROM admin_list";
$result = $conn->query($sql);

// Fetch logged in admin image
$stmt = $conn->prepare("SELECT image FROM admin_list WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($image);
if ($stmt->fetch() && !empty($image)) {
    $profile_image = 'image/' . $image;
} else {
    $profile_image = 'image/default_profile.jpg';
}
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Admin</title>
<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
<style>
/* Basic styling */
body {
    font-family: Arial, sans-serif;
    background: #f9f9f9;
    margin: 0; padding: 0;
}
.container {
    max-width: 900px;
    margin: 50px auto;
    background: white;
    padding: 20px 30px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
h1 {
    text-align: center;
    margin-bottom: 30px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 12px 15px;
    border: 1px solid #ddd;
    text-align: left;
}
th {
    background-color: #4CAF50;
    color: white;
}
img {
    border-radius: 50%;
}
button {
    background-color: #e74c3c;
    border: none;
    color: white;
    padding: 8px 12px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9rem;
}
button:hover {
    background-color: #c0392b;
}
.edit-btn {
    background-color: #3498db;
}
.edit-btn:hover {
    background-color: #2980b9;
}
.action-buttons button {
    margin-right: 5px;
}

/* Confirm dialog handled by JS */
</style>
<script>
function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this admin?")) {
        window.location.href = "manageadmin.php?delete_id=" + id;
    }
}
</script>
</head>
<body>

<div class="container">
    <h1>Admin Management</h1>
    <table>
        <thead>
            <tr>
                <th>Profile</th>
                <th>Username</th>
                <th>Email</th>
                <th>User Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()) : ?>
            <tr>
                <td><img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" width="50" height="50" alt="Profile"></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['user_type']); ?></td>
                <td class="action-buttons">
                    <button class="edit-btn" onclick="window.location.href='editadmin.php?id=<?php echo $row['id']; ?>'">Edit</button>
                    <button onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
