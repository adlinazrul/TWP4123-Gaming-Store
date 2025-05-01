<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        h1 {
            margin: 30px 0 10px;
            font-size: 32px;
            text-align: center;
            color: #333;
        }

        .container {
            padding: 20px;
            max-width: 1000px;
            margin: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background-color: #2c3e50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .button-group {
            display: flex;
            gap: 10px;
        }

        .button-group button {
            padding: 6px 12px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            transition: background-color 0.3s ease;
        }

        .button-group button:first-child {
            background-color: #e74c3c;
        }

        .button-group button:first-child:hover {
            background-color: #c0392b;
        }

        .button-group button:last-child {
            background-color: #d63031;
        }

        .button-group button:last-child:hover {
            background-color: #b71c1c;
        }
    </style>
</head>
<body>
    <h1>Admin Dashboard</h1>
    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>101</td>
                    <td>Product A</td>
                    <td>10</td>
                    <td>
                        <div class="button-group">
                            <button>Edit Quantity</button>
                            <button>Delete</button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>102</td>
                    <td>Product B</td>
                    <td>5</td>
                    <td>
                        <div class="button-group">
                            <button>Edit Quantity</button>
                            <button>Delete</button>
                        </div>
                    </td>
                </tr>
                <!-- Add more rows as needed -->
            </tbody>
        </table>
    </div>
</body>
</html>
