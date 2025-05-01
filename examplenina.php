<?php
// Include your database connection
include 'database.php';

// Fetch products from the database
$sql = "SELECT * FROM products";
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
		table {
			width: 100%;
			border-collapse: collapse;
		}

		th, td {
			padding: 10px;
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
			margin-bottom: 10px;
		}

		.add-product:hover {
			background-color: #a93226;
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
				<a href="#">
					<i class='bx bxs-dashboard'></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
			<li class="active">
				<a href="manageproduct.php">
					<i class='bx bxs-shopping-bag-alt'></i>
					<span class="text">Product Management</span>
				</a>
			</li>
			<li>
				<a href="order.html">
					<i class='bx bxs-doughnut-chart'></i>
					<span class="text">Order</span>
				</a>
			</li>
			<li>
				<a href="addmember.html">
					<i class='bx bxs-message-dots'></i>
					<span class="text">Message</span>
				</a>
			</li>
			<li>
				<a href="addadmin.php">
					<i class='bx bxs-group'></i>
					<span class="text">Team</span>
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
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu'></i>
			<a href="managecategory.html" class="nav-link">Categories</a>
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
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Product Management</h1>
					<ul class="breadcrumb">
						<li>
							<a href="#">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right'></i></li>
						<li>
							<a class="active" href="#">Product Management</a>
						</li>
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
							<td><?= $row['product_description']; ?></td>
							<td>RM <?= number_format($row['product_price'], 2); ?></td>
							<td><?= $row['product_quantity']; ?></td>
							<td>
								<a href="editproductquantity.php?id=<?= $row['id']; ?>"><button>Edit Quantity</button></a>
								<a href="deleteproduct.php?id=<?= $row['id']; ?>"><button>Delete</button></a>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</section>
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->

</body>
</html>
