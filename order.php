<?php
session_start();

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate and get order_id from URL
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id']) || intval($_GET['order_id']) <= 0) {
    die("Invalid order ID.");
}

$order_id = intval($_GET['order_id']);

// Check if order exists
$order_check_stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
$order_check_stmt->bind_param("i", $order_id);
$order_check_stmt->execute();
$order_check_result = $order_check_stmt->get_result();
if ($order_check_result->num_rows === 0) {
    die("Order not found.");
}
$order_check_stmt->close();

// Fetch items in the order
$stmt = $conn->prepare("SELECT * FROM items_ordered WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

if (count($items) === 0) {
    die("No items found for this order.");
}

// Fetch admin profile image
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
<meta charset="UTF-8" />
<title>Order Details</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="manageadmin.css" />
<style>
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    table th, table td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }
    table img {
        border-radius: 5px;
        width: 60px;
        height: 60px;
        object-fit: cover;
    }
    .details-container {
        padding: 20px;
    }
    .total-table {
        max-width: 300px;
        margin-left: auto;
        margin-right: 0;
        border: 1px solid #ddd;
    }
    .total-table th, .total-table td {
        padding: 10px;
        text-align: right;
        border: none;
        font-weight: bold;
    }
    select.status-select {
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
        background-color: #f9f9f9;
        font-weight: 600;
        color: #333;
        cursor: pointer;
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }
    select.status-select:hover {
        background-color: #e0e0e0;
        border-color: #888;
    }
    button.update-btn {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    button.update-btn:hover {
        background-color: #45a049;
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
                <h1>Order Details</h1>
                <ul class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active" href="#">Order Details</a></li>
                </ul>
            </div>
        </div>

        <div class="details-container">
            <form action="update_status.php" method="POST">
                <input type="hidden" name="order_id" value="<?= $order_id ?>">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Price (RM)</th>
                            <th>Quantity</th>
                            <th>Total (RM)</th>
                            <th>Status</th>
                            <th>Customer Name</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_price = 0;
                        foreach ($items as $index => $row) {
                            $item_total = $row['price_items'] * $row['quantity_items'];
                            $total_price += $item_total;
                            ?>
                        <tr>
                            <td><img src="/TWP4123-Gaming-Store/<?= htmlspecialchars($row['image_items']) ?>" alt="Product Image" /></td>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= number_format($row['price_items'], 2) ?></td>
                            <td><?= htmlspecialchars($row['quantity_items']) ?></td>
                            <td><?= number_format($item_total, 2) ?></td>
                            <td>
                                <select name="status_order[<?= $index ?>]" class="status-select" required>
                                    <?php
                                    $statuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];
                                    foreach ($statuses as $status) {
                                        $selected = ($status === $row['status_order']) ? 'selected' : '';
                                        echo "<option value=\"$status\" $selected>$status</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><?= htmlspecialchars($row['name_cust']) ?></td>
                            <td><?= htmlspecialchars($row['num_tel_cust']) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['address_cust'])) ?></td>
                            <td><?= htmlspecialchars($row['date']) ?></td>
                        </tr>
                        <input type="hidden" name="item_id[<?= $index ?>]" value="<?= $row['id'] ?>">
                        <?php } ?>
                    </tbody>
                </table>

                <table class="total-table">
                    <tr>
                        <th>Total Price:</th>
                        <td>RM <?= number_format($total_price, 2) ?></td>
                    </tr>
                </table>

                <div style="text-align: right; margin-top: 10px;">
                    <button type="submit" class="update-btn">Update Status</button>
                </div>
            </form>
        </div>
    </main>
</section>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
