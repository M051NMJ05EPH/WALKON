<?php
/**
 * Comprehensive Seeder for Walkon 2.0
 * Populates products across Jan 21-27, 2026
 */
include 'config.php';

// Ensure we are using the right database (handled in config.php but extra safety)
try {
    $pdo->exec("USE `walkon_shoes_v2` ");
} catch (PDOException $e) {
    die("Database walkon_shoes_v2 not found. Please run setup_v2_db.php first.");
}

set_time_limit(300); // Increase execution time

try {
    $pdo->beginTransaction();

    echo "<h1>🚀 Starting Comprehensive Seeding for Walkon 2.0</h1>";

    // 1. Ensure Seller Exists
    $stmt = $pdo->query("SELECT id FROM sellers LIMIT 1");
    $seller = $stmt->fetch();
    if (!$seller) {
        $pdo->prepare("INSERT INTO sellers (name, email, password, business_name) VALUES (?, ?, ?, ?)")
            ->execute(['WalkOn Official', 'admin@walkon.com', password_hash('walkon2026', PASSWORD_DEFAULT), 'WalkOn Premium Store']);
        $seller_id = $pdo->lastInsertId();
        echo "✅ Created Seller: WalkOn Official<br>";
    } else {
        $seller_id = $seller['id'];
        echo "ℹ️ Using existing Seller ID: $seller_id<br>";
    }

    // 2. Seed Categories
    $categories_data = [
        ['Sneakers', 'https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=800&q=80', 'Lifestyle and casual sneakers.'],
        ['Running Shoes', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80', 'Performance running footwear.'],
        ['Boots', 'https://images.unsplash.com/photo-1520639889313-727c97bc099f?auto=format&fit=crop&w=800&q=80', 'Durable and rugged boots.'],
        ['Formal Shoes', 'https://images.unsplash.com/photo-1533867617858-e7b97e060509?auto=format&fit=crop&w=800&q=80', 'Premium leather formal wear.'],
        ['Sports', 'https://images.unsplash.com/photo-1539185441755-769473a23570?auto=format&fit=crop&w=800&q=80', 'Specialized sports footwear.'],
        ['Sandals & Slides', 'https://images.unsplash.com/photo-1595341888016-a392ef81b7de?auto=format&fit=crop&w=800&q=80', 'Casual comfort for easy wear.']
    ];

    foreach ($categories_data as $cat) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, image_url, description) VALUES (?, ?, ?)");
        $stmt->execute($cat);
    }
    echo "✅ Categories checked/seeded.<br>";

    // 3. Seed Brands
    $brands_data = [
        ['Nike', 'https://upload.wikimedia.org/wikipedia/commons/a/a6/Logo_NIKE.svg'],
        ['Adidas', 'https://upload.wikimedia.org/wikipedia/commons/2/20/Adidas_Logo.svg'],
        ['PUMA', 'https://upload.wikimedia.org/wikipedia/commons/a/ad/Puma_Logo.svg'],
        ['Reebok', 'https://upload.wikimedia.org/wikipedia/commons/5/53/Reebok_2019_logo.svg'],
        ['New Balance', 'https://upload.wikimedia.org/wikipedia/commons/e/ea/New_Balance_logo.svg'],
        ['Jordan', 'https://upload.wikimedia.org/wikipedia/en/3/37/Jumpman_logo.svg'],
        ['Vans', 'https://upload.wikimedia.org/wikipedia/commons/9/91/Vans-logo.svg'],
        ['Timberland', 'https://upload.wikimedia.org/wikipedia/en/a/a2/Timberland_logo.svg']
    ];

    foreach ($brands_data as $brand) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO brands (name, logo_url) VALUES (?, ?)");
        $stmt->execute($brand);
    }
    echo "✅ Brands checked/seeded.<br>";

    // Fetch mappings for IDs
    $cat_ids = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_KEY_PAIR);
    $brand_ids = $pdo->query("SELECT id, name FROM brands")->fetchAll(PDO::FETCH_KEY_PAIR);

    // 4. Products Distribution
    $dates = [
        '2026-01-25', '2026-01-26', '2026-01-27', '2026-01-28'
    ];

    $product_templates = [
        'Nike' => [
            ['Nike Air Max 270', 'Sneakers', 12999, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff'],
            ['Nike Zoom Fly 5', 'Running Shoes', 14999, 'https://images.unsplash.com/photo-1552346154-21d32810aba3'],
            ['Nike Air Force 1', 'Sneakers', 9999, 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a']
        ],
        'Adidas' => [
            ['Adidas Ultraboost Light', 'Running Shoes', 18999, 'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb'],
            ['Adidas Forum Low', 'Sneakers', 8999, 'https://images.unsplash.com/photo-1608231387042-66d1773070a5'],
            ['Adidas Stan Smith', 'Sneakers', 7999, 'https://images.unsplash.com/photo-1595341888016-a392ef81b7de']
        ],
        'PUMA' => [
            ['PUMA RS-X3', 'Sneakers', 7999, 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa'],
            ['PUMA Deviate Nitro', 'Running Shoes', 13999, 'https://images.unsplash.com/photo-1605348532760-6753d2c43329']
        ],
        'Jordan' => [
            ['Air Jordan 1 Retro', 'Sneakers', 16999, 'https://images.unsplash.com/photo-1491553895911-0055eca6402d'],
            ['Air Jordan 4 OG', 'Sneakers', 19999, 'https://images.unsplash.com/photo-1512374382149-233c42b6a83b']
        ],
        'Timberland' => [
            ['Timberland Premium 6-Inch', 'Boots', 15999, 'https://images.unsplash.com/photo-1520639889313-727c97bc099f'],
            ['Timberland Chelsea Boot', 'Boots', 12999, 'https://images.unsplash.com/photo-1621315286082-d499bd30573e']
        ]
    ];

    $total_added = 0;

    foreach ($dates as $date) {
        echo "📅 Seeding for Date: <strong>$date</strong>... ";
        $daily_count = 0;

        foreach ($product_templates as $brand_name => $items) {
            $brand_id = $brand_ids[$brand_name] ?? array_values($brand_ids)[0];

            foreach ($items as $item) {
                $name = $item[0];
                $cat_name = $item[1];
                $price = $item[2];
                $img_url = $item[3];
                $cat_id = $cat_ids[$cat_name] ?? array_values($cat_ids)[0];

                // Append date to name to ensure uniqueness across dates if needed or just to make it distinct
                $final_name = $name . " (Edition " . substr($date, -2) . ")";

                // 1. Insert product_base
                $stmt = $pdo->prepare("INSERT INTO product_base (seller_id, category_id, name, status, created_at) VALUES (?, ?, ?, 'published', ?)");
                $stmt->execute([$seller_id, $cat_id, $final_name, $date . " 10:00:00"]);
                $product_id = $pdo->lastInsertId();

                // 2. Insert product_prices
                $pdo->prepare("INSERT INTO product_prices (product_id, price, max_price, created_at) VALUES (?, ?, ?, ?)")
                    ->execute([$product_id, $price, $price + 2000, $date . " 10:00:00"]);

                // 3. Insert product_stock
                $pdo->prepare("INSERT INTO product_stock (product_id, quantity, created_at) VALUES (?, ?, ?)")
                    ->execute([$product_id, rand(20, 100), $date . " 10:00:00"]);

                // 4. Insert product_skus
                $sku = strtoupper(substr($brand_name, 0, 3)) . "-" . rand(1000, 9999) . "-" . substr($date, -2);
                $pdo->prepare("INSERT INTO product_skus (product_id, sku, created_at) VALUES (?, ?, ?)")
                    ->execute([$product_id, $sku, $date . " 10:00:00"]);

                // 5. Insert product_specs
                $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, gender, outer_material, occasion) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$product_id, $brand_id, 'Unisex', 'Premium Leather/Mesh', 'Casual/Sport']);

                // 6. Insert product_media
                $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary, created_at) VALUES (?, ?, 1, ?)")
                    ->execute([$product_id, $img_url . "?auto=format&fit=crop&w=800&q=80", $date . " 10:00:00"]);

                // 7. Insert products_base (meta)
                $pdo->prepare("INSERT INTO products_base (product_id, meta_title, meta_description) VALUES (?, ?, ?)")
                    ->execute([$product_id, "Buy $final_name Online", "Get the premium $final_name at WalkOn Shop. Best quality and price for $date."]);

                // 8. Insert product_descriptions
                $pdo->prepare("INSERT INTO product_descriptions (product_id, content) VALUES (?, ?)")
                    ->execute([$product_id, "Experience ultimate comfort with the $final_name. Perfect for everyday wear or performance activities. Released on $date."]);

                $daily_count++;
                $total_added++;
            }
        }
        echo "Added $daily_count products.<br>";
    }

    $pdo->commit();
    echo "<h2>🎉 Success! Total $total_added products seeded across Jan 21-27, 2026.</h2>";
    echo "<a href='index.php' style='padding:10px 20px; background:#10b981; color:white; text-decoration:none; border-radius:5px;'>Go to Shop Home</a>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "<h2>❌ Error occurred during seeding:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>
