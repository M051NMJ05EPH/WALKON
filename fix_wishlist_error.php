<?php
/**
 * Fix & Diagnose Wishlist
 */
include 'config.php';

echo "<!DOCTYPE html><html><head><title>Wishlist Fixer</title><style>body{background:#030712;color:white;font-family:sans-serif;padding:40px;}</style></head><body>";
echo "<div style='max-width:800px; margin:0 auto; background:#111827; padding:40px; border-radius:20px; border:1px solid #374151;'>";
echo "<h1 style='color:#10b981'>Wishlist System Check</h1>";

try {
    // 1. Check Table Exists
    $check = $pdo->query("SHOW TABLES LIKE 'wishlist'");
    if ($check->rowCount() === 0) {
        echo "<p style='color:#fbbf24'>⚠️ Wishlist table missing. Creating it now...</p>";
        
        $pdo->exec("CREATE TABLE wishlist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_wishlist (user_id, product_id),
            FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        echo "<p style='color:#10b981'>✅ Wishlist table created successfully.</p>";
    } else {
        echo "<p style='color:#10b981'>✅ Wishlist table exists.</p>";
    }

    // 2. Check Dependencies
    $tables = ['product_base', 'product_prices', 'categories', 'product_media'];
    foreach ($tables as $t) {
        $chk = $pdo->query("SHOW TABLES LIKE '$t'");
        if ($chk->rowCount() > 0) {
            echo "<p style='color:#9ca3af'>✓ Table '$t' checks out.</p>";
        } else {
            echo "<p style='color:#ef4444'>❌ Critical: Table '$t' is missing!</p>";
        }
    }

    // 3. Test Query
    echo "<p style='color:#fbbf24'>Testing wishlist query logic...</p>";
    $stmt = $pdo->prepare("
        SELECT w.id 
        FROM wishlist w
        JOIN product_base pb ON w.product_id = pb.id
        LEFT JOIN product_prices pp ON pb.id = pp.product_id
        LEFT JOIN categories c ON pb.category_id = c.id
        LIMIT 1
    ");
    $stmt->execute();
    echo "<p style='color:#10b981'>✅ Query structure is valid.</p>";

    echo "<hr style='border-color:#374151; margin:20px 0;'>";
    echo "<h3 style='color:white'>Status: <span style='color:#10b981'>SYSTEM READY</span></h3>";
    echo "<p>The wishlist system has been verified and repaired.</p>";
    echo "<a href='wishlist.php' style='display:inline-block; background:#10b981; color:white; padding:12px 24px; text-decoration:none; border-radius:6px; font-weight:bold;'>Go to My Wishlist</a>";

} catch (PDOException $e) {
    echo "<hr style='border-color:#ef4444;'>";
    echo "<h3 style='color:#ef4444'>Error Detected</h3>";
    echo "<p>SQL Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Code: " . $e->getCode() . "</p>";
}

echo "</div></body></html>";
?>
