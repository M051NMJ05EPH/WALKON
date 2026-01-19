<?php
include 'config.php';

$category_images = [
    'Boots' => 'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=800&q=80',
    'Formal Shoes' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&q=80',
    'Running Shoes' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80',
    'Sandals & Slides' => 'https://images.unsplash.com/photo-1603487742131-4160ec999306?auto=format&fit=crop&w=800&q=80',
    'Sneakers' => 'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=800&q=80',
    'Sports' => 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=800&q=80'
];

try {
    $pdo->beginTransaction();
    
    foreach ($category_images as $name => $url) {
        $stmt = $pdo->prepare("UPDATE categories SET image_url = ? WHERE name = ?");
        $stmt->execute([$url, $name]);
        echo "Updated image for category: $name\n";
    }
    
    $pdo->commit();
    echo "\nCategory premium images updated successfully.\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
