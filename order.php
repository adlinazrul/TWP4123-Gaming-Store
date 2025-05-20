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

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Order not found.";
    exit;
}

$order = $result->fetch_assoc();

// Admin profile image
$admin_id = $_SESSION['admin_id'];
$query = "SELECT image FROM admin_list WHERE id = ?";
$img_stmt = $conn->prepare($query);
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
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    .details-container {
        padding: 20px;
    }
    .total-table {
        max-width: 400px;
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
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <table>
                    <tr><th>Customer Name</th><td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td></tr>
                    <tr><th>Email</th><td><?= htmlspecialchars($order['email']) ?></td></tr>
                    <tr><th>Phone</th><td><?= htmlspecialchars($order['phone_number']) ?></td></tr>
                    <tr><th>Address</th><td><?= htmlspecialchars($order['street_address'] . ', ' . $order['city'] . ', ' . $order['state'] . ', ' . $order['postcode'] . ', ' . $order['country']) ?></td></tr>
                    <tr><th>Card Number</th><td><?= htmlspecialchars($order['card_number']) ?></td></tr>
                    <tr><th>Cardholder Name</th><td><?= htmlspecialchars($order['cardholder_name']) ?></td></tr>
                    <tr><th>Expiry Date</th><td><?= htmlspecialchars($order['expiry_date']) ?></td></tr>
                    <tr><th>CVV</th><td><?= htmlspecialchars($order['cvv']) ?></td></tr>
                    <tr><th>Date</th><td><?= htmlspecialchars($order['date']) ?></td></tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <select name="status_order" class="status-select" required>
                                <?php
                                $statuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];
                                foreach ($statuses as $status) {
                                    $selected = ($status == $order['status_order']) ? 'selected' : '';
                                    echo "<option value=\"$status\" $selected>$status</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <table class="total-table">
                    <tr><th>Total Price:</th><td>RM <?= number_format($order['total_price'], 2) ?></td></tr>
                    <tr><th>Tax Fee:</th><td>RM <?= number_format($order['tax_fee'], 2) ?></td></tr>
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
