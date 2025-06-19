ORDERHISTORY.php

<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['email'])) {
    header("Location: custlogin.php");
    exit();
}

$email = $_SESSION['email'];

// Get customer ID
$customer_query = $conn->prepare("SELECT id FROM customers WHERE email = ?");
$customer_query->bind_param("s", $email);
$customer_query->execute();
$customer_result = $customer_query->get_result();
$customer = $customer_result->fetch_assoc();
$customer_id = $customer['id'];

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_rating'])) {
    $order_id = intval($_POST['order_id']);
    $product_id = intval($_POST['product_id']);
    $rating = intval($_POST['rating']);
    $review = $conn->real_escape_string($_POST['review']);

    $verify_query = $conn->prepare("SELECT i.id FROM items_ordered i JOIN orders o ON i.order_id = o.id WHERE i.order_id = ? AND o.user_id = ? AND i.status_order = 'Delivered'");
    $verify_query->bind_param("ii", $order_id, $customer_id);
    $verify_query->execute();
    $verify_result = $verify_query->get_result();

    if ($verify_result->num_rows > 0) {
        $rating_query = $conn->prepare("INSERT INTO rating (order_id, product_id, customer_id, rating, review) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating), review = VALUES(review)");
        $rating_query->bind_param("iiiis", $order_id, $product_id, $customer_id, $rating, $review);
        $rating_query->execute();
    }

    header("Location: ORDERHISTORY.php?rating_success=1");
    exit();
}

// Fetch order history
$orders_query = $conn->prepare("SELECT o.id as order_id, o.date, i.*, p.product_name, p.product_image FROM orders o JOIN items_ordered i ON o.id = i.order_id JOIN products p ON i.product_name = p.product_name WHERE o.user_id = ? ORDER BY o.date DESC");
$orders_query->bind_param("i", $customer_id);
$orders_query->execute();
$orders_result = $orders_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body class="bg-light">
<div class="container py-5">

    <?php if (isset($_GET['rating_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Your rating has been submitted!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Your Order History</h2>
        <div>
            <a href="index.php" class="btn btn-sm btn-secondary me-2">🏠 Home</a>
            <a href="all_product_user.php" class="btn btn-sm btn-success">🛒 Continue Shopping</a>
        </div>
    </div>

    <?php if ($orders_result->num_rows > 0): ?>
        <div class="row">
            <?php while ($order = $orders_result->fetch_assoc()):

                $product_name = $order['product_name'];
                $product_id = null;
                $prod_id_stmt = $conn->prepare("SELECT id FROM products WHERE product_name = ?");
                $prod_id_stmt->bind_param("s", $product_name);
                $prod_id_stmt->execute();
                $prod_id_result = $prod_id_stmt->get_result();
                if ($prod_row = $prod_id_result->fetch_assoc()) {
                    $product_id = $prod_row['id'];
                }
                $prod_id_stmt->close();

                $rated_query = $conn->prepare("SELECT rating, review FROM rating WHERE order_id = ? AND product_id = ? AND customer_id = ?");
                $rated_query->bind_param("iii", $order['order_id'], $product_id, $customer_id);
                $rated_query->execute();
                $rated_result = $rated_query->get_result();
                $rated = $rated_result->fetch_assoc();
                $rated_query->close();
            ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <img src="uploads/<?= htmlspecialchars($order['product_image']) ?>" 
                                         onerror="this.src='uploads/default.png';"
                                         alt="<?= htmlspecialchars($order['product_name']) ?>" 
                                         class="img-fluid rounded">
                                </div>
                                <div class="col-md-8">
                                    <h5><?= htmlspecialchars($order['product_name']) ?></h5>
                                    <p>
                                        <strong>Quantity:</strong> <?= $order['quantity_items'] ?><br>
                                        <strong>Price:</strong> RM <?= number_format($order['price_items'], 2) ?><br>
                                        <strong>Total:</strong> RM <?= number_format($order['price_items'] * $order['quantity_items'], 2) ?><br>
                                        <strong>Date:</strong> <?= date('d M Y, h:i A', strtotime($order['date'])) ?><br>
                                        <strong>Delivery Status:</strong> 
                                        <span class="<?= $order['status_order'] == 'Delivered' ? 'text-success' : 'text-warning' ?>">
                                            <?= $order['status_order'] ?>
                                        </span>
                                    </p>

                                    <?php if ($order['status_order'] == 'Delivered' && $product_id !== null): ?>
                                        <hr>
                                        <h6>Rate this product:</h6>
                                        <form method="POST" action="">
                                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                            <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                            <input type="hidden" name="rating" id="rating_input_<?= $order['order_id'] ?>_<?= $product_id ?>" value="<?= $rated ? $rated['rating'] : 0 ?>">

                                            <div class="rating-stars mb-2" data-input-id="rating_input_<?= $order['order_id'] ?>_<?= $product_id ?>">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="<?= ($rated && $i <= $rated['rating']) ? 'fas' : 'far' ?> fa-star text-warning me-1" data-rating="<?= $i ?>"></i>
                                                <?php endfor; ?>
                                            </div>

                                            <div class="mb-2">
                                                <textarea name="review" class="form-control" placeholder="Your review (optional)"><?= $rated ? htmlspecialchars($rated['review']) : '' ?></textarea>
                                            </div>
                                            
                                            <button type="submit" name="submit_rating" class="btn btn-sm btn-primary">
                                                <?= $rated ? 'Update Rating' : 'Submit Rating' ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">You haven't placed any orders yet.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Star rating logic
document.querySelectorAll('.rating-stars').forEach(container => {
    const inputId = container.dataset.inputId;
    const hiddenInput = document.getElementById(inputId);
    const stars = container.querySelectorAll('i');

    stars.forEach(star => {
        star.addEventListener('click', () => {
            const rating = parseInt(star.dataset.rating);
            hiddenInput.value = rating;

            stars.forEach((s, i) => {
                s.classList.toggle('fas', i < rating);
                s.classList.toggle('far', i >= rating);
            });
        });
    });
});
</script>
</body>
</html>
