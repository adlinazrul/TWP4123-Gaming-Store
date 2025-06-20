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
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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

// Handle search functionality
$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT * FROM products 
            WHERE product_name LIKE '%$search%' 
            OR product_description LIKE '%$search%'
            OR product_category LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM products";
}

$result = mysqli_query($conn, $sql);
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
        <li><a href="dashboard.php"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
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
        <li class="active">
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
<!-- SIDEBAR -->

<!-- CONTENT -->
<section id="content">
    <!-- NAVBAR -->
    <nav>
        <form action="manage_product.php" method="get">
            <div class="form-input">
                <input type="search" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
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

        <section id="product-list">
            <h2>Manage Products</h2>
            <?php if (!empty($search)): ?>
                <p>Showing results for: "<?php echo htmlspecialchars($search); ?>" <a href="manage_product.php" style="margin-left: 10px; color: #c0392b;">Clear search</a></p>
            <?php endif; ?>
            <button class="add-product" onclick="window.location.href='addproduct2.php'">Add New Product</button>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Price (RM)</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><img src="<?= $row['product_image']; ?>" alt="Product Image"></td>
                            <td><?= htmlspecialchars($row['product_name']); ?></td>
                            <td><?= htmlspecialchars($row['product_description']); ?></td>
                            <td><?= htmlspecialchars($row['product_category']); ?></td>
                            <td>RM <?= number_format($row['product_price'], 2); ?></td>
                            <td><?= $row['product_quantity']; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="editproductquantity.php?id=<?= $row['id']; ?>"><button>Edit Quantity</button></a>
                                    <a href="deleteproduct.php?id=<?= $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');"><button>Delete</button></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" align="center">No products found<?php echo !empty($search) ? ' matching your search.' : '.'; ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
    <!-- MAIN -->
</section>
<!-- CONTENT -->

<script>
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