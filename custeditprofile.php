<?php
session_start();
include "db_connect1.php";

// Ensure the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
$email = $_SESSION['email'];

// Fetch user info
$sql = "SELECT * FROM customers WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Stop if user not found
if (!$user) {
    die("User not found.");
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.html");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if this is a password change request
    if (isset($_POST['change_password'])) {
        $oldPassword = $_POST['old_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Verify old password
        if (password_verify($oldPassword, $user['password'])) {
            if ($newPassword === $confirmPassword) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql = "UPDATE customers SET password=? WHERE email=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $hashedPassword, $email);
                
                if ($stmt->execute()) {
                    echo "<script>alert('Password changed successfully!');</script>";
                } else {
                    echo "<script>alert('Error updating password.');</script>";
                }
                $stmt->close();
            } else {
                echo "<script>alert('New password and confirm password do not match.');</script>";
            }
        } else {
            echo "<script>alert('Old password is incorrect.');</script>";
        }
    } else {
        // Handle regular profile update
        $firstName = $_POST["first_name"];
        $lastName = $_POST["last_name"];
        $phone = $_POST["phone"];
        $username = $_POST["username"];
        $bio = $_POST["bio"];
        $profile_pic = $_FILES["profile_pic"]["name"];
        $target_dir = "uploads/";

        // Handle uploads
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (!empty($profile_pic)) {
            $target_file = $target_dir . basename($profile_pic);
            move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file);
        } else {
            $target_file = $user["profile_pic"] ?? "";
        }

        $sql = "UPDATE customers SET first_name=?, last_name=?, phone=?, username=?, bio=?, profile_pic=? WHERE email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $firstName, $lastName, $phone, $username, $bio, $target_file, $email);

        if ($stmt->execute()) {
            // Log changes
            $fields = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'username' => $username,
                'bio' => $bio,
                'profile_pic' => $target_file
            ];

            foreach ($fields as $field => $new_value) {
                if (!isset($user[$field]) || $user[$field] != $new_value) {
                    $old_value = $user[$field];
                    $sql_log = "INSERT INTO profile_edits (customer_id, field_changed, old_value, new_value) VALUES (?, ?, ?, ?)";
                    $stmt_log = $conn->prepare($sql_log);
                    $stmt_log->bind_param("isss", $user['id'], $field, $old_value, $new_value);
                    $stmt_log->execute();
                    $stmt_log->close();
                }
            }

            echo "<script>alert('Profile updated successfully!'); window.location.href='custeditprofile.php';</script>";
        } else {
            echo "Error updating profile: " . $stmt->error;
        }

        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS | Edit Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rubik:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ff0000;
            --secondary: #d10000;
            --dark: #0d0221;
            --light: #ffffff;
            --accent: #ff3333;
        }
        
        body {
            font-family: 'Rubik', sans-serif;
            background-color: var(--dark);
            color: var(--light);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        header {
            background: var(--dark);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(255, 0, 0, 0.3);
        }
        
        .nav-menu {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
        }
        
        .logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            text-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
        }
        
        .nav-links a {
            color: var(--light);
            text-decoration: none;
            font-family: 'Orbitron', sans-serif;
            font-weight: 400;
            transition: all 0.3s ease;
        }
        
        .nav-links a:hover {
            color: var(--primary);
        }
        
        .icons-left, .icons-right {
            display: flex;
            gap: 25px;
        }
        
        .icons-left i, .icons-right i {
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--light);
        }
        
        .icons-left i:hover, .icons-right i:hover {
            color: var(--primary);
        }
        
        .profile-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(255, 0, 0, 0.2);
        }
        
        .profile-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            color: var(--primary);
            text-align: center;
            margin-bottom: 40px;
            text-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
        }
        
        .profile-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.3);
            border-radius: 5px;
            color: var(--light);
            font-family: 'Rubik', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(255, 0, 0, 0.3);
        }
        
        .form-control[readonly] {
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.5);
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .profile-image-container {
            grid-column: span 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
            margin-bottom: 20px;
            box-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
        }
        
        .btn-container {
            grid-column: span 2;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 12px 30px;
            font-family: 'Orbitron', sans-serif;
            font-weight: 400;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-cancel {
            background: transparent;
            color: var(--light);
            border: 1px solid var(--primary);
        }
        
        .btn-cancel:hover {
            background: rgba(255, 0, 0, 0.2);
        }
        
        .btn-save {
            background: var(--primary);
            color: var(--light);
        }
        
        .btn-save:hover {
            background: var(--accent);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 0, 0, 0.4);
        }
         footer {
            background: #0a0118;
            padding: 50px 30px 20px;
            text-align: center;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .footer-links a {
            color: var(--light);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--primary);
        }
         .social-icons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .social-icons a {
            color: var(--light);
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .social-icons a:hover {
            color: var(--primary);
        }
        
        .copyright {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 0, 0, 0.1);
        }
        
        /* Password Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 3000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
        }
        
        .modal-content {
            background-color: var(--dark);
            margin: 10% auto;
            padding: 30px;
            border: 1px solid var(--primary);
            border-radius: 10px;
            width: 400px;
            max-width: 90%;
            box-shadow: 0 0 20px rgba(255, 0, 0, 0.5);
        }
        
        .modal-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.5rem;
        }
        
        .close-modal {
            color: var(--light);
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close-modal:hover {
            color: var(--primary);
        }
        
        .modal-btn-container {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }
        
        /* Mobile menu styles */
        #menuOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            z-index: 2000;
        }
        
        #menuContainer {
            position: fixed;
            top: 0;
            left: -400px;
            width: 400px;
            height: 100%;
            background: var(--dark);
            padding: 40px;
            transition: left 0.4s ease;
            z-index: 2001;
            border-right: 1px solid var(--primary);
        }
        
        #closeMenu {
            font-size: 2rem;
            color: var(--primary);
            cursor: pointer;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        
        #menuOverlay.active {
            display: block;
        }
        
        #menuOverlay.active #menuContainer {
            left: 0;
        }
        
        .menu-item {
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 0, 0, 0.1);
        }
        
        .menu-item a {
            color: var(--light);
            text-decoration: none;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            display: block;
        }
        
        .menu-item a:hover {
            color: var(--primary);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .profile-form {
                grid-template-columns: 1fr;
            }
            
            .profile-image-container,
            .btn-container {
                grid-column: span 1;
            }
            
            .profile-container {
                padding: 30px 20px;
                margin: 30px 20px;
            }
            
            .profile-title {
                font-size: 2rem;
            }
            
            .modal-content {
                margin: 20% auto;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="nav-menu">
            <div class="icons-left">
                <i class="fas fa-search"></i>
                <i class="fas fa-bars" id="menuIcon"></i>
            </div>
            
            <div class="logo">NEXUS</div>
            
            <div class="nav-links">
                <a href="index.php">HOME</a>
                <a href="nintendo_user.php">NINTENDO</a>
                <a href="console_user.php" class="active">CONSOLES</a>
                <a href="accessories_user.php">ACCESSORIES</a>
                <a href="vr_user.php">VR</a>
            </div>
            
            <div class="icons-right">
                
                </a>
                <i class="fas fa-shopping-cart"></i>
            </div>
        </nav>
    </header>

    <!-- Mobile Menu Overlay -->
    <div id="menuOverlay">
        <div id="menuContainer">
            <span id="closeMenu">&times;</span>
            <div id="menuContent">
                <div class="menu-item"><a href="ORDERHISTORY.html">ORDER</a></div>
                <div class="menu-item"><a href="custservice.html">HELP</a></div>
                <div class="menu-item"><a href="login_admin.php">LOGIN ADMIN</a></div>
                <div class="menu-item"><a href="?logout=1">LOGOUT</a></div>
            </div>
        </div>
    </div>

    <!-- Profile Edit Section -->
    <div class="profile-container">
        <h1 class="profile-title">EDIT PROFILE</h1>
        
        <form method="POST" enctype="multipart/form-data" class="profile-form">
            <div class="profile-image-container">
                <?php if (!empty($user['profile_pic'])): ?>
                    <img src="<?= $user['profile_pic'] ?>" alt="Profile Picture" class="profile-image">
                <?php else: ?>
                    <div class="profile-image" style="background: rgba(255, 0, 0, 0.2); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user" style="font-size: 3rem; color: var(--primary);"></i>
                    </div>
                <?php endif; ?>
                <input type="file" name="profile_pic" class="form-control" style="width: auto;">
            </div>
            
            <div class="form-group">
                <label for="first_name">FIRST NAME</label>
                <input type="text" id="first_name" name="first_name" class="form-control" pattern="[A-Za-z\s]+" title="Only letters and spaces allowed" value="<?= htmlspecialchars($user['first_name']) ?>">
            </div>
            
            <div class="form-group">
                <label for="last_name">LAST NAME</label>
                <input type="text" id="last_name" name="last_name" class="form-control" pattern="[A-Za-z\s]+" title="Only letters and spaces allowed" value="<?= htmlspecialchars($user['last_name']) ?>">
            </div>
            
            <div class="form-group">
                <label for="email">EMAIL</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
            </div>

             <div class="form-group">
                <label for="username">USERNAME</label>
                <input type="text" id="username" name="username" class="form-control" pattern="[A-Za-z\s]+" title="Only letters and spaces allowed" value="<?= htmlspecialchars($user['username']) ?>">
            </div>
            
            <div class="form-group">
                <label for="phone">PHONE</label>
                <input type="tel" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" 
                      pattern="[0-9]{10,15}"  title="Enter a valid phone number (10-15 digits)" required>

            </div>
            
            <div class="form-group" style="grid-column: span 2;">
                <label for="bio">BIO</label>
                <textarea id="bio" name="bio" class="form-control"><?= htmlspecialchars($user['bio']) ?></textarea>
            </div>
            
            <div class="btn-container">
                <div>
                    <a href="index.php" class="btn btn-cancel">BACK</a>
                    <a href="?logout=1" class="btn btn-cancel">LOGOUT</a>
                </div>
                <div>
                    <button type="button" id="changePasswordBtn" class="btn btn-cancel">CHANGE PASSWORD</button>
                    <button type="submit" class="btn btn-save">SAVE CHANGES</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Password Change Modal -->
    <div id="passwordModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2 class="modal-title">CHANGE PASSWORD</h2>

        <form method="POST" id="passwordForm">
            <div class="form-group" style="position: relative;">
                <label for="old_password">OLD PASSWORD</label>
                <input type="password" id="old_password" name="old_password" class="form-control" required>
                <span onclick="togglePassword('old_password')" style="position:absolute; top:68%; right:10px; transform:translateY(-50%); cursor:pointer;">👁️</span>
            </div>

            <div class="form-group" style="position: relative;">
                <label for="new_password">NEW PASSWORD</label>
                <input type="password" id="new_password" name="new_password" class="form-control"  pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
            title="Must contain at least one number, one lowercase letter, one uppercase letter, one special character, and be at least 8 characters long" required>
                <span onclick="togglePassword('new_password')" style="position:absolute; top:68%; right:10px; transform:translateY(-50%); cursor:pointer;">👁️</span>
            </div>

            <div class="form-group" style="position: relative;">
                <label for="confirm_password">CONFIRM PASSWORD</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
            title="Must contain at least one number, one lowercase letter, one uppercase letter, one special character, and be at least 8 characters long" required>
                <span onclick="togglePassword('confirm_password')" style="position:absolute; top:68%; right:10px; transform:translateY(-50%); cursor:pointer;">👁️</span>
            </div>

            <div class="modal-btn-container">
                <button type="button" id="cancelPassword" class="btn btn-cancel">CANCEL</button>
                <button type="submit" name="change_password" class="btn btn-save">SAVE</button>
            </div>
        </form>
    </div>
</div>


    <!-- Footer -->
    <footer>
        <div class="footer-links">
            <a href="#about">ABOUT US</a>
            <a href="#contact">CONTACT</a>
            <a href="tos"> TERMS OF SERVICE</a>
        </div>
        
        <div class="social-icons">
            <a href="#facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#instagram"><i class="fab fa-instagram"></i></a>
        </div>
        
        <div class="copyright">
            &copy; 2025 NEXUS GAMING STORE. ALL RIGHTS RESERVED.
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let menuOverlay = document.getElementById("menuOverlay");
            let menuContainer = document.getElementById("menuContainer");
            let menuIcon = document.getElementById("menuIcon");
            let closeMenu = document.getElementById("closeMenu");
  // Toggle password visibility for any field by ID
        window.togglePassword = function(id) {
            const input = document.getElementById(id);
            input.type = input.type === "password" ? "text" : "password";
        }
    
            // Open menu
            menuIcon.addEventListener("click", function () {
                menuOverlay.style.display = "block";
                setTimeout(() => {
                    menuOverlay.classList.add("active");
                }, 10);
            });

            // Close menu when clicking "X"
            closeMenu.addEventListener("click", function (e) {
                e.stopPropagation();
                menuOverlay.classList.remove("active");
                setTimeout(() => {
                    menuOverlay.style.display = "none";
                }, 300);
            });

            // Close menu when clicking outside of menu container
            menuOverlay.addEventListener("click", function (e) {
                if (e.target === menuOverlay) {
                    menuOverlay.classList.remove("active");
                    setTimeout(() => {
                        menuOverlay.style.display = "none";
                    }, 300);
                }
            });
            
            // Add animation to form elements on load
            const formElements = document.querySelectorAll('.form-group, .profile-image-container');
            formElements.forEach((el, index) => {
                el.style.opacity = "0";
                el.style.transform = "translateY(20px)";
                el.style.transition = "all 0.5s ease " + (index * 0.1) + "s";
                setTimeout(() => {
                    el.style.opacity = "1";
                    el.style.transform = "translateY(0)";
                }, 100);
            });
            
            // Password modal functionality
            const modal = document.getElementById("passwordModal");
            const btn = document.getElementById("changePasswordBtn");
            const span = document.getElementsByClassName("close-modal")[0];
            const cancelBtn = document.getElementById("cancelPassword");
            
            btn.onclick = function() {
                modal.style.display = "block";
            }
            
            span.onclick = function() {
                modal.style.display = "none";
            }
            
            cancelBtn.onclick = function() {
                modal.style.display = "none";
            }
            
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
            
            // Clear password form when modal closes
            modal.addEventListener('hidden', function() {
                document.getElementById("passwordForm").reset();
            });
        });
    </script>
</body>
</html>