<?php
include 'database.php';

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $sql = "SELECT * FROM products WHERE id = $product_id";
    $result = mysqli_query($conn, $sql);
    $product = mysqli_fetch_assoc($result);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_quantity = $_POST['product_quantity'];
    $sql = "UPDATE products SET product_quantity = '$product_quantity' WHERE id = $product_id";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Product quantity updated successfully!'); window.location.href='manageproduct.php';</script>";
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product Quantity</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            padding: 40px;
        }

        .form-container {
            max-width: 500px;
            margin: auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h1, h2 {
            text-align: center;
            color: #333;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #555;
        }

        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #1976D2;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Edit Product Quantity</h1>
        <h2><?= htmlspecialchars($product['product_name']); ?></h2>
        <form method="POST">
            <label for="product_quantity">Quantity:</label>
            <input type="number" name="product_quantity" value="<?= htmlspecialchars($product['product_quantity']); ?>" required>

            <button type="submit">Save Changes</button>
        </form>
        <a href="manageproduct.php" class="back-link">← Back to Product List</a>
    </div>
</body>
</html>
