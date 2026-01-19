<?php
include 'config.php';

try {
    // 1. Add image_url column to categories if it doesn't exist
    try {
        $pdo->query("SELECT image_url FROM categories LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE categories ADD COLUMN image_url VARCHAR(500)");
        echo "Added image_url column to categories.\n";
    }

    // 2. Data to seed
    $categories_data = [
        [
            "name" => "Sneakers",
            "subcategories" => ["Casual", "Lifestyle", "High-Top", "Low-Top"],
            "image" => "https://images.unsplash.com/photo-1556906781-9a412961c28c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
        ],
        [
            "name" => "Running Shoes",
            "subcategories" => ["Trail", "Road", "Racing", "Training"],
            "image" => "https://images.unsplash.com/photo-1595341888016-a392ef81b7de?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
        ],
        [
            "name" => "Boots",
            "subcategories" => ["Chelsea", "Combat", "Hiking"],
            "image" => "https://images.unsplash.com/photo-1608256246200-53e635b5b65f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
        ],
        [
            "name" => "Sandals & Slides",
            "subcategories" => ["Flip-Flops", "Sliders", "Sport Sandals"],
            "image" => "https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
        ],
        [
            "name" => "Formal Shoes",
            "subcategories" => ["Oxford", "Derby", "Loafers", "Brogues"],
            "image" => "https://images.unsplash.com/photo-1531310197839-ccf54634509e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
        ],
        [
            "name" => "Sports",
            "subcategories" => ["Football", "Basketball", "Tennis", "Cricket"],
            "image" => "https://images.unsplash.com/photo-1460353581641-37baddab0fa2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
        ]
    ];

    foreach ($categories_data as $cat) {
        // Check if category exists
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$cat['name']]);
        $existing = $stmt->fetch();

        if ($existing) {
            $cat_id = $existing['id'];
            // Update image if needed
            $update = $pdo->prepare("UPDATE categories SET image_url = ? WHERE id = ?");
            $update->execute([$cat['image'], $cat_id]);
            echo "Updated category: {$cat['name']}\n";
        } else {
            // Insert category
            $insert = $pdo->prepare("INSERT INTO categories (name, image_url) VALUES (?, ?)");
            $insert->execute([$cat['name'], $cat['image']]);
            $cat_id = $pdo->lastInsertId();
            echo "Inserted category: {$cat['name']}\n";
        }

        // Handle Subcategories (simple sync)
        foreach ($cat['subcategories'] as $sub_name) {
             $stmt_sub = $pdo->prepare("SELECT id FROM sub_categories WHERE category_id = ? AND name = ?");
             $stmt_sub->execute([$cat_id, $sub_name]);
             
             if (!$stmt_sub->fetch()) {
                 $ins_sub = $pdo->prepare("INSERT INTO sub_categories (category_id, name) VALUES (?, ?)");
                 $ins_sub->execute([$cat_id, $sub_name]);
                 echo "  - Added subcategory: $sub_name\n";
             }
        }
    }

    echo "Data seeding completed successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
