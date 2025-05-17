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

// Fetch order status counts for the chart
$statusCounts = [
    'pending' => 0,
    'processing' => 0,
    'completed' => 0,
    'cancelled' => 0
];

$statusQuery = "SELECT status_order, COUNT(DISTINCT order_id) as count FROM items_ordered GROUP BY status_order";
$result = $conn->query($statusQuery);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower($row['status_order']);
        if (isset($statusCounts[$status])) {
            $statusCounts[$status] = (int)$row['count'];
        }
    }
}

// Fetch recent 5 orders grouped by order_id
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

// Fetch recent 5 distinct customers
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
            --red: #a93226;
            --light-red: #f5d0ce;
            --dark-red: #7d241b;
            --grey: #eee;
            --dark-grey: #777777;
            --dark: #342e37;
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .chart-container:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
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
            transition: all 0.3s ease;
        }
        
        .box-info li:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        
        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(169, 50, 38, 0.1);
            color: var(--red);
            font-size: 24px;
            transition: all 0.3s ease;
        }
        
        .box-info li:hover .icon-circle {
            background: rgba(169, 50, 38, 0.2);
            transform: scale(1.05);
        }
        
        .box-info .text h3 {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }
        
        .box-info .text p {
            color: var(--dark);
            font-size: 14px;
            font-weight: 500;
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
            transition: all 0.3s ease;
        }
        
        .data-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
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
            background: rgba(169, 50, 38, 0.1);
            color: var(--red);
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .card-actions .view-all:hover {
            background: var(--red);
            color: white;
            transform: translateY(-2px);
        }
        
        /* ORDER ITEM STYLES */
        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            gap: 15px;
            transition: all 0.2s ease;
        }
        
        .order-item:hover {
            background: rgba(0, 0, 0, 0.02);
            transform: translateX(5px);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-id {
            font-weight: 600;
            color: var(--red);
            background: rgba(169, 50, 38, 0.1);
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 13px;
            transition: all 0.3s ease;
        }
        
        .order-item:hover .order-id {
            background: rgba(169, 50, 38, 0.2);
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
            color: var(--dark-grey);
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
            transition: all 0.3s ease;
        }
        
        .order-status.pending { 
            background-color: rgba(255, 193, 7, 0.2);
            color: #b38f00;
        }
        
        .order-status.processing { 
            background-color: rgba(253, 114, 56, 0.2); /* Using orange instead of teal */
            color: #c94a1f;
        }
        
        .order-status.completed { 
            background-color: rgba(40, 167, 69, 0.2);
            color: #1e7d34;
        }
        
        .order-status.cancelled { 
            background-color: rgba(169, 50, 38, 0.2);
            color: var(--red);
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
            transition: all 0.3s ease;
        }
        
        .customer-item:hover {
            background: rgba(0, 0, 0, 0.02);
            transform: translateX(5px);
        }
        
        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--red);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .customer-item:hover .customer-avatar {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(169, 50, 38, 0.2);
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
            color: var(--dark-grey);
            margin-top: 2px;
            font-weight: 500;
        }
        
        .customer-action {
            background: none;
            border: none;
            color: var(--dark-grey);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .customer-action:hover {
            color: var(--red);
            transform: scale(1.2);
        }
        
        /* NO DATA STYLES */
        .no-data {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
            color: var(--dark-grey);
        }
        
        .no-data i {
            font-size: 50px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .no-data p {
            margin: 0;
            font-size: 14px;
            font-weight: 500;
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
                <a href="manage_product.php">
                    <i class='bx bxs-shopping-bag-alt'></i>
                    <span class="text">Product Management</span>
                </a>
            </li>
            <li>
                <a href="managecategory.php">
                    <i class='bx bxs-category'></i>
                    <span class="text">Category Management</span>
                </a>
            </li>
            <li>
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

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
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
                        <h3><?php echo array_sum($statusCounts); ?></h3>
                        <p>Total Orders</p>
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
                                $salesResult = $conn2->query("SELECT SUM(price_items * quantity_items) AS total_sales FROM items_ordered WHERE status_order = 'completed'");
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
                        labels: ['Pending', 'Processing', 'Completed', 'Cancelled'],
                        datasets: [{
                            label: 'Order Status',
                            data: [
                                <?php echo $statusCounts['pending']; ?>,
                                <?php echo $statusCounts['processing']; ?>,
                                <?php echo $statusCounts['completed']; ?>,
                                <?php echo $statusCounts['cancelled']; ?>
                            ],
                            backgroundColor: [
                                'rgba(255, 193, 7, 0.7)', // Yellow for pending
                                'rgba(253, 114, 56, 0.7)', // Orange for processing
                                'rgba(40, 167, 69, 0.7)', // Green for completed
                                'rgba(169, 50, 38, 0.7)'  // Red for cancelled
                            ],
                            borderColor: [
                                'rgba(255, 193, 7, 1)',
                                'rgba(253, 114, 56, 1)',
                                'rgba(40, 167, 69, 1)',
                                'rgba(169, 50, 38, 1)'
                            ],
                            borderWidth: 1,
                            borderRadius: 8,
                            hoverBackgroundColor: [
                                'rgba(255, 193, 7, 1)',
                                'rgba(253, 114, 56, 1)',
                                'rgba(40, 167, 69, 1)',
                                'rgba(169, 50, 38, 1)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.raw;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
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
                            <a href="order.php" class="view-all">View All</a>
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
                                        $statusClass = strtolower($order['status_order']);
                                        if (!in_array($statusClass, ['pending', 'processing', 'completed', 'cancelled'])) {
                                            $statusClass = 'pending';
                                        }
                                    ?>
                                    <div class="order-status <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($statusClass); ?>
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
                            <a href="customer_list.php" class="view-all">View All</a>
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

        // Add smooth transitions to all interactive elements
        document.querySelectorAll('.box-info li, .data-card, .order-item, .customer-item, .icon-circle, .view-all, .customer-action')
            .forEach(element => {
                element.style.transition = 'all 0.3s ease';
            });
    </script>
</body>
</html>