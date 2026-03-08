<?php
require_once 'config.php';

try {
    // 1. Add color column if not exists
    echo "Checking 'product_media' table schema...\n";
    $columns = $pdo->query("DESCRIBE product_media")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('color', $columns)) {
        echo "Adding 'color' column to product_media...\n";
        $pdo->exec("ALTER TABLE product_media ADD COLUMN color VARCHAR(50) DEFAULT NULL");
    } else {
        echo "'color' column already exists.\n";
    }

    // 2. Clear old media for Product 122 (Sports Sandal) to avoid confusion
    echo "Updating media for Product ID 122 (Sports Sandal SS-101)...\n";
    $pdo->exec("DELETE FROM product_media WHERE product_id = 122");

    // 3. Insert new media with colors
    // Using placeholder images for demonstration
    $media = [
        [122, 'https://images.unsplash.com/photo-1603808033192-082d6919d3e1?auto=format&fit=crop&q=80&w=1000', 'image', 1, 'Navy Blue'],
        [122, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&q=80&w=1000', 'image', 0, 'Red'],
        // Add a default or side view if needed
    ];

    $stmt = $pdo->prepare("INSERT INTO product_media (product_id, url, type, is_primary, color) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($media as $m) {
        $stmt->execute($m);
    }
    echo "Inserted " . count($media) . " new images with color tags.\n";

    // 4. Ensure product_colors has matching colors
    $pdo->exec("DELETE FROM product_colors WHERE product_id = 122");
    $colors = [
        ['Navy Blue', '#000080'],
        ['Red', '#FF0000']
    ];
    $stmtColor = $pdo->prepare("INSERT INTO product_colors (product_id, color_name, color_code) VALUES (122, ?, ?)");
    foreach ($colors as $c) {
        $stmtColor->execute($c);
    }
    echo "Updated product_colors for consistency.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
