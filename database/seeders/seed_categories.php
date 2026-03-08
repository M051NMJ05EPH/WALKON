<?php
include 'config.php';

try {
    echo "<h1>Seeding Categories...</h1>";

    // Valid styling images for the bento grid
    // Sneakers (Large)
    // Boots (Wide)
    // Sports (Small)
    // Running Shoes (Small)

    $categories = [
        [
            "name" => "Sneakers",
            "image" => "https://images.unsplash.com/photo-1552346154-21d32810aba3?q=80&w=800&auto=format&fit=crop", // Urban style for large card
            "sub" => ["High-Top", "Canvas", "Basketball", "Skate"]
        ],
        [
            "name" => "Boots",
            "image" => "https://images.unsplash.com/photo-1608256246200-53e635b5b65f?q=80&w=800&auto=format&fit=crop", // Rugged/Leather
            "sub" => ["Chelsea", "Combat", "Hiking", "Chukka"]
        ],
        [
            "name" => "Sports",
            "image" => "https://images.unsplash.com/photo-1511556532299-8f662fc26c06?q=80&w=500&auto=format&fit=crop", // Action shot
            "sub" => ["Running", "Training", "Football", "Tennis"]
        ],
        [
            "name" => "Running Shoes",
            "image" => "https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=500&auto=format&fit=crop", // Dynamic
            "sub" => ["Road", "Trail", "Racing"]
        ],
        [
            "name" => "Formal Shoes",
            "image" => "https://images.unsplash.com/photo-1614252369475-531eba835eb1?q=80&w=500&auto=format&fit=crop",
            "sub" => ["Oxfords", "Loafers"]
        ],
        [
            "name" => "Sandals & Slides",
            "image" => "https://images.unsplash.com/photo-1545127398-5aae4d57c668?q=80&w=500&auto=format&fit=crop",
            "sub" => ["Slides", "Sandals"]
        ]
    ];

    foreach ($categories as $cat) {
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$cat['name']]);
        $existing = $stmt->fetch();

        if ($existing) {
            $catId = $existing['id'];
            $update = $pdo->prepare("UPDATE categories SET image_url = ? WHERE id = ?");
            $update->execute([$cat['image'], $catId]);
            echo "Updated: {$cat['name']}<br>";
        } else {
            $insert = $pdo->prepare("INSERT INTO categories (name, image_url) VALUES (?, ?)");
            $insert->execute([$cat['name'], $cat['image']]);
            $catId = $pdo->lastInsertId();
            echo "Inserted: {$cat['name']}<br>";
        }

        // Subcategories
        foreach ($cat['sub'] as $sub) {
            $checkSub = $pdo->prepare("SELECT id FROM sub_categories WHERE category_id = ? AND name = ?");
            $checkSub->execute([$catId, $sub]);
            if (!$checkSub->fetch()) {
                $insSub = $pdo->prepare("INSERT INTO sub_categories (category_id, name) VALUES (?, ?)");
                $insSub->execute([$catId, $sub]);
            }
        }
    }

    echo "<h3>Seeding Complete!</h3>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>Error: " . $e->getMessage() . "</h3>";
}
?>
