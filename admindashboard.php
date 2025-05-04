<?php
session_start();
include 'db_connection.php'; // your DB connection file

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$sql = "SELECT name, position, profile_pic FROM admins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="admindashboard.css">

	<title>Admin</title>
	<style>
		.profile-dropdown {
			position: absolute;
			top: 60px;
			right: 20px;
			background: white;
			box-shadow: 0 0 10px rgba(0,0,0,0.2);
			border-radius: 10px;
			padding: 15px;
			display: none;
			z-index: 999;
			width: 200px;
		}
		.profile-dropdown img {
			width: 60px;
			height: 60px;
			border-radius: 50%;
			display: block;
			margin: 0 auto 10px;
		}
		.profile-dropdown p {
			text-align: center;
			margin: 5px 0;
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
		<li class="active">
			<a href="#"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a>
		</li>
		<li><a href="manageproduct.php"><i class='bx bxs-shopping-bag-alt'></i><span class="text">Product Management</span></a></li>
		<li><a href="order.php"><i class='bx bxs-doughnut-chart'></i><span class="text">Order</span></a></li>
		<li><a href="customer_list.php"><i class='bx bxs-user'></i><span class="text">Customer</span></a></li>
		<li><a href="addadmin.php"><i class='bx bxs-group'></i><span class="text">Admin</span></a></li>
	</ul>
	<ul class="side-menu">
		<li><a href="#"><i class='bx bxs-cog'></i><span class="text">Settings</span></a></li>
		<li><a href="logout.php" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
	</ul>
</section>

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
			<i class='bx bxs-bell'></i><span class="num"></span>
		</a>
		<a href="#" class="profile" id="profileBtn">
			<img src="image/<?php echo htmlspecialchars($admin['profile_pic']); ?>" alt="Profile">
		</a>
		<div class="profile-dropdown" id="profileDropdown">
			<img src="image/<?php echo htmlspecialchars($admin['profile_pic']); ?>" alt="Profile">
			<p><strong><?php echo htmlspecialchars($admin['name']); ?></strong></p>
			<p><?php echo htmlspecialchars($admin['position']); ?></p>
		</div>
	</nav>

	<!-- MAIN -->
	<main>
		<div class="head-title">
			<div class="left">
				<h1>Dashboard</h1>
				<ul class="breadcrumb">
					<li><a href="#">Dashboard</a></li>
					<li><i class='bx bx-chevron-right'></i></li>
					<li><a class="active" href="#">Home</a></li>
				</ul>
			</div>
			<a href="#" class="btn-download">
				<i class='bx bxs-cloud-download'></i>
				<span class="text">Download PDF</span>
			</a>
		</div>
		<!-- Your dashboard content continues here... -->
	</main>
</section>

<script>
	const profileBtn = document.getElementById('profileBtn');
	const profileDropdown = document.getElementById('profileDropdown');

	profileBtn.addEventListener('click', function(event) {
		event.preventDefault();
		profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
	});

	// Close dropdown when clicking outside
	document.addEventListener('click', function(event) {
		if (!profileBtn.contains(event.target) && !profileDropdown.contains(event.target)) {
			profileDropdown.style.display = 'none';
		}
	});
</script>

</body>
</html>
