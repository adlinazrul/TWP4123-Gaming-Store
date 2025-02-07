<?php
// Ensure form submission method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $contact = $_POST["contact"];
    $address = $_POST["address"];
    $payment_method = $_POST["payment_method"];

    // Optional: Handle credit card or bank details
    if ($payment_method == "credit-card") {
        $cc_number = $_POST["cc-number"];
        $cc_name = $_POST["cc-name"];
        $cc_expiry = $_POST["cc-expiry"];
        $cc_cvv = $_POST["cc-cvv"];
    } elseif ($payment_method == "bank") {
        $bank_name = $_POST["bank-name"];
    }

    // Validate and store/process payment info (Dummy response for now)
    echo "<h2>Payment Successful!</h2>";
    echo "Thank you, <b>$email</b>. Your payment via <b>$payment_method</b> has been processed!";
} else {
    echo "Invalid request!";
}
?>
