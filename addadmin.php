<?php

session_start();

// Check if the session variable is set
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
} else {
    header("Location: login_admin.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $user_type = $_POST['user_type'];

    $target_dir = "uploads/";
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

    $check_email = "SELECT * FROM admin_list WHERE email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Error: Email already exists!'); window.location.href='admindashboard.php';</script>";
    } else {
        $sql = "INSERT INTO admin_list (username, email, password, image, user_type) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $email, $password, $image_name, $user_type);

        if ($stmt->execute()) {
            echo "<script>alert('Admin added successfully!'); window.location.href='admindashboard.php';</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
    }
    $stmt->close();
}

$sql = "SELECT * FROM admin_list";
$result = $conn->query($sql);

if ($admin_id) {
    $query = "SELECT image FROM admin_list WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($image);
    if ($stmt->fetch() && !empty($image)) {
        $profile_image = 'image/' . $image;
    } else {
        $profile_image = 'image/default_profile.jpg';
    }
    $stmt->close();
} else {
    $profile_image = 'image/default_profile.jpg';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin</title>
<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="manageadmin.css" />
<style>
    .container {
        display: flex;
        flex-direction: column;
        gap: 40px;
        padding: 20px;
    }

    #add-employee, #view-employees {
        background: #ffffff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    form {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 15px;
        align-items: center;
    }

    form label {
        text-align: right;
        font-weight: bold;
    }

    form input[type="search"] {
        width: 300px;
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    form input[type="text"],
    form input[type="email"],
    form input[type="number"],
    form input[type="password"],
    form input[type="file"] {
        width: 100%;
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .role-buttons {
        display: flex;
        gap: 15px;
        justify-content: start;
    }

    .role-buttons input[type="radio"] {
        display: none;
    }

    .role-buttons label {
        cursor: pointer;
        padding: 10px 20px;
        background-color: #ef4444;
        color: white;
        border-radius: 5px;
        font-weight: bold;
        user-select: none;
        transition: background-color 0.3s;
    }

    .role-buttons input[type="radio"]:checked + label {
        background-color: #dc2626;
        box-shadow: 0 0 10px #dc2626;
    }

    form button[type="submit"] {
        grid-column: 2;
        padding: 10px 20px;
        background-color: #ef4444;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
    }

    form button[type="submit"]:hover {
        background-color: #dc2626;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table th, table td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }

    table img {
        border-radius: 5px;
    }

    table button {
        padding: 5px 10px;
        background-color: #ef4444;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin: 0 5px;
    }

    table button:hover {
        background-color: #dc2626;
    }
</style>
</head>
<body>

<section id="sidebar">
    <a href="#" class="brand"><br><span class="text">Admin Dashboard</span></a>
    <ul class="side-menu top">
        <li><a href="admindashboard.php"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
        <li><a href="manageproduct.php"><i class='bx bxs-shopping-bag-alt'></i><span class="text">Product Management</span></a></li>
        <li><a href="manage_category.php"><i class='bx bxs-category'></i><span class="text">Category Management</span></a></li>
        <li><a href="order.php"><i class='bx bxs-doughnut-chart'></i><span class="text">Order</span></a></li>
        <li><a href="customer_list.php"><i class='bx bxs-user'></i><span class="text">Customer</span></a></li>
        <li class="active"><a href="#"><i class='bx bxs-group'></i><span class="text">Admin</span></a></li>
    </ul>
    <ul class="side-menu">
        <li><a href="#"><i class='bx bxs-cog'></i><span class="text">Settings</span></a></li>
        <li><a href="index.html" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
    </ul>
</section>

<section id="content">
    <nav>
        <form action="#">
            <div class="form-input">
                <input type="search" placeholder="Search...">
                <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
            </div>
        </form>
        <a href="#" class="notification"><i class='bx bxs-bell'></i></a>
        <a href="profile_admin.php" class="profile"><img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Picture"></a>
    </nav>

    <main>
        <div class="head-title" style="margin-bottom: 30px;">
            <div class="left">
                <h1>Admin Management System</h1>
                <ul class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active" href="#">Admin Management</a></li>
                </ul>
            </div>
        </div>

        <div class="container">
            <section id="add-employee">
                <h2>Add Admin</h2>
                <form method="POST" enctype="multipart/form-data">
                    <label>Username:</label>
                    <input type="text" name="username" required>

                    <label>Email:</label>
                    <input type="email" name="email" required>

                    <label>Password:</label>
                    <input type="password" name="password" required>

                    <label>Confirm Password:</label>
                    <input type="password" name="confirm_password" required>

                    <label>Profile Image:</label>
                    <input type="file" name="image" accept="image/*" required>

                    <label>Roles:</label>
                    <div class="role-buttons">
                        <input type="radio" id="admin" name="user_type" value="Admin" required>
                        <label for="admin">Admin</label>

                        <input type="radio" id="superadmin" name="user_type" value="Super Admin" required>
                        <label for="superadmin">Super Admin</label>
                    </div>

                    <button type="submit" name="submit">Add Admin</button>
                </form>
            </section>

            <section id="view-employees">
                <h2>Admin List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" width="60" height="60"></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['user_type']); ?></td>
                                <td>
                                    <button>Edit</button>
                                    <button>Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>
</section>
</body>
</html>
