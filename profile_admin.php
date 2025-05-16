<?php
session_start();
require_once "connection.php"; // Your database connection

// Example: get admin ID from session (adjust as needed)
$admin_id = $_SESSION['admin_id'] ?? 1; // fallback to 1 for testing

// Handle form submission to update profile
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize input
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $salary = $_POST['salary'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Handle image upload if exists
    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $uploadDir = "image/";
        $uploadFile = $uploadDir . $imageName;
        move_uploaded_file($imageTmpPath, $uploadFile);
    }

    // Update query, only update image if new image uploaded
    if ($imageName) {
        $sql = "UPDATE admin_list SET username=?, email=?, salary=?, password=?, image=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssissi", $username, $email, $salary, $password, $imageName, $admin_id);
    } else {
        $sql = "UPDATE admin_list SET username=?, email=?, salary=?, password=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $username, $email, $salary, $password, $admin_id);
    }
    $stmt->execute();
    $stmt->close();
}

// Fetch admin data from database
$sql = "SELECT * FROM admin_list WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Fallbacks for empty fields
$imageSrc = !empty($admin['image']) ? "image/" . htmlspecialchars($admin['image']) : "image/default_profile.jpg";
$role = htmlspecialchars($admin['role'] ?? 'Admin'); // Default role Admin if empty
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Profile</title>
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f0f0;
    margin: 0; padding: 0;
  }
  .container {
    max-width: 480px;
    margin: 40px auto;
    background: #fff;
    padding: 30px 40px 40px 40px;
    border-radius: 15px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.12);
  }
  h2 {
    text-align: center;
    margin-bottom: 35px;
    color: #333;
  }

  /* Profile image wrapper */
  .profile-image-wrapper {
    position: relative;
    width: 180px;
    height: 180px;
    margin: 0 auto 10px auto;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
  }
  .profile-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: filter 0.3s ease;
  }
  .profile-image-wrapper:hover img {
    filter: brightness(0.5);
  }
  .overlay-text {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-weight: 700;
    font-size: 18px;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
    user-select: none;
  }
  .profile-image-wrapper:hover .overlay-text,
  .profile-image-wrapper:focus .overlay-text {
    opacity: 1;
  }
  #imageInput {
    display: none;
  }

  /* Role badge */
  .role-badge {
    max-width: 180px;
    margin: 10px auto 30px auto;
    padding: 10px 20px;
    background: linear-gradient(135deg, #ef4444, #f97316);
    color: white;
    font-weight: 700;
    font-size: 16px;
    border-radius: 30px;
    text-align: center;
    letter-spacing: 0.1em;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.6);
    user-select: none;
    text-transform: uppercase;
  }

  label {
    display: block;
    margin-bottom: 6px;
    color: #444;
    font-weight: 600;
  }
  input[type="text"], input[type="email"], input[type="number"], input[type="password"] {
    width: 100%;
    padding: 10px 14px;
    margin-bottom: 22px;
    border-radius: 10px;
    border: 1.5px solid #ddd;
    font-size: 16px;
    transition: border-color 0.3s ease;
  }
  input[type="text"]:focus, input[type="email"]:focus, input[type="number"]:focus, input[type="password"]:focus {
    outline: none;
    border-color: #f97316;
  }
  input[readonly] {
    background: #eee;
    cursor: not-allowed;
    color: #777;
  }

  /* Password container and toggle button */
  .password-container {
    position: relative;
  }
  .toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: #f97316;
    border: none;
    color: white;
    padding: 6px 12px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
  }
  .toggle-password:hover {
    background: #ef4444;
  }

  input[type="submit"] {
    width: 100%;
    background: #f97316;
    color: white;
    font-weight: 700;
    font-size: 18px;
    padding: 14px;
    border: none;
    border-radius: 15px;
    cursor: pointer;
    transition: background 0.3s ease;
  }
  input[type="submit"]:hover {
    background: #ef4444;
  }
</style>
</head>
<body>

<div class="container" role="main" aria-label="Admin Profile Form">
  <h2>My Profile</h2>

  <form method="post" enctype="multipart/form-data" id="profileForm" aria-describedby="profileDesc">
    <div id="profileDesc" class="sr-only">Update your profile details including username, email, salary, and password. You can also change your profile image by clicking on it.</div>

    <div class="profile-image-wrapper" id="imageWrapper" tabindex="0" aria-label="Change Profile Image">
      <img src="<?= $imageSrc ?>" alt="Profile Image" id="profileImage" />
      <div class="overlay-text">Change Image Profile</div>
      <input type="file" name="image" id="imageInput" accept="image/*" aria-label="Upload new profile image" />
    </div>

    <div class="role-badge" aria-label="User Role Badge"><?= $role ?></div>

    <label for="username">Username:</label>
    <input type="text" id="username" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required aria-required="true" />

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required aria-required="true" />

    <label for="position">Position:</label>
    <input type="text" id="position" name="position" value="<?= htmlspecialchars($admin['position']) ?>" readonly aria-readonly="true" />

    <label for="salary">Salary:</label>
    <input type="number" id="salary" name="salary" value="<?= htmlspecialchars($admin['salary']) ?>" required aria-required="true" />

    <label for="password">Password:</label>
    <div class="password-container">
      <input type="password" id="password" name="password" value="<?= htmlspecialchars($admin['password']) ?>" required aria-required="true" />
      <button type="button" class="toggle-password" onclick="togglePasswordVisibility()" aria-pressed="false" aria-label="Toggle password visibility">Show</button>
    </div>

    <input type="submit" value="Update Profile" />
  </form>
</div>

<script>
  function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.querySelector('.toggle-password');
    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      toggleBtn.textContent = 'Hide';
      toggleBtn.setAttribute('aria-pressed', 'true');
    } else {
      passwordInput.type = 'password';
      toggleBtn.textContent = 'Show';
      toggleBtn.setAttribute('aria-pressed', 'false');
    }
  }

  // Clicking the image wrapper triggers the hidden file input
  document.getElementById('imageWrapper').addEventListener('click', () => {
    document.getElementById('imageInput').click();
  });

  // Preview chosen image immediately
  document.getElementById('imageInput').addEventListener('change', (event) => {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = e => {
        document.getElementById('profileImage').src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  });

  // Keyboard accessibility for imageWrapper (Enter key)
  document.getElementById('imageWrapper').addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      document.getElementById('imageInput').click();
    }
  });
</script>

</body>
</html>
