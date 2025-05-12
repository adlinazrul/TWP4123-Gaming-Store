<?php
include 'database.php';

// Validate ID from query string
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    // Prepare and execute the delete query
    $sql = "DELETE FROM product_categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirect after successful deletion
        header("Location: managecategory.php");
        exit;
    } else {
        echo "Error deleting category.";
    }
} else {
    echo "Invalid category ID.";
}
?>
