<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $current_status = $_POST['current_status'];

    $new_status = ($current_status === 'active') ? 'not active' : 'active';

    $conn = new mysqli("localhost", "root", "", "gaming_store");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("UPDATE customers SET account_status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $customer_id);

    if ($stmt->execute()) {
        header("Location: cust_list.php");
        exit;
    } else {
        echo "Error updating status.";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: customer_list.php");
    exit;
}
?>
