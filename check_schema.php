<?php
include 'config.php';
try {
    $stmt = $pdo->query("DESCRIBE product_base");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns in product_base: " . implode(", ", $columns) . "\n";
    
    // Also check if there's any data
    $count = $pdo->query("SELECT COUNT(*) FROM product_base")->fetchColumn();
    echo "Count in product_base: $count\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
