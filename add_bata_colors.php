<?php
include 'config.php';

try {
    $pdo->exec("USE `walkon_shoes_v2`");
    echo "<h1>🎨 Adding Colors to Bata City Formal</h1>";

    $productName = 'Bata City Formal';
    $stmt = $pdo->prepare("SELECT id FROM product_base WHERE name = ?");
    $stmt->execute([$productName]);
    $pid = $stmt->fetchColumn();

    if ($pid) {
        // 1. Add Colors
        $colors = [
            ['name' => 'Classic Black', 'hex' => '#000000'],
            ['name' => 'Burgundy Red', 'hex' => '#7f1d1d'],
            ['name' => 'Navy Blue', 'hex' => '#1e3a8a']
        ];
        
        // Clear existing colors to prevent duplicates if any
        $pdo->prepare("DELETE FROM product_colors WHERE product_id = ?")->execute([$pid]);

        $stmtColor = $pdo->prepare("INSERT INTO product_colors (product_id, color_name, color_hex) VALUES (?, ?, ?)");
        foreach ($colors as $c) {
            $stmtColor->execute([$pid, $c['name'], $c['hex']]);
        }
        echo "✅ Colors added: Black, Red, Blue.<br>";

        // 2. Add Media
        // We keep the existing black image which is likely primary.
        // We add new images for Red and Blue.
        
        $new_images = [
            'https://images.unsplash.com/photo-1614252369475-531eba835eb1', // Red
            'https://images.unsplash.com/photo-1478186111896-bc5d3663673b'  // Blue
        ];

        $stmtMedia = $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary) VALUES (?, ?, 0)");
        foreach ($new_images as $url) {
            // Check if exists to avoid spamming
            $check = $pdo->prepare("SELECT id FROM product_media WHERE product_id = ? AND url = ?");
            $check->execute([$pid, $url]);
            if (!$check->fetchColumn()) {
                $stmtMedia->execute([$pid, $url]);
                echo "✅ Added image: $url<br>";
            } else {
                 echo "ℹ️ Image already exists: $url<br>";
            }
        }

    } else {
        echo "❌ Product '$productName' not found. Please ensure it was seeded correctly.";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
