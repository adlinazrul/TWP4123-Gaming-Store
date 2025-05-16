<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

// DB connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch admin profile image
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

// Fetch total unique orders count from items_ordered table
$orderCountQuery = "SELECT COUNT(DISTINCT order_id) AS total_orders FROM items_ordered";
$result = $conn->query($orderCountQuery);
$totalOrders = 0;
if ($result && $row = $result->fetch_assoc()) {
    $totalOrders = (int)$row['total_orders'];
}

// Fetch recent 5 orders grouped by order_id, showing order_id, total price, date, status, customer name
$recentOrders = [];
$sql = "
    SELECT 
        order_id,
        name_cust,
        date,
        status_order,
        SUM(price_items * quantity_items) AS total_price,
        COUNT(*) AS items_count
    FROM items_ordered
    GROUP BY order_id, name_cust, date, status_order
    ORDER BY date DESC
    LIMIT 5
";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentOrders[] = $row;
    }
}

// Fetch recent 5 distinct customers (name_cust) from items_ordered ordered by latest date
$recentCustomers = [];
$sqlCust = "
    SELECT DISTINCT name_cust 
    FROM items_ordered
    WHERE name_cust IS NOT NULL AND name_cust != ''
    ORDER BY date DESC
    LIMIT 5
";
$resultCust = $conn->query($sqlCust);
if ($resultCust) {
    while ($row = $resultCust->fetch_assoc()) {
        $recentCustomers[] = $row['name_cust'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="admindashboard.css" />
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --light: #f9f9f9;
            --blue: #3c91e6;
            --light-blue: #cfe8ff;
            --grey: #eee;
            --dark-grey: #aaaaaa;
            --dark: #342e37;
            --red: #db504a;
            --yellow: #ffce26;
            --light-yellow: #fff2c6;
            --orange: #fd7238;
            --light-orange: #ffe0d3;
            --green: #28a745;
            --light-green: #d1f5d9;
            --teal: #17a2b8;
            --light-teal: #d1f0f5;
        }

        /* CHART STYLES */
        .chart-container {
            width: 100%;
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        canvas {
            width: 100% !important;
            height: auto !important;
        }

        /* MAIN CONTENT STYLES */
        main {
            padding: 20px;
        }
        
        /* BOX INFO STYLES */
        .box-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-top: 36px;
        }
        
        .box-info li {
            padding: 24px;
            background: var(--light);
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .box-info li:hover {
            transform: translateY(-5px);
        }
        
        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 123, 255, 0.1);
            color: var(--blue);
            font-size: 24px;
        }
        
        .box-info .text h3 {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }
        
        .box-info .text p {
            color: var(--dark-grey);
            font-size: 14px;
        }
        
        /* DATA CONTAINER STYLES */
        .data-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 24px;
        }
        
        .data-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .card-header h3 {
            font-size: 18px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-actions .view-all {
            background: rgba(0, 123, 255, 0.1);
            color: var(--blue);
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .card-actions .view-all:hover {
            background: var(--blue);
            color: white;
        }
        
        /* ORDER ITEM STYLES */
        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            gap: 15px;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-id {
            font-weight: 600;
            color: var(--blue);
            background: rgba(0, 123, 255, 0.1);
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 13px;
        }
        
        .order-details {
            flex: 1;
        }
        
        .customer-name, .order-date {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
        }
        
        .customer-name {
            font-weight: 500;
            color: var(--dark);
        }
        
        .order-date {
            color: var(--grey);
            margin-top: 3px;
        }
        
        .order-total {
            font-weight: 600;
            color: var(--dark);
        }
        
        .order-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .order-status.completed { 
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--green);
        }
        
        .order-status.pending { 
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--yellow);
        }
        
        .order-status.process { 
            background-color: rgba(23, 162, 184, 0.1);
            color: var(--teal);
        }
        
        /* CUSTOMER LIST STYLES */
        .customer-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .customer-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            gap: 15px;
        }
        
        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--blue);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .customer-info {
            flex: 1;
        }
        
        .customer-name {
            font-weight: 500;
            color: var(--dark);
            font-size: 14px;
        }
        
        .customer-activity {
            font-size: 12px;
            color: var(--grey);
            margin-top: 2px;
        }
        
        .customer-action {
            background: none;
            border: none;
            color: var(--grey);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .customer-action:hover {
            color: var(--blue);
            transform: scale(1.1);
        }
        
        /* NO DATA STYLES */
        .no-data {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
            color: var(--grey);
        }
        
        .no-data i {
            font-size: 50px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .no-data p {
            margin: 0;
            font-size: 14px;
        }
        
        /* RESPONSIVE STYLES */
        @media (max-width: 992px) {
            .data-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <br />
            <span class="text">Admin Dashboard</span>
        </a>
        <ul class="side-menu top">
            <li class="active">
                <a href="#">
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
            <li>
                <a href="order.php">
                    <i class='bx bxs-doughnut-chart'></i>
                    <span class="text">Order</span>
                </a>
            </li>
            <li>
                <a href="customer_list.php">
                    <i class='bx bxs-user'></i>
                    <span class="text">Customer</span>
                </a>
            </li>
            <li>
                <a href="addadmin.php">
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

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <a href="managecategory.html" class="nav-link">Categories</a>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search..." />
                    <button type="submit" class="search-btn">
                        <i class='bx bx-search'></i>
                    </button>
                </div>
            </form>
            <a href="#" class="notification">
                <i class='bx bxs-bell'></i>
                <span class="num"></span>
            </a>
            <a href="profile_admin.php" class="profile">
                <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Picture" />
            </a>
        </nav>

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Dashboard</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Home</a></li>
                    </ul>
                </div>
                <a href="#" class="btn-download">
                    <i class='bx bxs-cloud-download'></i>
                    <span class="text">Download PDF</span>
                </a>
            </div>

            <ul class="box-info">
                <li>
                    <div class="icon-circle">
                        <i class='bx bxs-calendar-check'></i>
                    </div>
                    <span class="text">
                        <h3><?php echo $totalOrders; ?></h3>
                        <p>New Orders</p>
                    </span>
                </li>
                <li>
                    <div class="icon-circle">
                        <i class='bx bxs-group'></i>
                    </div>
                    <span class="text">
                        <h3><?php echo count($recentCustomers); ?></h3>
                        <p>Recent Customers</p>
                    </span>
                </li>
                <li>
                    <div class="icon-circle">
                        <i class='bx bxs-dollar-circle'></i>
                    </div>
                    <span class="text">
                        <h3>RM 
                            <?php 
                                $conn2 = new mysqli($servername, $username, $password, $dbname);
                                $salesResult = $conn2->query("SELECT SUM(price_items * quantity_items) AS total_sales FROM items_ordered");
                                $totalSales = 0;
                                if ($salesResult && $salesRow = $salesResult->fetch_assoc()) {
                                    $totalSales = $salesRow['total_sales'] ?? 0;
                                }
                                $conn2->close();
                                echo number_format($totalSales, 2);
                            ?>
                        </h3>
                        <p>Total Sales</p>
                    </span>
                </li>
            </ul>

            <!-- CHART -->
            <div class="chart-container">
                <canvas id="dashboardChart"></canvas>
            </div>

            <script>
                const ctx = document.getElementById('dashboardChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Total Orders'],
                        datasets: [{
                            label: 'Number of Orders',
                            data: [<?php echo $totalOrders; ?>],
                            backgroundColor: ['#3c91e6'],
                            borderRadius: 15,
                            hoverBackgroundColor: ['#2a7bc8']
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1 }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            </script>

            <!-- DATA CONTAINERS -->
            <div class="data-container">
                <!-- RECENT ORDERS CARD -->
                <div class="data-card orders-card">
                    <div class="card-header">
                        <h3><i class='bx bxs-receipt'></i> Recent Orders</h3>
                        <div class="card-actions">
                            <button class="view-all">View All</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (count($recentOrders) > 0): ?>
                            <?php foreach ($recentOrders as $order): ?>
                                <div class="order-item">
                                    <div class="order-id">#<?php echo htmlspecialchars($order['order_id']); ?></div>
                                    <div class="order-details">
                                        <div class="customer-name">
                                            <i class='bx bxs-user'></i> <?php echo htmlspecialchars($order['name_cust']); ?>
                                        </div>
                                        <div class="order-date">
                                            <i class='bx bxs-calendar'></i> <?php echo htmlspecialchars($order['date']); ?>
                                        </div>
                                    </div>
                                    <div class="order-total">
                                        RM <?php echo number_format($order['total_price'], 2); ?>
                                    </div>
                                    <?php
                                        $statusClass = '';
                                        switch (strtolower($order['status_order'])) {
                                            case 'completed': $statusClass = 'completed'; break;
                                            case 'pending': $statusClass = 'pending'; break;
                                            case 'process': $statusClass = 'process'; break;
                                            default: $statusClass = 'pending';
                                        }
                                    ?>
                                    <div class="order-status <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($order['status_order']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-data">
                                <i class='bx bxs-inbox'></i>
                                <p>No orders found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- RECENT CUSTOMERS CARD -->
                <div class="data-card customers-card">
                    <div class="card-header">
                        <h3><i class='bx bxs-user-detail'></i> Recent Customers</h3>
                        <div class="card-actions">
                            <button class="view-all">View All</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (count($recentCustomers) > 0): ?>
                            <ul class="customer-list">
                                <?php foreach ($recentCustomers as $cust): ?>
                                    <li class="customer-item">
                                        <div class="customer-avatar">
                                            <?php echo strtoupper(substr($cust, 0, 1)); ?>
                                        </div>
                                        <div class="customer-info">
                                            <div class="customer-name"><?php echo htmlspecialchars($cust); ?></div>
                                            <div class="customer-activity">Recently purchased</div>
                                        </div>
                                        <button class="customer-action">
                                            <i class='bx bxs-envelope'></i>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="no-data">
                                <i class='bx bxs-user-x'></i>
                                <p>No customers found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </section>

    <script>
        // Sidebar toggle
        let sidebar = document.querySelector("#sidebar");
        let sidebarBtn = document.querySelector("nav .bx-menu");

        sidebarBtn.addEventListener("click", () => {
            sidebar.classList.toggle("active");
        });
    </script>
</body>
</html>