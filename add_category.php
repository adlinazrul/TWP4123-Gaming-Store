<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Get and sanitize inputs
$categoryName = trim($_POST['categoryName']);
$categoryDescription = trim($_POST['categoryDescription']);

if (!empty($categoryName) && !empty($categoryDescription)) {
    $stmt = $conn->prepare("INSERT INTO product_categories (category_name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $categoryName, $categoryDescription);

    if ($stmt->execute()) {
        echo "<script>alert('Category added successfully!'); window.location.href='manage_categories.php';</script>";
    } else {
        echo "<script>alert('Error: Could not insert category.'); window.history.back();</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
}

$conn->close();
?>
