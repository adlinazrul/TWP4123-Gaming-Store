<?php
session_start();
include 'db_connection.php'; // Ensure this file correctly connects to your database

if (!isset($_SESSION['email'])) {
    header("Location: custlogin.php");
    exit();
}

// Ensure the request is a POST request and the 'mark_delivered' button was clicked
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_delivered'])) {
    $item_ordered_id = intval($_POST['item_ordered_id']);
    $email = $_SESSION['email'];

    // Get customer ID
    $customer_query = $conn->prepare("SELECT id FROM customers WHERE email = ?");
    $customer_query->bind_param("s", $email);
    $customer_query->execute();
    $customer_result = $customer_query->get_result();
    $customer = $customer_result->fetch_assoc();
    $customer_id = $customer['id'];
    $customer_query->close();

    if (!$customer_id) {
        header("Location: ORDERHISTORY.php?status_error=1&msg=user_not_found");
        exit();
    }

    // Update the status of the specific item in items_ordered to 'Delivered'
    // Critical: Verify that the item belongs to the logged-in user's order
    // and that its current status is 'Paid' before marking as 'Delivered'.
    $update_status_query = $conn->prepare("
        UPDATE items_ordered io
        JOIN orders o ON io.order_id = o.id
        SET io.status_order = 'Delivered'
        WHERE io.id = ?
        AND o.user_id = ?
        AND io.status_order = 'Paid' -- Only allow marking as delivered if it's currently 'Paid'
    ");
    $update_status_query->bind_param("ii", $item_ordered_id, $customer_id);

    if ($update_status_query->execute()) {
        if ($update_status_query->affected_rows > 0) {
            header("Location: ORDERHISTORY.php?status_updated=1");
            exit();
        } else {
            // No rows affected could mean:
            // 1. The item_ordered_id doesn't exist.
            // 2. The item doesn't belong to this user.
            // 3. The item's status is not 'Paid' (e.g., it's already 'Delivered').
            header("Location: ORDERHISTORY.php?status_error=1&msg=not_eligible");
            exit();
        }
    } else {
        // Database error during update
        header("Location: ORDERHISTORY.php?status_error=1&msg=db_update_error");
        exit();
    }
    $update_status_query->close();
} else {
    // If accessed directly or without the correct POST parameters
    header("Location: ORDERHISTORY.php");
    exit();
}
$conn->close();
?>