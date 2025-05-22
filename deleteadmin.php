<?php
session_start();

$servername = "localhost";
$username = "root"; 
$password = "";
$dbname = "gaming_store";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("<script>
        alert('Database connection failed!');
        window.location.href = 'addadmin.php';
    </script>");
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo "<script>
        alert('Access denied. Please log in.');
        window.location.href = 'login.php';
    </script>";
    exit;
}

$current_admin_id = $_SESSION['admin_id'];

// Check if ID is set via GET
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Prevent self-deletion
    if ($id == $current_admin_id) {
        echo "<script>
            alert('Error: You cannot delete your own account.');
            window.location.href = 'addadmin.php';
        </script>";
        exit;
    }

    // Fetch image to delete
    $query = "SELECT image FROM admin_list WHERE id = $id";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image_path = "uploads/" . $row['image'];
        if (!empty($row['image']) && file_exists($image_path)) {
            unlink($image_path); // Delete the image file
        }
    }

    // Delete from database
    $sql = "DELETE FROM admin_list WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo "<script>
            alert('Admin deleted successfully!');
            window.location.href = 'addadmin.php';
        </script>";
        exit;
    } else {
        echo "<script>
            alert('Error deleting admin.');
            window.location.href = 'addadmin.php';
        </script>";
        exit;
    }

} else {
    echo "<script>
        alert('Invalid request.');
        window.location.href = 'addadmin.php';
    </script>";
    exit;
}

$conn->close();
?>
