<?php
include 'database.php';

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Delete the product from the database
    $sql = "DELETE FROM products WHERE id = $product_id";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Product deleted successfully!'); window.location.href='manageproduct.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
