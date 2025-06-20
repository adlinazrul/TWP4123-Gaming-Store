<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

// DB connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch order status counts for the chart
$statusCounts = [
    'pending' => 0,
    'processing' => 0,
    'completed' => 0,
    'cancelled' => 0
];

$statusQuery = "SELECT status_order, COUNT(DISTINCT order_id) as count FROM items_ordered GROUP BY status_order";
$result = $conn->query($statusQuery);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower($row['status_order']);
        if (isset($statusCounts[$status])) {
            $statusCounts[$status] = (int)$row['count'];
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard - Order Status Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --light: #f9f9f9;
            --red: #a93226;
            --light-red: #f5d0ce;
            --dark-red: #7d241b;
            --grey: #eee;
            --dark-grey: #777777;
            --dark: #342e37;
            --yellow: #ffce26;
            --light-yellow: #fff2c6;
            --orange: #fd7238;
            --light-orange: #ffe0d3;
            --green: #28a745;
            --light-green: #d1f5d9;
            --teal: #17a2b8;
            --light-teal: #d1f0f5;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        .chart-container {
            width: 100%;
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .chart-container:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        canvas {
            width: 100% !important;
            height: auto !important;
        }

        .chart-title {
            text-align: center;
            color: var(--dark);
            margin-bottom: 20px;
            font-size: 24px;
        }

        @media (max-width: 768px) {
            .chart-container {
                padding: 15px;
                border-radius: 15px;
            }
            
            .chart-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- CHART CONTAINER -->
    <div class="chart-container">
        <h2 class="chart-title">Order Status Overview</h2>
        <canvas id="dashboardChart"></canvas>
    </div>

    <script>
        const ctx = document.getElementById('dashboardChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Processing', 'Completed', 'Cancelled'],
                datasets: [{
                    label: 'Order Status',
                    data: [
                        <?php echo $statusCounts['pending']; ?>,
                        <?php echo $statusCounts['processing']; ?>,
                        <?php echo $statusCounts['completed']; ?>,
                        <?php echo $statusCounts['cancelled']; ?>
                    ],
                    backgroundColor: [
                        'rgba(255, 193, 7, 0.7)', // Yellow for pending
                        'rgba(253, 114, 56, 0.7)', // Orange for processing
                        'rgba(40, 167, 69, 0.7)', // Green for completed
                        'rgba(169, 50, 38, 0.7)'  // Red for cancelled
                    ],
                    borderColor: [
                        'rgba(255, 193, 7, 1)',
                        'rgba(253, 114, 56, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(169, 50, 38, 1)'
                    ],
                    borderWidth: 1,
                    borderRadius: 8,
                    hoverBackgroundColor: [
                        'rgba(255, 193, 7, 1)',
                        'rgba(253, 114, 56, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(169, 50, 38, 1)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    }
                },
                // Touch interactions configuration
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                // For mobile responsiveness
                onHover: (event, chartElement) => {
                    event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                },
                onClick: (event, elements) => {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const status = ['pending', 'processing', 'completed', 'cancelled'][index];
                        alert(`You clicked on ${status} orders: ${chart.data.datasets[0].data[index]} orders`);
                        // You could also redirect to a filtered orders page:
                        // window.location.href = `orders.php?status=${status}`;
                    }
                }
            }
        });

        // Make chart responsive on window resize
        window.addEventListener('resize', function() {
            chart.resize();
        });
    </script>
</body>
</html>