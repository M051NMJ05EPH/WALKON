<?php
include 'config.php';
try {
    $stmt = $pdo->query("DESCRIBE sellers");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in sellers table:\n";
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']})\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
