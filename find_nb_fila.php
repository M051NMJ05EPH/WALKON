<?php
include 'config.php';

$names = ['New Balance 530 Silver Metallic', 'Fila Men Red Replica'];

foreach ($names as $name) {
    $stmt = $pdo->prepare("SELECT * FROM product_base WHERE name LIKE ?");
    $stmt->execute(["%$name%"]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo "Found: " . $product['name'] . " (ID: " . $product['id'] . ")\n";
        
        $stmt_media = $pdo->prepare("SELECT * FROM product_media WHERE product_id = ?");
        $stmt_media->execute([$product['id']]);
        $media = $stmt_media->fetchAll(PDO::FETCH_ASSOC);
        
        if ($media) {
            foreach ($media as $m) {
                echo "  - Media: " . $m['url'] . "\n";
            }
        } else {
            echo "  - No media found.\n";
        }
    } else {
        echo "Not Found: $name\n";
    }
}
?>
