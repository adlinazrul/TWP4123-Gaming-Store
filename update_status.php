<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $status_ordered = $_POST['status_ordered'] ?? null;

    if ($id && $status_ordered) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "gaming_store";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $allowed_status = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status_ordered, $allowed_status)) {
            die("Invalid status");
        }

        $stmt = $conn->prepare("UPDATE items_ordered SET status_ordered = ? WHERE id = ?");
        $stmt->bind_param("si", $status_ordered, $id);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        header("Location: order.php");
        exit;
    } else {
        die("Missing parameters");
    }
} else {
    die("Invalid request");
}
?>
