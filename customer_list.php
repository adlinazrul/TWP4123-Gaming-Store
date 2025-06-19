<?php
session_start();

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
    
    $searchSql = "SELECT id, account_status, first_name, last_name, email, phone, username, bio, address, city, state, postcode, country 
                 FROM customers 
                 WHERE first_name LIKE '%$searchTerm%' OR 
                       last_name LIKE '%$searchTerm%' OR 
                       email LIKE '%$searchTerm%' OR 
                       phone LIKE '%$searchTerm%' OR 
                       username LIKE '%$searchTerm%' OR 
                       CONCAT(first_name, ' ', last_name) LIKE '%$searchTerm%'";
    $searchResult = $conn->query($searchSql);
    
    if ($searchResult) {
        while ($row = $searchResult->fetch_assoc()) {
            $searchResults[] = $row;
        }
    }
}

// Fetch all customers if not showing search results
if (!$showSearchResults) {
    $sql = "SELECT id, account_status, first_name, last_name, email, phone, birthdate, username, bio, address, city, state, postcode, country FROM customers";
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
    <title>Customer List</title>
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
            color: #a93226;
            transform: translateX(-3px);
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
            <li class="active">
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
            <li>
                <a href="order.php">
                    <i class='bx bxs-doughnut-chart'></i>
                    <span class="text">Order</span>
                </a>
            </li>
    </ul>
    <ul class="side-menu">
        <li><a href="#"><i class='bx bxs-cog'></i><span class="text">Settings</span></a></li>
        <li><a href="index.html" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
    </ul>
</section>

<section id="content">
    <nav>
        <form id="searchForm" method="GET" action="">
            <div class="form-input">
                <input type="search" id="searchInput" name="query" placeholder="Search customers..." 
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
            </div>
        </form>
        <a href="#" class="notification"><i class='bx bxs-bell'></i></a>
        <a href="profile_admin.php" class="profile"><img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Picture"></a>
    </nav>

    <main>
        <div class="head-title" style="margin-bottom: 30px;">
            <div class="left">
                <h1>Customer List</h1>
                <ul class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active" href="#">Customer</a></li>
                </ul>
            </div>
        </div>

        <div class="container">
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
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Username</th>
                                    <th>Bio</th>
                                    <th>Address</th>
                                    <th>City</th>
                                    <th>State</th>
                                    <th>Postcode</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($searchResults as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['id']) ?></td>
                                        <td>
                                            <?php 
                                                $fullName = $row['first_name'] . ' ' . $row['last_name'];
                                                echo preg_replace(
                                                    "/(" . preg_quote($searchQuery, '/') . ")/i", 
                                                    '<span class="highlight">$1</span>', 
                                                    htmlspecialchars($fullName)
                                                ); 
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                echo preg_replace(
                                                    "/(" . preg_quote($searchQuery, '/') . ")/i", 
                                                    '<span class="highlight">$1</span>', 
                                                    htmlspecialchars($row['email'])
                                                ); 
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                echo preg_replace(
                                                    "/(" . preg_quote($searchQuery, '/') . ")/i", 
                                                    '<span class="highlight">$1</span>', 
                                                    htmlspecialchars($row['phone'])
                                                ); 
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                echo preg_replace(
                                                    "/(" . preg_quote($searchQuery, '/') . ")/i", 
                                                    '<span class="highlight">$1</span>', 
                                                    htmlspecialchars($row['username'])
                                                ); 
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['bio']) ?></td>
                                        <td><?= htmlspecialchars($row['address']) ?></td>
                                        <td><?= htmlspecialchars($row['city']) ?></td>
                                        <td><?= htmlspecialchars($row['state']) ?></td>
                                        <td><?= htmlspecialchars($row['postcode']) ?></td>
                                        <td>
                                            <form method="POST" action="toggle_status_admin.php">
                                                <input type="hidden" name="customer_id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="current_status" value="<?= $row['account_status'] ?>">
                                                <button type="submit" style="background-color: <?= $row['account_status'] == 'active' ? '#4CAF50' : '#f44336' ?>; color: white; border: none; padding: 5px 10px; cursor: pointer;">
                                                    <?= ucfirst($row['account_status']) ?>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="text-align: center; padding: 20px;">
                            <i class='bx bxs-error-circle' style="font-size: 48px; color: #d03b3b;"></i>
                            <p>No customers found matching "<?php echo htmlspecialchars($searchQuery); ?>"</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- REGULAR CUSTOMER LIST -->
                <h2>Registered Customers</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Username</th>
                            <th>Bio</th>
                            <th>Address</th>
                            <th>City</th>
                            <th>State</th>
                            <th>Postcode</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($result) && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['phone']) ?></td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['bio']) ?></td>
                                    <td><?= htmlspecialchars($row['address']) ?></td>
                                    <td><?= htmlspecialchars($row['city']) ?></td>
                                    <td><?= htmlspecialchars($row['state']) ?></td>
                                    <td><?= htmlspecialchars($row['postcode']) ?></td>
                                    <td>
                                        <form method="POST" action="toggle_status_admin.php">
                                            <input type="hidden" name="customer_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $row['account_status'] ?>">
                                            <button type="submit" style="background-color: <?= $row['account_status'] == 'active' ? '#4CAF50' : '#f44336' ?>; color: white; border: none; padding: 5px 10px; cursor: pointer;">
                                                <?= ucfirst($row['account_status']) ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="12">No customers found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</section>

<script>
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