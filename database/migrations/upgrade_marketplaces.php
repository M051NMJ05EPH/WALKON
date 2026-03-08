<?php
include 'config.php';

try {
    // 1. Add category column to marketplaces
    $stmt = $pdo->query("SHOW COLUMNS FROM marketplaces LIKE 'category'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE marketplaces ADD COLUMN category VARCHAR(50) DEFAULT 'Marketplace' AFTER name");
        echo "Added 'category' column to marketplaces.\n";
    }

    // 2. Create seller_marketplaces table
    $pdo->exec("CREATE TABLE IF NOT EXISTS seller_marketplaces (
        seller_id INT NOT NULL,
        marketplace_id INT NOT NULL,
        status ENUM('connected', 'disconnected') DEFAULT 'disconnected',
        last_sync TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (seller_id, marketplace_id)
    )");
    echo "Created 'seller_marketplaces' table.\n";

    // 3. Update existing marketplaces with categories
    $categories = [
        'Amazon' => 'E-commerce',
        'Flipkart' => 'E-commerce',
        'Shopify' => 'Direct',
        'TikTok Shop' => 'Social',
        'Myntra' => 'E-commerce',
        'Ebay' => 'E-commerce',
        'Facebook Shop' => 'Social',
        'Instagram Shop' => 'Social',
        'Walmart' => 'E-commerce'
    ];

    $stmt = $pdo->prepare("UPDATE marketplaces SET category = ? WHERE name = ?");
    foreach ($categories as $name => $cat) {
        $stmt->execute([$cat, $name]);
    }
    echo "Updated marketplace categories.\n";

    // 4. Connect current seller to their existing marketplaces based on product_channels
    $stmt_sellers = $pdo->query("SELECT id FROM sellers");
    $sellers = $stmt_sellers->fetchAll(PDO::FETCH_COLUMN);

    $stmt_check = $pdo->prepare("INSERT IGNORE INTO seller_marketplaces (seller_id, marketplace_id, status) 
                               SELECT ?, m.id, 'connected' 
                               FROM marketplaces m 
                               WHERE EXISTS (
                                   SELECT 1 FROM product_channels pc 
                                   JOIN product_base pb ON pc.product_id = pb.id 
                                   WHERE pc.channel_name = m.name AND pb.seller_id = ?
                               )");
    
    foreach ($sellers as $sid) {
        $stmt_check->execute([$sid, $sid]);
    }
    echo "Initialized seller marketplace connections.\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
?>
