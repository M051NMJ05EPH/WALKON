<?php
include 'config.php';

$images = [
    'Elite Leather Oxford' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&q=80',
    'Neo-Mesh Ultra Runner' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80',
    'Synthetic Pro Court' => 'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=800&q=80',
    'Rugged Timberland Boot' => 'https://images.unsplash.com/photo-1520639889313-7272a6131c48?auto=format&fit=crop&w=800&q=80',
    'Cloud-Mesh Sports Trainer' => 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=800&q=80'
];

try {
    $pdo->beginTransaction();
    
    foreach ($images as $name => $url) {
        $stmt = $pdo->prepare("SELECT id FROM product_base WHERE name = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$name]);
        $res = $stmt->fetch();
        
        if ($res) {
            $pid = $res['id'];
            // Check if image already exists
            $check = $pdo->prepare("SELECT id FROM product_media WHERE product_id = ? AND url = ?");
            $check->execute([$pid, $url]);
            if (!$check->fetch()) {
                $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary) VALUES (?, ?, ?)")
                    ->execute([$pid, $url, 1]);
                echo "Added image for: $name (ID: $pid)\n";
            } else {
                echo "Image already exists for: $name\n";
            }
        }
    }
    
    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
