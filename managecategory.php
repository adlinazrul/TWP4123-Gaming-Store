<?php
session_start();

// Prevent caching of protected pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
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
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Location: login_admin.php?logout=1");
    exit;
}

// CONNECT TO DATABASE FIRST
$host = 'localhost';
$dbname = 'gaming_store';
$username = 'root';
$password = ''; // Change if your MySQL has a password

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the session variable is set
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

if ($admin_id) {
    // Correct SQL query to fetch the profile image (use 'image' column)
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
    $sql = "SELECT * FROM product_categories 
            WHERE category_name LIKE '%$search%' 
            OR description LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM product_categories";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Admin - Category Management</title>
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="admindashboard.css">
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

		.add-category {
			background-color: #c0392b;
			color: white;
			padding: 10px 20px;
			border: none;
			border-radius: 5px;
			cursor: pointer;
			margin-bottom: 20px;
		}

		.add-category:hover {
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
		}

		.action-buttons a:first-child button {
			background-color: #c0392b;
		}

		.action-buttons a:first-child button:hover {
			background-color: #c0392b;
		}

		.action-buttons a:last-child button {
			background-color: #e74c3c;
		}

		.action-buttons a:last-child button:hover {
			background-color: #c0392b;
		}
	</style>
</head>
<body>

<!-- SIDEBAR -->
<section id="sidebar">
	<a href="#" class="brand">
		<br>
		<span class="text">  Admin Dashboard</span>
	</a>
	<ul class="side-menu top">
		<li>
			<a href="admindashboard.php">
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
            <li class="active">
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
<!-- SIDEBAR -->

<!-- CONTENT -->
<section id="content">
	<nav>
		<form action="managecategory.php" method="get">
			<div class="form-input">
				<input type="search" name="search" placeholder="Search categories..." value="<?php echo htmlspecialchars($search); ?>">
				<button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
			</div>
		</form>
		<a href="#" class="notification">
			<i class='bx bxs-bell'></i>
		</a>
		<a href="profile_admin.php" class="profile">
			<img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Picture">
		</a>
	</nav>

	<main>
		<div class="head-title" style="margin-bottom: 30px;">
			<div class="left">
				<h1>Category Management</h1>
				<ul class="breadcrumb">
					<li><a href="#">Dashboard</a></li>
					<li><i class='bx bx-chevron-right'></i></li>
					<li><a class="active" href="#">Category Management</a></li>
				</ul>
			</div>
		</div>

		<section id="category-list">
			<h2>Manage Categories</h2>
			<?php if (!empty($search)): ?>
				<p>Showing results for: "<?php echo htmlspecialchars($search); ?>" <a href="managecategory.php" style="margin-left: 10px; color: #c0392b;">Clear search</a></p>
			<?php endif; ?>
			<button class="add-category" onclick="window.location.href='addcategory.php'">Add New Category</button>
			<table>
				<thead>
					<tr>
						<th>ID</th>
						<th>Category Name</th>
						<th>Description</th>
						<th>Created At</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if ($result->num_rows > 0): ?>
						<?php while ($row = $result->fetch_assoc()): ?>
							<tr>
								<td><?= $row['id']; ?></td>
								<td><?= htmlspecialchars($row['category_name']); ?></td>
								<td><?= htmlspecialchars($row['description']); ?></td>
								<td><?= $row['created_at']; ?></td>
								<td class="action-buttons">
									<a href="editcategory.php?id=<?= $row['id']; ?>"><button>Edit</button></a>
									<a href="deletecategory.php?id=<?= $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this category?');"><button>Delete</button></a>
								</td>
							</tr>
						<?php endwhile; ?>
					<?php else: ?>
						<tr><td colspan="5" align="center">No categories found<?php echo !empty($search) ? ' matching your search.' : '.'; ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</section>
	</main>
</section>

</body>
</html>

<?php $conn->close(); ?>