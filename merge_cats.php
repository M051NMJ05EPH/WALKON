<?php
include 'config.php';

try {
    $pdo->beginTransaction();

    // 1. Merge "Formal Shoes" (18) into "Formal" (4)
    // Get image from 18
    $stmt = $pdo->query("SELECT image_url FROM categories WHERE id = 18");
    $img = $stmt->fetchColumn();
    if ($img) {
        // Update 4 with this image
        $pdo->prepare("UPDATE categories SET image_url = ? WHERE id = 4")->execute([$img]);
    }
    
    // Move relations
    $pdo->exec("UPDATE IGNORE sub_categories SET category_id = 4 WHERE category_id = 18");
    $pdo->exec("UPDATE product_base SET category_id = 4 WHERE category_id = 18");
    
    // Delete 18
    $pdo->exec("DELETE FROM categories WHERE id = 18");
    echo "Merged 'Formal Shoes' into 'Formal'.\n";

    // 2. Merge "Sandals & Slides" (17) into "Sandals" (3)
    // Get image from 17
    $stmt = $pdo->query("SELECT image_url FROM categories WHERE id = 17");
    $img = $stmt->fetchColumn();
    if ($img) {
        $pdo->prepare("UPDATE categories SET image_url = ? WHERE id = 3")->execute([$img]);
    }

    // Move relations
    $pdo->exec("UPDATE IGNORE sub_categories SET category_id = 3 WHERE category_id = 17");
    $pdo->exec("UPDATE product_base SET category_id = 3 WHERE category_id = 17");

    // Delete 17
    $pdo->exec("DELETE FROM categories WHERE id = 17");
    echo "Merged 'Sandals & Slides' into 'Sandals'.\n";
    
    $pdo->commit();

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
