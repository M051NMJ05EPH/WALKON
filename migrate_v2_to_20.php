<?php
/**
 * Migration Script: walkon_shoes_v2 -> walkon2.0
 */
include 'config.php';

$source_db = 'walkon_shoes_v2';
$target_db = 'walkon_shoes_v2';

try {
    // We need a root connection to access multiple DBs
    $host = '127.0.0.1';
    $user = 'root';
    $pass = '';
    $pdo_root = new PDO("mysql:host=$host", $user, $pass);
    $pdo_root->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>📂 Database Migration</h1>";
    echo "<p>Source: <strong>$source_db</strong> | Target: <strong>$target_db</strong></p>";

    // Ensure target exists
    $pdo_root->exec("CREATE DATABASE IF NOT EXISTS `$target_db` ");
    
    // Migration Logic: Copy tables one by one
    // We'll use "INSERT INTO target.table SELECT * FROM source.table" 
    // But we must do it in order of dependencies.

    $tables = [
        'sellers',
        'categories',
        'brands',
        'sub_categories',
        'product_base',
        'product_prices',
        'product_media',
        'product_skus',
        'product_specs',
        'users'
    ];

    $pdo_root->exec("USE `$target_db` ");
    $pdo_root->exec("SET FOREIGN_KEY_CHECKS = 0");

    foreach ($tables as $table) {
        try {
            // Check if source table exists
            $stmt = $pdo_root->query("SHOW TABLES FROM `$source_db` LIKE '$table'");
            if ($stmt->rowCount() == 0) {
                echo "⚠️ Skipping Table `$table`: Not found in source.<br>";
                continue;
            }

            // Clear target table
            $pdo_root->exec("TRUNCATE TABLE `$target_db`.`$table` ");
            
            // Copy data
            $pdo_root->exec("INSERT INTO `$target_db`.`$table` SELECT * FROM `$source_db`.`$table` ");
            
            $count = $pdo_root->query("SELECT COUNT(*) FROM `$target_db`.`$table` ")->fetchColumn();
            echo "✅ Migrated Table: <strong>$table</strong> ($count rows)<br>";
            
        } catch (Exception $e) {
            echo "❌ Error migrating Table `$table`: " . $e->getMessage() . "<br>";
        }
    }

    $pdo_root->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "<h2>🎉 Migration Complete!</h2>";
    echo "<p>The data from $source_db has been moved to $target_db.</p>";
    echo "<p><a href='index.php' style='padding:10px 20px; background:#10b981; color:white; text-decoration:none; border-radius:5px;'>Check Website</a></p>";

} catch (PDOException $e) {
    echo "<h2>❌ Connection Error: " . $e->getMessage() . "</h2>";
}
?>
