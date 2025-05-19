<?php
include 'product.php';

// Your existing PHP code to handle form submission
if (isset($_POST['add_product'])) {
    $name = $_POST['product_name'];
    $price = $_POST['product_price'];
    $quantity = $_POST['product_quantity'];
    $description = $_POST['product_description'];
    $category = $_POST['product_category']; // New category field

    // Handle file upload
    $image = $_FILES['product_image']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image);

    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
        // Updated SQL to include product_category
        $sql = "INSERT INTO products (product_name, product_price, product_quantity, product_description, product_image, product_category) 
                VALUES ('$name', '$price', '$quantity', '$description', '$target_file', '$category')";
        
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Product added successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Error uploading image');</script>";
    }
}

// Fetch products from database
$result = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>Add Product</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
   <link rel="stylesheet" href="newproduct.css" />
</head>
<body>

<div class="container">

   <div class="admin-product-form-container">
      <form action="" method="post" enctype="multipart/form-data">
         <h3>Add a new product</h3>
         <input type="text" placeholder="Enter product name" name="product_name" class="box" required />
         <input type="number" placeholder="Enter product price" name="product_price" class="box" required />
         <input type="number" placeholder="Enter product quantity" name="product_quantity" class="box" required />
         <input type="text" placeholder="Enter description" name="product_description" class="box" required />

         <!-- New dropdown for category -->
         <select name="product_category" class="box" required>
            <option value="" disabled selected>Select category</option>
            <option value="Electronics">Electronics</option>
            <option value="Clothing">Clothing</option>
            <option value="Books">Books</option>
            <option value="Home & Garden">Home & Garden</option>
            <option value="Other">Other</option>
         </select>

         <input type="file" accept="image/png, image/jpeg, image/jpg" name="product_image" class="box" required />
         <input type="submit" class="btn" name="add_product" value="Add Product" />
      </form>
   </div>

   <div class="product-display">
      <h2>Product List</h2>
      <table class="product-display-table">
         <thead>
            <tr>
               <th>Product Image</th>
               <th>Product Name</th>
               <th>Category</th>
               <th>Product Price</th>
               <th>Product Quantity</th>
               <th>Product Description</th>
            </tr>
         </thead>
         <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
               <td><img src="<?= htmlspecialchars($row['product_image']); ?>" width="80" /></td>
               <td><?= htmlspecialchars($row['product_name']); ?></td>
               <td><?= htmlspecialchars($row['product_category']); ?></td>
               <td>RM <?= number_format($row['product_price'], 2); ?></td>
               <td><?= (int)$row['product_quantity']; ?></td>
               <td><?= htmlspecialchars($row['product_description']); ?></td>
            </tr>
            <?php } ?>
         </tbody>
      </table>
   </div>

</div>

</body>
</html>
