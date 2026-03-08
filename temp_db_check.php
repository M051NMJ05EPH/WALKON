<?php
include 'config.php';
try {
    $tables = ['product_base', 'seller_marketplaces', 'api_credentials', 'sellers'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        echo "$table: " . ($stmt->rowCount() > 0 ? "Exists" : "Missing") . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
