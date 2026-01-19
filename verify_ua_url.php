<?php
include 'config.php';
try {
    $stmt = $pdo->prepare("SELECT url FROM product_media WHERE product_id = 130 AND is_primary = 1");
    $stmt->execute();
    $media = $stmt->fetch();
    echo "Image URL for Product 130: " . ($media ? $media['url'] : "No image found") . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
