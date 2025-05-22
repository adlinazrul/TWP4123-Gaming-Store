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
    $id = intval($_GET['id']);

    // Fetch image file to delete
    $query = "SELECT image FROM admin_list WHERE id = $id";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image_path = "uploads/" . $row['image'];
        if (file_exists($image_path)) {
            unlink($image_path); // Delete the image file
        }
    }

    // Delete from database
    $sql = "DELETE FROM admin_list WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
    echo "<script>
        alert('Admin deleted successfully!');
        window.location.href = 'add_admin.php';
    </script>";
    exit;
} else {
    echo "<script>
        alert('Error deleting admin');
        window.location.href = 'add_admin.php';
    </script>";
}

}

$conn->close();
?>
