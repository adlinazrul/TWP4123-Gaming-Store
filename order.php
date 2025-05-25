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

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id']) || intval($_GET['order_id']) <= 0) {
    die("Invalid order ID.");
}

$order_id = intval($_GET['order_id']);

$order_check_stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$order_check_stmt->bind_param("i", $order_id);
$order_check_stmt->execute();
$order_result = $order_check_stmt->get_result();
if ($order_result->num_rows === 0) {
    die("Order not found.");
}
$order_data = $order_result->fetch_assoc();
$order_check_stmt->close();

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
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th, td { padding: 12px; text-align: center; border-bottom: 1px solid #ddd; }
    img { border-radius: 5px; width: 60px; height: 60px; object-fit: cover; }
    .details-container { padding: 20px; }
    select.status-select { padding: 6px 10px; border-radius: 6px; border: 1px solid #ccc; }
    button.update-btn { background-color: #4CAF50; color: white; border: none; padding: 12px 25px; border-radius: 6px; font-size: 16px; cursor: pointer; }
    .order-row { cursor: pointer; }
</style>
<script>
function toggleDetails(index) {
    var detailsRow = document.getElementById('details-' + index);
    detailsRow.style.display = detailsRow.style.display === 'table-row' ? 'none' : 'table-row';
}
</script>
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
                            <th>Quantity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $index => $row): ?>
                        <tr class="order-row" onclick="toggleDetails(<?= $index ?>)">
                            <td><img src="/TWP4123-Gaming-Store/<?= htmlspecialchars($row['image_items']) ?>" alt="Product Image" /></td>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= htmlspecialchars($row['quantity_items']) ?></td>
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
                        </tr>
                        <tr id="details-<?= $index ?>" style="display:none;">
                            <td colspan="4">
                                <strong>Customer:</strong> <?= htmlspecialchars($row['name_cust']) ?><br>
                                <strong>Phone:</strong> <?= htmlspecialchars($row['num_tel_cust']) ?><br>
                                <strong>Address:</strong> <?= htmlspecialchars($row['address_cust']) ?><br>
                                <strong>Date:</strong> <?= htmlspecialchars($row['date']) ?><br>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" class="update-btn">Update Status</button>
            </form>
        </div>
    </main>
</section>
</body>
</html>
