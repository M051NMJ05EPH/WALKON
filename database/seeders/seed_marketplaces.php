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
    // $pdo->exec("TRUNCATE TABLE marketplaces");

    // 3. Seed data
    $marketplaces = [
        [
            'name' => 'Amazon',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg',
            'description' => 'The world\'s largest e-commerce marketplace. Reach millions of customers globally with FBA and Prime shipping.',
            'website_url' => 'https://www.amazon.in',
            'display_order' => 1
        ],
        [
            'name' => 'Flipkart',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/en/d/d1/Flipkart_logo.svg',
            'description' => 'India\'s leading e-commerce platform. Access 400+ million customers with fast delivery and easy returns.',
            'website_url' => 'https://www.flipkart.com',
            'display_order' => 2
        ],
        [
            'name' => 'Shopify',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/e/e7/Shopify_logo.svg',
            'description' => 'Build your own branded online store. Complete control over design, checkout, and customer experience.',
            'website_url' => 'https://www.shopify.com',
            'display_order' => 3
        ],
        [
            'name' => 'eBay',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/1/1b/EBay_logo.svg',
            'description' => 'Global auction and retail marketplace. Perfect for unique products and international buyers.',
            'website_url' => 'https://www.ebay.com',
            'display_order' => 4
        ],
        [
            'name' => 'TikTok Shop',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/en/a/a9/TikTok_logo.svg',
            'description' => 'Social commerce revolution. Sell directly through viral videos and live streams to Gen Z audiences.',
            'website_url' => 'https://shop.tiktok.com',
            'display_order' => 5
        ],
        [
            'name' => 'Instagram Shopping',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/e/e7/Instagram_logo_2016.svg',
            'description' => 'Tag products in posts and stories. Turn your Instagram followers into customers seamlessly.',
            'website_url' => 'https://business.instagram.com/shopping',
            'display_order' => 6
        ],
        [
            'name' => 'Myntra',
            'logo_url' => 'https://constant.myntassets.com/web/assets/img/icon.5d108c855.svg',
            'description' => 'India\'s #1 fashion destination. Premium positioning for footwear and lifestyle brands.',
            'website_url' => 'https://www.myntra.com',
            'display_order' => 7
        ],
        [
            'name' => 'Ajio',
            'logo_url' => 'https://assets.ajio.com/static/img/Ajio-Logo.svg',
            'description' => 'Reliance\'s fashion-forward marketplace. Exclusive collections and handpicked brands.',
            'website_url' => 'https://www.ajio.com',
            'display_order' => 8
        ],
        [
            'name' => 'Meesho',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/en/1/14/Meesho_logo.png',
            'description' => 'India\'s social commerce leader. Zero commission and millions of resellers at your service.',
            'website_url' => 'https://www.meesho.com',
            'display_order' => 9
        ],
        [
            'name' => 'Snapdeal',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/f/fa/Snapdeal_new_logo.svg',
            'description' => 'Value-focused marketplace. Reach price-conscious customers across Tier 2 and Tier 3 cities.',
            'website_url' => 'https://www.snapdeal.com',
            'display_order' => 10
        ],
        [
            'name' => 'Noon',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/a/a8/Noon_logo.svg',
            'description' => 'Middle East\'s leading marketplace. Expand your reach to UAE, Saudi Arabia, and Egypt.',
            'website_url' => 'https://www.noon.com',
            'display_order' => 11
        ],
        [
            'name' => 'Zalando',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/9/9b/Zalando_logo.svg',
            'description' => 'Europe\'s premier fashion platform. Premium positioning in Germany, UK, France, and more.',
            'website_url' => 'https://www.zalando.com',
            'display_order' => 12
        ],
        [
            'name' => 'Alibaba',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/9/96/Alibaba_Chinese_logo.svg',
            'description' => 'B2B wholesale giant. Connect with global retailers and bulk buyers.',
            'website_url' => 'https://www.alibaba.com',
            'display_order' => 13
        ],
        [
            'name' => 'Etsy',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/8/89/Etsy_logo.svg',
            'description' => 'Handmade and unique marketplace. Perfect for artisan and custom footwear designs.',
            'website_url' => 'https://www.etsy.com',
            'display_order' => 14
        ],
        [
            'name' => 'Walmart',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/c/ca/Walmart_logo.svg',
            'description' => 'America\'s retail giant, now online. Massive reach with competitive pricing.',
            'website_url' => 'https://www.walmart.com',
            'display_order' => 15
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
