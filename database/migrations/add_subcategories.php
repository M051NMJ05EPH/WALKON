<?php
// add_subcategories.php
// This script adds predefined subcategories (Men, Women, Boy, Girl, Babies, Kids, Unisex)
// to each existing category in the `sub_categories` table.

require 'config.php';

$subcategories = ['Men', 'Women', 'Boy', 'Girl', 'Babies', 'Kids', 'Unisex'];

try {
    // Fetch all categories
    $stmt = $pdo->query('SELECT id FROM categories');
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $pdo->beginTransaction();
    $insertStmt = $pdo->prepare('INSERT INTO sub_categories (category_id, name) VALUES (?, ?)');
    foreach ($categories as $catId) {
        foreach ($subcategories as $sub) {
            // Check if already exists to avoid duplicates
            $check = $pdo->prepare('SELECT COUNT(*) FROM sub_categories WHERE category_id = ? AND name = ?');
            $check->execute([$catId, $sub]);
            if ($check->fetchColumn() == 0) {
                $insertStmt->execute([$catId, $sub]);
            }
        }
    }
    $pdo->commit();
    echo "Subcategories added successfully.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
