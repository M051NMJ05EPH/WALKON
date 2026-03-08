<?php
require 'config.php';

try {
    $stmt = $pdo->query("
        SELECT b.name, COUNT(pb.id) as product_count 
        FROM brands b
        LEFT JOIN product_specs ps ON b.id = ps.brand_id
        LEFT JOIN product_base pb ON ps.product_id = pb.id
        GROUP BY b.id
    ");
    $brand_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Product count per brand:\n";
    foreach ($brand_stats as $stat) {
        echo "- " . $stat['name'] . ": " . $stat['product_count'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
