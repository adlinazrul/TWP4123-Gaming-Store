<?php
session_start();

// Check if the session variable is set
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// DELETE admin
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM admin_list WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        echo "<script>alert('Admin deleted successfully!'); window.location.href='admindashboard.php';</script>";
    } else {
        echo "<script>alert('Error deleting admin: " . $stmt->error . "');</script>";
    }
    $stmt->close();
    exit;
}

// EDIT admin - handle POST from edit form
if (isset($_POST['edit_submit'])) {
    $edit_id = $_POST['edit_id'];
    $edit_username = $_POST['edit_username'];
    $edit_email = $_POST['edit_email'];
    $edit_user_type = $_POST['edit_user_type'];

    // Validate email format
    if (!filter_var($edit_email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Error: Invalid email format!'); window.location.href='admindashboard.php';</script>";
        exit;
    }

    // Check if a new image is uploaded
    $image_name = null;
    if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $image_name = basename($_FILES["edit_image"]["name"]);
        $target_file = $target_dir . $image_name;
        move_uploaded_file($_FILES["edit_image"]["tmp_name"], $target_file);
    }

    // Update query with or without image
    if ($image_name) {
        $update_sql = "UPDATE admin_list SET username = ?, email = ?, user_type = ?, image = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssi", $edit_username, $edit_email, $edit_user_type, $image_name, $edit_id);
    } else {
        $update_sql = "UPDATE admin_list SET username = ?, email = ?, user_type = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssi", $edit_username, $edit_email, $edit_user_type, $edit_id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Admin updated successfully!'); window.location.href='admindashboard.php';</script>";
    } else {
        echo "<script>alert('Error updating admin: " . $stmt->error . "');</script>";
    }
    $stmt->close();
    exit;
}

// ADD new admin (your existing POST add admin logic)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Error: Invalid email format!'); window.location.href='admindashboard.php';</script>";
        exit;
    }
    if ($password !== $confirm_password) {
        echo "<script>alert('Error: Passwords do not match!'); window.location.href='admindashboard.php';</script>";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

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
        $stmt->bind_param("sssss", $username, $email, $hashed_password, $image_name, $user_type);

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
<title>Admin</title>
<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="manageadmin.css" />
<style>
    /* Your existing styles */
    /* Changed edit button color to red */
    .edit-btn {
        padding: 8px 16px;
        background-color: #ef4444; /* red */
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .edit-btn:hover {
        background-color: #dc2626;
    }

    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        padding-top: 80px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5);
    }
    .modal-content {
        background-color: #fff;
        margin: auto;
        padding: 20px;
        border-radius: 8px;
        width: 400px;
        position: relative;
    }
    .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    .close-btn:hover {
        color: black;
    }
    .modal form label {
        display: block;
        margin-top: 10px;
        font-weight: bold;
    }
    .modal form input[type="text"],
    .modal form input[type="email"],
    .modal form select,
    .modal form input[type="file"] {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    .modal form button[type="submit"] {
        margin-top: 15px;
        padding: 10px 20px;
        background-color: #ef4444;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .modal form button[type="submit"]:hover {
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
        <li><a href="manage_order.php"><i class='bx bx-cart'></i><span class="text">Order Management</span></a></li>
        <li><a href="manage_user.php"><i class='bx bxs-user-account'></i><span class="text">User Management</span></a></li>
        <li class="active"><a href="#"><i class='bx bxs-user'></i><span class="text">Admin Management</span></a></li>
        <li><a href="manage_feedback.php"><i class='bx bxs-message-alt-detail'></i><span class="text">Feedback Management</span></a></li>
    </ul>
    <ul class="side-menu">
        <li><a href="logout.php" class="logout"><i class='bx bx-log-out'></i><span class="text">Logout</span></a></li>
    </ul>
</section>

<section id="content">
<header>
    <h1>Admin</h1>
    <div class="profile">
        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" />
        <span class="name"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
    </div>
</header>

<main>
    <div class="container">
        <h1 class="head-title">Admin Management</h1>

        <!-- Add Admin Form -->
        <form action="admindashboard.php" method="POST" enctype="multipart/form-data" class="admin-form">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" placeholder="Enter username" required />

            <label for="email">Email</label>
            <input type="email" name="email" id="email" placeholder="Enter email" required />

            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Enter password" required />

            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password" required />

            <label for="user_type">User Type</label>
            <select name="user_type" id="user_type" required>
                <option value="" disabled selected>Select user type</option>
                <option value="admin">Admin</option>
                <option value="super admin">Super Admin</option>
            </select>

            <label for="image">Profile Image</label>
            <input type="file" name="image" id="image" accept="image/*" />

            <button type="submit" name="submit" class="btn-submit">Add Admin</button>
        </form>

        <!-- Admins Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>User Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="Admin Image" style="width:40px; height:40px; object-fit:cover; border-radius:50%;" /></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['user_type']) ?></td>
                            <td>
                                <!-- Edit Button triggers modal -->
                                <button class="edit-btn" 
                                        data-id="<?= $row['id'] ?>" 
                                        data-username="<?= htmlspecialchars($row['username'], ENT_QUOTES) ?>" 
                                        data-email="<?= htmlspecialchars($row['email'], ENT_QUOTES) ?>" 
                                        data-usertype="<?= htmlspecialchars($row['user_type'], ENT_QUOTES) ?>" 
                                        data-image="<?= htmlspecialchars($row['image'], ENT_QUOTES) ?>">
                                    <i class='bx bx-edit-alt'></i>Edit
                                </button>

                                <!-- Delete Button -->
                                <a href="admindashboard.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this admin?');" class="btn-delete">
                                    <i class='bx bx-trash'></i>Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</section>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" id="editCloseBtn">&times;</span>
        <h2>Edit Admin</h2>
        <form action="admindashboard.php" method="POST" enctype="multipart/form-data" id="editForm">
            <input type="hidden" name="edit_id" id="edit_id" />

            <label for="edit_username">Username</label>
            <input type="text" name="edit_username" id="edit_username" required />

            <label for="edit_email">Email</label>
            <input type="email" name="edit_email" id="edit_email" required />

            <label for="edit_user_type">User Type</label>
            <select name="edit_user_type" id="edit_user_type" required>
                <option value="admin">Admin</option>
                <option value="super admin">Super Admin</option>
            </select>

            <label for="edit_image">Profile Image (Leave blank to keep current)</label>
            <input type="file" name="edit_image" id="edit_image" accept="image/*" />

            <button type="submit" name="edit_submit">Update Admin</button>
        </form>
    </div>
</div>

<script>
    // Modal logic
    const modal = document.getElementById("editModal");
    const editCloseBtn = document.getElementById("editCloseBtn");

    // Open modal and fill form data on edit button click
    document.querySelectorAll(".edit-btn").forEach(button => {
        button.addEventListener("click", () => {
            modal.style.display = "block";
            document.getElementById("edit_id").value = button.getAttribute("data-id");
            document.getElementById("edit_username").value = button.getAttribute("data-username");
            document.getElementById("edit_email").value = button.getAttribute("data-email");
            document.getElementById("edit_user_type").value = button.getAttribute("data-usertype");
            document.getElementById("edit_image").value = ""; // clear file input
        });
    });

    // Close modal when clicking on X
    editCloseBtn.onclick = function() {
        modal.style.display = "none";
    };

    // Close modal when clicking outside modal content
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };
</script>

</body>
</html>
