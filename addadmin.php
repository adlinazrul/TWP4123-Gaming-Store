<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search
$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';
$showSearchResults = false;
$searchResults = [];

if (!empty($searchQuery)) {
    $showSearchResults = true;
    $searchTerm = $conn->real_escape_string($searchQuery);
    
    $searchSql = "SELECT * FROM admin_list WHERE 
                 username LIKE '%$searchTerm%' OR 
                 email LIKE '%$searchTerm%' OR 
                 user_type LIKE '%$searchTerm%'";
    $searchResult = $conn->query($searchSql);
    
    if ($searchResult) {
        while ($row = $searchResult->fetch_assoc()) {
            $searchResults[] = $row;
        }
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password_raw = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format!'); window.location.href='addadmin.php';</script>";
        exit;
    }

    // Password validation
    if ($password_raw !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.location.href='addadmin.php';</script>";
        exit;
    }

    // Strong password requirements
    $errors = [];
    if (strlen($password_raw) < 12) {
        $errors[] = "Password must be at least 12 characters long";
    }
    if (!preg_match('/[A-Z]/', $password_raw)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password_raw)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/\d/', $password_raw)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password_raw)) {
        $errors[] = "Password must contain at least one special character";
    }

    if (!empty($errors)) {
        echo "<script>alert('" . implode("\\n", $errors) . "'); window.location.href='addadmin.php';</script>";
        exit;
    }

    // Hash password
    $password = password_hash($password_raw, PASSWORD_BCRYPT);

    // Handle image upload
    $target_dir = "uploads/";
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    
    // Move uploaded file
    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        echo "<script>alert('Error uploading image!'); window.location.href='addadmin.php';</script>";
        exit;
    }

    // Check if email exists
    $check_email = "SELECT * FROM admin_list WHERE email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result_check = $stmt->get_result();

    if ($result_check->num_rows > 0) {
        echo "<script>alert('Error: Email already exists!'); window.location.href='addadmin.php';</script>";
    } else {
        // Validate email domain
        $domain = substr(strrchr($email, "@"), 1);
        $valid_university_pattern = '/\.edu\.my$/i';
        $check_mx = checkdnsrr($domain, "MX");

        if (!preg_match($valid_university_pattern, $domain) && !$check_mx) {
            echo "<script>alert('Invalid email domain! Only valid public or Malaysian university emails allowed.'); window.location.href='addadmin.php';</script>";
            exit;
        }

        // Insert new admin
        $sql = "INSERT INTO admin_list (username, email, password, image, user_type) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $email, $password, $image_name, $user_type);

        if ($stmt->execute()) {
            echo "<script>alert('Admin added successfully!'); window.location.href='addadmin.php';</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
    }
    $stmt->close();
}

// Fetch all admins (unless showing search results)
if (!$showSearchResults) {
    $sql = "SELECT * FROM admin_list";
    $result = $conn->query($sql);
}

