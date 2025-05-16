<?php
session_start();

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

$admin_id = $_SESSION['admin_id'];

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

// Fetch total orders count for chart
$orderCountQuery = "SELECT COUNT(DISTINCT order_id) AS total_orders FROM orders WHERE order_id > 0";
$result = $conn->query($orderCountQuery);

$totalOrders = 0;
if ($result && $row = $result->fetch_assoc()) {
    $totalOrders = (int)$row['total_orders'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet' />
	<link rel="stylesheet" href="admindashboard.css" />
	<title>Admin</title>
	<!-- Chart.js CDN -->
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<style>
		.chart-container {
			width: 100%;
			max-width: 700px;
			margin: 40px auto;
			background: #fff;
			padding: 20px;
			border-radius: 20px;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
		}
		canvas {
			width: 100% !important;
			height: auto !important;
		}
	</style>
</head>
<body>

	<!-- SIDEBAR -->
	<section id="sidebar">
		<a href="#" class="brand">
			<br />
			<span class="text">Admin Dashboard</span>
		</a>
		<ul class="side-menu top">
			<li class="active">
				<a href="#">
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
			<li>
				<a href="manage_category.php">
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

	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu'></i>
			<a href="managecategory.html" class="nav-link">Categories</a>
			<form action="#">
				<div class="form-input">
					<input type="search" placeholder="Search..." />
					<button type="submit" class="search-btn">
						<i class='bx bx-search'></i>
					</button>
				</div>
			</form>
			<a href="#" class="notification">
				<i class='bx bxs-bell'></i>
				<span class="num"></span>
			</a>
			<a href="profile_admin.php" class="profile">
				<img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Picture" />
			</a>
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

			<ul class="box-info">
				<li>
					<i class='bx bxs-calendar-check'></i>
					<span class="text">
						<h3>1020</h3>
						<p>New Order</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-group'></i>
					<span class="text">
						<h3>2834</h3>
						<p>Visitors</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-dollar-circle'></i>
					<span class="text">
						<h3>RM 2543</h3>
						<p>Total Sales</p>
					</span>
				</li>
			</ul>

			<!-- CHART -->
			<div class="chart-container">
				<canvas id="dashboardChart"></canvas>
			</div>

			<script>
				const ctx = document.getElementById('dashboardChart').getContext('2d');
				new Chart(ctx, {
					type: 'bar',
					data: {
						labels: ['Total Orders'],
						datasets: [{
							label: 'Number of Orders',
							data: [<?php echo $totalOrders; ?>],
							backgroundColor: ['#007bff'],
							borderRadius: 15,
							hoverBackgroundColor: ['#0056b3']
						}]
					},
					options: {
						responsive: true,
						scales: {
							y: {
								beginAtZero: true,
								stepSize: 1
							}
						},
						plugins: {
							legend: {
								display: true,
								labels: {
									color: '#333',
									font: { size: 14 }
								}
							},
							tooltip: {
								enabled: true
							}
						}
					}
				});
			</script>

			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Recent Orders</h3>
						<i class='bx bx-search'></i>
						<i class='bx bx-filter'></i>
					</div>
					<table>
						<thead>
							<tr>
								<th>User</th>
								<th>Date Order</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><img src="image/people1.jpg" /><p>Kevin</p></td>
								<td>01-01-2025</td>
								<td><span class="status completed">Completed</span></td>
							</tr>
							<tr>
								<td><img src="image/people2.jpg" /><p>Brian</p></td>
								<td>06-01-2025</td>
								<td><span class="status pending">Pending</span></td>
							</tr>
							<tr>
								<td><img src="image/woman1.jpg" /><p>Camila</p></td>
								<td>07-02-2025</td>
								<td><span class="status process">Process</span></td>
							</tr>
						</tbody>
					</table>
				</div>

				<div class="todo">
					<div class="head">
						<h3>Recent Customer</h3>
						<i class='bx bx-plus'></i>
						<i class='bx bx-filter'></i>
					</div>
					<ul class="todo-list">
						<li class="completed">
							<p>Kevin</p>
						</li>
						<li class="completed">
							<p>Brian</p>
						</li>
						<li class="not-completed">
							<p>Camila</p>
						</li>
					</ul>
				</div>
			</div>
		</main>
	</section>

	<script src="admindashboard.js"></script>
</body>
</html>
