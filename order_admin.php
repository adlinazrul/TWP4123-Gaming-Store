<?php
session_start();

// Prevent caching of protected pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

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
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all orders
$sql = "SELECT id, first_name, last_name, date, total_price, status_order FROM orders ORDER BY date DESC";
$result = $conn->query($sql);

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

        /* Status badges */
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
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
            transition: background-color 0.3s;
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
        <li class="active">
            <a href="order_admin.php">
                <i class='bx bxs-doughnut-chart'></i>
                <span class="text">Order</span>
            </a>
        </li>
    </ul>
    <ul class="side-menu">

        <li><a href="?logout=1" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
    </ul>
</section>

<section id="content">
    <nav>
        <form action="#">
            <div class="form-input">
                <input type="search" placeholder="Search...">
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
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($order = $result->fetch_assoc()): 
                            $statusClass = 'status-' . strtolower(str_replace(' ', '-', $order['status_order']));
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($order['id']) ?></td>
                                <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                                <td><?= htmlspecialchars($order['date']) ?></td>
                                <td>RM <?= number_format($order['total_price'], 2) ?></td>
                                <td><span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($order['status_order']) ?></span></td>
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
    document.querySelectorAll('.view-details-btn').forEach(button => {
        button.addEventListener('click', () => {
            const orderId = button.getAttribute('data-order-id');
            const popup = document.getElementById('popup');
            const overlay = document.getElementById('popup-overlay');
            const content = document.getElementById('order-items-content');

            content.innerHTML = 'Loading...';

            // Show popup and overlay
            popup.style.display = 'block';
            overlay.style.display = 'block';

            // Fetch order items
            fetch('order_details2.php?order_id=' + orderId)
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.text();
                })
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    content.innerHTML = '<p style="color:red;">Failed to load order items. Please try again.</p>';
                });
        });
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

    // Close popup when pressing Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.getElementById('popup').style.display = 'none';
            document.getElementById('popup-overlay').style.display = 'none';
        }
    });

    // Prevent form resubmission on page refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>

</body>
</html>

<?php $conn->close(); ?>