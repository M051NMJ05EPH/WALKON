<?php
include 'config.php';

try {
    echo "<h1>Adding Footwear Subcategories...</h1>";

    // 1. Ensure 'Footwear' category exists
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = 'Footwear'");
    $stmt->execute();
    $cat = $stmt->fetch();

    if (!$cat) {
        $pdo->prepare("INSERT INTO categories (name, description) VALUES ('Footwear', 'All types of footwear')")->execute();
        $footwear_id = $pdo->lastInsertId();
        echo "✅ Created 'Footwear' category.<br>";
    } else {
        $footwear_id = $cat['id'];
        echo "ℹ️ 'Footwear' category already exists (ID: $footwear_id).<br>";
    }

    // 2. Define subcategories
    $subcategories = [
        'Sneakers',
        'Formal Shoes',
        'Casual Shoes',
        'Sports Shoes',
        'Sandals & Floaters',
        'Boots',
        'Loafers',
        'Slides & Flip Flops',
        'Ethnic Shoes',
        'Performance Trainers',
        'Luxury Heels',
        'Designer Sneakers',
        'Orthopedic Footwear',
        'Sustainable Footwear'
    ];

    $stmt_check = $pdo->prepare("SELECT id FROM sub_categories WHERE category_id = ? AND name = ?");
    $stmt_insert = $pdo->prepare("INSERT INTO sub_categories (category_id, name) VALUES (?, ?)");

    foreach ($subcategories as $sub) {
        $stmt_check->execute([$footwear_id, $sub]);
        if (!$stmt_check->fetch()) {
            $stmt_insert->execute([$footwear_id, $sub]);
            echo "✅ Added subcategory: $sub<br>";
        } else {
            echo "ℹ️ Subcategory '$sub' already exists.<br>";
        }
    }

    echo "<h3>Done!</h3>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>Error: " . $e->getMessage() . "</h3>";
}
?>
