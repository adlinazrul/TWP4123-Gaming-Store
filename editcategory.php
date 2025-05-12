<?php
// Start session
session_start();
include 'db_connection.php';

$success_message = $error_message = "";
$category_name = "";
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch existing category data
if ($category_id > 0) {
    $query = "SELECT * FROM product_categories WHERE id = ?";
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
        $update_query = "UPDATE product_categories SET category_name = ? WHERE id = ?";
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            width: 50%;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #2c3e50;
            text-align: center;
        }

        .success, .error {
            padding: 10px;
            margin: 20px 0;
            border-radius: 5px;
            font-weight: bold;
        }

        .success {
            background-color: #2ecc71;
            color: white;
        }

        .error {
            background-color: #e74c3c;
            color: white;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 14px;
            margin-bottom: 8px;
        }

        input[type="text"] {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        button {
            padding: 10px 15px;
            background-color: #3498db;
            border: none;
            color: white;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }

        .back-button {
            padding: 10px 15px;
            background-color: #e67e22;
            border: none;
            color: white;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .back-button:hover {
            background-color: #d35400;
        }
    </style>
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

    <a href="manage_category.php" class="back-button">Back to Manage Categories</a>
</div>

</body>
</html>
