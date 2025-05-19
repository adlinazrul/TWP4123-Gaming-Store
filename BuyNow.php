<?php
// Database configuration
require_once 'config.php'; // Contains $host, $user, $password, $dbname

try {
    $conn = new mysqli($host, $user, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Pagination setup
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $itemsPerPage = 12;
    $offset = ($page - 1) * $itemsPerPage;

    // Get total products count
    $countQuery = $conn->query("SELECT COUNT(*) as total FROM products");
    $totalProducts = $countQuery->fetch_assoc()['total'];
    $totalPages = ceil($totalProducts / $itemsPerPage);

    // Get products with pagination
    $sql = "SELECT * FROM products ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $itemsPerPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

} catch (Exception $e) {
    error_log($e->getMessage());
    $error = "We're experiencing technical difficulties. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - NEXUS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Your existing CSS plus these additions */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 10px;
        }
        .pagination a {
            color: #00ffc3;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #00ffc3;
            border-radius: 5px;
        }
        .pagination a.active {
            background-color: #00ffc3;
            color: #000;
        }
        .pagination a:hover:not(.active) {
            background-color: rgba(0, 255, 195, 0.1);
        }
        .error-message {
            color: #ff4444;
            text-align: center;
            padding: 20px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="product-container">
        <h1>All Products</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php else: ?>
            <div class="product-grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="product-card">
                            <div class="product-image" style="background-image: url('<?php echo htmlspecialchars($row['product_image']); ?>');"></div>
                            <div class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></div>
                            <div class="product-description"><?php echo htmlspecialchars($row['product_description']); ?></div>
                            <div class="product-price">RM <?php echo number_format($row['product_price'], 2); ?></div>
                            <div class="product-category">Category: <?php echo htmlspecialchars($row['product_category']); ?></div>
                            <a class="view-btn" href="product_detail.php?id=<?php echo $row['id']; ?>">View Product</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align: center;">
                        <p>No products available.</p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" <?php echo $i == $page ? 'class="active"' : ''; ?>>
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Close connections
if (isset($stmt)) $stmt->close();
if (isset($conn)) $conn->close();
?>