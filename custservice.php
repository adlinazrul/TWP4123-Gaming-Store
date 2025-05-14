<?php
include 'db_connect1.php'; // Correctly include the database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate form inputs
    $name = htmlspecialchars(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL);
    $subject = htmlspecialchars(trim($_POST["subject"]));
    $message = htmlspecialchars(trim($_POST["message"]));

    // Basic validation
    if (!$name || !$email || !$subject || !$message) {
        echo "<h2>Error: All fields are required and email must be valid.</h2>";
        exit;
    }

    // Prepare and execute the SQL statement
    $sql = "INSERT INTO cust_service (name, email, subject, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $subject, $message);

    if ($stmt->execute()) {
        echo "<h2>Thank you, $name!</h2>";
        echo "<p>Your message has been received and we will get back to you soon.</p>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Subject:</strong> $subject</p>";
        echo "<p><strong>Message:</strong> $message</p>";
    } else {
        echo "<h2>Error: " . $stmt->error . "</h2>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<h2>Invalid Request</h2>";
}
?>
