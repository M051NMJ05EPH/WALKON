<?php
include 'config.php';

try {
    echo "Starting data migration...\n";

    // 1. Fetch existing products
    $stmt = $pdo->query("SELECT id, category, subcategory, product_name, description, images FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($products) . " products to migrate.\n";

    foreach ($products as $p) {
        // Map Category
        $cat_id = null;
        if (!empty($p['category'])) {
            $cat_stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
            $cat_stmt->execute([$p['category']]);
            $cat_row = $cat_stmt->fetch();
            if ($cat_row) {
                $cat_id = $cat_row['id'];
            }
        }

        // Map Sub-category
        $sub_cat_id = null;
        if ($cat_id && !empty($p['subcategory'])) {
            $sub_stmt = $pdo->prepare("SELECT id FROM sub_categories WHERE category_id = ? AND name = ?");
            $sub_stmt->execute([$cat_id, $p['subcategory']]);
            $sub_row = $sub_stmt->fetch();
            if ($sub_row) {
                $sub_cat_id = $sub_row['id'];
            }
        }

        // Update product with IDs
        $update_p = $pdo->prepare("UPDATE products SET category_id = ?, sub_category_id = ? WHERE id = ?");
        $update_p->execute([$cat_id, $sub_cat_id, $p['id']]);

        // Insert into product_details
        $ins_detail = $pdo->prepare("INSERT INTO product_details (product_id, description, images) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE description=VALUES(description), images=VALUES(images)");
        $ins_detail->execute([
            $p['id'],
            $p['description'] ?? '',
            $p['images'] ?? '[]'
        ]);
        
        echo "Migrated product ID: " . $p['id'] . "\n";
    }

    echo "Data migration completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
