<?php
// Include DB connection
include 'database.php';

// Insert logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['category_name'];
    $desc = $_POST['description'];

    $sql = "INSERT INTO product_categories (category_name, description) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name, $desc);

    if ($stmt->execute()) {
        header("Location: managecategory.php");
        exit;
    } else {
        $error = "Failed to add category.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Category</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="admindashboard.css">
    <style>
        .form-container {
            margin: 50px auto;
            max-width: 600px;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            margin-top: 20px;
            background-color: #c0392b;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }

        button:hover {
            background-color: #a93226;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
        <main>
            <center><h1>Add New Category</h1></center>
            <div class="form-container">
                <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
                <form method="POST">
                    <label for="category_name">Category Name:</label>
                    <input type="text" id="category_name" name="category_name" required>

                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4" required></textarea>

                    <button type="submit">Add Category</button>
                </form>
            </div>
        </main>
    </section>
</body>
</html>
