<?php
require 'config.php';

$name = 'Metro Formal Oxford';
$url = 'images/metro_oxford.png';

echo "Updating '$name' with URL: $url\n";

// 1. Get Product ID
$stmt = $pdo->prepare("SELECT id FROM product_base WHERE name LIKE ?");
$stmt->execute(["%$name%"]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if ($product) {
    $pid = $product['id'];
    echo "Found Product ID: $pid\n";
    
    // 2. Update Image
    // Check if image exists first
    $check = $pdo->prepare("SELECT id FROM product_media WHERE product_id = ?");
    $check->execute([$pid]);
    if ($check->rowCount() > 0) {
        $update = $pdo->prepare("UPDATE product_media SET url = ? WHERE product_id = ?");
        $update->execute([$url, $pid]);
        echo "Updated existing image.\n";
    } else {
        $insert = $pdo->prepare("INSERT INTO product_media (product_id, url, type, is_primary, created_at) VALUES (?, ?, 'image', 1, NOW())");
        $insert->execute([$pid, $url]);
        echo "Inserted new image.\n";
    }
} else {
    echo "Product '$name' not found!\n";
}
?>
