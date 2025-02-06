<?php
$servername = "localhost";
$username = "root"; // Change this if necessary
$password = "";
$dbname = "gaming_store";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle product addition
if (isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_quantity = $_POST['product_quantity'];
    $product_description = $_POST['product_description'];

    // Image upload handling
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["product_image"]["name"]);
    move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file);

    // Insert product into database
    $sql = "INSERT INTO products (product_name, product_price, product_quantity, product_description, product_image)
            VALUES ('$product_name', '$product_price', '$product_quantity', '$product_description', '$target_file')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Product added successfully!');</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle product deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM products WHERE id=$id");
    echo "<script>alert('Product deleted successfully!'); window.location='addproduct.php';</script>";
}

// Fetch all products
$result = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <link rel="stylesheet" href="newproduct.css">
</head>
<body>

<div class="container">
    <div class="admin-product-form-container">
        <form action="addproduct.php" method="post" enctype="multipart/form-data">
            <h3>Add a new product</h3>
            <input type="text" placeholder="Enter product name" name="product_name" class="box" required>
            <input type="number" placeholder="Enter product price" name="product_price" class="box" required>
            <input type="number" placeholder="Enter product quantity" name="product_quantity" class="box" required>
            <input type="text" placeholder="Enter description" name="product_description" class="box" required>
            <input type="file" accept="image/png, image/jpeg, image/jpg" name="product_image" class="box" required>
            <input type="submit" class="btn" name="add_product" value="Add Product">
        </form>
    </div>

    <div class="product-display">
        <h2>Product List</h2>
        <table class="product-display-table">
            <thead>
                <tr>
                    <th>Product Image</th>
                    <th>Product Name</th>
                    <th>Product Price</th>
                    <th>Product Quantity</th>
                    <th>Product Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><img src="<?= $row['product_image']; ?>" width="80"></td>
                    <td><?= $row['product_name']; ?></td>
                    <td>RM <?= $row['product_price']; ?></td>
                    <td><?= $row['product_quantity']; ?></td>
                    <td><?= $row['product_description']; ?></td>
                    <td>
                        <a href="editproduct.php?id=<?= $row['id']; ?>" class="btn">Edit</a>
                        <a href="addproduct.php?delete=<?= $row['id']; ?>" class="btn" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>
