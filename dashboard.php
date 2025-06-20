<?php
session_start();

// Prevent caching of secure pages
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

// Handle logout
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
    
    // Redirect to login page with no-cache headers
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
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

// Handle search functionality
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchResults = [];
$showSearchResults = false;

if (!empty($searchQuery)) {
    $showSearchResults = true;
    $searchTerm = $conn->real_escape_string($searchQuery);
    
    // Search for customers in items_ordered table
    $searchSql = "
        SELECT DISTINCT name_cust 
        FROM items_ordered
        WHERE name_cust LIKE '%$searchTerm%'
        ORDER BY name_cust
        LIMIT 10
    ";
    
    $searchResult = $conn->query($searchSql);
    if ($searchResult) {
        while ($row = $searchResult->fetch_assoc()) {
            $searchResults[] = $row['name_cust'];
        }
    }
}

// Fetch only Pending and Completed order counts from items_ordered table
$statusCounts = [
    'pending' => 0,
    'completed' => 0
];

$statusQuery = "SELECT 
                SUM(CASE WHEN LOWER(status_order) = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN LOWER(status_order) = 'completed' THEN 1 ELSE 0 END) as completed_count
                FROM items_ordered";
$result = $conn->query($statusQuery);
if ($result) {
    $row = $result->fetch_assoc();
    $statusCounts['pending'] = (int)$row['pending_count'];
    $statusCounts['completed'] = (int)$row['completed_count'];
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
        /* [Previous CSS styles remain exactly the same] */
        /* ... */

        /* SEARCH RESULTS STYLES */
        .search-results-container {
            margin-top: 30px;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .highlight {
            background-color: var(--light-yellow);
            padding: 2px 4px;
            border-radius: 4px;
        }
        
        .search-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .clear-search {
            color: var(--red);
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .clear-search:hover {
            color: var(--dark-red);
            transform: translateX(-3px);
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
                <a href="#">
                    <i class='bx bxs-cog'></i>
                    <span class="text">Settings</span>
                </a>
            </li>
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
            <form method="GET" action="">
                <div class="form-input">
                    <input type="search" name="search" placeholder="Search customers..." 
                           value="<?php echo htmlspecialchars($searchQuery); ?>" />
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
            <?php if ($showSearchResults): ?>
                <!-- SEARCH RESULTS SECTION -->
                <div class="search-results-container">
                    <div class="data-card">
                        <div class="search-header">
                            <h3><i class='bx bx-search'></i> Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h3>
                            <a href="?" class="clear-search">
                                <i class='bx bx-x'></i> Clear search
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (count($searchResults) > 0): ?>
                                <ul class="customer-list">
                                    <?php foreach ($searchResults as $cust): ?>
                                        <li class="customer-item">
                                            <div class="customer-avatar">
                                                <?php echo strtoupper(substr($cust, 0, 1)); ?>
                                            </div>
                                            <div class="customer-info">
                                                <div class="customer-name">
                                                    <?php 
                                                        echo preg_replace(
                                                            "/(" . preg_quote($searchQuery, '/') . ")/i", 
                                                            '<span class="highlight">$1</span>', 
                                                            htmlspecialchars($cust)
                                                        ); 
                                                    ?>
                                                </div>
                                                <div class="customer-activity">Found in orders</div>
                                            </div>
                                            <a href="cust_list.php?search=<?php echo urlencode($cust); ?>" class="customer-action">
                                                <i class='bx bxs-user-detail'></i>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class='bx bxs-error-circle'></i>
                                    <p>No customers found matching "<?php echo htmlspecialchars($searchQuery); ?>"</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- REGULAR DASHBOARD CONTENT -->
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
                                    alert(`Viewing ${status} orders: ${chart.data.datasets[0].data[elements[0].index]} orders`);
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

        // Focus search input when search icon is clicked
        document.querySelector('.search-btn').addEventListener('click', function() {
            document.querySelector('input[name="search"]').focus();
        });
    </script>
</body>
</html>