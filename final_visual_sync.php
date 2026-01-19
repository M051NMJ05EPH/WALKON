<?php
include 'config.php';

try {
    $pdo->beginTransaction();

    // 1. Update Category Images
    $category_images = [
        'Boots' => 'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=800&q=80',
        'Formal Shoes' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&q=80',
        'Running Shoes' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80',
        'Sandals & Slides' => 'https://images.unsplash.com/photo-1603487742131-4160ec999306?auto=format&fit=crop&w=800&q=80',
        'Sneakers' => 'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=800&q=80',
        'Sports' => 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=800&q=80'
    ];
    foreach ($category_images as $name => $url) {
        $pdo->prepare("UPDATE categories SET image_url = ? WHERE name = ?")->execute([$url, $name]);
    }

    // 2. Update Product Images
    $product_images = [
        'Elite Leather Oxford' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&q=80',
        'Neo-Mesh Ultra Runner' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80',
        'Synthetic Pro Court' => 'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=800&q=80',
        'Rugged Timberland Boot' => 'https://images.unsplash.com/photo-1520639889313-72721e0ab9ef?auto=format&fit=crop&w=800&q=80',
        'Cloud-Mesh Sports Trainer' => 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=800&q=80'
    ];
    foreach ($product_images as $name => $url) {
        $stmt = $pdo->prepare("SELECT id FROM product_base WHERE name = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$name]);
        $res = $stmt->fetch();
        if ($res) {
            $pid = $res['id'];
            $pdo->prepare("DELETE FROM product_media WHERE product_id = ?")->execute([$pid]);
            $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary) VALUES (?, ?, ?)")->execute([$pid, $url, 1]);
        }
    }

    $pdo->commit();
    echo "SUCCESS: Premium images synced for Categories and Products.\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
