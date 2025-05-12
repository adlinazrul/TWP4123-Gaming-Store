<?php
$host = 'localhost';
$dbname = 'gaming_store';
$username = 'root';
$password = ''; // Change this if your MySQL has a password

// Create a new connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to fetch all categories
$sql = "SELECT * FROM product_categories";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Product Categories</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        h2 {
            margin-top: 20px;
        }
        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #2c3e50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f4f4f4;
        }
        .action-links a {
            margin-right: 10px;
            color: #3498db;
            text-decoration: none;
        }
        .action-links a:hover {
            text-decoration: underline;
        }
        .add-btn {
            display: block;
            width: fit-content;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .add-btn:hover {
            background-color: #219150;
        }
    </style>
</head>
<body>

<h2 align="center">Product Categories</h2>

<a href="addcategory.php" class="add-btn">Add New Category</a>

<table>
    <tr>
        <th>ID</th>
        <th>Category Name</th>
        <th>Description</th>
        <th>Created At</th>
        <th>Actions</th>
    </tr>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['category_name']); ?></td>
                <td><?= htmlspecialchars($row['description']); ?></td>
                <td><?= $row['created_at']; ?></td>
                <td class="action-links">
                    <a href="editcategory.php?id=<?= $row['id']; ?>">Edit</a>
                    <a href="deletecategory.php?id=<?= $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="5" align="center">No categories found.</td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>

<?php $conn->close(); ?>
