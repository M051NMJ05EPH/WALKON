<?php
/**
 * Fix Product Dates - Redistributes products from Jan 21 to Jan 25-28
 */
include 'config.php';

try {
    $pdo->exec("USE `walkon_shoes_v2` ");
    echo "<h1>🛠️ Fixing Product Creation Dates</h1>";

    // Products to update
    $old_date = '2026-01-21';
    $new_dates = ['2026-01-25', '2026-01-26', '2026-01-27', '2026-01-28'];

    // Get all products that were created on Jan 21
    $stmt = $pdo->prepare("SELECT id FROM product_base WHERE DATE(created_at) = ?");
    $stmt->execute([$old_date]);
    $products = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($products)) {
        echo "<p>No products found with date $old_date. They might already be updated or not seeded yet.</p>";
        echo "<p><a href='seed_products_daily.php'>Run Seeder First</a></p>";
        exit;
    }

    echo "<p>Found " . count($products) . " products on $old_date. Redistributing...</p>";

    $pdo->beginTransaction();
    $i = 0;
    foreach ($products as $id) {
        $target_date = $new_dates[$i % count($new_dates)] . " 10:00:00";
        
        // Update all relevant tables
        $tables = ['product_base', 'product_prices', 'product_stock', 'product_skus', 'product_media'];
        
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("UPDATE $table SET created_at = ? WHERE product_id = ? OR id = ?"); // Handling both PK 'id' and FK 'product_id'
            // For product_base, 'id' is the product ID. For others, it's usually 'product_id'
            if ($table === 'product_base') {
                $stmt = $pdo->prepare("UPDATE $table SET created_at = ? WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("UPDATE $table SET created_at = ? WHERE product_id = ?");
            }
            $stmt->execute([$target_date, $id]);
        }
        
        $i++;
    }

    $pdo->commit();
    echo "<h2>✅ Success! redistirbuted $i products to the range Jan 25-28.</h2>";
    echo "<p><a href='verify_daily_seeds.php' style='padding:10px 20px; background:#10b981; color:white; text-decoration:none; border-radius:5px;'>Check Results</a></p>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
?>
