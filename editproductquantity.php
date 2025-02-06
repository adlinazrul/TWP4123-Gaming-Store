<?php
// Include your database connection file here
include 'database.php';

// Check if product ID is passed via URL
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Fetch product data from the database
    $sql = "SELECT * FROM products WHERE id = $product_id";
    $result = mysqli_query($conn, $sql);
    $product = mysqli_fetch_assoc($result);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get updated quantity from the form
    $product_quantity = $_POST['product_quantity'];

    // Update the product quantity in the database
    $sql = "UPDATE products SET product_quantity = '$product_quantity' WHERE id = $product_id";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Product quantity updated successfully!');</script>";
        header("Location: manageproduct.php"); // Redirect to product management page
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product Quantity</title>
    <link rel="stylesheet" href="editproduct.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Edit Product Quantity</h1>
        </header>
        <main>
            <section id="edit-product">
                <h2>Editing Product: <?= $product['product_name']; ?></h2>
                <form method="POST">
                    <label for="product_quantity">Quantity:</label>
                    <input type="number" name="product_quantity" value="<?= $product['product_quantity']; ?>" required><br>

                    <button type="submit">Save Changes</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
