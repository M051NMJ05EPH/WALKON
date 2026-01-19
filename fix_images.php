<?php
include 'config.php';

$images = [
    'Elite Leather Oxford' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=1000&q=80',
    'Neo-Mesh Ultra Runner' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=80',
    'Synthetic Pro Court' => 'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=1000&q=80',
    'Rugged Timberland Boot' => 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?auto=format&fit=crop&w=1000&q=80',
    'Cloud-Mesh Sports Trainer' => 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=1000&q=80'
];

try {
    $pdo->beginTransaction();
    foreach ($images as $name => $url) {
        $stmt = $pdo->prepare("SELECT id FROM product_base WHERE name = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$name]);
        $res = $stmt->fetch();
        if ($res) {
            $pid = $res['id'];
            $pdo->prepare("UPDATE product_media SET url = ? WHERE product_id = ? AND is_primary = 1")->execute([$url, $pid]);
        }
    }
    $pdo->commit();
    echo "SUCCESS: Product images refreshed.\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
