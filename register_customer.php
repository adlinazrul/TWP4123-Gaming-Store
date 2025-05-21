 <?php
include 'db_connect1.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = sanitize($_POST["first_name"] ?? '');
    $last_name = sanitize($_POST["last_name"] ?? '');
    $username = trim($_POST['username']);
    $email = sanitize($_POST["email"] ?? '');
    $phone = sanitize($_POST["phone"] ?? '');
    $address = sanitize($_POST["address"] ?? '');
    $city = sanitize($_POST["city"] ?? '');
    $state = sanitize($_POST["state"] ?? '');
    $postcode = sanitize($_POST["postcode"] ?? '');
    $country = sanitize($_POST["country"] ?? '');
    $password = password_hash($_POST["password"] ?? '', PASSWORD_BCRYPT);


    $sql = "INSERT INTO customers 
        (first_name, last_name, username, email, phone, address, city, state, postcode, country, password) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "sssssssssss",
        $first_name, $last_name, $username, $email, $phone,
        $address, $city, $state, $postcode, $country, $password
    );

    if ($stmt->execute()) {
        echo "New customer registered successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
