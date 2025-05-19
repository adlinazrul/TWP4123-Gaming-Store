<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "gaming_store";

try {
    $conn = new mysqli($host, $username, $password, $database);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if form was submitted via POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate and sanitize input
        $required_fields = ['firstname', 'lastname', 'email', 'address', 'city', 'postcode', 'state', 
                          'cardname', 'cardnumber', 'expmonth', 'expyear', 'cvv'];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Get and sanitize input
        $first_name   = $conn->real_escape_string($_POST['firstname']);
        $last_name    = $conn->real_escape_string($_POST['lastname']);
        $email        = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $phone        = $conn->real_escape_string($_POST['phone'] ?? '');
        $address      = $conn->real_escape_string($_POST['address']);
        $city         = $conn->real_escape_string($_POST['city']);
        $postcode          = $conn->real_escape_string($_POST['postcode']);
        $state        = $conn->real_escape_string($_POST['state']);
        $card_name    = $conn->real_escape_string($_POST['cardname']);
        $card_number  = $conn->real_escape_string($_POST['cardnumber']);
        $exp_month    = $conn->real_escape_string($_POST['expmonth']);
        $exp_year     = $conn->real_escape_string($_POST['expyear']);
        $cvv          = $conn->real_escape_string($_POST['cvv']);
        $order_total  = floatval($_POST['order_total'] ?? 0.0);

        // Prepare and execute query
        $sql = "INSERT INTO buynow 
                (first_name, last_name, email, phone, address, city, postcode, state, 
                 card_name, card_number, exp_month, exp_year, cvv, order_total) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Preparation error: " . $conn->error);
        }

        $stmt->bind_param("sssssssssssssd", $first_name, $last_name, $email, $phone, $address, $city, $postcode, $state,
                          $card_name, $card_number, $exp_month, $exp_year, $cvv, $order_total);

        if (!$stmt->execute()) {
            throw new Exception("Execution error: " . $stmt->error);
        }

        $stmt->close();
        $conn->close();
        
        // Success response
        echo json_encode(['success' => true, 'message' => 'Order placed successfully!']);
        exit;
    } else {
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    // Error response
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
?>