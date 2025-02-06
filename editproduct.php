<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get product data
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM products WHERE id=$id");
$product = $result->fetch_assoc();

// Handle product update
if (isset($_POST['update_product'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_quantity = $_POST['product_quantity'];
    $product_description = $_POST['product_description'];

    // Check if image is uploaded
    if ($_FILES["product_image"]["name"]) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["product_image"]["name"]);
        move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file);
        $conn->query("UPDATE products SET product_name='$product_name', product_price='$product_price', product_quantity='$product_quantity', product_description='$product_description', product_image='$target_file' WHERE id=$id");
    } else {
        $conn->query("UPDATE products SET product_name='$product_name', product_price='$product_price', product_quantity='$product_quantity', product_description='$product_description' WHERE id=$id");
    }

    echo "<script>alert('Product updated successfully!'); window.location='addproduct.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
</head>
<body>

<div class="container">
    <h3>Edit Product</h3>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="text" name="product_name" value="<?= $product['product_name']; ?>" required>
        <input type="number" name="product_price" value="<?= $product['product_price']; ?>" required>
        <input type="number" name="product_quantity" value="<?= $product['product_quantity']; ?>" required>
        <input type="text" name="product_description" value="<?= $product['product_description']; ?>" required>
        <input type="file" accept="image/png, image/jpeg, image/jpg" name="product_image">
        <input type="submit" name="update_product" value="Update Product">
    </form>
</div>

</body>
</html>
