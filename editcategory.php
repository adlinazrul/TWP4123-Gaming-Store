<?php
include 'database.php';
$id = $_GET['id'];

$sql = "SELECT * FROM product_categories WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['category_name'];
    $desc = $_POST['description'];

    $update = "UPDATE product_categories SET category_name=?, description=? WHERE id=?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ssi", $name, $desc, $id);
    if ($stmt->execute()) {
        header("Location: managecategory.php");
        exit;
    } else {
        $error = "Update failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Category</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="admindashboard.css">
    <style>
        <?php include 'addcategory-style.css'; ?>
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <section id="content">
        <?php include 'navbar.php'; ?>
        <main>
            <h1>Edit Category</h1>
            <div class="form-container">
                <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
                <form method="POST">
                    <label for="category_name">Category Name:</label>
                    <input type="text" id="category_name" name="category_name" value="<?= htmlspecialchars($category['category_name']); ?>" required>

                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($category['description']); ?></textarea>

                    <button type="submit">Update Category</button>
                </form>
            </div>
        </main>
    </section>
</body>
</html>
