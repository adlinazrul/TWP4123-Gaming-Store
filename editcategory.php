<?php
// Start session
session_start();
include 'db_connection.php';

// Check if user is logged in


$success_message = $error_message = "";
$category_name = "";
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch existing category data
if ($category_id > 0) {
    $query = "SELECT * FROM categories WHERE category_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $category_name = $row['category_name'];
    } else {
        $error_message = "Category not found.";
    }
} else {
    $error_message = "Invalid category ID.";
}

// Update category
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = trim($_POST["category_name"]);
    
    if (!empty($new_name)) {
        $update_query = "UPDATE categories SET category_name = ? WHERE category_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $new_name, $category_id);

        if ($stmt->execute()) {
            $success_message = "Category updated successfully.";
            $category_name = $new_name;
        } else {
            $error_message = "Error updating category.";
        }
    } else {
        $error_message = "Please enter a category name.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Category</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<div class="container">
    <h2>Edit Category</h2>

    <?php if (!empty($success_message)): ?>
        <div class="success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="category_name">Category Name:</label>
        <input type="text" name="category_name" id="category_name" value="<?php echo htmlspecialchars($category_name); ?>" required>

        <button type="submit">Update Category</button>
    </form>
</div>

</body>
</html>
