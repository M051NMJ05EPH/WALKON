<?php
include 'config.php';

try {
    echo "Starting deep migration...\n";

    // 1. Create tables
    echo "Creating new tables...\n";
    $sql = file_get_contents('deep_normalize.sql');
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($queries as $query) {
        if (!empty($query)) {
            $pdo->exec($query);
        }
    }

    // 2. Insert initial categories if they don't exist (safety)
    $categories = ['Sneakers', 'Boots', 'Sandals', 'Formal', 'Sports'];
    foreach ($categories as $cat) {
        $slug = strtolower($cat);
        $pdo->prepare("INSERT IGNORE INTO categories (name, slug) VALUES (?, ?)")->execute([$cat, $slug]);
    }

    // 3. Migrate data from legacy products table
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Migrating " . count($products) . " products to granular structure...\n";

    foreach ($products as $p) {
        // Find Category ID
        $cat_id = null;
        if (!empty($p['category'])) {
            $cat_stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
            $cat_stmt->execute([$p['category']]);
            $cat_id = $cat_stmt->fetchColumn() ?: null;
        }

        // Find Sub-category ID
        $sub_cat_id = null;
        if ($cat_id && !empty($p['subcategory'])) {
            $sub_stmt = $pdo->prepare("SELECT id FROM sub_categories WHERE category_id = ? AND name = ?");
            $sub_stmt->execute([$cat_id, $p['subcategory']]);
            $sub_cat_id = $sub_stmt->fetchColumn() ?: null;
        }

        // A. Insert into product_base
        $ins_base = $pdo->prepare("INSERT INTO product_base (seller_id, category_id, sub_category_id, name, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $status = in_array($p['status'], ['draft', 'published', 'scheduled']) ? $p['status'] : 'published';
        $ins_base->execute([
            $p['seller_id'],
            $cat_id,
            $sub_cat_id,
            $p['product_name'],
            $status,
            $p['created_at']
        ]);
        $new_product_id = $pdo->lastInsertId();

        // B. Insert into product_variants
        $ins_variant = $pdo->prepare("INSERT INTO product_variants (product_id, sku, price, min_price, max_price, quantity, size, color, smart_pricing_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins_variant->execute([
            $new_product_id,
            $p['sku'],
            $p['price'],
            $p['min_price'],
            $p['max_price'],
            $p['quantity'],
            $p['sizes'],
            $p['colors'],
            $p['smart_pricing_status']
        ]);

        // C. Insert into product_descriptions
        if (!empty($p['description'])) {
            $ins_desc = $pdo->prepare("INSERT INTO product_descriptions (product_id, content) VALUES (?, ?)");
            $ins_desc->execute([$new_product_id, $p['description']]);
        }

        // D. Insert into product_media
        if (!empty($p['images'])) {
            $imgs = json_decode($p['images'], true);
            if (is_array($imgs)) {
                foreach ($imgs as $idx => $url) {
                    $ins_media = $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary) VALUES (?, ?, ?)");
                    $ins_media->execute([$new_product_id, $url, ($idx === 0)]);
                }
            }
        }

        // E. Insert into product_channels
        if (!empty($p['channels'])) {
            $channels = explode(',', $p['channels']);
            foreach ($channels as $ch) {
                $ch = trim($ch);
                if (!empty($ch)) {
                    $ins_channel = $pdo->prepare("INSERT INTO product_channels (product_id, channel_name) VALUES (?, ?)");
                    $ins_channel->execute([$new_product_id, $ch]);
                }
            }
        }

        echo "Deeply migrated product ID: " . $p['id'] . " -> New Base ID: $new_product_id\n";
    }

    echo "Deep migration completed successfully!\n";

} catch (Exception $e) {
    echo "Deep migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
