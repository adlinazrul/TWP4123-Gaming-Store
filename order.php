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

// Fetch orders from the items_ordered table
$sql = "SELECT * FROM items_ordered";
$result = $conn->query($sql);

// Check if the query was successful
if ($result === false) {
    die("Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="manageadmin.css">
    <style>
        /* Your custom CSS styles */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        table img {
            border-radius: 5px;
        }

        table button {
            padding: 5px 10px;
            background-color: #ef4444;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        table button:hover {
            background-color: #dc2626;
        }
    </style>
</head>
<body>

<section id="sidebar">
    <!-- Sidebar content here -->
</section>

<section id="content">
    <nav>
        <i class='bx bx-menu'></i>
        <a href="managecategory.html" class="nav-link">Categories</a>
        <form action="#">
            <div class="form-input">
                <input type="search" placeholder="Search...">
                <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
            </div>
        </form>
        <a href="#" class="notification"><i class='bx bxs-bell'></i></a>
        <a href="#" class="profile"><img src="image/adlina.jpg"></a>
    </nav>

    <main>
        <div class="head-title" style="margin-bottom: 30px;">
            <div class="left">
                <h1>Order Management</h1>
                <ul class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active" href="#">Order Management</a></li>
                </ul>
            </div>
        </div>

        <div class="container">
            <section id="view-orders">
                <h2>Order List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Image</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Loop through the results and display each order
                        while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $row['order_id'] ?></td>
                                <td><?= $row['product_name'] ?></td>
                                <td>RM <?= number_format($row['price_items'], 2) ?></td>
                                <td><?= $row['quantity_items'] ?></td>
                                <td><img src="uploads/<?= $row['image_items'] ?>" width="50"></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>
</section>

</body>
</html>

<?php 
// Close connection
$conn->close();
?>
