<?php
session_start();

// Prevent caching of protected pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Add these headers to prevent page from being cached
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// Check if logout request
if (isset($_GET['logout'])) {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Invalidate the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Redirect to login with no-cache headers
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Location: login_admin.php?logout=1");
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

// DB connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store"; // Ensure this is your database name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search functionality for 'orders' table
$searchResults = [];
$searchQuery = "";
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
    if (!empty($searchQuery)) {
        $searchTerm = "%{$searchQuery}%";
        
        // Search in 'orders' table
        $stmt = $conn->prepare("
            SELECT 
                id AS order_id, 
                CONCAT(first_name, ' ', last_name) AS name_cust, 
                date, 
                status_order, 
                total_price
            FROM orders
            WHERE 
                id LIKE ? OR 
                first_name LIKE ? OR 
                last_name LIKE ? OR 
                status_order LIKE ? OR
                email LIKE ? OR
                phone_number LIKE ? OR
                street_address LIKE ? OR
                city LIKE ? OR
                state LIKE ? OR
                postcode LIKE ? OR
                country LIKE ? OR
                cardholder_name LIKE ?
            ORDER BY date DESC
            LIMIT 10
        ");
        $stmt->bind_param("ssssssssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $searchResults['orders'] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Search in customers (using orders table for customer names)
        $stmt = $conn->prepare("
            SELECT DISTINCT CONCAT(first_name, ' ', last_name) AS name_cust
            FROM orders
            WHERE CONCAT(first_name, ' ', last_name) LIKE ? OR email LIKE ? OR phone_number LIKE ?
            ORDER BY date DESC
            LIMIT 10
        ");
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $searchResults['customers'] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
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

// Fetch only Pending and Completed order counts from 'orders' table
$statusCounts = [
    'pending' => 0,
    'completed' => 0
];

$statusQuery = "SELECT 
                SUM(CASE WHEN LOWER(status_order) = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN LOWER(status_order) = 'completed' THEN 1 ELSE 0 END) as completed_count
                FROM orders"; // Changed to 'orders' table
$result = $conn->query($statusQuery);
if ($result) {
    $row = $result->fetch_assoc();
    $statusCounts['pending'] = (int)$row['pending_count'];
    $statusCounts['completed'] = (int)$row['completed_count'];
}

// Fetch recent 5 orders from the 'orders' table
$recentOrders = [];
$sql = "
    SELECT 
        id AS order_id, 
        CONCAT(first_name, ' ', last_name) AS name_cust, 
        date, 
        status_order, 
        total_price
    FROM orders
    ORDER BY date DESC
    LIMIT 5
"; // Changed to 'orders' table
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Add a placeholder for items_count if needed for consistency with original structure,
        // although 'orders' table itself doesn't contain this specific count per order line.
        $row['items_count'] = 1; 
        $recentOrders[] = $row;
    }
}

// Fetch total orders count from 'orders' table
$totalOrdersQuery = "SELECT COUNT(*) AS total_orders FROM orders";
$totalOrdersResult = $conn->query($totalOrdersQuery);
$totalOrders = 0;
if ($totalOrdersResult && $row = $totalOrdersResult->fetch_assoc()) {
    $totalOrders = $row['total_orders'];
}

// Fetch total sales from 'orders' table where status is 'completed'
$totalSales = 0;
$salesResult = $conn->query("SELECT SUM(total_price) AS total_sales FROM orders WHERE status_order = 'completed'"); // Changed to 'orders' table
if ($salesResult && $salesRow = $salesResult->fetch_assoc()) {
    $totalSales = $salesRow['total_sales'] ?? 0;
}


// Fetch recent 5 distinct customers from 'orders' table
$recentCustomers = [];
$sqlCust = "
    SELECT DISTINCT CONCAT(first_name, ' ', last_name) AS name_cust 
    FROM orders
    WHERE first_name IS NOT NULL AND first_name != ''
    ORDER BY date DESC
    LIMIT 5
"; // Changed to 'orders' table
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
            background-color: rgba(253, 114, 56, 0.2);
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
        
        /* SEARCH RESULTS STYLES */
        .search-results {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        
        .search-results h3 {
            color: var(--red);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .search-results-count {
            margin-bottom: 20px;
            color: var(--dark-grey);
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
                <a href="cust_list.php">
                    <i class='bx bxs-user'></i>
                    <span class="text">Customer</span>
                </a>
            </li>
            <li>
                <a href="managecategory.php">
                    <i class='bx bxs-category'></i>
                    <span class="text">Category Management</span>
                </a>
            </li>
            <li>
                <a href="manage_product.php">
                    <i class='bx bxs-shopping-bag-alt'></i>
                    <span class="text">Product Management</span>
                </a>
            </li>
            
            <li>
                <a href="order_admin.php">
                    <i class='bx bxs-doughnut-chart'></i>
                    <span class="text">Order</span>
                </a>
            </li>
            
        </ul>
        <ul class="side-menu">
            <li>
                <a href="?logout=1" class="logout">
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
            <form action="" method="GET">
                <div class="form-input">
                    <input type="search" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($searchQuery); ?>" />
                    <button type="submit" class="search-btn">
                        <i class='bx bx-search'></i>
                    </button>
                </div>
            </form>
            
            <a href="profileadmin.php" class="profile">
                <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Picture" />
            </a>
        </nav>

        <!-- MAIN -->
        <main>
            <?php if (!empty($searchQuery)): ?>
                <!-- SEARCH RESULTS SECTION -->
                <div class="search-results">
                    <h3><i class='bx bx-search'></i> Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h3>
                    
                    <?php if (empty($searchResults['orders']) && empty($searchResults['customers'])): ?>
                        <div class="no-data">
                            <i class='bx bxs-error-circle'></i>
                            <p>No results found for your search</p>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($searchResults['orders'])): ?>
                            <div class="search-results-count">
                                Found <?php echo count($searchResults['orders']); ?> order(s)
                            </div>
                            <div class="card-body">
                                <?php foreach ($searchResults['orders'] as $order): ?>
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
                                            // The status classes here should match the general statuses you expect to display.
                                            // Ensure 'paid' and 'processing' are included if they are valid statuses in your 'orders' table
                                            if (!in_array($statusClass, ['pending', 'processing', 'completed', 'cancelled', 'paid'])) {
                                                $statusClass = 'pending';
                                            }
                                        ?>
                                        <div class="order-status <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($statusClass); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($searchResults['customers'])): ?>
                            <div class="search-results-count" style="margin-top: 20px;">
                                Found <?php echo count($searchResults['customers']); ?> customer(s)
                            </div>
                            <ul class="customer-list">
                                <?php foreach ($searchResults['customers'] as $customer): ?>
                                    <li class="customer-item">
                                        <div class="customer-avatar">
                                            <?php echo strtoupper(substr($customer['name_cust'], 0, 1)); ?>
                                        </div>
                                        <div class="customer-info">
                                            <div class="customer-name"><?php echo htmlspecialchars($customer['name_cust']); ?></div>
                                            <div class="customer-activity">Customer</div>
                                        </div>
                                        <button class="customer-action">
                                            <i class='bx bxs-envelope'></i>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <div style="margin-top: 20px; text-align: center;">
                        <a href="?" class="view-all">Back to Dashboard</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- NORMAL DASHBOARD CONTENT -->
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
                            <h3><?php echo $totalOrders; ?></h3> <!-- Changed to use $totalOrders -->
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
                                    echo number_format($totalSales, 2); // Changed to use $totalSales
                                ?>
                            </h3>
                            <p>Total Sales</p>
                        </span>
                    </li>
                </ul>

                <!-- CHART - Now showing only Pending and Completed orders -->
                <div class="chart-container">
                    <canvas id="dashboardChart"></canvas>
                </div>

                <script>
                    const ctx = document.getElementById('dashboardChart').getContext('2d');
                    const chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Pending', 'Completed'],
                            datasets: [{
                                label: 'Order Status',
                                data: [
                                    <?php echo $statusCounts['pending']; ?>,
                                    <?php echo $statusCounts['completed']; ?>
                                ],
                                backgroundColor: [
                                    'rgba(255, 193, 7, 0.7)', // Yellow for pending
                                    'rgba(40, 167, 69, 0.7)'  // Green for completed
                                ],
                                borderColor: [
                                    'rgba(255, 193, 7, 1)',
                                    'rgba(40, 167, 69, 1)'
                                ],
                                borderWidth: 1,
                                borderRadius: 8,
                                hoverBackgroundColor: [
                                    'rgba(255, 193, 7, 1)',
                                    'rgba(40, 167, 69, 1)'
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
                            },
                            onClick: (event, elements) => {
                                if (elements.length > 0) {
                                    const status = ['pending', 'completed'][elements[0].index];
                                    // Use console.log or a custom modal instead of alert()
                                    console.log(`Viewing ${status} orders: ${chart.data.datasets[0].data[elements[0].index]} orders`);
                                    // Uncomment to redirect to filtered orders page:
                                    // window.location.href = `order_admin.php?status=${status}`;
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
                                            if (!in_array($statusClass, ['pending', 'processing', 'completed', 'cancelled', 'paid'])) {
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
                                                <div class="customer-activity">Customer</div>
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
            <?php endif; ?>
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
