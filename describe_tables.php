<?php
require 'config.php';

$tables = ['product_base', 'products'];

foreach ($tables as $table) {
    echo "DESCRIBE $table:\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "----------------\n";
}
?>
