<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gaming_store");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Validate if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start transaction for atomic operations
    $conn->begin_transaction();
    
    try {
        // 1. Retrieve customer information
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone_number'];
        $street = $_POST['street_address'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $postcode = $_POST['postcode'];
        $country = $_POST['country'];
        $cardName = $_POST['cardholder_name'];
        $cardNumber = $_POST['card_number'];
        $expiryDate = $_POST['expiry_date'];
        $cvv = $_POST['cvv'];
        $totalPrice = $_POST['total_price'];
        $tax = $_POST['tax_fee']; 
        $date = date('Y-m-d H:i:s');
        $userEmail = $_SESSION['email'] ?? $email;

        // 2. Insert order into `orders` table
       $order_status = "Pending";

$stmt = $conn->prepare("INSERT INTO orders 
(email, total_price, tax_fee, first_name, last_name, phone_number, street_address, city, state, postcode, country, cardholder_name, card_number, expiry_date, cvv, date, status_order) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "sddssssssssssssss", 
    $userEmail, $totalPrice, $tax, $firstName, $lastName, $phone, $street, $city, 
    $state, $postcode, $country, $cardName, $cardNumber, $expiryDate, $cvv, $date, $order_status
);

        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        // 3. Process ordered items with stock validation
        $processed_items = [];
        $out_of_stock_items = [];

        if (isset($_POST['order_type']) && $_POST['order_type'] === 'buy_now') {
            // Single product checkout (from checkout2.php)
            $product_id = intval($_POST['product_id']);
            $quantity = intval($_POST['quantity']);
            
            // Verify stock and deduct
            $stock_check = $conn->prepare("SELECT id, product_name, product_price, product_quantity FROM products WHERE id = ? FOR UPDATE");
            $stock_check->bind_param("i", $product_id);
            $stock_check->execute();
            $product = $stock_check->get_result()->fetch_assoc();
            
            if ($product && $product['product_quantity'] >= $quantity) {
                // Deduct stock
                $update_stock = $conn->prepare("UPDATE products SET product_quantity = product_quantity - ? WHERE id = ?");
                $update_stock->bind_param("ii", $quantity, $product_id);
                $update_stock->execute();
                
                // Record order item
                $stmt2 = $conn->prepare("INSERT INTO items_ordered (order_id, product_id, product_name, price_items, quantity_items, status_order, was_in_stock) VALUES (?, ?, ?, ?, ?, 'Paid', TRUE)");
                $stmt2->bind_param("iisdi", $order_id, $product_id, $product['product_name'], $product['product_price'], $quantity);
                $stmt2->execute();
                $stmt2->close();
                
                $processed_items[] = $product['product_name'] . " (x$quantity)";
            } else {
                $out_of_stock_items[] = $product['product_name'] . " (Available: " . ($product['product_quantity'] ?? 0) . ")";
            }
        } else {
            // Cart checkout (from checkout.php)
            foreach ($_POST['cart'] as $item) {
                $product_id = intval($item['product_id']);
                $quantity = intval($item['quantity']);
                
                // Verify stock and deduct
                $stock_check = $conn->prepare("SELECT id, product_name, product_price, product_quantity FROM products WHERE id = ? FOR UPDATE");
                $stock_check->bind_param("i", $product_id);
                $stock_check->execute();
                $product = $stock_check->get_result()->fetch_assoc();
                
                if ($product && $product['product_quantity'] >= $quantity) {
                    // Deduct stock
                    $update_stock = $conn->prepare("UPDATE products SET product_quantity = product_quantity - ? WHERE id = ?");
                    $update_stock->bind_param("ii", $quantity, $product_id);
                    $update_stock->execute();
                    
                    // Record order item
                    $stmt3 = $conn->prepare("INSERT INTO items_ordered (order_id, product_id, product_name, price_items, quantity_items, status_order, was_in_stock) VALUES (?, ?, ?, ?, ?, 'Paid', TRUE)");
                    $stmt3->bind_param("iisdi", $order_id, $product_id, $product['product_name'], $product['product_price'], $quantity);
                    $stmt3->execute();
                    $stmt3->close();
                    
                    $processed_items[] = $product['product_name'] . " (x$quantity)";
                } else {
                    $out_of_stock_items[] = $product['product_name'] . " (Available: " . ($product['product_quantity'] ?? 0) . ")";
                }
            }
            
            // Clear cart only if all items were processed
            if (empty($out_of_stock_items)) {
                $conn->query("DELETE FROM cart_items WHERE email = '$userEmail'");
            }
        }

        // 4. Check if any items were processed
        if (empty($processed_items)) {
            throw new Exception("No items were available for purchase");
        }

        // 5. Update order status to reflect stock update
        $conn->query("UPDATE orders SET stock_updated = TRUE WHERE id = $order_id");

        // 6. Record in stock history (optional)
        // (Implement if you created the stock_history table)

        // Commit transaction if all operations succeeded
        $conn->commit();

        // 7. Prepare success/partial success message
        $message = "✅ Order #$order_id placed successfully!";
        if (!empty($out_of_stock_items)) {
            $message .= "\n\nSome items were out of stock:\n- " . implode("\n- ", $out_of_stock_items);
            $_SESSION['out_of_stock_items'] = $out_of_stock_items;
        }

        // 8. Clear session data and redirect
        unset($_SESSION['single_product']);
        unset($_SESSION['checkout_source']);
        echo "<script>alert('".addslashes($message)."'); window.location.href='orderhistory.php';</script>";
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('❌ Order failed: ".addslashes($e->getMessage())."'); window.location.href='cart.php';</script>";
        exit;
    }
} else {
    echo "❌ Invalid access.";
}
$conn->close();
?>