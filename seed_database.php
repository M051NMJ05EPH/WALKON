<?php
session_start();
include 'config.php';

echo "<h1>Database Seeding Script</h1>";
echo "<p>Adding sample data to database...</p>";

try {
    // 1. Create and populate marketplaces table
    echo "<h2>1. Marketplaces Table</h2>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS marketplaces (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        logo_url VARCHAR(255),
        category VARCHAR(100),
        is_active TINYINT(1) DEFAULT 1,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Check if marketplaces exist
    $count = $pdo->query("SELECT COUNT(*) FROM marketplaces")->fetchColumn();
    if ($count == 0) {
        $marketplaces = [
            ['Amazon', 'Global e-commerce marketplace', 'https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg', 'E-commerce', 1],
            ['Flipkart', 'India\'s leading online shopping platform', 'https://static-assets-web.flixcart.com/batman-returns/batman-returns/p/images/fkheaderlogo_exploreplus-44005d.svg', 'E-commerce', 2],
            ['Myntra', 'Fashion & lifestyle marketplace', 'https://constant.myntassets.com/web/assets/img/logo.png', 'E-commerce', 3],
            ['Snapdeal', 'Value shopping destination', 'https://i3.sdlcdn.com/img/snapdeal/darwin/logo/sdLogo.png', 'E-commerce', 4],
            ['Instagram', 'Social commerce platform', 'https://upload.wikimedia.org/wikipedia/commons/a/a5/Instagram_icon.png', 'Social Media', 5],
            ['Facebook Shop', 'Social marketplace integration', 'https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg', 'Social Media', 6],
        ];
        
        $stmt = $pdo->prepare("INSERT INTO marketplaces (name, description, logo_url, category, display_order) VALUES (?, ?, ?, ?, ?)");
        foreach ($marketplaces as $m) {
            $stmt->execute($m);
        }
        echo "<p>✅ Added " . count($marketplaces) . " marketplaces</p>";
    } else {
        echo "<p>✅ Marketplaces already exist ($count)</p>";
    }
    
    // 2. Create seller_marketplaces table
    echo "<h2>2. Seller Marketplaces Table</h2>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS seller_marketplaces (
        id INT AUTO_INCREMENT PRIMARY KEY,
        seller_id INT NOT NULL,
        marketplace_id INT NOT NULL,
        status VARCHAR(50) DEFAULT 'disconnected',
        last_sync TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_seller_marketplace (seller_id, marketplace_id)
    )");
    echo "<p>✅ Created seller_marketplaces table</p>";
    
    // 3. Ensure sellers exist and add sample sellers
    echo "<h2>3. Sellers</h2>";
    $seller_count = $pdo->query("SELECT COUNT(*) FROM sellers")->fetchColumn();
    if ($seller_count == 0) {
        // Add sample sellers
        $stmt = $pdo->prepare("INSERT INTO sellers (email, store_name, created_at) VALUES (?, ?, NOW())");
        $sellers = [
            ['seller1@walkon.com', 'Premium Footwear Co'],
            ['seller2@walkon.com', 'Urban Shoes India'],
            ['seller3@walkon.com', 'Elite Sneakers Hub'],
        ];
        foreach ($sellers as $s) {
            try {
                $stmt->execute($s);
            } catch (Exception $e) { /* May already exist */ }
        }
        echo "<p>✅ Added sample sellers</p>";
    } else {
        echo "<p>✅ Sellers exist ($seller_count)</p>";
    }
    
    // 4. Connect sellers to marketplaces
    echo "<h2>4. Connecting Sellers to Marketplaces</h2>";
    $sellers = $pdo->query("SELECT id FROM sellers LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
    $marketplaces_ids = $pdo->query("SELECT id FROM marketplaces")->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO seller_marketplaces (seller_id, marketplace_id, status, last_sync) VALUES (?, ?, 'connected', NOW())");
    $connections = 0;
    foreach ($sellers as $seller_id) {
        // Connect each seller to 2-3 random marketplaces
        $connected = array_rand(array_flip($marketplaces_ids), min(3, count($marketplaces_ids)));
        foreach ((array)$connected as $marketplace_id) {
            $stmt->execute([$seller_id, $marketplace_id]);
            $connections++;
        }
    }
    echo "<p>✅ Created $connections seller-marketplace connections</p>";
    
    // 5. Add sample products if needed
    echo "<h2>5. Products</h2>";
    $product_count = $pdo->query("SELECT COUNT(*) FROM product_base")->fetchColumn();
    if ($product_count < 10) {
        // Get category IDs
        $category_id = $pdo->query("SELECT id FROM categories LIMIT 1")->fetchColumn();
        if (!$category_id) {
            $pdo->exec("INSERT INTO categories (name) VALUES ('Sneakers'), ('Formal Shoes'), ('Sandals')");
            $category_id = $pdo->lastInsertId();
        }
        
        // Get brand IDs
        $brand_id = $pdo->query("SELECT id FROM brands LIMIT 1")->fetchColumn();
        if (!$brand_id) {
            $pdo->exec("INSERT INTO brands (name) VALUES ('Nike'), ('Adidas'), ('Puma'), ('Reebok')");
            $brand_id = $pdo->lastInsertId();
        }
        
        // Add sample products
        $products = [
            ['Nike Air Max 90', rand(3000, 8000), 1],
            ['Adidas Ultraboost', rand(5000, 10000), 1],
            ['Puma RS-X', rand(4000, 7000), 2],
            ['Reebok Classic Leather', rand(3500, 6000), 2],
            ['Nike Court Vision', rand(3000, 5500), 3],
            ['Adidas Superstar', rand(4000, 7000), 1],
            ['Puma Suede Classic', rand(3500, 6500), 2],
            ['Nike Revolution 6', rand(2500, 4500), 3],
            ['Adidas Grand Court', rand(3000, 5000), 1],
            ['Puma Carina', rand(2800, 4800), 2],
        ];
        
        foreach ($products as $p) {
            $seller_id = $sellers[array_rand($sellers)];
            $stmt = $pdo->prepare("INSERT INTO product_base (name, category_id, seller_id, status) VALUES (?, ?, ?, 'published')");
            $stmt->execute([$p[0], $category_id, $seller_id]);
            $product_id = $pdo->lastInsertId();
            
            // Add price
            $pdo->prepare("INSERT INTO product_prices (product_id, price, max_price) VALUES (?, ?, ?)")
                ->execute([$product_id, $p[1], $p[1] + 1000]);
            
            // Add specs
            $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, gender) VALUES (?, ?, ?)")
                ->execute([$product_id, $brand_id, 'Unisex']);
            
            // Add to channels
            $channel_name = ['Amazon', 'Flipkart', 'Myntra'][array_rand(['Amazon', 'Flipkart', 'Myntra'])];
            $pdo->prepare("INSERT INTO product_channels (product_id, channel_name) VALUES (?, ?)")
                ->execute([$product_id, $channel_name]);
        }
        echo "<p>✅ Added " . count($products) . " sample products</p>";
    } else {
        echo "<p>✅ Products exist ($product_count)</p>";
    }
    
    // 6. Create cart and wishlist tables
    echo "<h2>6. Cart & Wishlist Tables</h2>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_cart_item (user_id, product_id)
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_wishlist (user_id, product_id)
    )");
    echo "<p>✅ Created cart and wishlist tables</p>";
    
    // 7. Add profile_photo and phone columns to users
    echo "<h2>7. User Table Updates</h2>";
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) DEFAULT NULL");
        echo "<p>✅ Added profile_photo column</p>";
    } catch (Exception $e) {
        echo "<p>✅ profile_photo column exists</p>";
    }
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL");
        echo "<p>✅ Added phone column</p>";
    } catch (Exception $e) {
        echo "<p>✅ phone column exists</p>";
    }
    
    echo "<hr><h2>✅ Database Seeding Complete!</h2>";
    echo "<p><a href='customer_dashboard.php'>View Customer Dashboard</a></p>";
    echo "<p><a href='marketplaces.php'>View Marketplaces Hub</a></p>";
    echo "<p><a href='profile.php'>View Profile Settings</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
