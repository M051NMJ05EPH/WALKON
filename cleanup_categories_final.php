<?php
include 'config.php';

try {
    $allowed_categories = [
        "Sneakers",
        "Running Shoes",
        "Boots",
        "Sandals & Slides",
        "Formal Shoes",
        "Sports"
    ];

    $in_placeholder = implode(',', array_fill(0, count($allowed_categories), '?'));
    
    // 1. Get IDs of categories to be removed
    $stmt = $pdo->prepare("SELECT id, name FROM categories WHERE name NOT IN ($in_placeholder)");
    $stmt->execute($allowed_categories);
    $to_remove = $stmt->fetchAll();

    if ($to_remove) {
        foreach ($to_remove as $cat) {
            echo "Removing category: {$cat['name']} (ID: {$cat['id']})\n";
            
            // Subcategories should be deleted via ON DELETE CASCADE if set up correctly.
            // But let's be explicit just in case.
            $del_subs = $pdo->prepare("DELETE FROM sub_categories WHERE category_id = ?");
            $del_subs->execute([$cat['id']]);

            $del_cat = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $del_cat->execute([$cat['id']]);
        }
        echo "Cleanup completed.\n";
    } else {
        echo "No extra categories found to remove.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
