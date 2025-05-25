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

$sql = "SELECT id, first_name, last_name, date, total_price, status_order FROM orders ORDER BY date DESC";
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
<meta charset="UTF-8" />
<title>Orders List</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet' />
<link rel="stylesheet" href="manageadmin.css" />
<style>
/* Keep your popup and table styles */

table {
    border-collapse: collapse;
    width: 100%;
}
th, td {
    padding: 8px 12px;
    border: 1px solid #ccc;
    text-align: left;
}
th {
    background-color: #f4f4f4;
}

#popup {
    display: none;
    position: fixed;
    top: 10%;
    left: 50%;
    transform: translateX(-50%);
    width: 80%;
    max-height: 80vh;
    overflow-y: auto;
    background-color: white;
    border: 2px solid #333;
    padding: 20px;
    z-index: 1000;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}
#popup-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 999;
}
.close-btn {
    cursor: pointer;
    color: red;
    font-weight: bold;
    float: right;
    font-size: 18px;
}

/* Adjust main content spacing so it doesn't hide behind sidebar */
main {
    margin: 20px;
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
        <li class="active"><a href="orders.php"><i class='bx bxs-doughnut-chart'></i><span class="text">Order</span></a></li>
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
        <a href="orders.php" class="back-link"><i class='bx bx-left-arrow-alt'></i> Orders</a>
        <a href="#" class="notification"><i class='bx bxs-bell'></i></a>
        <a href="profile_admin.php" class="profile"><img src="<?= htmlspecialchars($profile_image) ?>" alt="Profile Picture" /></a>
    </nav>

    <main>
        <h1>Orders List</h1>

        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Date</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($order = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']) ?></td>
                        <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                        <td><?= htmlspecialchars($order['date']) ?></td>
                        <td>RM <?= number_format($order['total_price'], 2) ?></td>
                        <td><?= htmlspecialchars($order['status_order']) ?></td>
                        <td>
                            <button class="view-details-btn" data-order-id="<?= htmlspecialchars($order['id']) ?>">View Details</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No orders found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </main>
</section>

<!-- Popup container -->
<div id="popup-overlay"></div>
<div id="popup">
    <span class="close-btn" id="close-popup">&times;</span>
    <h2>Order Items</h2>
    <div id="order-items-content">Loading...</div>
</div>

<script>
document.querySelectorAll('.view-details-btn').forEach(button => {
    button.addEventListener('click', () => {
        const orderId = button.getAttribute('data-order-id');
        const popup = document.getElementById('popup');
        const overlay = document.getElementById('popup-overlay');
        const content = document.getElementById('order-items-content');

        content.innerHTML = 'Loading...';

        popup.style.display = 'block';
        overlay.style.display = 'block';

        fetch('order_items.php?order_id=' + orderId)
            .then(res => res.text())
            .then(html => {
                content.innerHTML = html;
            })
            .catch(() => {
                content.innerHTML = '<p style="color:red;">Failed to load order items.</p>';
            });
    });
});

document.getElementById('close-popup').addEventListener('click', () => {
    document.getElementById('popup').style.display = 'none';
    document.getElementById('popup-overlay').style.display = 'none';
});
document.getElementById('popup-overlay').addEventListener('click', () => {
    document.getElementById('popup').style.display = 'none';
    document.getElementById('popup-overlay').style.display = 'none';
});
</script>

</body>
</html>

<?php $conn->close(); ?>
