<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM customer_list";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="manageadmin.css">
    <style>
        .container {
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        table th, table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f3f4f6;
        }
    </style>
</head>
<body>

<section id="sidebar">
    <a href="#" class="brand"><br><span class="text">Admin Dashboard</span></a>
    <ul class="side-menu top">
        <li><a href="admindashboard.html"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
        <li><a href="manageproduct.php"><i class='bx bxs-shopping-bag-alt'></i><span class="text">Product Management</span></a></li>
        <li><a href="order.html"><i class='bx bxs-doughnut-chart'></i><span class="text">Order</span></a></li>
        <li class="active"><a href="customer_list.php"><i class='bx bxs-user'></i><span class="text">Customer</span></a></li>
        <li><a href="addadmin.php"><i class='bx bxs-group'></i><span class="text">Admin</span></a></li>
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
                <h1>Customer List</h1>
                <ul class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active" href="#">Customer</a></li>
                </ul>
            </div>
        </div>

        <div class="container">
            <h2>Registered Customers</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= $row['name'] ?></td>
                                <td><?= $row['email'] ?></td>
                                <td><?= $row['phone'] ?></td>
                                <td><?= $row['address'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No customers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</section>

</body>
</html>

<?php $conn->close(); ?>
