<?php
session_start();

// CONNECT TO DATABASE
$host = 'localhost';
$dbname = 'gaming_store';
$username = 'root';
$password = ''; // Update if your MySQL has a password

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the session variable is set
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
} else {
    header("Location: login_admin.php");
    exit;
}

if ($admin_id) {
    // Fetch the profile image
    $query = "SELECT image FROM admin_list WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($image);
    if ($stmt->fetch() && !empty($image)) {
        $profile_image = 'image/' . $image;
    } else {
        $profile_image = 'image/default_profile.jpg';
    }
    $stmt->close();
} else {
    $profile_image = 'image/default_profile.jpg';
}

// Fetch orders
$sql = "SELECT * FROM orders ORDER BY date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Order Management</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="admindashboard.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
        }

        h1 {
            margin: 30px 0 10px;
            font-size: 32px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #c0392b;
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons a button {
            padding: 6px 12px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
        }

        .action-buttons a:first-child button {
            background-color: #c0392b;
        }

        .action-buttons a:first-child button:hover {
            background-color: #a93226;
        }

        .action-buttons a:last-child button {
            background-color: #e74c3c;
        }

        .action-buttons a:last-child button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<section id="sidebar">
    <a href="#" class="brand">
        <br>
        <span class="text">Admin Dashboard</span>
    </a>
    <ul class="side-menu top">
        <li>
            <a href="admindashboard.php">
                <i class='bx bxs-dashboard'></i>
                <span class="text">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="manageproduct.php">
                <i class='bx bxs-shopping-bag-alt'></i>
                <span class="text">Product Management</span>
            </a>
        </li>
        <li>
            <a href="manage_category.php">
                <i class='bx bxs-category'></i>
                <span class="text">Category Management</span>
            </a>
        </li>
        <li class="active">
            <a href="order_admin.php">
                <i class='bx bxs-doughnut-chart'></i>
                <span class="text">Order</span>
            </a>
        </li>
        <li>
            <a href="cust_list.php">
                <i class='bx bxs-user'></i>
                <span class="text">Customer</span>
            </a>
        </li>
        <li>
            <a href="view_admin.php">
                <i class='bx bxs-group'></i>
                <span class="text">Admin</span>
            </a>
        </li>
    </ul>
    <ul class="side-menu">
        <li>
            <a href="#">
                <i class='bx bxs-cog'></i>
                <span class="text">Settings</span>
            </a>
        </li>
        <li>
            <a href="index.html" class="logout">
                <i class='bx bxs-log-out-circle'></i>
                <span class="text">Logout</span>
            </a>
        </li>
    </ul>
</section>
<!-- SIDEBAR -->

<!-- CONTENT -->
<section id="content">
    <nav>
        <form action="#">
            <div class="form-input">
                <input type="search" placeholder="Search...">
                <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
            </div>
        </form>
        <a href="#" class="notification">
            <i class='bx bxs-bell'></i>
        </a>
        <a href="profile_admin.php" class="profile">
            <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Picture">
        </a>
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

        <section id="order-list">
            <h2>Manage Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id']; ?></td>
                                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                                <td><?= htmlspecialchars($row['phone_number']); ?></td>
                                <td>RM <?= number_format($row['total_price'], 2); ?></td>
                                <td><?= htmlspecialchars($row['status_order']); ?></td>
                                <td><?= $row['date']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" align="center">No orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</section>

</body>
</html>

<?php $conn->close(); ?>
