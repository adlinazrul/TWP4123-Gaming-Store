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

// 5. Get and validate order ID and action from POST
if (!isset($_POST['order_id']) || !is_numeric($_POST['order_id'])) {
    $response['message'] = 'Invalid order ID provided.';
    $response['error_code'] = 'invalid_order_id';
    echo json_encode($response);
    exit();
}
$order_id = intval($_POST['order_id']);

$action = isset($_POST['action']) ? $_POST['action'] : '';
$set_hidden = null;
if ($action === 'hide') {
    $set_hidden = TRUE;
} elseif ($action === 'unhide') {
    $set_hidden = FALSE;
} else {
    $response['message'] = 'Invalid action specified.';
    $response['error_code'] = 'invalid_action';
    echo json_encode($response);
    exit();
}

// Update the is_hidden status
$update_stmt = $conn->prepare("UPDATE orders SET is_hidden = ? WHERE id = ? AND user_id = ?");
if (!$update_stmt) {
    $response['message'] = 'Database prepare error: ' . $conn->error;
    $response['error_code'] = 'db_prepare_error';
    echo json_encode($response);
    exit();
}
$update_stmt->bind_param("iii", $set_hidden, $order_id, $customer_id);

if ($update_stmt->execute()) {
    if ($update_stmt->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = 'Order visibility updated successfully.';
        $response['new_status'] = $set_hidden ? 'hidden' : 'visible';
    } else {
        // This could mean the order ID or user ID was incorrect, or it was already in that state
        $response['message'] = 'Order not found or no change in status needed.';
        $response['error_code'] = 'not_found_or_no_change';
    }
} else {
    $response['message'] = 'Error updating order visibility: ' . $update_stmt->error;
    $response['error_code'] = 'db_error';
}

$update_stmt->close();
$conn->close();
echo json_encode($response);
?>