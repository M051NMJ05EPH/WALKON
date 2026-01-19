<?php
include 'config.php';

try {
    // 1. Create the table
    $pdo->exec("CREATE TABLE IF NOT EXISTS marketplaces (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        logo_url VARCHAR(500),
        description TEXT,
        website_url VARCHAR(500),
        is_active TINYINT(1) DEFAULT 1,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "Table 'marketplaces' created/verified.\n";

    // 2. Clear existing (optional, but good for fresh seeding)
    $pdo->exec("TRUNCATE TABLE marketplaces");

    // 3. Seed data
    $marketplaces = [
        [
            'name' => 'Amazon Shoes',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg',
            'description' => 'The world\'s largest footwear marketplace. reach millions of customers globally.',
            'website_url' => 'https://amazon.com',
            'display_order' => 1
        ],
        [
            'name' => 'Shopify',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/e/e7/Shopify_logo.svg',
            'description' => 'Power your independent shoe brand with a custom storefront and global reach.',
            'website_url' => 'https://shopify.com',
            'display_order' => 2
        ],
        [
            'name' => 'TikTok Shop',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/en/a/a9/TikTok_logo.svg',
            'description' => 'Sell directly through viral shoe reviews and short-form video content.',
            'website_url' => 'https://tiktok.com',
            'display_order' => 3
        ],
        [
            'name' => 'Instagram Shop',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/e/e7/Instagram_logo_2016.svg',
            'description' => 'Visual-first shopping experience perfect for trending sneaker drops.',
            'website_url' => 'https://instagram.com',
            'display_order' => 4
        ],
        [
            'name' => 'eBay Footwear',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/1/1b/EBay_logo.svg',
            'description' => 'The destination for collectible sneakers and authenticated luxury kicks.',
            'website_url' => 'https://ebay.com',
            'display_order' => 5
        ],
        [
            'name' => 'Flipkart',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/en/d/d1/Flipkart_logo.svg',
            'description' => 'Expand your reach across India\'s leading e-commerce footwear segment.',
            'website_url' => 'https://flipkart.com',
            'display_order' => 6
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO marketplaces (name, logo_url, description, website_url, display_order) VALUES (?, ?, ?, ?, ?)");

    foreach ($marketplaces as $m) {
        $stmt->execute([$m['name'], $m['logo_url'], $m['description'], $m['website_url'], $m['display_order']]);
    }

    echo "Marketplaces seeded successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
