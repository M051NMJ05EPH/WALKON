<?php
include 'config.php';

try {
    // Check if is_active column exists
    $stmt = $pdo->query("DESCRIBE marketplaces");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('is_active', $columns)) {
        $pdo->exec("ALTER TABLE marketplaces ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER website_url");
        echo "Added 'is_active' column to marketplaces table.<br>";
    } else {
        echo "'is_active' column already exists.<br>";
    }
    
    if (!in_array('display_order', $columns)) {
        $pdo->exec("ALTER TABLE marketplaces ADD COLUMN display_order INT DEFAULT 0 AFTER is_active");
        echo "Added 'display_order' column to marketplaces table.<br>";
    }
    
    // Now run the seeder logic again
    $marketplaces = [
        [
            'name' => 'Amazon',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg',
            'description' => 'Global leader in e-commerce and cloud computing.',
            'website_url' => 'https://www.amazon.com',
            'display_order' => 1
        ],
        [
            'name' => 'Flipkart',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/en/7/7a/Flipkart_logo.svg',
            'description' => 'India\'s leading e-commerce marketplace.',
            'website_url' => 'https://www.flipkart.com',
            'display_order' => 2
        ],
        [
            'name' => 'eBay',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/1/1b/EBay_logo.svg',
            'description' => 'Global e-commerce leader connecting millions of buyers and sellers.',
            'website_url' => 'https://www.ebay.com',
            'display_order' => 3
        ],
        [
            'name' => 'TikTok Shop',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/en/a/a9/TikTok_logo.svg',
            'description' => 'Sell products directly on TikTok through in-feed videos and LIVEs.',
            'website_url' => 'https://shop.tiktok.com',
            'display_order' => 4
        ],
        [
            'name' => 'Instagram Shop',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/e/e7/Instagram_logo_2016.svg',
            'description' => 'A place for people to shop and discover products they love on Instagram.',
            'website_url' => 'https://www.instagram.com',
            'display_order' => 5
        ],
        [
            'name' => 'Shopify',
            'logo_url' => 'https://cdn.shopify.com/shopifycloud/brochure/assets/brand-assets/shopify-logo-primary-logo-456baa801ee66a0a435671082365958316831c9960c480451dd0330bcdae304f.svg',
            'description' => 'The commerce platform built for you.',
            'website_url' => 'https://www.shopify.com',
            'display_order' => 6
        ]
    ];

    $pdo->exec("UPDATE marketplaces SET is_active = 0");

    foreach ($marketplaces as $m) {
        $stmt_check = $pdo->prepare("SELECT id FROM marketplaces WHERE name = ?");
        $stmt_check->execute([$m['name']]);
        $existing = $stmt_check->fetch();
        
        if ($existing) {
            $stmt_update = $pdo->prepare("UPDATE marketplaces SET logo_url = ?, description = ?, website_url = ?, is_active = 1, display_order = ? WHERE id = ?");
            $stmt_update->execute([$m['logo_url'], $m['description'], $m['website_url'], $m['display_order'], $existing['id']]);
        } else {
            $stmt_insert = $pdo->prepare("INSERT INTO marketplaces (name, logo_url, description, website_url, is_active, display_order) VALUES (?, ?, ?, ?, 1, ?)");
            $stmt_insert->execute([$m['name'], $m['logo_url'], $m['description'], $m['website_url'], $m['display_order']]);
        }
    }

    echo "Successfully seeded official marketplaces.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
