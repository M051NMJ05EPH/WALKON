<?php
include 'config.php';
try {
    $tables = ['product_sizes', 'product_colors'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "Table '$table' exists.\n";
            // Show columns
            $cols = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_COLUMN);
            echo "Columns: " . implode(", ", $cols) . "\n";
        } else {
            echo "Table '$table' DOES NOT exist.\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
