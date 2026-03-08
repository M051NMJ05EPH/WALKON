<?php
require_once 'config.php';

echo "--- Debugging product_colors Schema ---\n";
try {
    $stmt = $pdo->query("DESCRIBE product_colors");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error describing table: " . $e->getMessage() . "\n";
}
