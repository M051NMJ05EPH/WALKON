<?php
include 'config.php';

echo "=== marketplaces Schema ===\n";
try {
    $stmt = $pdo->query("DESCRIBE marketplaces");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($results);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== current marketplaces ===\n";
try {
    $stmt = $pdo->query("SELECT * FROM marketplaces");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($results);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
