<?php
$servername = "localhost";
$username = "root";  // Your database username
$password = "";  // Your database password
$dbname = "gaming_store";  // Your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $salary = $_POST['salary'];
    $password = $_POST['password'];

    // Image upload handling
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image);
    } else {
        $image = NULL; // No image uploaded
    }

    // Prepare the SQL query to insert the data into the table
    $sql = "INSERT INTO admin_staff (username, email, position, salary, password, image)
            VALUES ('$username', '$email', '$position', '$salary', '$password', '$image')";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
