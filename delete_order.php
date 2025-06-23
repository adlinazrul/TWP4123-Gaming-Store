<?php
session_start();
include 'db_connection.php'; // Ensure this file correctly connects to your database

header('Content-Type: application/json'); // Respond with JSON

$response = ['success' => false, 'message' => 'An unknown error occurred.', 'error_code' => 'unknown'];

// 1. Check for POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    $response['error_code'] = 'invalid_request';
    echo json_encode($response);
    exit();
}

// 2. Check for logged-in user
if (!isset($_SESSION['email'])) {
    $response['message'] = 'User not logged in.';
    $response['error_code'] = 'unauthorized';
    echo json_encode($response);
    exit();
}

// 3. Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $response['message'] = 'Security token mismatch. Please refresh the page.';
    $response['error_code'] = 'csrf_mismatch';
    echo json_encode($response);
    exit();
}

// 4. Get customer ID from session
$email = $_SESSION['email'];
$customer_query = $conn->prepare("SELECT id FROM customers WHERE email = ?");
$customer_query->bind_param("s", $email);
$customer_query->execute();
$customer_result = $customer_query->get_result();
$customer = $customer_result->fetch_assoc();
$customer_id = $customer['id'];
$customer_query->close();

if (!$customer_id) {
    $response['message'] = 'Customer ID not found.';
    $response['error_code'] = 'customer_not_found';
    echo json_encode($response);
    exit();
}

// 5. Get and validate order ID from POST
if (!isset($_POST['order_id']) || !is_numeric($_POST['order_id'])) {
    $response['message'] = 'Invalid order ID provided.';
    $response['error_code'] = 'invalid_order_id';
    echo json_encode($response);
    exit();
}
$order_id = intval($_POST['order_id']);

// Start transaction to ensure data integrity
$conn->begin_transaction();

try {
    // Verify that the order belongs to the logged-in customer
    $verify_order_stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
    $verify_order_stmt->bind_param("ii", $order_id, $customer_id);
    $verify_order_stmt->execute();
    $verify_order_result = $verify_order_stmt->get_result();

    if ($verify_order_result->num_rows === 0) {
        $response['message'] = 'Order not found or does not belong to you.';
        $response['error_code'] = 'not_found';
        $conn->rollback();
        echo json_encode($response);
        exit();
    }
    $verify_order_stmt->close();

    // Delete related entries from items_ordered table
    $delete_items_stmt = $conn->prepare("DELETE FROM items_ordered WHERE order_id = ?");
    $delete_items_stmt->bind_param("i", $order_id);
    if (!$delete_items_stmt->execute()) {
        throw new Exception("Error deleting items ordered: " . $delete_items_stmt->error);
    }
    $delete_items_stmt->close();

    // Delete the order from the orders table
    $delete_order_stmt = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
    $delete_order_stmt->bind_param("ii", $order_id, $customer_id);
    if (!$delete_order_stmt->execute()) {
        throw new Exception("Error deleting order: " . $delete_order_stmt->error);
    }
    $delete_order_stmt->close();

    // Commit the transaction if all deletions are successful
    $conn->commit();
    $response['success'] = true;
    $response['message'] = 'Order and associated items deleted successfully. Ratings for this order are preserved.';
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    $response['message'] = 'Database error: ' . $e->getMessage();
    $response['error_code'] = 'db_error';
}

$conn->close();
echo json_encode($response);
?>