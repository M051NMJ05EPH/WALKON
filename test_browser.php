<?php
require_once 'config.php';

$product_id = 122;
echo "<h1>Testing via Browser</h1>";
echo "Testing query for Product ID: $product_id<br>";

try {
    $sql = "SELECT color_name, color_code FROM product_colors WHERE product_id = ?";
    echo "SQL: $sql<br>";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $colors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Query Successful!<br>";
    echo "<pre>";
    print_r($colors);
    echo "</pre>";

} catch (PDOException $e) {
    echo "Query Failed: " . $e->getMessage() . "<br>";
}
