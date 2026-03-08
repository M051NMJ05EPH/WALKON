<?php
include 'config.php';

$updates = [
    ['id' => 128, 'url' => 'uploads/products/charged_assert_new.png'],
    ['id' => 4, 'url' => 'uploads/products/chelsea_boot_new.png']
];

foreach ($updates as $u) {
    echo "Updating ID {$u['id']} to {$u['url']}...\n";
    // First update the primary media
    $stmt = $pdo->prepare("UPDATE product_media SET url = ? WHERE product_id = ? AND is_primary = 1");
    $stmt->execute([$u['url'], $u['id']]);
    
    // If no primary media updated, maybe it doesn't have one or it's not marked primary?
    if ($stmt->rowCount() == 0) {
        $stmt = $pdo->prepare("UPDATE product_media SET url = ? WHERE product_id = ? ORDER BY id ASC LIMIT 1");
        $stmt->execute([$u['url'], $u['id']]);
    }
    echo "Updated " . $stmt->rowCount() . " rows.\n";
}
?>
