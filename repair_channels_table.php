<?php
require 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS product_channels (
        product_id INT NOT NULL,
        channel_name VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (product_id, channel_name),
        FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql);
    echo "✅ product_channels table created successfully.";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
