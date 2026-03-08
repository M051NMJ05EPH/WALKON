<?php
include 'config.php';
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS api_credentials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        seller_id INT,
        channel VARCHAR(50),
        api_key TEXT,
        api_secret TEXT,
        access_token TEXT,
        is_active BOOLEAN DEFAULT 1,
        expires_at DATETIME
    )");
    echo "api_credentials table checked/created.\n";
    
    // Also check seller_marketplaces table columns just in case
    // The previous check said it exists, but might be missing columns
    
    // Seed some mock credentials for the stats
    $pdo->exec("INSERT IGNORE INTO api_credentials (seller_id, channel, is_active) VALUES (1, 'amazon', 1), (1, 'shopify', 1), (1, 'ebay', 1)");
    echo "Sample credentials seeded.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
