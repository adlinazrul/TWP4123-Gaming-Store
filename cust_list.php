<?php
session_start();

// Enhanced security headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Check if logout request
if (isset($_GET['logout'])) {
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
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
    
    // Redirect to login with security headers
    header("Location: login_admin.php?logout=1");
    exit;
}

// Check if the session variable is set
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Database connection with error handling
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Prepared statement for security
    $sql = "SELECT id, first_name, last_name, email, phone, birthdate, username, bio, 
                   address, city, state, postcode, country, account_status 
            FROM customers";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch admin profile image with prepared statement
    $profile_image = 'image/default_profile.jpg';
    if ($admin_id) {
        $query = "SELECT image FROM admin_list WHERE id = ?";
        $stmt_img = $conn->prepare($query);
        if (!$stmt_img) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt_img->bind_param("i", $admin_id);
        $stmt_img->execute();
        $stmt_img->bind_result($image);
        if ($stmt_img->fetch() && !empty($image)) {
            $profile_image = 'image/' . htmlspecialchars($image);
        }
        $stmt_img->close();
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://unpkg.com; style-src 'self' https://unpkg.com 'unsafe-inline'; img-src 'self' data:;">
    <title>Customer List - Admin Dashboard</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="manageadmin.css">
    <style>
        .container {
            padding: 20px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        table th, table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            word-wrap: break-word;
            max-width: 200px;
        }

        table th {
            background-color: #d03b3b;
            color: white;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .status-btn {
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .status-btn.active {
            background-color: #4CAF50;
            color: white;
        }

        .status-btn.inactive {
            background-color: #f44336;
            color: white;
        }

        .status-btn:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }

        .search-container {
            display: flex;
            margin-bottom: 20px;
        }

        .search-container input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            td, th {
                min-width: 120px;
            }
        }
    </style>
</head>
<body>

<section id="sidebar">
    <a href="#" class="brand"><br><span class="text">Admin Dashboard</span></a>
    <ul class="side-menu top">
        <li><a href="admindashboard.php"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
        <li class="active">
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
        <li><a href="?logout=1" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
    </ul>
</section>

<section id="content">
    <nav>
        <form action="#" method="GET" id="searchForm">
            <div class="form-input">
                <input type="search" name="search" placeholder="Search customers..." id="searchInput" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
            </div>
        </form>
        <a href="profile_admin.php" class="profile"><img src="<?= $profile_image ?>" alt="Profile Picture"></a>
    </nav>

    <main>
        <div class="head-title" style="margin-bottom: 30px;">
            <div class="left">
                <h1>Customer Management</h1>
                <ul class="breadcrumb">
                    <li><a href="admindashboard.php">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active" href="cust_list.php">Customer List</a></li>
                </ul>
            </div>
        </div>

        <div class="container">
            <div class="search-container">
                <input type="text" id="liveSearch" placeholder="Search in current results...">
            </div>
            
            <table id="customerTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Username</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                <td><a href="mailto:<?= htmlspecialchars($row['email']) ?>"><?= htmlspecialchars($row['email']) ?></a></td>
                                <td><?= !empty($row['phone']) ? htmlspecialchars($row['phone']) : 'N/A' ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td>
                                    <?= !empty($row['address']) ? htmlspecialchars($row['address']) . ', ' : '' ?>
                                    <?= !empty($row['city']) ? htmlspecialchars($row['city']) . ', ' : '' ?>
                                    <?= !empty($row['state']) ? htmlspecialchars($row['state']) : '' ?>
                                </td>
                                <td>
                                    <form method="POST" action="toggle_status_admin2.php" class="status-form">
                                        <input type="hidden" name="customer_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="current_status" value="<?= $row['account_status'] ?>">
                                        <button type="submit" class="status-btn <?= $row['account_status'] ?>">
                                            <?= ucfirst($row['account_status']) ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <a href="view_customer.php?id=<?= $row['id'] ?>" class="view-btn" title="View Details"><i class='bx bx-show'></i></a>
                                    <a href="edit_customer.php?id=<?= $row['id'] ?>" class="edit-btn" title="Edit"><i class='bx bx-edit'></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="no-results">No customers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</section>

<script>
    // Live search functionality
    document.getElementById('liveSearch').addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#customerTable tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchValue) ? '' : 'none';
        });
    });

    // Confirm before changing status
    document.querySelectorAll('.status-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const currentStatus = this.querySelector('input[name="current_status"]').value;
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            if (!confirm(`Are you sure you want to change this customer's status to ${newStatus}?`)) {
                e.preventDefault();
            }
        });
    });
</script>

</body>
</html>

<?php 
$stmt->close();
$conn->close();
?>