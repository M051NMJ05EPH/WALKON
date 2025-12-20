<?php session_start(); if (!isset($_SESSION['user_id'])) header('Location: signup.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products - WALKON Supplier Dashboard</title>
    <style>
        body {font-family:Arial; padding:20px; background:#f5f5f5;}
        table {width:100%; border-collapse:collapse; margin-top:20px;}
        th, td {padding:12px; border:1px solid #ddd; text-align:left;}
        th {background:#00796b; color:white;}
        img {width:80px; height:auto; border-radius:8px;}
    </style>
</head>
<body>
    <h1>Welcome, Supplier! Manage Your Shoes</h1>
    <a href="logout.php">Logout</a>
    <table>
        <tr><th>Image</th><th>Name</th><th>SKU</th><th>Price</th><th>Stock</th></tr>
        <?php
        require 'db_connect.php';
        $stmt = $pdo->query("SELECT * FROM products LIMIT 10");
        while ($row = $stmt->fetch()) {
            echo "<tr>
                <td><img src='{$row['image_url']}' alt='shoe'></td>
                <td>{$row['name']}</td>
                <td>{$row['sku']}</td>
                <td>\${$row['price']}</td>
                <td>{$row['stock']}</td>
            </tr>";
        }
        ?>
    </table>
</body>
</html>