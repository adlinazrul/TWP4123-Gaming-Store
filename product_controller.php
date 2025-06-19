<?php
// product_controller.php
require_once 'db_connect.php';

class ProductController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get product stock
    public function getStock($product_id) {
        $stmt = $this->conn->prepare("SELECT product_quantity FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($quantity);
        $stmt->fetch();
        $stmt->close();
        return $quantity;
    }

    // Reduce stock after purchase
    public function reduceStock($product_id, $qty) {
        $stmt = $this->conn->prepare("
            UPDATE products 
            SET product_quantity = product_quantity - ? 
            WHERE id = ? AND product_quantity >= ?
        ");
        $stmt->bind_param("iii", $qty, $product_id, $qty);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        $stmt->close();
        return $success;
    }

    // Increase stock (e.g. refund, cancel)
    public function addStock($product_id, $qty) {
        $stmt = $this->conn->prepare("
            UPDATE products 
            SET product_quantity = product_quantity + ? 
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $qty, $product_id);
        $stmt->execute();
        $stmt->close();
    }

    // ✅ NEW: Get all cart items with validated stock
    public function getCartWithStockValidation($email) {
        $stmt = $this->conn->prepare("
            SELECT ci.product_id, ci.quantity AS cart_quantity, p.product_name, 
                   p.product_price, p.product_image, p.product_quantity
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.email = ?
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $raw_cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $cart_items = [];
        foreach ($raw_cart_items as $item) {
            $product_id = $item['product_id'];
            $cart_quantity = (int)$item['cart_quantity'];
            $stock_quantity = (int)$item['product_quantity'];

            // Remove if stock is 0 or less
            if ($stock_quantity <= 0) {
                $removeStmt = $this->conn->prepare("DELETE FROM cart_items WHERE email = ? AND product_id = ?");
                $removeStmt->bind_param("si", $email, $product_id);
                $removeStmt->execute();
                $removeStmt->close();
                continue;
            }

            // Adjust cart if more than available stock
            if ($cart_quantity > $stock_quantity) {
                $updateStmt = $this->conn->prepare("UPDATE cart_items SET quantity = ? WHERE email = ? AND product_id = ?");
                $updateStmt->bind_param("isi", $stock_quantity, $email, $product_id);
                $updateStmt->execute();
                $updateStmt->close();
                $item['cart_quantity'] = $stock_quantity;
            }

            $item['quantity'] = $item['cart_quantity']; // final quantity
            $cart_items[] = $item;
        }

        return $cart_items;
    }
}
