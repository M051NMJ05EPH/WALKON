<?php
include 'config.php';

try {
    // 1. Rename "Formal Shoes" to "Formal"
    $stmt = $pdo->prepare("UPDATE categories SET name = 'Formal', slug = 'formal' WHERE name = 'Formal Shoes'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "Renamed 'Formal Shoes' to 'Formal'.\n";
    } else {
        echo "Category 'Formal Shoes' not found or already renamed.\n";
    }

    // 2. Rename "Sandals & Slides" to "Sandals"
    $stmt = $pdo->prepare("UPDATE categories SET name = 'Sandals', slug = 'sandals' WHERE name = 'Sandals & Slides'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "Renamed 'Sandals & Slides' to 'Sandals'.\n";
    } else {
        echo "Category 'Sandals & Slides' not found or already renamed.\n";
    }

    // 3. Update related subcategories if necessary?
    // Subcategories are linked by ID, so renaming the parent category doesn't break links.
    // However, if we wanted to change subcategories, we'd need more info.
    // User said "sandal & slides only & sandales removed". 
    // If they meant "Sandals" is the name, we are good.

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
