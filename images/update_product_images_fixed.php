<?php
include 'config.php';

$updates = [
    124 => 'uploads/timberland_boot_124.png',
    91 => 'uploads/power_running_shoe_91.png',
    82 => 'uploads/sparx_casual_82.png'
];

foreach($updates as $id => $url) {
    echo "Updating product ID: $id with URL: $url\n";
    
    // Check if entry exists in product_media
    $stmt = $pdo->prepare("SELECT id FROM product_media WHERE product_id = ? AND is_primary = 1");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    
    if($existing) {
        $stmt = $pdo->prepare("UPDATE product_media SET url = ? WHERE id = ?");
        $stmt->execute([$url, $existing['id']]);
        echo "Updated existing primary image.\n";
    } else {
        $stmt = $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary) VALUES (?, ?, 1)");
        $stmt->execute([$id, $url]);
        echo "Inserted new primary image.\n";
    }
    
    // Also update the fallback if needed or just ensure it works
}
?>
