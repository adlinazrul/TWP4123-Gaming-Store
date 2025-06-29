<?php
session_start();

// Prevent page caching
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Enhanced logout handling
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
    
    // Redirect with no-cache headers and prevent back-button access
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Location: login_admin.php?logout=1");
    exit;
}

// Check if the session variable is set
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
} else {
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

// Handle search functionality
$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';
$showSearchResults = false;
$searchResults = [];

if (!empty($searchQuery)) {
    $showSearchResults = true;
    $searchTerm = $conn->real_escape_string($searchQuery);
    
    $searchSql = "SELECT id, first_name, last_name, date, total_price, status_order 
                 FROM orders 
                 WHERE 
                    id LIKE '%$searchTerm%' OR 
                    first_name LIKE '%$searchTerm%' OR 
                    last_name LIKE '%$searchTerm%' OR 
                    CONCAT(first_name, ' ', last_name) LIKE '%$searchTerm%' OR 
                    date LIKE '%$searchTerm%' OR 
                    total_price LIKE '%$searchTerm%' OR 
                    status_order LIKE '%$searchTerm%'
                 ORDER BY date DESC";
    $searchResult = $conn->query($searchSql);
    
    if ($searchResult) {
        while ($row = $searchResult->fetch_assoc()) {
            $searchResults[] = $row;
        }
    }
}

// Get all orders (if not showing search results)
if (!$showSearchResults) {
    $sql = "SELECT id, first_name, last_name, date, total_price, status_order FROM orders ORDER BY date DESC";
    $result = $conn->query($sql);
}

// Fetch admin profile image
if ($admin_id) {
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders List</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="manageadmin.css">
    <style>
        .container {
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table th, table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #d03b3b;
            color: white;
        }

        /* Popup styles */
        #popup {
            display: none;
            position: fixed;
            top: 10%;
            left: 270px; /* Adjust this based on your sidebar width */
            width: calc(100% - 290px); /* Leaves space for sidebar and some margin */
            max-width: 800px; /* Optional: limit max width */
            max-height: 80vh;
            overflow-y: auto;
            background-color: white;
            border: 2px solid #333;
            padding: 20px;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border-radius: 10px;
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

        button.view-details-btn {
            background-color: #d03b3b;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        button.view-details-btn:hover {
            background-color: #a22a2a;
        }

        @media (max-width: 768px) {
            #popup {
                left: 20px;
                width: calc(100% - 40px);
            }
        }

        /* Search results styling */
        .search-results-container {
            margin-top: 30px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .highlight {
            background-color: #fff2c6;
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
            color: #d03b3b;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .clear-search:hover {
            color: #a22a2a;
            transform: translateX(-3px);
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #777;
        }

        .no-data i {
            font-size: 40px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<section id="sidebar">
    <a href="#" class="brand"><br><span class="text">Admin Dashboard</span></a>
    <ul class="side-menu top">
        <li><a href="admindashboard.php"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
        <li>
                <a href="addadmin.php">
                    <i class='bx bxs-group'></i>
                    <span class="text">Admin</span>
                </a>
            </li>
            <li>
                <a href="customer_list.php">
                    <i class='bx bxs-user'></i>
                    <span class="text">Customer</span>
                </a>
            </li>
            <li>
                <a href="manage_category.php">
                    <i class='bx bxs-category'></i>
                    <span class="text">Category Management</span>
                </a>
            </li>
            <li>
                <a href="manageproduct.php">
                    <i class='bx bxs-shopping-bag-alt'></i>
                    <span class="text">Product Management</span>
                </a>
            </li>
            <li class="active">
                <a href="order.php">
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

<section id="content">
    <nav>
        <form id="searchForm" method="GET" action="">
            <div class="form-input">
                <input type="search" id="searchInput" name="query" placeholder="Search orders..." 
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
            </div>
        </form>
        
        <a href="profile_admin.php" class="profile"><img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Picture"></a>
    </nav>

    <main>
        <div class="head-title" style="margin-bottom: 30px;">
            <div class="left">
                <h1>Orders List</h1>
                <ul class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active" href="#">Orders</a></li>
                </ul>
            </div>
        </div>

        <?php if ($showSearchResults): ?>
            <!-- SEARCH RESULTS SECTION -->
            <div class="search-results-container">
                <div class="search-header">
                    <h3><i class='bx bx-search'></i> Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h3>
                    <a href="?" class="clear-search">
                        <i class='bx bx-x'></i> Clear search
                    </a>
                </div>
                
                <?php if (count($searchResults) > 0): ?>
                    <div class="container">
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
                                <?php foreach ($searchResults as $order): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                                echo preg_replace(
                                                    "/(" . preg_quote($searchQuery, '/') . ")/i", 
                                                    '<span class="highlight">$1</span>', 
                                                    htmlspecialchars($order['id'])
                                                ); 
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $customerName = htmlspecialchars($order['first_name'] . ' ' . $order['last_name']);
                                                echo preg_replace(
                                                    "/(" . preg_quote($searchQuery, '/') . ")/i", 
                                                    '<span class="highlight">$1</span>', 
                                                    $customerName
                                                ); 
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                echo preg_replace(
                                                    "/(" . preg_quote($searchQuery, '/') . ")/i", 
                                                    '<span class="highlight">$1</span>', 
                                                    htmlspecialchars($order['date'])
                                                ); 
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $priceFormatted = "RM " . number_format($order['total_price'], 2);
                                                echo preg_replace(
                                                    "/(" . preg_quote($searchQuery, '/') . ")/i", 
                                                    '<span class="highlight">$1</span>', 
                                                    $priceFormatted
                                                ); 
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                echo preg_replace(
                                                    "/(" . preg_quote($searchQuery, '/') . ")/i", 
                                                    '<span class="highlight">$1</span>', 
                                                    htmlspecialchars($order['status_order'])
                                                ); 
                                            ?>
                                        </td>
                                        <td>
                                            <button class="view-details-btn" data-order-id="<?= $order['id'] ?>">View Details</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class='bx bxs-error-circle'></i>
                        <p>No results found for "<?php echo htmlspecialchars($searchQuery); ?>"</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- REGULAR ORDERS LIST -->
            <div class="container">
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
                        <?php if (isset($result) && $result->num_rows > 0): ?>
                            <?php while($order = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['id']) ?></td>
                                    <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                                    <td><?= htmlspecialchars($order['date']) ?></td>
                                    <td>RM <?= number_format($order['total_price'], 2) ?></td>
                                    <td><?= htmlspecialchars($order['status_order']) ?></td>
                                    <td>
                                        <button class="view-details-btn" data-order-id="<?= $order['id'] ?>">View Details</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6">No orders found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Popup container -->
        <div id="popup-overlay"></div>
        <div id="popup">
            <span class="close-btn" id="close-popup">&times;</span>
            <h2>Order Items</h2>
            <div id="order-items-content">Loading...</div>
        </div>
    </main>
</section>

<script>
    // Show popup and fetch order items via AJAX
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-details-btn')) {
            const orderId = e.target.getAttribute('data-order-id');
            const popup = document.getElementById('popup');
            const overlay = document.getElementById('popup-overlay');
            const content = document.getElementById('order-items-content');

            content.innerHTML = 'Loading...';

            // Show popup and overlay
            popup.style.display = 'block';
            overlay.style.display = 'block';

            // Fetch order items
            fetch('order_details.php?order_id=' + orderId)
                .then(res => res.text())
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(() => {
                    content.innerHTML = '<p style="color:red;">Failed to load order items.</p>';
                });
        }
    });

    // Close popup
    document.getElementById('close-popup').addEventListener('click', () => {
        document.getElementById('popup').style.display = 'none';
        document.getElementById('popup-overlay').style.display = 'none';
    });
    document.getElementById('popup-overlay').addEventListener('click', () => {
        document.getElementById('popup').style.display = 'none';
        document.getElementById('popup-overlay').style.display = 'none';
    });

    // Focus search input when search icon is clicked
    document.querySelector('.search-btn')?.addEventListener('click', function() {
        document.getElementById('searchInput').focus();
    });

    // Clear search when clicking the X button
    document.querySelector('.clear-search')?.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = '?';
    });

    // Submit form when pressing Enter in search input
    document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('searchForm').submit();
        }
    });
</script>

</body>
</html>

<?php $conn->close(); ?>