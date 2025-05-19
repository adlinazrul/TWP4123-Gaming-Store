<?php
session_start();

// Check if the session variable is set
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$admin_id = $_SESSION['admin_id'];

// Fetch profile image
$query = "SELECT image FROM admin_list WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($image);
$profile_image = ($stmt->fetch() && !empty($image)) ? 'image/' . $image : 'image/default_profile.jpg';
$stmt->close();

// Fetch products
$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin - Product Management</title>
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="admindashboard.css">
	<style>
		body { font-family: Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 0; }
		h1 { margin: 30px 0 10px; font-size: 32px; color: #333; }
		h2 { margin-bottom: 15px; }
		table { width: 100%; border-collapse: collapse; background-color: #fff; box-shadow: 0 0 10px rgba(0, 0, 0, 0.05); }
		th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
		th { background-color: #c0392b; color: white; }
		img { width: 50px; height: auto; }
		.add-product { background-color: #c0392b; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 20px; }
		.add-product:hover { background-color: #a93226; }
		.action-buttons { display: flex; gap: 10px; }
		.action-buttons a button { padding: 6px 12px; font-size: 14px; border: none; border-radius: 5px; cursor: pointer; color: white; transition: background-color 0.3s ease; }
		.action-buttons a:first-child button { background-color: #2980b9; }
		.action-buttons a:first-child button:hover { background-color: #1f618d; }
		.action-buttons a:last-child button { background-color: #e74c3c; }
		.action-buttons a:last-child button:hover { background-color: #c0392b; }
		#sidebar { width: 250px; position: fixed; height: 100%; background: #2c3e50; color: white; padding: 20px 0; }
		#sidebar a { color: white; display: block; padding: 10px 20px; text-decoration: none; }
		#sidebar a:hover, #sidebar a.active { background: #34495e; }
		#content { margin-left: 250px; padding: 20px; }
		nav { display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background: #fff; border-bottom: 1px solid #ccc; }
		nav .profile img { width: 40px; height: 40px; border-radius: 50%; }
	</style>
</head>
<body>

<!-- SIDEBAR -->
<section id="sidebar">
	<a href="#" class="brand"><br><span class="text">Admin Dashboard</span></a>
	<ul class="side-menu top">
		<li><a href="admindashboard.php"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
		<li class="active"><a href="manageproduct.php"><i class='bx bxs-shopping-bag-alt'></i><span class="text">Product Management</span></a></li>
		<li><a href="manage_category.php"><i class='bx bxs-category'></i><span class="text">Category Management</span></a></li>
		<li><a href="order.php"><i class='bx bxs-doughnut-chart'></i><span class="text">Order</span></a></li>
		<li><a href="customer_list.php"><i class='bx bxs-user'></i><span class="text">Customer</span></a></li>
		<li><a href="addadmin.php"><i class='bx bxs-group'></i><span class="text">Admin</span></a></li>
	</ul>
	<ul class="side-menu">
		<li><a href="#"><i class='bx bxs-cog'></i><span class="text">Settings</span></a></li>
		<li><a href="index.html" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
	</ul>
</section>
<!-- SIDEBAR -->

<!-- CONTENT -->
<section id="content">
	<!-- NAVBAR -->
	<nav>
		<form action="#">
			<div class="form-input">
				<input type="search" placeholder="Search...">
				<button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
			</div>
		</form>
		<a href="#" class="notification"><i class='bx bxs-bell'></i></a>
		<a href="profile_admin.php" class="profile"><img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Picture"></a>
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
						<td><?= htmlspecialchars($row['product_name']); ?></td>
						<td><?= htmlspecialchars($row['product_category']); ?></td>
						<td><?= htmlspecialchars($row['product_description']); ?></td>
						<td>RM <?= number_format($row['product_price'], 2); ?></td>
						<td><?= $row['product_quantity']; ?></td>
						<td>
							<div class="action-buttons">
								<a href="editproductquantity.php?id=<?= $row['id']; ?>"><button>Edit</button></a>
								<a href="deleteproduct.php?id=<?= $row['id']; ?>"><button>Delete</button></a>
							</div>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</section>
	</main>
</section>

</body>
</html>
