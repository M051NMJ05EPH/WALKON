<?php
include 'config.php';
try {
    echo "=== Brands ===\n";
    $stmt = $pdo->query("SELECT id, name FROM brands");
    while($row = $stmt->fetch()) { echo $row['id'] . ': ' . $row['name'] . "\n"; }
    
    echo "\n=== Product Count ===\n";
    echo $pdo->query("SELECT COUNT(*) FROM product_base")->fetchColumn() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
