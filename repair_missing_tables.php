<?php
include 'config.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h1>Repairing Missing Tables...</h1>";

    // 1. Create Product Descriptions Table
    $sql_desc = "CREATE TABLE IF NOT EXISTS product_descriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        content TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql_desc);
    echo "✅ product_descriptions table created.<br>";

    // 2. Create Product Stock Table
    $sql_stock = "CREATE TABLE IF NOT EXISTS product_stock (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        quantity INT DEFAULT 0,
        temp_hold INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql_stock);
    echo "✅ product_stock table created.<br>";

    // 3. Create Product Sync Logs Table
    $sql_sync = "CREATE TABLE IF NOT EXISTS product_sync_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        seller_id INT NOT NULL,
        product_id INT NOT NULL,
        channel VARCHAR(50) NOT NULL,
        status ENUM('pending', 'success', 'error', 'failed') DEFAULT 'pending',
        message TEXT,
        sync_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE,
        INDEX idx_seller_product (seller_id, product_id),
        INDEX idx_channel_status (channel, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql_sync);
    echo "✅ product_sync_logs table created.<br>";

    // 4. Create Smart Pricing Log Table
    $sql_smart_log = "CREATE TABLE IF NOT EXISTS smart_pricing_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        seller_id INT NOT NULL,
        product_id INT NOT NULL,
        old_price DECIMAL(10, 2),
        new_price DECIMAL(10, 2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE,
        INDEX idx_seller_date (seller_id, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql_smart_log);
    echo "✅ smart_pricing_log table created.<br>";

    // 5. Add Smart Pricing columns to product_prices if missing
    $columns = [
        'min_price' => "DECIMAL(10,2) DEFAULT NULL",
        'max_price' => "DECIMAL(10,2) DEFAULT NULL",
        'smart_pricing_status' => "TINYINT(1) DEFAULT 0"
    ];

    foreach ($columns as $col => $definition) {
        $check = $pdo->query("SHOW COLUMNS FROM product_prices LIKE '$col'")->fetch();
        if (!$check) {
            $pdo->exec("ALTER TABLE product_prices ADD COLUMN $col $definition");
            echo "✅ Column '$col' added to product_prices.<br>";
        } else {
            echo "ℹ️ Column '$col' already exists in product_prices.<br>";
        }
    }
    
    echo "<h3>Repair Complete!</h3>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>Error: " . $e->getMessage() . "</h3>";
}
?>
