<?php
require_once 'config.php';

try {
    echo "--- Table Structure ---\n";
    $stmt = $pdo->query("DESCRIBE platform_features");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

    echo "\n--- Table Data ---\n";
    $stmt = $pdo->query("SELECT * FROM platform_features");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