// Get profile image
$query = "SELECT image FROM admin_list WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($image);
if ($stmt->fetch() && !empty($image)) {
    $profile_image = 'uploads/' . $image;
} else {
    $profile_image = 'image/default_profile.jpg';
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <style>
        :root {
            --light: #f9f9f9;
            --red: #a93226;
            --light-red: #f5d0ce;
            --dark-red: #7d241b;
            --grey: #eee;
            --dark-grey: #777777;
            --dark: #342e37;
            --yellow: #ffce26;
            --light-yellow: #fff2c6;
            --orange: #fd7238;
            --light-orange: #ffe0d3;
            --green: #28a745;
            --light-green: #d1f5d9;
            --teal: #17a2b8;
            --light-teal: #d1f0f5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--light);
        }

        /* SIDEBAR */
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100%;
            background: var(--red);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        #sidebar.hide {
            width: 80px;
        }

        #sidebar .brand {
            font-size: 24px;
            font-weight: 700;
            height: 56px;
            display: flex;
            align-items: center;
            color: var(--yellow);
            position: sticky;
            top: 0;
            left: 0;
            background: var(--dark-red);
            padding-left: 15px;
            z-index: 500;
        }

        #sidebar .brand .text {
            margin-left: 10px;
        }

        #sidebar .side-menu {
            width: 100%;
            margin-top: 20px;
        }

        #sidebar .side-menu li {
            height: 48px;
            margin-left: 6px;
            background: transparent;
            border-radius: 48px 0 0 48px;
            padding: 4px;
        }

        #sidebar .side-menu li.active {
            position: relative;
            background: var(--light);
        }

        #sidebar .side-menu li.active::before {
            content: '';
            position: absolute;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            top: -40px;
            right: 0;
            box-shadow: 20px 20px 0 var(--light);
            z-index: -1;
        }

        #sidebar .side-menu li.active::after {
            content: '';
            position: absolute;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            bottom: -40px;
            right: 0;
            box-shadow: 20px -20px 0 var(--light);
            z-index: -1;
        }

        #sidebar .side-menu li a {
            width: 100%;
            height: 100%;
            background: var(--dark-red);
            border-radius: 48px;
            display: flex;
            align-items: center;
            color: var(--light);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        #sidebar .side-menu li.active a {
            color: var(--red);
            background: var(--light);
        }

        #sidebar .side-menu.top li a:hover {
            color: var(--red);
            background: var(--light);
        }

        #sidebar .side-menu li a i {
            min-width: calc(60px - ((4px + 6px) * 2));
            display: flex;
            justify-content: center;
            font-size: 20px;
        }

        #sidebar .side-menu li a .text {
            transition: all 0.3s ease;
        }

        #sidebar.hide .side-menu li a .text {
            opacity: 0;
        }

        #sidebar .side-menu li a.logout {
            color: var(--light-red);
        }

        /* CONTENT */
        #content {
            position: relative;
            width: calc(100% - 280px);
            left: 280px;
            transition: all 0.3s ease;
        }

        #sidebar.hide ~ #content {
            width: calc(100% - 80px);
            left: 80px;
        }

        /* NAVBAR */
        nav {
            height: 56px;
            background: var(--light);
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            left: 0;
            z-index: 500;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        nav .form-input {
            position: relative;
            width: 300px;
            height: 40px;
            display: flex;
            align-items: center;
        }

        nav .form-input input {
            width: 100%;
            height: 100%;
            border: none;
            outline: none;
            background: var(--grey);
            border-radius: 20px;
            padding: 0 15px;
            font-size: 14px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        nav .form-input .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--dark-grey);
        }

        nav .profile {
            display: flex;
            align-items: center;
            background: var(--grey);
            border-radius: 20px;
            height: 40px;
            padding: 0 10px 0 2px;
            text-decoration: none;
            color: var(--dark);
        }

        nav .profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        /* MAIN CONTENT */
        main {
            padding: 20px;
        }

        .head-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .head-title .left h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .breadcrumb li {
            list-style: none;
            font-size: 14px;
        }

        .breadcrumb li a {
            text-decoration: none;
            color: var(--dark-grey);
        }

        .breadcrumb li a.active {
            color: var(--red);
            font-weight: 500;
        }

        .breadcrumb li i {
            font-size: 12px;
        }

        /* ADMIN MANAGEMENT STYLES */
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

        form input[type="text"],
        form input[type="email"],
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
            background-color: var(--red);
            color: white;
            border-radius: 5px;
            font-weight: bold;
            user-select: none;
            transition: background-color 0.3s;
        }

        .role-buttons input[type="radio"]:checked + label {
            background-color: var(--dark-red);
            box-shadow: 0 0 10px rgba(169, 50, 38, 0.5);
        }

        form button[type="submit"] {
            grid-column: 2;
            padding: 10px 20px;
            background-color: var(--red);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        form button[type="submit"]:hover {
            background-color: var(--dark-red);
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
            width: 100px;
            height: 100px;
            object-fit: cover;
        }

        table button {
            padding: 5px 10px;
            background-color: var(--red);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 5px;
            transition: 0.3s;
        }

        table button:hover {
            background-color: var(--dark-red);
        }

        table button a {
            color: white;
            text-decoration: none;
        }

        /* Search results styling */
        .search-results-container {
            margin-top: 30px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .highlight {
            background-color: var(--light-yellow);
            padding: 2px 4px;
            border-radius: 4px;
        }

        .search-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .clear-search {
            color: var(--red);
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .clear-search:hover {
            color: var(--dark-red);
            transform: translateX(-3px);
        }

        /* Password strength meter */
        #password-strength {
            display: block;
            margin-top: 5px;
            font-weight: bold;
            font-size: 14px;
        }

        .password-requirements {
            background: var(--light-yellow);
            padding: 10px;
            border-radius: 5px;
            margin-top: 5px;
            font-size: 13px;
            color: var(--dark);
        }

        .password-requirements ul {
            padding-left: 20px;
            margin-top: 5px;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: var(--dark-grey);
        }

        .no-data i {
            font-size: 50px;
            margin-bottom: 10px;
            color: var(--red);
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand"><i class='bx bxs-shield'></i><span class="text">Admin Panel</span></a>
        <ul class="side-menu top">
            <li><a href="admindashboard.php"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
            <li class="active">
                <a href="addadmin.php">
                    <i class='bx bxs-group'></i>
                    <span class="text">Admin</span>
                </a>
            </li>
            <li>
                <a href="customer_list.php">
                    <i class='bx bxs-user'></i>
                    <span class="text">Customer</span>
                </a>
            </li>
            <li>
                <a href="manage_category.php">
                    <i class='bx bxs-category'></i>
                    <span class="text">Category</span>
                </a>
            </li>
            <li>
                <a href="manageproduct.php">
                    <i class='bx bxs-shopping-bag-alt'></i>
                    <span class="text">Products</span>
                </a>
            </li>
            <li>
                <a href="order.php">
                    <i class='bx bxs-doughnut-chart'></i>
                    <span class="text">Orders</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="index.html" class="logout">
                    <i class='bx bxs-log-out-circle'></i>
                    <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </section>

    <!-- CONTENT -->
    <section id="content">
        <nav>
            <form id="searchForm" method="GET" action="">
                <div class="form-input">
                    <input type="search" id="searchInput" name="query" placeholder="Search admins..." 
                           value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                </div>
            </form>
           
            <a href="profile_admin.php" class="profile">
                <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Picture">
                <span><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
            </a>
        </nav>

        <main>
            <div class="head-title" style="margin-bottom: 30px;">
                <div class="left">
                    <h1>Admin Management System</h1>
                    <ul class="breadcrumb">
                        <li><a href="admindashboard.php">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Admin Management</a></li>
                    </ul>
                </div>
            </div>

            <?php if ($showSearchResults): ?>
                <!-- SEARCH RESULTS SECTION -->
                <div class="search-results-container">
                    <div class="data-card">
                        <div class="search-header">
                            <h3><i class='bx bx-search'></i> Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h3>
                            <a href="?" class="clear-search">
                                <i class='bx bx-x'></i> Clear search
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (count($searchResults) > 0): ?>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Roles</th>
                                            <th>Image</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($searchResults as $row): ?>
                                            <tr>
                                                <td>
                                                    <?php 
                                                        echo preg_replace(
                                                            "/(" . preg_quote($searchQuery, '/') . ")/i", 
                                                            '<span class="highlight">$1</span>', 
                                                            htmlspecialchars($row['username'])
                                                        ); 
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                        echo preg_replace(
                                                            "/(" . preg_quote($searchQuery, '/') . ")/i", 
                                                            '<span class="highlight">$1</span>', 
                                                            htmlspecialchars($row['email'])
                                                        ); 
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $role = strtolower(trim($row['user_type']));
                                                    $roleDisplay = ($role === 'superadmin' || $role === 'super admin') ? "Super Admin" : "Admin";
                                                    echo preg_replace(
                                                        "/(" . preg_quote($searchQuery, '/') . ")/i", 
                                                        '<span class="highlight">$1</span>', 
                                                        $roleDisplay
                                                    ); 
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $imgPath = !empty($row['image']) ? "uploads/" . htmlspecialchars($row['image']) : "image/default_profile.jpg";
                                                    ?>
                                                    <img src="<?php echo $imgPath; ?>" alt="Admin Image">
                                                </td>
                                                <td>
                                                    <button><a href="edit_admin.php?id=<?php echo $row['id']; ?>">Edit</a></button>
                                                    <?php if ($row['id'] != $_SESSION['admin_id']): ?>
                                                        <form method="GET" action="deleteadmin.php" style="display:inline;">
                                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                            <button type="submit" onclick="return confirm('Are you sure you want to delete this admin?')">Delete</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span style="color: green; font-style: italic;">You</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class='bx bxs-error-circle'></i>
                                    <p>No results found for "<?php echo htmlspecialchars($searchQuery); ?>"</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- REGULAR ADMIN MANAGEMENT CONTENT -->
                <div class="container">
                    <section id="add-employee">
                        <h2>Add New Admin</h2>
                        <form method="POST" action="addadmin.php" enctype="multipart/form-data" onsubmit="return validatePassword()">
                            <label for="username">Username:</label>
                            <div>
                                <input type="text" name="username" id="username" required>
                            </div>

                            <label for="email">Email:</label>
                            <div>
                                <input type="email" name="email" id="email" required>
                            </div>

                            <label for="password">Password:</label>
                            <div>
                                <input type="password" name="password" id="password" required>
                                <div id="password-strength"></div>
                                <div class="password-requirements">
                                    <strong>Password Requirements:</strong>
                                    <ul>
                                        <li>Minimum 12 characters</li>
                                        <li>At least one uppercase letter (A-Z)</li>
                                        <li>At least one lowercase letter (a-z)</li>
                                        <li>At least one number (0-9)</li>
                                        <li>At least one special character (!@#$%^&* etc.)</li>
                                    </ul>
                                </div>
                            </div>

                            <label for="confirm_password">Confirm Password:</label>
                            <div>
                                <input type="password" name="confirm_password" id="confirm_password" required>
                            </div>

                            <label for="image">Profile Image:</label>
                            <div>
                                <input type="file" name="image" id="image" accept="image/*" required>
                            </div>

                            <label>Roles:</label>
                            <div class="role-buttons">
                                <input type="radio" id="admin" name="user_type" value="Admin" required>
                                <label for="admin">Admin</label>

                                <input type="radio" id="superadmin" name="user_type" value="Super Admin" required>
                                <label for="superadmin">Super Admin</label>
                            </div>

                            <div></div>
                            <button type="submit" name="submit">Add Admin</button>
                        </form>
                    </section>

                    <section id="view-employees">
                        <h2>Admin List</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Roles</th>
                                    <th>Image</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($result) && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td>
                                                <?php
                                                $role = strtolower(trim($row['user_type']));
                                                if ($role === 'superadmin' || $role === 'super admin') {
                                                    echo "Super Admin";
                                                } elseif ($role === 'admin') {
                                                    echo "Admin";
                                                } else {
                                                    echo htmlspecialchars($row['user_type']);
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $imgPath = !empty($row['image']) ? "uploads/" . htmlspecialchars($row['image']) : "image/default_profile.jpg";
                                                ?>
                                                <img src="<?php echo $imgPath; ?>" alt="Admin Image">
                                            </td>
                                            <td>
                                                <button><a href="edit_admin.php?id=<?php echo $row['id']; ?>">Edit</a></button>
                                                <?php if ($row['id'] != $_SESSION['admin_id']): ?>
                                                    <form method="GET" action="deleteadmin.php" style="display:inline;">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" onclick="return confirm('Are you sure you want to delete this admin?')">Delete</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span style="color: green; font-style: italic;">You</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center;">No admins found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </section>
                </div>
            <?php endif; ?>
        </main>
    </section>

    <script>
        // Password validation function
        function validatePassword() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Check if passwords match
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return false;
            }
            
            // Check password strength
            const minLength = 12;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            const hasSpecialChars = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
            
            let errors = [];
            
            if (password.length < minLength) {
                errors.push(`Password must be at least ${minLength} characters long`);
            }
            
            if (!hasUpperCase) {
                errors.push('Password must contain at least one uppercase letter');
            }
            
            if (!hasLowerCase) {
                errors.push('Password must contain at least one lowercase letter');
            }
            
            if (!hasNumbers) {
                errors.push('Password must contain at least one number');
            }
            
            if (!hasSpecialChars) {
                errors.push('Password must contain at least one special character');
            }
            
            if (errors.length > 0) {
                alert(errors.join('\n'));
                return false;
            }
            
            return true;
        }

        // Password strength meter
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthMeter = document.getElementById('password-strength');
            
            if (!strengthMeter) return;
            
            // Calculate strength
            let strength = 0;
            if (password.length >= 12) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Update meter
            const strengthText = ['Very Weak', 'Weak', 'Medium', 'Strong', 'Very Strong'][strength];
            const strengthColor = ['#ff0000', '#ff5e00', '#ffbb00', '#a4ff00', '#00ff00'][strength];
            
            strengthMeter.textContent = `Strength: ${strengthText}`;
            strengthMeter.style.color = strengthColor;
        });

        // Sidebar toggle
        let sidebar = document.querySelector("#sidebar");
        let sidebarBtn = document.querySelector("nav .bx-menu");

        if (sidebarBtn) {
            sidebarBtn.addEventListener("click", () => {
                sidebar.classList.toggle("hide");
            });
        }

        // Focus search input when search icon is clicked
        document.querySelector('.search-btn')?.addEventListener('click', function() {
            document.getElementById('searchInput').focus();
        });

        // Clear search when clicking the X button
        document.querySelector('.clear-search')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '?';
        });

        // Submit form when pressing Enter in search input
        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('searchForm').submit();
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>