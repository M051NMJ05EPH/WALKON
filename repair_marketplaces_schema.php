<?php
include 'config.php';

try {
    echo "Repairing 'marketplaces' table schema...\n";
    
    // Drop tables in correct order due to FK constraints
    $pdo->exec("DROP TABLE IF EXISTS product_marketplaces");
    $pdo->exec("DROP TABLE IF EXISTS marketplaces");
    
    $pdo->exec("CREATE TABLE marketplaces (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        logo_url VARCHAR(500),
        description TEXT,
        website_url VARCHAR(500),
        is_active TINYINT(1) DEFAULT 1,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Recreate product_marketplaces if needed (legacy compatibility)
    $pdo->exec("CREATE TABLE product_marketplaces (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        marketplace_id INT NOT NULL,
        product_url VARCHAR(255),
        status ENUM('active','inactive') DEFAULT 'active',
        FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE,
        FOREIGN KEY (marketplace_id) REFERENCES marketplaces(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "Tables 'marketplaces' and 'product_marketplaces' recreated successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
