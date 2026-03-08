<?php
include 'config.php';

echo "<h2>Fixing Database Schema Issues</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .success { background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 8px; color: #155724; }
    .error { background: #f8d7da; padding: 15px; margin: 10px 0; border-radius: 8px; color: #721c24; }
    .info { background: #d1ecf1; padding: 15px; margin: 10px 0; border-radius: 8px; color: #0c5460; }
</style>";

try {
    // Check which tables are missing 'id' column
    $tables_to_check = [
        'product_base',
        'product_skus', 
        'product_prices',
        'product_media',
        'product_specs',
        'product_stock',
        'product_channels'
    ];
    
    echo "<div class='info'><h3>🔍 Checking Tables...</h3></div>";
    
    foreach ($tables_to_check as $table) {
        try {
            // Check if table exists
            $check_table = $pdo->query("SHOW TABLES LIKE '$table'");
            
            if ($check_table->rowCount() > 0) {
                // Check columns in this table
                $columns = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
                
                $has_id = false;
                $column_names = [];
                
                foreach ($columns as $col) {
                    $column_names[] = $col['Field'];
                    if ($col['Field'] === 'id') {
                        $has_id = true;
                    }
                }
                
                if ($has_id) {
                    echo "<div class='success'>✅ Table `$table` has 'id' column</div>";
                } else {
                    echo "<div class='error'>❌ Table `$table` is missing 'id' column</div>";
                    echo "<div class='info'>Available columns: " . implode(', ', $column_names) . "</div>";
                    
                    // Try to add id column if it's missing
                    try {
                        // Check if there's already a primary key
                        $has_primary = false;
                        foreach ($columns as $col) {
                            if ($col['Key'] === 'PRI') {
                                $has_primary = true;
                                echo "<div class='info'>📌 Table already has primary key: {$col['Field']}</div>";
                                break;
                            }
                        }
                        
                        if (!$has_primary) {
                            // Add id column as auto-increment primary key
                            $pdo->exec("ALTER TABLE `$table` ADD COLUMN `id` INT AUTO_INCREMENT PRIMARY KEY FIRST");
                            echo "<div class='success'>✅ Added 'id' column to `$table`</div>";
                        }
                    } catch (PDOException $e) {
                        echo "<div class='error'>⚠️ Could not add 'id' to `$table`: " . $e->getMessage() . "</div>";
                    }
                }
            } else {
                echo "<div class='error'>❌ Table `$table` does not exist</div>";
            }
            
        } catch (PDOException $e) {
            echo "<div class='error'>Error checking table `$table`: " . $e->getMessage() . "</div>";
        }
    }
    
    // Special check for the actual error - might be in a join query
    echo "<hr><div class='info'><h3>🔍 Checking Common Query Issues...</h3></div>";
    
    // Test a typical product query to see if it works
    try {
        $test_query = $pdo->query("
            SELECT pb.id, pb.name, ps.sku 
            FROM product_base pb 
            LEFT JOIN product_skus ps ON pb.id = ps.product_id 
            LIMIT 1
        ");
        
        if ($test_query) {
            echo "<div class='success'>✅ Product base queries working correctly</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Product query error: " . $e->getMessage() . "</div>";
        
        // Try to identify which table is causing the issue
        echo "<div class='info'>Analyzing error...</div>";
        
        if (strpos($e->getMessage(), 'product_skus') !== false) {
            echo "<div class='error'>Issue detected in product_skus table</div>";
        } elseif (strpos($e->getMessage(), 'product_base') !== false) {
            echo "<div class='error'>Issue detected in product_base table</div>";
        }
    }
    
    echo "<hr>";
    echo "<div class='success'>";
    echo "<h3>✅ Schema Check Complete</h3>";
    echo "<p><a href='fix_all_product_images.php' style='display: inline-block; padding: 10px 20px; background: #10b981; color: white; text-decoration: none; border-radius: 8px;'>⬅ Back to Product Images</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>Database Connection Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
