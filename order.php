<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM orders";  // Assuming the orders table is called 'orders'
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="manageorder.css">
    <style>
        .container {
            display: flex;
            flex-direction: column;
            gap: 40px;
            padding: 20px;
        }

        #view-orders {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
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
    <a href="#" class="brand"><br><span class="text">Admin Dashboard</span></a>
    <ul class="side-menu top">
        <li><a href="admindashboard.html"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
        <li><a href="manageproduct.php"><i class='bx bxs-shopping-bag-alt'></i><span class="text">Product Management</span></a></li>
        <li class="active"><a href="#"><i class='bx bxs-doughnut-chart'></i><span class="text">Order</span></a></li>
        <li><a href="customer_list.php"><i class='bx bxs-user'></i><span class="text">Customer</span></a></li>
        <li><a href="admin_list.php"><i class='bx bxs-group'></i><span class="text">Admin</span></a></li>
    </ul>
    <ul class="side-menu">
        <li><a href="#"><i class='bx bxs-cog'></i><span class="text">Settings</span></a></li>
        <li><a href="index.html" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
    </ul>
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
                <h1>Order Management System</h1>
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
                            <th>Customer ID</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $row['order_id'] ?></td>
                                <td><?= $row['customer_id'] ?></td>
                                <td><?= $row['product_name'] ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td>RM <?= number_format($row['total_price'], 2) ?></td>
                                <td><?= $row['status'] ?></td>
                                <td>
                                    <button onclick="viewOrder(<?= $row['order_id'] ?>)">View</button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>
</section>

<script>
function viewOrder(order_id) {
    window.location.href = "view_order_details.php?order_id=" + order_id;
}
</script>

</body>
</html>

<?php $conn->close(); ?>
