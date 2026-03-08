<?php
include 'config.php';

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
        'description' => 'Multinational e-commerce corporation facilitating consumer-to-consumer and business-to-consumer sales.',
        'website_url' => 'https://www.ebay.com',
        'display_order' => 3
    ],
    [
        'name' => 'TikTok Shop',
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/en/a/a9/TikTok_logo.svg',
        'description' => 'New innovative shopping feature which enables merchants and creators to showcase and sell products.',
        'website_url' => 'https://www.tiktok.com',
        'display_order' => 4
    ],
    [
        'name' => 'Instagram Shop',
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/e/e7/Instagram_logo_2016.svg',
        'description' => 'Immersive storefront that lets people explore your best products.',
        'website_url' => 'https://www.instagram.com',
        'display_order' => 5
    ],
    [
        'name' => 'Shopify',
        'logo_url' => 'https://cdn.shopify.com/shopifycloud/brochure/assets/brand-assets/shopify-logo-primary-logo-456baa801ee66a0a435671082365958316831c9960c480451dd0330bcdae304f.svg',
        'description' => 'Complete commerce platform that lets you start, grow, and manage a business.',
        'website_url' => 'https://www.shopify.com',
        'display_order' => 6
    ]
];

try {
    // First, deactivate existing ones to avoid confusion if we want only these 6
    $pdo->exec("UPDATE marketplaces SET is_active = 0");

    $stmt = $pdo->prepare("INSERT INTO marketplaces (name, logo_url, description, website_url, is_active, display_order) 
                           VALUES (:name, :logo_url, :description, :website_url, 1, :display_order)
                           ON DUPLICATE KEY UPDATE 
                           logo_url = VALUES(logo_url), 
                           description = VALUES(description), 
                           website_url = VALUES(website_url), 
                           is_active = 1, 
                           display_order = VALUES(display_order)");

    foreach ($marketplaces as $m) {
        $stmt->execute($m);
    }

    echo "Successfully seeded " . count($marketplaces) . " official marketplaces.";
} catch (PDOException $e) {
    echo "Error seeding marketplaces: " . $e->getMessage();
}
?>
