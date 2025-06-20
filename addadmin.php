<?php
session_start();

// Check if the session variable is set
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
} else {
    // Redirect to login if not logged in
    header("Location: login_admin.php");
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
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

// Handle search functionality
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format!'); window.location.href='addadmin.php';</script>";
        exit;
    }

    $password_raw = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'];

    if ($password_raw !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.location.href='addadmin.php';</script>";
        exit;
    }

    // New password requirements
    if (strlen($password_raw) < 12 || 
        !preg_match('/[A-Z]/', $password_raw) || 
        !preg_match('/[a-z]/', $password_raw) || 
        !preg_match('/[0-9]/', $password_raw) || 
        !preg_match('/[^A-Za-z0-9]/', $password_raw)) {
        echo "<script>alert('Password must be at least 12 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character!'); window.location.href='addadmin.php';</script>";
        exit;
    }

    $password = password_hash($password_raw, PASSWORD_BCRYPT);

    $target_dir = "uploads/";
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

    $check_email = "SELECT * FROM admin_list WHERE email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result_check = $stmt->get_result();

    if ($result_check->num_rows > 0) {
        echo "<script>alert('Error: Email already exists!'); window.location.href='addadmin.php';</script>";
    } else {
        $domain = substr(strrchr($email, "@"), 1);

        // List of accepted domains or rules
        $valid_university_pattern = '/\.edu\.my$/i'; // allows all Malaysian university emails
        $check_mx = checkdnsrr($domain, "MX"); // check MX records

        // If it's not a .edu.my OR a domain with MX, reject
        if (!preg_match($valid_university_pattern, $domain) && !$check_mx) {
            echo "<script>alert('Invalid email domain! Only valid public or Malaysian university emails allowed.'); window.location.href='addadmin.php';</script>";
            exit;
        }

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

// Fetch all admins for the table (unless we're showing search results)
if (!$showSearchResults) {
    $sql = "SELECT * FROM admin_list";
    $result = $conn->query($sql);
}

if ($admin_id) {
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
} else {
    $profile_image = 'image/default_profile.jpg';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Management</title>
<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="manageadmin.css" />
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
    form input[type="number"],
    form input[type="password"],
    form input[type="file"] {
        width: 100%;
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    /* Search form styling to match dashboard */
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
        background: var(--light);
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

    /* New role selection buttons styling */
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
</style>
</head>
<body>

<section id="sidebar">
    <a href="#" class="brand"><br><span class="text">Admin Dashboard</span></a>
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
                <span class="text">Category Management</span>
            </a>
        </li>
        <li>
            <a href="manageproduct.php">
                <i class='bx bxs-shopping-bag-alt'></i>
                <span class="text">Product Management</span>
            </a>
        </li>
        <li>
            <a href="order.php">
                <i class='bx bxs-doughnut-chart'></i>
                <span class="text">Order</span>
            </a>
        </li>
    </ul>
    <ul class="side-menu">
        <li><a href="?logout=1" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
    </ul>
</section>

<section id="content">
    <nav>
        <form id="searchForm" method="GET" action="">
            <div class="form-input">
                <input type="search" id="searchInput" name="query" placeholder="Search admins..." 
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
            </div>
        </form>
       
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
                                                <img src="<?php echo $imgPath; ?>" alt="Admin Image" width="100" height="100">
                                            </td>
                                            <td>
                                                <button><a href="edit_admin.php?id=<?php echo $row['id']; ?>" style="color:white; text-decoration:none;">Edit</a></button>
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
                    <h2>Add Admin</h2>
                    <form method="POST" action="addadmin.php" enctype="multipart/form-data">
                        <label>Username:</label>
                        <input type="text" name="username" required>

                        <label>Email:</label>
                        <input type="email" name="email" required>

                        <label>Password:</label>
                        <input type="password" name="password" required id="password" placeholder="At least 12 characters with uppercase, lowercase, number, and special character">

                        <label>Confirm Password:</label>
                        <input type="password" name="confirm_password" required id="confirm_password">

                        <label>Profile Image:</label>
                        <input type="file" name="image" accept="image/*" required>

                        <label>Roles:</label>
                        <div class="role-buttons">
                            <input type="radio" id="admin" name="user_type" value="Admin" required>
                            <label for="admin">Admin</label>

                            <input type="radio" id="superadmin" name="user_type" value="Super Admin" required>
                            <label for="superadmin">Super Admin</label>
                        </div>

                        <button type="submit" name="submit" onclick="return validatePassword()">Add Admin</button>
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
                                            <img src="<?php echo $imgPath; ?>" alt="Admin Image" width="100" height="100">
                                        </td>
                                        <td>
                                            <button><a href="edit_admin.php?id=<?php echo $row['id']; ?>" style="color:white; text-decoration:none;">Edit</a></button>
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
                                <tr><td colspan="5">No admins found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
            </div>
        <?php endif; ?>
    </main>
</section>

<script>
function validatePassword() {
    let password = document.getElementById('password').value;
    let confirm = document.getElementById('confirm_password').value;
    
    if (password !== confirm) {
        alert('Passwords do not match');
        return false;
    }
    
    // Check password requirements
    if (password.length < 12) {
        alert('Password must be at least 12 characters long');
        return false;
    }
    if (!/[A-Z]/.test(password)) {
        alert('Password must contain at least one uppercase letter');
        return false;
    }
    if (!/[a-z]/.test(password)) {
        alert('Password must contain at least one lowercase letter');
        return false;
    }
    if (!/[0-9]/.test(password)) {
        alert('Password must contain at least one number');
        return false;
    }
    if (!/[^A-Za-z0-9]/.test(password)) {
        alert('Password must contain at least one special character');
        return false;
    }
    
    return true;
}

// Sidebar toggle
let sidebar = document.querySelector("#sidebar");
let sidebarBtn = document.querySelector("nav .bx-menu");

sidebarBtn.addEventListener("click", () => {
    sidebar.classList.toggle("active");
});

// Focus search input when search icon is clicked
document.querySelector('.search-btn')?.addEventListener('click', function() {
    document.getElementById('searchInput').focus();
});

// Clear search when clicking the X button
document.querySelector('.clear-search')?.addEventListener('click', function(e) {
    e.preventDefault();
    window.location.href = '?';
});

// Optional: Submit form when pressing Enter in search input
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