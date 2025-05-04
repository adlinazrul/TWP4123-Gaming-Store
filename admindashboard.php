<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: loginadmin.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root"; // Change if needed
$password = "";     // Change if needed
$dbname = "gaming_store"; // Your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch admin details
$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];

// You can add additional queries here to display relevant admin data on the dashboard
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Gaming Store</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #C70039;
            color: white;
            padding: 15px;
            text-align: center;
        }

        .dashboard-container {
            padding: 20px;
        }

        .admin-info {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .admin-info h2 {
            margin: 0 0 10px 0;
        }

        .admin-info p {
            margin: 5px 0;
        }

        .action-btns {
            display: flex;
            justify-content: space-around;
        }

        .action-btns a {
            background-color: #C70039;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            text-align: center;
        }

        .action-btns a:hover {
            background-color: #900C3F;
        }

        footer {
            text-align: center;
            padding: 10px;
            background-color: #C70039;
            color: white;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
    </style>
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
</header>

<div class="dashboard-container">
    <div class="admin-info">
        <h2>Welcome, <?php echo htmlspecialchars($admin_username); ?></h2>
        <p><strong>Admin ID:</strong> <?php echo $admin_id; ?></p>
        <p><strong>Username:</strong> <?php echo $admin_username; ?></p>
    </div>

    <div class="action-btns">
        <a href="manage_products.php">Manage Products</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<footer>
    <p>&copy; 2025 Gaming Store. All Rights Reserved.</p>
</footer>

</body>
</html>

<?php
$conn->close();
?>
try