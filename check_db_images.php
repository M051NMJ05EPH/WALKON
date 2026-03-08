<?php
require_once 'config.php';

try {
    echo "Checking database for 'img_%' ...\n";
    $stmt = $pdo->query("SELECT count(*) as count FROM product_media WHERE url LIKE '%img_%'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Found " . $result['count'] . " images matching 'img_%' in product_media.\n";

    if ($result['count'] > 0) {
        $stmt = $pdo->query("SELECT * FROM product_media WHERE url LIKE '%img_%' LIMIT 5");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            echo " - " . $row['url'] . " (Product ID: " . $row['product_id'] . ")\n";
        }
    } else {
        // Check products table legacy column
        echo "Checking legacy 'products' table for 'img_%'...\n";
        $stmt = $pdo->query("SELECT count(*) as count FROM products WHERE images LIKE '%img_%'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Found " . $result['count'] . " legacy products matching 'img_%'.\n";
        
        // Check product_base
             echo "Checking 'product_base' count...\n";
             $stmt = $pdo->query("SELECT count(*) as count FROM product_base");
             $res = $stmt->fetch();
             echo "Total products in product_base: " . $res['count'] . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
