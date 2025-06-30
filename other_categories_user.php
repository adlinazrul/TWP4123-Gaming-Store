<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_store";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user information
$email = $_SESSION['email'];
$sql = "SELECT * FROM customers WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found!";
    exit();
}

// Fetch all categories EXCEPT the ones shown in the image with optional search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categories_sql = "SELECT * FROM product_categories 
                  WHERE category_name NOT IN ('NINTENDO', 'CONSOLE', 'ACCESSORIES', 'VR')";

if (!empty($search)) {
    $searchTerm = "%" . $conn->real_escape_string($search) . "%";
    $categories_sql .= " AND category_name LIKE ?";
    $stmt = $conn->prepare($categories_sql);
    $stmt->bind_param("s", $searchTerm);
} else {
    $stmt = $conn->prepare($categories_sql);
}

$stmt->execute();
$categories_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS | All Categories</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rubik:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
       :root {
            --primary: #ff0000;
            --secondary: #d10000;
            --dark: #0d0221;
            --light: #ffffff;
            --accent: #ff3333;
            --gray: #7a7a7a;
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
            cursor: pointer;
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
            position: relative;
        }
        
        .nav-links a:hover {
            color: var(--primary);
            text-decoration: none; 
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: var(--primary);
            bottom: -5px;
            left: 0;
            transition: width 0.3s ease;
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        .nav-links a.active {
            color: var(--primary);
        }
        
        .nav-links a.active::after {
            width: 100%;
        }
        
        .icons-left, .icons-right {
            display: flex;
            gap: 25px;
            align-items: center;
        }
        
        .icons-left i, .icons-right i {
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--light);
        }
        
        .icons-left i:hover, .icons-right i:hover {
            color: var(--primary);
            transform: scale(1.1);
        }
        
        .cart-count {
            background: var(--primary);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 0.7rem;
            position: absolute;
            top: -5px;
            right: -5px;
            font-family: 'Rubik', sans-serif;
        }
        
        .cart-icon-container {
            position: relative;
        }
        
        .categories-listing {
            max-width: 1400px;
    margin: 50px auto;
    padding: 0 30px;
    width: calc(100% - 60px); /* Ensures equal padding on both sides */
        }
        
        .section-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 50px;
            color: var(--primary);
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            width: 50%;
            height: 3px;
            background: var(--primary);
            bottom: -10px;
            left: 25%;
            border-radius: 3px;
        }
        
        .categories-grid {
            display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); /* Further reduced from 250px */
    gap: 15px; /* Reduced from 20px */
        }
        
        .category-card {
            background: rgba(255, 255, 255, 0.05);
    border-radius: 6px; /* Smaller radius */
    border: 1px solid rgba(255, 0, 0, 0.1);
    transition: all 0.3s ease;
    text-align: center;
    padding: 12px; /* Reduced padding */
    min-height: 30px; /* Reduced height */
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer; /* Indicates it's clickable */
        }
        
        .category-card:hover {
            transform: translateY(-5px); /* Smaller hover movement */
    box-shadow: 0 10px 20px rgba(255, 0, 0, 0.15); /* More subtle shadow */
    border-color: rgba(255, 0, 0, 0.3);
        }
        
        .category-image {
            height: 200px;
            width: 100%;
            object-fit: cover;
            background-color: #000;
            transition: transform 0.5s ease;
        }
        
        .category-card:hover .category-image {
            transform: scale(1.05);
        }
        
        .category-info h3 {
            margin: 0 0 10px;
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
        }

        .category-card h3 {
    margin: 0;
    font-family: 'Orbitron', sans-serif;
    color: var(--light); /* Changed from primary to light (white) */
    font-size: 1rem; /* Smaller font size */
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
}
        
        .view-category {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 8px 20px;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Orbitron', sans-serif;
            width: calc(100% - 40px);
            text-decoration: none;
            display: block;
            text-align: center;
            margin: 0 auto;
            box-sizing: border-box;
        }
        
        .view-category:hover {
            background: var(--primary);
            color: var(--dark);
        }
        
        footer {
            background: #0a0118;
            padding: 50px 30px 20px;
            text-align: center;
            position: relative;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .footer-links a {
            color: var(--light);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            padding: 5px 0;
        }
        
        .footer-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: var(--primary);
            bottom: 0;
            left: 0;
            transition: width 0.3s ease;
        }
        
        .footer-links a:hover::after {
            width: 100%;
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
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            text-decoration: none;
        }
        
        .social-icons a:hover {
            color: var(--primary);
            transform: translateY(-3px);
            background: rgba(255, 0, 0, 0.2);
        }
        
        .copyright {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 0, 0, 0.1);
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
            transition: transform 0.3s ease;
        }
        
        #closeMenu:hover {
            transform: rotate(90deg);
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
            padding-left: 10px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .nav-links {
                gap: 15px;
            }
        }
        
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .logo {
                font-size: 1.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            #menuContainer {
                width: 100%;
                max-width: 320px;
            }
            
            .category-card {
                max-width: 100%;
            }
            
            .footer-links {
                gap: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .section-title {
                font-size: 1.8rem;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 10px;
            }
        }

        @media (max-width: 768px) {
    .categories-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    
    .category-card {
        min-height: 70px;
        padding: 10px;
    }
    
    .category-card h3 {
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .categories-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 10px;
    }
    
    .category-card {
        min-height: 60px;
    }
    
    .category-card h3 {
        font-size: 0.8rem;
    }
}
        
        /* Add these new styles for the search overlay */
        #searchOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 2000;
            display: none;
            justify-content: center;
            align-items: center;
        }

        #searchContainer {
            width: 80%;
            max-width: 800px;
            position: relative;
        }

        #searchForm {
            display: flex;
            position: relative;
        }

        #searchInput {
            width: 100%;
            padding: 20px;
            font-size: 1.5rem;
            background: transparent;
            border: none;
            border-bottom: 3px solid var(--primary);
            color: var(--light);
            outline: none;
            font-family: 'Rubik', sans-serif;
        }

        #searchForm button {
            background: transparent;
            border: none;
            color: var(--light);
            font-size: 1.5rem;
            position: absolute;
            right: 60px;
            top: 20px;
            cursor: pointer;
        }

        #closeSearch {
            position: absolute;
            right: 10px;
            top: 20px;
            font-size: 2rem;
            color: var(--light);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        #closeSearch:hover {
            color: var(--primary);
            transform: scale(1.2);
        }

        .search-results-message {
            grid-column: 1/-1;
            text-align: center;
            margin-bottom: 20px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
        }
        
        /* Responsive adjustments for search */
        @media (max-width: 768px) {
            #searchInput {
                font-size: 1.2rem;
                padding: 15px;
            }

            #searchForm button {
                right: 50px;
                top: 15px;
            }

            #closeSearch {
                top: 15px;
            }
        }

        @media (max-width: 480px) {
            #searchInput {
                font-size: 1rem;
                padding: 10px;
            }

            #searchForm button {
                right: 40px;
                top: 10px;
                font-size: 1.2rem;
            }

            #closeSearch {
                top: 10px;
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Search Overlay -->
    <div id="searchOverlay">
        <div id="searchContainer">
            <form id="searchForm" method="GET" action="">
                <input type="text" name="search" id="searchInput" placeholder="Search categories..." autocomplete="off" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
                <span id="closeSearch">&times;</span>
            </form>
        </div>
    </div>

    <header>
        <nav class="nav-menu">
            <div class="icons-left">
                <i class="fas fa-search" id="searchIcon"></i>
                <i class="fas fa-bars" id="menuIcon"></i>
            </div>
            
            <div class="logo" onclick="window.location.href='index.php'">NEXUS</div>
            
            <div class="nav-links">
                <a href="index.php">HOME</a>
                <a href="nintendo_user.php">NINTENDO</a>
                <a href="console_user.php">CONSOLES</a>
                <a href="accessories_user.php">ACCESSORIES</a>
                <a href="vr_user.php">VR</a>
                <a href="other_categories_user.php" class="active">OTHERS</a>
            </div>
            
            <div class="icons-right">
                <a href="custeditprofile.php">
                    <i class="fas fa-user"></i>
                </a>
                <div class="cart-icon-container">
                    <a href="cart.php"><i class="fas fa-shopping-cart"></i></a>
                </div>
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
            </div>
        </div>
    </div>

    <!-- Categories Listing Section -->
    <section class="categories-listing">
        <h2 class="section-title">OTHER CATEGORIES</h2>
        
        <div class="categories-grid">
            <?php
            if (!empty($search)) {
                echo '<div class="search-results-message">';
                echo 'Search results for: <strong>"' . htmlspecialchars($search) . '"</strong>';
                echo '</div>';
            }
            
            if ($categories_result->num_rows > 0) {
                while($category = $categories_result->fetch_assoc()) {
                    echo '<div class="category-card" onclick="window.location.href=\'category_products.php?category=' . urlencode($category['category_name']) . '\'">';
                    echo '<h3>' . htmlspecialchars($category['category_name']) . '</h3>';
                    echo '</div>';
                }
            } else {
                if (!empty($search)) {
                    echo '<div class="search-results-message" style="grid-column: 1/-1;">';
                    echo 'No categories found matching: <strong>"' . htmlspecialchars($search) . '"</strong>';
                    echo '</div>';
                } else {
                    echo '<p style="grid-column: 1/-1; text-align: center;">No categories available at the moment.</p>';
                }
            }
            $conn->close();
            ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-links">
            <a href="ABOUTUS.html">ABOUT US</a>
            <a href="CONTACT.html">CONTACT</a>
            <a href="TOS.html">TERMS OF SERVICE</a>
        </div>
        
        <div class="social-icons">
            <a href="#facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#instagram"><i class="fab fa-instagram"></i></a>
        </div>
        
        <div class="copyright">
            &copy; 2025 NEXUS GAMING STORE. ALL RIGHTS RESERVED.<br>
            NEXUS is not affiliated with Nintendo or any other game publishers.
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Mobile menu functionality
            const menuOverlay = document.getElementById("menuOverlay");
            const menuIcon = document.getElementById("menuIcon");
            const closeMenu = document.getElementById("closeMenu");

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

            // Search functionality
            const searchIcon = document.getElementById("searchIcon");
            const searchOverlay = document.getElementById("searchOverlay");
            const closeSearch = document.getElementById("closeSearch");

            // Open search
            searchIcon.addEventListener("click", function() {
                searchOverlay.style.display = "flex";
                document.getElementById("searchInput").focus();
            });

            // Close search
            closeSearch.addEventListener("click", function() {
                searchOverlay.style.display = "none";
            });

            // Close search when clicking outside
            searchOverlay.addEventListener("click", function(e) {
                if (e.target === searchOverlay) {
                    searchOverlay.style.display = "none";
                }
            });

            // Prevent form from closing when clicking inside
            document.getElementById("searchContainer").addEventListener("click", function(e) {
                e.stopPropagation();
            });

            // Add click functionality to category cards
            const cards = document.querySelectorAll(".category-card");
            cards.forEach(card => {
                card.addEventListener("mouseenter", function() {
                    this.style.transform = "translateY(-5px)";
                    this.style.boxShadow = "0 10px 20px rgba(255, 0, 0, 0.15)";
                });
                
                card.addEventListener("mouseleave", function() {
                    this.style.transform = "translateY(0)";
                    this.style.boxShadow = "none";
                });
            });
        });
    </script>
</body>
</html>