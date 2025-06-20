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

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
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
    
    $searchSql = "SELECT * FROM products WHERE 
                 product_name LIKE '%$searchTerm%' OR 
                 product_category LIKE '%$searchTerm%' OR 
                 product_description LIKE '%$searchTerm%'";
    $searchResult = mysqli_query($conn, $searchSql);
    
    if ($searchResult) {
        while ($row = mysqli_fetch_assoc($searchResult)) {
            $searchResults[] = $row;
        }
    }
}

// Fetch profile image
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

// Fetch products (only if not showing search results)
if (!$showSearchResults) {
    $sql = "SELECT * FROM products";
    $result = mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="admindashboard.css">
    <title>Admin - Product Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
        }
        h1 {
            margin: 30px 0 10px;
            font-size: 32px;
            color: #333;
        }
        h2 {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #c0392b;
            color: white;
        }
        img {
            width: 50px;
            height: auto;
        }
        .add-product {
            background-color: #c0392b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .add-product:hover {
            background-color: #a93226;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .action-buttons a button {
            padding: 6px 12px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            transition: background-color 0.3s ease;
        }
        .action-buttons a:first-child button {
            background-color: #e74c3c;
        }
        .action-buttons a:first-child button:hover {
            background-color: #c0392b;
        }
        .action-buttons a:last-child button {
            background-color: #d63031;
        }
        .action-buttons a:last-child button:hover {
            background-color: #b71c1c;
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
            color: #c0392b;
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
        .no-data {
            text-align: center;
            padding: 20px;
            color: #777;
        }
        .no-data i {
            font-size: 50px;
            margin-bottom: 10px;
            color: #ccc;
        }
        /* Confirmation dialog for delete */
        .confirm-delete {
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            display: none;
        }
        .confirm-delete-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<section id="sidebar">
    <a href="#" class="brand">
        <br>
        <span class="text">Admin Dashboard</span>
    </a>
    <ul class="side-menu top">
        <li>
            <a href="admindashboard.php">
                <i class='bx bxs-dashboard'></i>
                <span class="text">Dashboard</span>
            </a>
        </li>
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
        <li class="active">
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
        <li>
            <a href="?logout=1" class="logout">
                <i class='bx bxs-log-out-circle'></i>
                <span class="text">Logout</span>
            </a>
        </li>
    </ul>
</section>
<!-- SIDEBAR -->

<!-- CONTENT -->
<section id="content">
    <!-- NAVBAR -->
    <nav>
        <form id="searchForm" method="GET" action="">
            <div class="form-input">
                <input type="search" id="searchInput" name="query" placeholder="Search products..." 
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
            </div>
        </form>
        
        <a href="profile_admin.php" class="profile">
            <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Picture">
        </a>
    </nav>
    <!-- NAVBAR -->

    <!-- MAIN -->
    <main>
        <div class="head-title" style="margin-bottom: 30px;">
            <div class="left">
                <h1>Product Management</h1>
                <ul class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active" href="#">Product Management</a></li>
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
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Price (RM)</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($searchResults as $row): ?>
                                <tr>
                                    <td><img src="<?= $row['product_image']; ?>" alt="Product Image"></td>
                                    <td>
                                        <?php 
                                            echo preg_replace(
                                                "/(" . preg_quote($searchQuery, '/') . ")/i", 
                                                '<span class="highlight">$1</span>', 
                                                htmlspecialchars($row['product_name'])
                                            ); 
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                            echo preg_replace(
                                                "/(" . preg_quote($searchQuery, '/') . ")/i", 
                                                '<span class="highlight">$1</span>', 
                                                htmlspecialchars($row['product_category'])
                                            ); 
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                            echo preg_replace(
                                                "/(" . preg_quote($searchQuery, '/') . ")/i", 
                                                '<span class="highlight">$1</span>', 
                                                htmlspecialchars($row['product_description'])
                                            ); 
                                        ?>
                                    </td>
                                    <td>RM <?= number_format($row['product_price'], 2); ?></td>
                                    <td><?= $row['product_quantity']; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="editproductquantity.php?id=<?= $row['id']; ?>"><button>Edit Quantity</button></a>
                                            <a href="deleteproduct.php?id=<?= $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');"><button>Delete</button></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        <i class='bx bxs-error-circle'></i>
                        <p>No results found for "<?php echo htmlspecialchars($searchQuery); ?>"</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- REGULAR PRODUCT MANAGEMENT CONTENT -->
            <section id="product-list">
                <h2>Manage Products</h2>
                <button class="add-product" onclick="window.location.href='addproduct.php'">Add New Product</button>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Price (RM)</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><img src="<?= $row['product_image']; ?>" alt="Product Image"></td>
                            <td><?= $row['product_name']; ?></td>
                            <td><?= $row['product_category']; ?></td>
                            <td><?= $row['product_description']; ?></td>
                            <td>RM <?= number_format($row['product_price'], 2); ?></td>
                            <td><?= $row['product_quantity']; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="editproductquantity.php?id=<?= $row['id']; ?>"><button>Edit Quantity</button></a>
                                    <a href="deleteproduct.php?id=<?= $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');"><button>Delete</button></a>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>
    </main>
    <!-- MAIN -->
</section>
<!-- CONTENT -->

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

// Sidebar toggle
let sidebar = document.querySelector("#sidebar");
let sidebarBtn = document.querySelector("nav .bx-menu");

sidebarBtn.addEventListener("click", () => {
    sidebar.classList.toggle("active");
});

// Prevent form resubmission on page refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>

</body>
</html>

<?php
$conn->close();
?>