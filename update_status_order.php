<?php
// Connect to DB

$order_id = $_POST['order_id'];
$status_order = $_POST['status_order'];

// Sanitize inputs before query in production!

$query = "UPDATE orders SET status_order = '$status_order' WHERE id = $order_id";
$result = mysqli_query($conn, $query);

if ($result) {
    header("Location: order_details.php?order_id=$order_id&msg=success");
} else {
    echo "Error updating status.";
}
?>
