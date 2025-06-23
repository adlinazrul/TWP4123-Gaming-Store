<?php
include 'product.php'; // Make sure this connects $conn to your database

// Fetch categories from product_categories table
$category_query = "SELECT * FROM product_categories";
$category_result = $conn->query($category_query);

// Handle form submission
if (isset($_POST['add_product'])) {
    $name = $_POST['product_name'];
    $price = $_POST['product_price'];
    $quantity = $_POST['product_quantity'];
    $description = $_POST['product_description'];
    $category = $_POST['product_category'];

    // Handle file upload
    $image = $_FILES['product_image']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image);

    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
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

// Fetch existing products
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
   <script>
      function validateQuantity(event) {
         const charCode = event.which ? event.which : event.keyCode;
         return (charCode >= 48 && charCode <= 57);
      }
      
      function validatePrice(event) {
         const charCode = event.which ? event.which : event.keyCode;
         return (charCode >= 48 && charCode <= 57) || charCode === 46;
      }
   </script>
</head>
<body>

<div class="container">

   <div class="admin-product-form-container">
      <form action="" method="post" enctype="multipart/form-data">
         <h3>Add a new product</h3>
         <input type="text" placeholder="Enter product name" name="product_name" class="box" required />
         <input type="number" placeholder="Enter product price" name="product_price" class="box" required 
                min="0.01" step="0.01" onkeypress="return validatePrice(event)" />
         <input type="number" placeholder="Enter product quantity" name="product_quantity" class="box" required 
                min="1" step="1" onkeypress="return validateQuantity(event)" />
         <input type="text" placeholder="Enter description" name="product_description" class="box" required />

         <!-- Dynamic category dropdown -->
         <select name="product_category" class="box" required>
            <option value="" disabled selected>Select category</option>
            <?php
            if ($category_result->num_rows > 0) {
                while ($cat = $category_result->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($cat['category_name']) . '">' . htmlspecialchars($cat['category_name']) . '</option>';
                }
            } else {
                echo '<option value="">No categories found</option>';
            }
            ?>
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