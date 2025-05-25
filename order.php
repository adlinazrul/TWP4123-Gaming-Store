<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all orders
$sql = "SELECT * FROM orders ORDER BY id DESC";
$result = $conn->query($sql);

// Get admin profile image
$admin_id = $_SESSION['admin_id'];
$img_stmt = $conn->prepare("SELECT image FROM admin_list WHERE id = ?");
$img_stmt->bind_param("i", $admin_id);
$img_stmt->execute();
$img_stmt->bind_result($image);
$profile_image = ($img_stmt->fetch() && !empty($image)) ? 'image/' . $image : 'image/default_profile.jpg';
$img_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order List</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="manageadmin.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        a.details-link {
            background-color:rgb(219, 52, 52);
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
        }
        a.details-link:hover {
            background-color:rgb(185, 51, 41);
        }
    </style>
</head>
<body>
<section id="sidebar">
    <a href="#" class="brand"><br><span class="text">Admin Dashboard</span></a>
    <ul class="side-menu top">
        <li><a href="admindashboard.php"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
        <li><a href="manageproduct.php"><i class='bx bxs-shopping-bag-alt'></i><span class="text">Product Management</span></a></li>
        <li><a href="manage_category.php"><i class='bx bxs-category'></i><span class="text">Category Management</span></a></li>
        <li class="active"><a href="order.php"><i class='bx bxs-doughnut-chart'></i><span class="text">Order</span></a></li>
        <li><a href="customer_list.php"><i class='bx bxs-user'></i><span class="text">Customer</span></a></li>
        <li><a href="addadmin.php"><i class='bx bxs-group'></i><span class="text">Admin</span></a></li>
    </ul>
    <ul class="side-menu">
        <li><a href="#"><i class='bx bxs-cog'></i><span class="text">Settings</span></a></li>
        <li><a href="index.html" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
    </ul>
</section>

<section id="content">
    <nav>
        <form action="#">
            <div class="form-input">
                <input type="search" placeholder="Search..." />
                <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
            </div>
        </form>
        <a href="#" class="notification"><i class='bx bxs-bell'></i></a>
        <a href="profile_admin.php" class="profile"><img src="<?= htmlspecialchars($profile_image) ?>" alt="Profile Picture" /></a>
    </nav>

    <main>
        <div class="head-title" style="margin-bottom: 30px;">
            <div class="left">
                <h1>Order List</h1>
                <ul class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active" href="#">Order</a></li>
                </ul>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Date</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['name_cust']) ?></td>
                            <td><?= htmlspecialchars($row['num_tel_cust']) ?></td>
                            <td><?= htmlspecialchars($row['date']) ?></td>
                            <td><a class="details-link" href="order_details.php?order_id=<?= $row['id'] ?>">Details</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</section>
</body>
</html>
