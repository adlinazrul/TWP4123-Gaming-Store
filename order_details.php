<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    die("Invalid order ID.");
}

$order_id = intval($_GET['order_id']);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch order info
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    die("Order not found.");
}

$order = $order_result->fetch_assoc();
$stmt->close();

// Get admin profile image
$admin_id = $_SESSION['admin_id'];
$img_stmt = $conn->prepare("SELECT image FROM admin_list WHERE id = ?");
$img_stmt->bind_param("i", $admin_id);
$img_stmt->execute();
$img_stmt->bind_result($image);
$profile_image = ($img_stmt->fetch() && !empty($image)) ? 'image/' . $image : 'image/default_profile.jpg';
$img_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Order Details - Order #<?= htmlspecialchars($order['id']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="manageadmin.css" />
    <style>
        .details-container {
            max-width: 800px;
            margin: 30px auto;
            border: 1px solid #ddd;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
        }
        .details-container h2 {
            margin-bottom: 20px;
        }
        .details-container table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-container th, .details-container td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 16px;
            background-color: rgb(219, 52, 52);
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }
        .back-link:hover {
            background-color: rgb(185, 51, 41);
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
        <a href="order.php" class="back-link"><i class='bx bx-left-arrow-alt'></i> Back to Orders</a>
        <a href="#" class="notification"><i class='bx bxs-bell'></i></a>
        <a href="profile_admin.php" class="profile"><img src="<?= htmlspecialchars($profile_image) ?>" alt="Profile Picture" /></a>
    </nav>

    <main>
        <div class="details-container">
            <h2>Order Details - Order #<?= htmlspecialchars($order['id']) ?></h2>
            <table>
                <tr>
                    <th>Customer Name</th>
                    <td><?= htmlspecialchars($order['name_cust']) ?></td>
                </tr>
                <tr>
                    <th>Phone Number</th>
                    <td><?= htmlspecialchars($order['num_tel_cust']) ?></td>
                </tr>
                <tr>
                    <th>Order Date</th>
                    <td><?= htmlspecialchars($order['date']) ?></td>
                </tr>
                <tr>
                    <th>Shipping Address</th>
                    <td><?= htmlspecialchars($order['address']) ?></td>
                </tr>
                <tr>
                    <th>Total Amount</th>
                    <td>RM <?= number_format($order['total_amount'], 2) ?></td>
                </tr>
                <tr>
                    <th>Order Status</th>
                    <td><?= htmlspecialchars($order['status']) ?></td>
                </tr>
            </table>

            <!-- You can add order items details here if available -->

        </div>
    </main>
</section>
</body>
</html>
