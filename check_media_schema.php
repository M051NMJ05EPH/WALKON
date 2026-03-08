<?php
include 'config.php';

echo "=== product_media Schema ===\n";
try {
    $stmt = $pdo->query("DESCRIBE product_media");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($results);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
