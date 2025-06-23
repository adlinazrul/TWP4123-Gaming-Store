<?php
session_start();
include 'db_connection.php'; // Make sure this file correctly establishes $conn

// Set up error logging
ini_set('log_errors', 'On');
ini_set('error_log', __DIR__ . '/php_errors.log'); // Log errors to a file in the same directory

// Ensure the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: custlogin.php");
    exit();
}

$email = $_SESSION['email'];
$customer_id = null; // Initialize customer_id

try {
    // Fetch customer ID
    $user_query = $conn->prepare("SELECT id FROM customers WHERE email = ?");
    if (!$user_query) {
        throw new Exception("Failed to prepare customer query: " . $conn->error);
    }
    $user_query->bind_param("s", $email);
    $user_query->execute();
    $user_result = $user_query->get_result();
    $user = $user_result->fetch_assoc();
    if ($user) {
        $customer_id = $user['id'];
    } else {
        throw new Exception("Customer not found for email: " . htmlspecialchars($email));
    }
    $user_query->close();

    // Start a transaction
    $conn->begin_transaction();

    // Collect common form data
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $customer_email = $_POST['email'] ?? ''; // Renamed to avoid conflict with session email
    $phone_number = $_POST['phone_number'] ?? '';
    $street_address = $_POST['street_address'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $postcode = $_POST['postcode'] ?? '';
    $country = $_POST['country'] ?? '';
    $cardholder_name = $_POST['cardholder_name'] ?? '';
    $card_number = str_replace(' ', '', $_POST['card_number'] ?? ''); // Remove spaces
    $expiry_date = $_POST['expiry_date'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $total_price = floatval($_POST['total_price'] ?? 0);
    $tax_fee = floatval($_POST['tax_fee'] ?? 0);
    $order_type = $_POST['order_type'] ?? ''; // 'buy_now' or 'cart'

    // Validate essential fields
    if (empty($first_name) || empty($last_name) || empty($customer_email) || empty($phone_number) || empty($street_address) ||
        empty($city) || empty($state) || empty($postcode) || empty($country) || empty($cardholder_name) ||
        empty($card_number) || empty($expiry_date) || empty($cvv) || $total_price <= 0) {
        throw new Exception("Missing essential order details. Please fill in all required fields.");
    }

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (user_id, first_name, last_name, email, phone_number, street_address, city, state, postcode, country, card_number, cardholder_name, expiry_date, cvv, total_price, tax_fee, status_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Failed to prepare order insertion statement: " . $conn->error);
    }

    $status_order = "Paid"; // Default status for new orders

    // Bind parameters for orders table insertion
    $stmt->bind_param(
        "isssssssssssssdds", // s for strings, d for decimal/double, i for integer
        $customer_id,
        $first_name,
        $last_name,
        $customer_email,
        $phone_number,
        $street_address,
        $city,
        $state,
        $postcode,
        $country,
        $card_number,
        $cardholder_name,
        $expiry_date,
        $cvv,
        $total_price,
        $tax_fee,
        $status_order
    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to execute order insertion: " . $stmt->error);
    }

    $order_id = $stmt->insert_id; // Get the ID of the newly inserted order
    $stmt->close();

    if ($order_id === 0) {
        throw new Exception("Order ID not generated.");
    }

    $was_in_stock = 1; // Default for items that were available at time of order

    // Process items (either single product or cart)
    if ($order_type === 'buy_now') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);

        if ($product_id <= 0 || $quantity <= 0) {
            throw new Exception("Invalid product ID or quantity for Buy Now.");
        }

        // Fetch product details for items_ordered table, including product_image
        $product_query = $conn->prepare("SELECT product_name, product_price, product_quantity, product_image FROM products WHERE id = ? FOR UPDATE");
        if (!$product_query) {
            throw new Exception("Failed to prepare product query: " . $conn->error);
        }
        $product_query->bind_param("i", $product_id);
        $product_query->execute();
        $product_result = $product_query->get_result();
        $product = $product_result->fetch_assoc();
        $product_query->close();

        // Check if product exists
        if (!$product) {
            throw new Exception("Product with ID " . $product_id . " not found.");
        }

        // Re-check stock to be safe (though checkout2.php does it)
        if ($product['product_quantity'] < $quantity) {
            throw new Exception("Product " . htmlspecialchars($product['product_name']) . " is no longer in stock for the requested quantity. Available: " . $product['product_quantity']);
        }

        // Prepare and execute insertion into items_ordered
        $stmt_items = $conn->prepare("INSERT INTO items_ordered (order_id, product_id, product_name, price_items, quantity_items, image_items, status_order, was_in_stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt_items) {
            throw new Exception("Failed to prepare items_ordered insertion statement: " . $conn->error);
        }

        $stmt_items->bind_param("iisdisii",
            $order_id,
            $product_id,
            $product['product_name'],
            $product['product_price'],
            $quantity,
            $product['product_image'],
            $status_order,
            $was_in_stock
        );

        if (!$stmt_items->execute()) {
            throw new Exception("Failed to execute items_ordered insertion for product " . htmlspecialchars($product['product_name']) . ": " . $stmt_items->error);
        }
        $stmt_items->close();

        // Update product stock
        $update_stock = $conn->prepare("UPDATE products SET product_quantity = product_quantity - ? WHERE id = ?");
        if (!$update_stock) {
            throw new Exception("Failed to prepare stock update statement: " . $conn->error);
        }
        $update_stock->bind_param("ii", $quantity, $product_id);
        if (!$update_stock->execute()) {
            throw new Exception("Failed to update stock for product " . htmlspecialchars($product['product_name']) . ": " . $update_stock->error);
        }
        $update_stock->close();

    } elseif ($order_type === 'cart') {
        // Retrieve cart items from the POST data submitted by checkout.php
        $cart_items_from_post = $_POST['cart'] ?? [];

        if (empty($cart_items_from_post)) {
            throw new Exception("Your cart is empty or no items were submitted from the checkout form. Cannot process order.");
        }

        foreach ($cart_items_from_post as $cart_item) {
            $product_id = intval($cart_item['product_id'] ?? 0);
            $quantity = intval($cart_item['quantity'] ?? 1);

            if ($product_id <= 0 || $quantity <= 0) {
                error_log("Skipping invalid cart item during processing: product_id=" . $product_id . ", quantity=" . $quantity);
                continue; // Skip to the next item
            }

            // Fetch product details for items_ordered table, including product_image
            $product_query = $conn->prepare("SELECT product_name, product_price, product_quantity, product_image FROM products WHERE id = ? FOR UPDATE");
            if (!$product_query) {
                throw new Exception("Failed to prepare product query for cart item: " . $conn->error);
            }
            $product_query->bind_param("i", $product_id);
            $product_query->execute();
            $product_result = $product_query->get_result();
            $product = $product_result->fetch_assoc();
            $product_query->close();

            // Check if product exists
            if (!$product) {
                throw new Exception("Product with ID " . $product_id . " not found in cart processing.");
            }

            // Re-check stock just before finalizing the order
            if ($product['product_quantity'] < $quantity) {
                throw new Exception("Cart item " . htmlspecialchars($product['product_name']) . " is out of stock. Only " . $product['product_quantity'] . " available.");
            }

            // Prepare and execute insertion into items_ordered
            $stmt_items = $conn->prepare("INSERT INTO items_ordered (order_id, product_id, product_name, price_items, quantity_items, image_items, status_order, was_in_stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt_items) {
                throw new Exception("Failed to prepare items_ordered insertion statement for cart item: " . $conn->error);
            }

            $stmt_items->bind_param("iisdisii",
                $order_id,
                $product_id,
                $product['product_name'],
                $product['product_price'],
                $quantity,
                $product['product_image'],
                $status_order,
                $was_in_stock
            );

            if (!$stmt_items->execute()) {
                throw new Exception("Failed to execute items_ordered insertion for cart product " . htmlspecialchars($product['product_name']) . ": " . $stmt_items->error);
            }
            $stmt_items->close();

            // Update product stock
            $update_stock = $conn->prepare("UPDATE products SET product_quantity = product_quantity - ? WHERE id = ?");
            if (!$update_stock) {
                throw new Exception("Failed to prepare stock update statement for cart item: " . $conn->error);
            }
            $update_stock->bind_param("ii", $quantity, $product_id);
            if (!$update_stock->execute()) {
                throw new Exception("Failed to update stock for cart product " . htmlspecialchars($product['product_name']) . ": " . $update_stock->error);
            }
            $update_stock->close();
        }

        // After successfully processing all cart items and updating stock, clear the user's cart from the database
        $clear_cart_query = $conn->prepare("DELETE FROM cart_items WHERE email = ?");
        if (!$clear_cart_query) {
            throw new Exception("Failed to prepare clear cart query: " . $conn->error);
        }
        $clear_cart_query->bind_param("s", $email);
        if (!$clear_cart_query->execute()) {
            throw new Exception("Failed to clear cart after checkout: " . $clear_cart_query->error);
        }
        $clear_cart_query->close();

    } else {
        throw new Exception("Invalid order type specified. Order type was: " . htmlspecialchars($order_type));
    }

    // Update stock_updated status in orders table
    $update_order_status = $conn->prepare("UPDATE orders SET stock_updated = TRUE WHERE id = ?");
    if (!$update_order_status) {
        throw new Exception("Failed to prepare final order status update: " . $conn->error);
    }
    $update_order_status->bind_param("i", $order_id);
    if (!$update_order_status->execute()) {
        throw new Exception("Failed to set stock_updated for order " . $order_id . ": " . $update_order_status->error);
    }
    $update_order_status->close();

    // Commit the transaction
    $conn->commit();

    // Redirect to a success page or order history
    header("Location: ORDERHISTORY.php?order_success=1&order_id=" . $order_id);
    exit();

} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    error_log("Order processing failed: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    echo "<script>alert('Order failed: " . htmlspecialchars($e->getMessage()) . "'); window.location.href='index.php';</script>";
    exit();
} finally {
    if (isset($conn) && $conn->ping()) { // Check if connection is still alive before closing
        $conn->close();
    }
}
?>