<?php
include 'config.php';
try {
    $stmt = $pdo->query("DESCRIBE product_marketplaces");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
