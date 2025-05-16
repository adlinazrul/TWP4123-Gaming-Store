<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

// DB connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch admin profile image
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

// Fetch total unique orders count from items_ordered table
$orderCountQuery = "SELECT COUNT(DISTINCT order_id) AS total_orders FROM items_ordered";
$result = $conn->query($orderCountQuery);
$totalOrders = 0;
if ($result && $row = $result->fetch_assoc()) {
    $totalOrders = (int)$row['total_orders'];
}

// Fetch recent 5 orders grouped by order_id, showing order_id, total price, date, status, customer name
$recentOrders = [];
$sql = "
    SELECT 
        order_id,
        name_cust,
        date,
        status_order,
        SUM(price_items * quantity_items) AS total_price,
        COUNT(*) AS items_count
    FROM items_ordered
    GROUP BY order_id, name_cust, date, status_order
    ORDER BY date DESC
    LIMIT 5
";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentOrders[] = $row;
    }
}

// Fetch recent 5 distinct customers (name_cust) from items_ordered ordered by latest date
$recentCustomers = [];
$sqlCust = "
    SELECT DISTINCT name_cust 
    FROM items_ordered
    WHERE name_cust IS NOT NULL AND name_cust != ''
    ORDER BY date DESC
    LIMIT 5
";
$resultCust = $conn->query($sqlCust);
if ($resultCust) {
    while ($row = $resultCust->fetch_assoc()) {
        $recentCustomers[] = $row['name_cust'];
    }
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
	<title>Admin Dashboard</title>
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
		.status {
			padding: 4px 10px;
			border-radius: 12px;
			color: white;
			font-weight: 600;
			font-size: 0.85rem;
		}
		.status.completed { background-color: #28a745; }
		.status.pending { background-color: #ffc107; }
		.status.process { background-color: #17a2b8; }
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
						<h3><?php echo $totalOrders; ?></h3>
						<p>New Orders</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-group'></i>
					<span class="text">
						<h3><?php echo count($recentCustomers); ?></h3>
						<p>Recent Customers</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-dollar-circle'></i>
					<span class="text">
						<h3>RM 
							<?php 
								// Calculate total sales (sum of price_items * quantity_items)
								$conn2 = new mysqli($servername, $username, $password, $dbname);
								$salesResult = $conn2->query("SELECT SUM(price_items * quantity_items) AS total_sales FROM items_ordered");
								$totalSales = 0;
								if ($salesResult && $salesRow = $salesResult->fetch_assoc()) {
									$totalSales = $salesRow['total_sales'] ?? 0;
								}
								$conn2->close();
								echo number_format($totalSales, 2);
							?>
						</h3>
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
						scales: {
							y: {
								beginAtZero: true,
								ticks: { stepSize: 1 }
							}
						},
						plugins: {
							legend: { display: false }
						}
					}
				});
			</script>

			<!-- RECENT ORDERS TABLE -->
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Recent Orders</h3>
					</div>
					<table>
						<thead>
							<tr>
								<th>Order ID</th>
								<th>Customer</th>
								<th>Date</th>
								<th>Status</th>
								<th>Total Price (RM)</th>
							</tr>
						</thead>
						<tbody>
							<?php if (count($recentOrders) > 0): ?>
								<?php foreach ($recentOrders as $order): ?>
									<tr>
										<td><?php echo htmlspecialchars($order['order_id']); ?></td>
										<td><?php echo htmlspecialchars($order['name_cust']); ?></td>
										<td><?php echo htmlspecialchars($order['date']); ?></td>
										<td>
											<?php
												$statusClass = '';
												switch (strtolower($order['status_order'])) {
													case 'completed': $statusClass = 'completed'; break;
													case 'pending': $statusClass = 'pending'; break;
													case 'process': $statusClass = 'process'; break;
													default: $statusClass = 'pending';
												}
											?>
											<span class="status <?php echo $statusClass; ?>">
												<?php echo htmlspecialchars($order['status_order']); ?>
											</span>
										</td>
										<td><?php echo number_format($order['total_price'], 2); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr><td colspan="5" style="text-align:center;">No orders found</td></tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<!-- RECENT CUSTOMERS -->
				<div class="customers">
					<div class="cardHeader">
						<h2>Recent Customers</h2>
					</div>
					<ul class="todo-list">
						<?php if (count($recentCustomers) > 0): ?>
							<?php foreach ($recentCustomers as $cust): ?>
								<li><?php echo htmlspecialchars($cust); ?></li>
							<?php endforeach; ?>
						<?php else: ?>
							<li>No customers found</li>
						<?php endif; ?>
					</ul>
				</div>
			</div>

		</main>
	</section>

	<script>
		// Sidebar toggle
		let sidebar = document.querySelector("#sidebar");
		let sidebarBtn = document.querySelector("nav .bx-menu");

		sidebarBtn.addEventListener("click", () => {
			sidebar.classList.toggle("active");
		});
	</script>
</body>
</html>
