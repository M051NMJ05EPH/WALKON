<?php
include 'config.php';

try {
    $stmt = $pdo->query("SELECT id, name, category_id, seller_id FROM product_base");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h1>Products Check</h1>";
    if (empty($products)) {
        echo "No products found.<br>";
    } else {
        echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Category ID</th><th>Seller ID</th></tr>";
        foreach ($products as $p) {
            echo "<tr>";
            echo "<td>" . $p['id'] . "</td>";
            echo "<td>" . $p['name'] . "</td>";
            echo "<td>" . ($p['category_id'] ?? 'NULL') . "</td>";
            echo "<td>" . $p['seller_id'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check Specs for Brands
    echo "<h1>Specs Check (Brands)</h1>";
    $stmt = $pdo->query("SELECT product_id, brand_id, gender FROM product_specs");
    $specs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
     if (empty($specs)) {
        echo "No specs found.<br>";
    } else {
        echo "<table border='1'><tr><th>Product ID</th><th>Brand ID</th><th>Gender</th></tr>";
        foreach ($specs as $s) {
            echo "<tr>";
            echo "<td>" . $s['product_id'] . "</td>";
            echo "<td>" . ($s['brand_id'] ?? 'NULL') . "</td>";
             echo "<td>" . ($s['gender'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
