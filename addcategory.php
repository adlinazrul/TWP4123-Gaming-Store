<?php
// Include DB connection
include 'database.php';

// Insert logic
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['category_name']);
    $desc = trim($_POST['description']);

    if (!empty($name) && !empty($desc)) {
        $sql = "INSERT INTO product_categories (category_name, description) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $name, $desc);

        if ($stmt->execute()) {
            header("Location: manage_category.php");
            exit;
        } else {
            $error = "Failed to add category.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Category</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="admindashboard.css"> <!-- Make sure this CSS file exists -->

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .form-container {
            margin: 50px auto;
            max-width: 600px;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        h1 {
            text-align: center;
            color: #c0392b;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        button {
            margin-top: 20px;
            background-color: #c0392b;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }

        button:hover {
            background-color: #a93226;
        }

        .error {
            color: red;
            margin-top: 10px;
            text-align: center;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            background-color: #e74c3c;
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
        }

        .back-btn:hover {
            background-color: #e74c3c;
        }
    </style>
</head>
<body>

    <main>
        <br>
        <h1>Add New Category</h1>
        <div class="form-container">
            <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

            <form method="POST" action="">
                <label for="category_name">Category Name:</label>
                <input type="text" id="category_name" name="category_name" required>

                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required></textarea>

                <button type="submit">Add Category</button>
            </form>

            <a href="managecategory.php" class="back-btn">← Back to Manage Category</a>
        </div>
    </main>

</body>
</html>
