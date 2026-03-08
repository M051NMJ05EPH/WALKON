<?php
include 'config.php';

// Set execution time to unlimited as this might take a while
set_time_limit(0);

try {
    // Ensure we are using the correct database
    $pdo->exec("USE `walkon_shoes_v2` ");
    echo "<h1>🚀 WALKON Master Seeder v2.0</h1><hr>";

    // --- 0. HELPER: TRUNCATE TABLES (Clean Slate) ---
    echo "<h3>🧹 Cleaning existing data...</h3>";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $tables = [
        'platform_features', 'brands', 'categories', 'sub_categories', 
        'sellers', 'users', 'product_base', 'product_descriptions', 
        'product_stock', 'product_prices', 'product_skus', 'product_media', 
        'product_specs', 'product_channels', 'product_colors', 'product_sizes',
        'marketplaces', 'site_settings', 'orders', 'daily_sales_analytics'
    ];
    foreach ($tables as $table) {
        $pdo->exec("TRUNCATE TABLE `$table` ");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // --- 1. CORE ENTITIES: USERS & SELLERS ---
    echo "<h3>👤 Seeding Users & Sellers...</h3>";
    
    // Create Admin User
    $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, is_verified) VALUES (?, ?, ?, ?, ?)")
        ->execute(['Admin', 'WalkOn', 'admin@walkon.com', password_hash('admin123', PASSWORD_DEFAULT), 1]);
    
    // Create Demo Seller
    $pdo->prepare("INSERT INTO sellers (name, email, password, business_name, is_active) VALUES (?, ?, ?, ?, ?)")
        ->execute(['Demo Seller', 'seller@walkon.com', password_hash('seller123', PASSWORD_DEFAULT), 'WalkOn Official Store', 1]);
    $seller_id = $pdo->lastInsertId();

    // --- 2. INFRASTRUCTURE: CATEGORIES & BRANDS ---
    echo "<h3>🌱 Seeding Categories...</h3>";
    $categories = [
        ['Sneakers', 'https://images.unsplash.com/photo-1552346154-21d32810aba3', 'Premium lifestyle sneakers and streetwear.'],
        ['Boots', 'https://images.unsplash.com/photo-1605733513597-a8f8d410fe3c?q=80&w=1200', 'Durable and stylish boots for all terrains.'],
        ['Sports', 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2', 'High-performance athletic footwear.'],
        ['Running Shoes', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff', 'Lightweight tech for marathon and trail running.']
    ];
    $cat_stmt = $pdo->prepare("INSERT INTO categories (name, image_url, description) VALUES (?, ?, ?)");
    $cat_ids = [];
    foreach ($categories as $c) {
        $cat_stmt->execute($c);
        $cat_ids[$c[0]] = $pdo->lastInsertId();
    }

    echo "<h3>🛡️ Seeding Brands...</h3>";
    $brands = [
        ['Nike', 'https://upload.wikimedia.org/wikipedia/commons/a/a6/Logo_NIKE.svg'],
        ['Adidas', 'https://upload.wikimedia.org/wikipedia/commons/2/20/Adidas_Logo.svg'],
        ['Puma', 'https://upload.wikimedia.org/wikipedia/commons/8/88/Puma_Logo.svg'],
        ['Jordan', 'https://upload.wikimedia.org/wikipedia/en/3/37/Jumpman_logo.svg'],
        ['Reebok', 'https://upload.wikimedia.org/wikipedia/commons/5/5f/Reebok_Logo.svg'],
        ['New Balance', 'https://upload.wikimedia.org/wikipedia/commons/e/ea/New_Balance_logo.svg'],
        ['Vans', 'https://upload.wikimedia.org/wikipedia/commons/9/91/Vans_logo.svg'],
        ['Bata', 'https://upload.wikimedia.org/wikipedia/commons/c/c6/Bata_logo.svg']
    ];
    $brand_stmt = $pdo->prepare("INSERT INTO brands (name, logo_url) VALUES (?, ?)");
    $brand_ids = [];
    foreach ($brands as $b) {
        $brand_stmt->execute($b);
        $brand_ids[$b[0]] = $pdo->lastInsertId();
    }

    // --- 3. INFRASTRUCTURE: MARKETPLACES ---
    echo "<h3>🌐 Seeding Marketplaces...</h3>";
    $marketplaces = [
        ['Amazon', 'https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg', 'Global leader in e-commerce.', 'https://amazon.com', 1],
        ['Flipkart', 'https://upload.wikimedia.org/wikipedia/en/7/7a/Flipkart_logo.svg', 'Indias leading marketplace.', 'https://flipkart.com', 2],
        ['eBay', 'https://upload.wikimedia.org/wikipedia/commons/1/1b/EBay_logo.svg', 'Global auction and retail site.', 'https://ebay.com', 3],
        ['TikTok Shop', 'https://upload.wikimedia.org/wikipedia/en/a/a9/TikTok_logo.svg', 'Social commerce platform.', 'https://shop.tiktok.com', 4],
        ['Instagram Shop', 'https://upload.wikimedia.org/wikipedia/commons/e/e7/Instagram_logo_2016.svg', 'Visual discovery shopping.', 'https://instagram.com', 5],
        ['Shopify', 'https://cdn.shopify.com/shopifycloud/brochure/assets/brand-assets/shopify-logo-primary-logo.svg', 'Independent store platform.', 'https://shopify.com', 6]
    ];
    $mkt_stmt = $pdo->prepare("INSERT INTO marketplaces (name, logo_url, description, website_url, display_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($marketplaces as $m) $mkt_stmt->execute($m);

    // --- 4. PLATFORM FEATURES ---
    echo "<h3>✨ Seeding Platform Features...</h3>";
    $features = [
        ['Multi-Channel Sync', 'Instant inventory synchronization across 15+ global marketplaces.', 'fas fa-layer-group'],
        ['Smart Analytics', 'Deep insights into your sales performance with AI-driven forecasting.', 'fas fa-chart-line'],
        ['Auto-Pricing', 'Stay competitive with real-time price matching algorithms.', 'fas fa-bolt'],
        ['Global Logistics', 'Integrated shipping solutions with major worldwide carriers.', 'fas fa-truck-moving']
    ];
    $feat_stmt = $pdo->prepare("INSERT INTO platform_features (title, description, icon) VALUES (?, ?, ?)");
    foreach ($features as $f) $feat_stmt->execute($f);

    // --- 5. PRODUCT CATALOG: SAMPLES ---
    echo "<h3>👟 Seeding Sample Products...</h3>";
    $products = [
        [
            'name' => 'Nike Air Max Pulse',
            'category' => 'Sneakers',
            'brand' => 'Nike',
            'price' => 14999,
            'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff',
            'desc' => 'The Air Max Pulse combines street style with rugged performance.',
            'sku' => 'NK-AMP-001'
        ],
        [
            'name' => 'Adidas Yeezy Boost 350',
            'category' => 'Sneakers',
            'brand' => 'Adidas',
            'price' => 22000,
            'image' => 'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb',
            'desc' => 'The global icon of modern sneaker culture.',
            'sku' => 'AD-YZY-350'
        ],
        [
            'name' => 'Timberland Premium 6-Inch',
            'category' => 'Boots',
            'brand' => 'Timberland',
            'price' => 19800,
            'image' => 'https://images.unsplash.com/photo-1520639889313-72721e0ab9ef',
            'desc' => 'The original waterproof boot that started it all.',
            'sku' => 'TM-P6I-001'
        ],
        [
            'name' => 'Nike Air Jordan 1 Retro',
            'category' => 'Sneakers',
            'brand' => 'Jordan',
            'price' => 17999,
            'image' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772',
            'desc' => 'The legend that changed basketball and fashion forever.',
            'sku' => 'JD-AJ1-001'
        ],
        [
            'name' => 'Puma RS-X Reinvent',
            'category' => 'Sports',
            'brand' => 'Puma',
            'price' => 11000,
            'image' => 'https://images.unsplash.com/photo-1608231387042-66d1773070a5',
            'desc' => 'Retrofuturistic design with extreme cushioning.',
            'sku' => 'PM-RSX-001'
        ]
    ];

    foreach ($products as $p) {
        // Base
        $pdo->prepare("INSERT INTO product_base (seller_id, category_id, name, status) VALUES (?, ?, ?, 'published')")
            ->execute([$seller_id, $cat_ids[$p['category']], $p['name']]);
        $pid = $pdo->lastInsertId();

        // Details
        $pdo->prepare("INSERT INTO product_descriptions (product_id, content) VALUES (?, ?)")->execute([$pid, $p['desc']]);
        $pdo->prepare("INSERT INTO product_prices (product_id, price, min_price, max_price, smart_pricing_status) VALUES (?, ?, ?, ?, 1)")
            ->execute([$pid, $p['price'], $p['price']-2000, $p['price']+3000]);
        $pdo->prepare("INSERT INTO product_stock (product_id, quantity) VALUES (?, ?)")->execute([$pid, 100]);
        $pdo->prepare("INSERT INTO product_skus (product_id, sku) VALUES (?, ?)")->execute([$pid, $p['sku']]);
        $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary) VALUES (?, ?, 1)")->execute([$pid, $p['image']]);
        
        // Specs
        $brand_id = isset($brand_ids[$p['brand']]) ? $brand_ids[$p['brand']] : null;
        $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, gender, occasion) VALUES (?, ?, 'Unisex', 'Lifestyle')")
            ->execute([$pid, $brand_id]);

        // Sync Channels
        foreach (['Amazon', 'Flipkart', 'Shopify'] as $ch) {
            $pdo->prepare("INSERT INTO product_channels (product_id, channel_name) VALUES (?, ?)")->execute([$pid, $ch]);
        }
    }

    // --- 6. TRANSACTIONS: ORDERS & ANALYTICS ---
    echo "<h3>📊 Generating Sales History...</h3>";
    for ($i = 0; $i < 10; $i++) {
        $p_idx = array_rand($products);
        $price = $products[$p_idx]['price'];
        $pdo->prepare("INSERT INTO orders (seller_id, product_id, customer_name, quantity, unit_price, total_price, status, channel, order_date) VALUES (?, ?, ?, ?, ?, ?, 'delivered', 'Amazon', DATE_SUB(NOW(), INTERVAL ? DAY))")
            ->execute([$seller_id, $i+1, 'Demo Customer', 1, $price, $price, rand(0, 30)]);
    }

    echo "<hr><h1>✅ SUCCESS! Everything seeded.</h1>";
    echo "<p><a href='index.php'>Go to Homepage</a> | <a href='shop.php'>Go to Shop</a></p>";

} catch (PDOException $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
?>
