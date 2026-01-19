<?php
include 'config.php';

try {
    echo "Starting ultra-granular migration...\n";

    // 1. Create tables
    echo "Creating new tables...\n";
    $sql = file_get_contents('ultra_normalize.sql');
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($queries as $query) {
        if (!empty($query)) {
            $pdo->exec($query);
        }
    }

    // Clear new tables to prevent duplicates during re-runs
    $tables = ['product_skus', 'product_prices', 'product_stock', 'product_sizes', 'product_colors', 'product_descriptions', 'product_media', 'product_channels', 'product_base'];
    foreach ($tables as $t) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE $t; SET FOREIGN_KEY_CHECKS = 1;");
    }

    // 2. Fetch existing products from LEGACY table
    // Note: Use `products` table as source.
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Migrating " . count($products) . " products...\n";

    foreach ($products as $p) {
        // Resolve Category/Subcategory IDs
        $cat_id = null;
        if (!empty($p['category'])) {
            $cat_stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
            $cat_stmt->execute([$p['category']]);
            $cat_row = $cat_stmt->fetch();
            $cat_id = $cat_row ? $cat_row['id'] : null;
        }

        $sub_cat_id = null;
        if ($cat_id && !empty($p['subcategory'])) {
            $sub_stmt = $pdo->prepare("SELECT id FROM sub_categories WHERE category_id = ? AND name = ?");
            $sub_stmt->execute([$cat_id, $p['subcategory']]);
            $sub_row = $sub_stmt->fetch();
            $sub_cat_id = $sub_row ? $sub_row['id'] : null;
        }

        // 1. Base
        $stmt = $pdo->prepare("INSERT INTO product_base (id, seller_id, category_id, sub_category_id, name, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $status = in_array($p['status'], ['draft', 'published', 'scheduled']) ? $p['status'] : 'published';
        $stmt->execute([$p['id'], $p['seller_id'], $cat_id, $sub_cat_id, $p['product_name'], $status, $p['created_at']]);
        $new_id = $p['id']; 

        // 2. SKU
        if (!empty($p['sku'])) {
            $pdo->prepare("INSERT INTO product_skus (product_id, sku) VALUES (?, ?)")->execute([$new_id, $p['sku']]);
        }

        // 3. Prices
        $pdo->prepare("INSERT INTO product_prices (product_id, price, min_price, max_price, smart_pricing_status) VALUES (?, ?, ?, ?, ?)")
            ->execute([$new_id, $p['price'], $p['min_price'], $p['max_price'], $p['smart_pricing_status']]);

        // 4. Stock
        $pdo->prepare("INSERT INTO product_stock (product_id, quantity) VALUES (?, ?)")
            ->execute([$new_id, $p['quantity']]);

        // 5. Sizes (Explode comma separated)
        if (!empty($p['sizes'])) {
            $sizes = array_map('trim', explode(',', $p['sizes']));
            foreach ($sizes as $size) {
                if ($size) $pdo->prepare("INSERT INTO product_sizes (product_id, size) VALUES (?, ?)")->execute([$new_id, $size]);
            }
        } elseif (!empty($p['size'])) { // check singular
             $pdo->prepare("INSERT INTO product_sizes (product_id, size) VALUES (?, ?)")->execute([$new_id, $p['size']]);
        }

        // 6. Colors (Explode comma separated)
        if (!empty($p['colors'])) {
            $colors = array_map('trim', explode(',', $p['colors']));
            foreach ($colors as $color) {
                if ($color) $pdo->prepare("INSERT INTO product_colors (product_id, color) VALUES (?, ?)")->execute([$new_id, $color]);
            }
        } elseif (!empty($p['color'])) { // check singular
            $pdo->prepare("INSERT INTO product_colors (product_id, color) VALUES (?, ?)")->execute([$new_id, $p['color']]);
        }

        // 7. Description
        if (!empty($p['description'])) {
            $pdo->prepare("INSERT INTO product_descriptions (product_id, content) VALUES (?, ?)")->execute([$new_id, $p['description']]);
        }

        // 8. Media
        if (!empty($p['images'])) {
            $imgs = json_decode($p['images'], true);
            if (is_array($imgs)) {
                foreach ($imgs as $idx => $url) {
                    $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary) VALUES (?, ?, ?)")
                        ->execute([$new_id, $url, ($idx === 0)]);
                }
            }
        }

        // 9. Channels
        if (!empty($p['channels'])) {
            $channels = array_map('trim', explode(',', $p['channels']));
            foreach ($channels as $ch) {
                if ($ch) $pdo->prepare("INSERT INTO product_channels (product_id, channel_name) VALUES (?, ?)")->execute([$new_id, $ch]);
            }
        }

        echo "Migrated Product $new_id\n";
    }

    echo "Ultra-granular migration completed!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
