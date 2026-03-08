<?php
require_once 'config.php';

$product_id = 122;
echo "Testing query for Product ID: $product_id\n";

try {
    $sql = "SELECT color_name, color_code FROM product_colors WHERE product_id = ?";
    echo "SQL: $sql\n";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $colors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Query Successful!\n";
    print_r($colors);

} catch (PDOException $e) {
    echo "Query Failed: " . $e->getMessage() . "\n";
}
