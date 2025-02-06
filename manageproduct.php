<?php
// Include your database connection file here
include 'database.php';

// Fetch products from the database
$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Gaming Store Admin</title>
    <link rel="stylesheet" href="manageproduct.css">
    <style>
        /* Add some basic styles for the table */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #c0392b; /* Dark Red */
            color: white;
        }

        img {
            width: 50px; /* Set a fixed width for images */
            height: auto; /* Maintain aspect ratio */
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Product Management</h1>
        </header>
        <main>
            <section id="product-list">
                <h2>Manage Products</h2>
                <button class="add-product" onclick="window.location.href='addproduct.php'">Add New Product</button>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price (RM)</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><img src="<?= $row['product_image']; ?>" alt="Product Image"></td>
                            <td><?= $row['product_name']; ?></td>
                            <td><?= $row['category']; ?></td>
                            <td>RM <?= number_format($row['product_price'], 2); ?></td>
                            <td><?= $row['product_quantity']; ?></td>
                            <td>
                                <!-- Edit and Delete buttons -->
                                <a href="editproductquantity.php?id=<?= $row['id']; ?>"><button>Edit Quantity</button></a>
                                <a href="deleteproduct.php?id=<?= $row['id']; ?>"><button>Delete</button></a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>
        </main>
        <footer></footer>
    </div>
</body>
</html>
