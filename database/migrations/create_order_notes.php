<?php
include 'config.php';

echo "=== Creating Order Notes Table ===\n\n";

try {
    $sql = "CREATE TABLE IF NOT EXISTS order_notes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        user_id INT NOT NULL,
        note TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_order_id (order_id),
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    echo "✓ order_notes table created successfully\n";

    echo "\n=== Migration Complete ===\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
