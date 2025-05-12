<?php
$host = 'localhost';
$dbname = 'gaming_store';
$username = 'root';
$password = ''; // Change if your MySQL has a password

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "SELECT * FROM product_categories";
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
			background-color: #3498db;
		}

		.action-buttons a:first-child button:hover {
			background-color: #2980b9;
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
		<span class="text">Admin Dashboard</span>
	</a>
	<ul class="side-menu top">
		<li>
			<a href="admindashboard.html">
				<i class='bx bxs-dashboard'></i>
				<span class="text">Dashboard</span>
			</a>
		</li>
		<li>
			<a href="manageproduct.php">
				<i class='bx bxs-shopping-bag-alt'></i>
				<span class="text">Product Management</span>
			</a>
		</li>
		<li class="active">
			<a href="managecategory.php">
				<i class='bx bxs-category'></i>
				<span class="text">Category Management</span>
			</a>
		</li>
		<li>
			<a href="order.php">
				<i class='bx bxs-doughnut-chart'></i>
				<span class="text">Order</span>
			</a>
		</li>
		<li>
			<a href="customer_list.php">
				<i class='bx bxs-user'></i>
				<span class="text">Customer</span>
			</a>
		</li>
		<li>
			<a href="addadmin.php">
				<i class='bx bxs-group'></i>
				<span class="text">Admin</span>
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
			<a href="index.html" class="logout">
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
		<i class='bx bx-menu'></i>
		<a href="managecategory.php" class="nav-link">Categories</a>
		<form action="#">
			<div class="form-input">
				<input type="search" placeholder="Search...">
				<button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
			</div>
		</form>
		<a href="#" class="notification">
			<i class='bx bxs-bell'></i>
		</a>
		<a href="#" class="profile">
			<img src="image/adlina.jpg">
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
						<tr><td colspan="5" align="center">No categories found.</td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</section>
	</main>
</section>

</body>
</html>

<?php $conn->close(); ?>
