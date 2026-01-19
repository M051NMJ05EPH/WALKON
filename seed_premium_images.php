<?php
include 'config.php';

// Curated high-quality shoe images from Unsplash
$premium_images = [
    'Elite Leather Oxford' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=1000&q=80',
    'Neo-Mesh Ultra Runner' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=80',
    'Synthetic Pro Court' => 'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=1000&q=80',
    'Rugged Timberland Boot' => 'https://images.unsplash.com/photo-1520639889313-72721e0ab9ef?auto=format&fit=crop&w=1000&q=80',
    'Cloud-Mesh Sports Trainer' => 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=1000&q=80'
];

try {
    $pdo->beginTransaction();

    foreach ($premium_images as $name => $url) {
        $stmt = $pdo->prepare("SELECT id FROM product_base WHERE name = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$name]);
        $res = $stmt->fetch();
        
        if ($res) {
            $pid = $res['id'];
            
            // Delete existing primary media if any
            $pdo->prepare("DELETE FROM product_media WHERE product_id = ? AND is_primary = 1")->execute([$pid]);
            
            // Insert premium image as primary
            $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary) VALUES (?, ?, 1)")
                ->execute([$pid, $url]);
                
            echo "Updated primary image for: $name (ID: $pid)\n";
        } else {
            echo "Product not found: $name\n";
        }
    }

    $pdo->commit();
    echo "\nSUCCESS: All premium images added to database.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
