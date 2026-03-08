<?php
require_once 'config.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM product_base WHERE name LIKE ?");
    $stmt->execute(['%Sports Sandal SS-101%']);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo "Product Found: " . $product['name'] . " (ID: " . $product['id'] . ")\n";
        
        // Colors
        $stmt = $pdo->prepare("SELECT * FROM product_colors WHERE product_id = ?");
        $stmt->execute([$product['id']]);
        $colors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Colors: " . count($colors) . "\n";
        foreach ($colors as $c) {
            echo " - " . $c['color_name'] . " (" . $c['color_code'] . ")\n";
        }

        // Media
        $stmt = $pdo->prepare("SELECT * FROM product_media WHERE product_id = ?");
        $stmt->execute([$product['id']]);
        $media = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Media: " . count($media) . "\n";
        foreach ($m as $media) {
            echo " - " . $m['url'] . " (" . $m['type'] . ")\n";
        }

    } else {
        echo "Product 'Sports Sandal SS-101' not found.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
