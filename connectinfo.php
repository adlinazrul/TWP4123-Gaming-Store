<?php
// Database connection
$servername = "localhost"; // Change this if using an external server
$username = "root";        // Your MySQL username
$password = "";            // Your MySQL password
$dbname = "gaming_store";  // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure form submission method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? "";
    $contact = $_POST["contact"] ?? "";
    $address = $_POST["address"] ?? "";
    $payment_method = $_POST["payment_method"] ?? "";

    // Check if required fields are empty
    if (empty($email) || empty($contact) || empty($address) || $payment_method == "choose_payment") {
        die("<h3>Error: All required fields must be filled!</h3> <a href='payment.html'>Go Back</a>");
    }

    // Handle optional fields
    $cc_number = $_POST["cc-number"] ?? null;
    $cc_name = $_POST["cc-name"] ?? null;
    $cc_expiry = $_POST["cc-expiry"] ?? null;
    $cc_cvv = $_POST["cc-cvv"] ?? null;
    $bank_name = $_POST["bank-name"] ?? null;

    // Insert data into database
    $sql = "INSERT INTO payments (email, contact, address, payment_method, cc_number, cc_name, cc_expiry, cc_cvv, bank_name) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $email, $contact, $address, $payment_method, $cc_number, $cc_name, $cc_expiry, $cc_cvv, $bank_name);

    if ($stmt->execute()) {
        echo "<h2>Payment Successful!</h2>";
        echo "Thank you, <b>$email</b>. Your payment via <b>$payment_method</b> has been recorded!";
    } else {
        echo "<h3>Error: " . $stmt->error . "</h3>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request!";
}
?>
