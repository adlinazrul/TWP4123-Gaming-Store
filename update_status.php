<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $statuses = $_POST['status_order'] ?? [];
    $item_ids = $_POST['item_id'] ?? [];

    $conn = new mysqli("localhost", "root", "", "gaming_store");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("UPDATE items_ordered SET status_order = ? WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    foreach ($item_ids as $index => $item_id) {
        $status = $statuses[$index] ?? '';
        $stmt->bind_param("si", $status, $item_id);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the same order details page
    header("Location: order.php?order_id=" . $order_id);
    exit;
}
?>
