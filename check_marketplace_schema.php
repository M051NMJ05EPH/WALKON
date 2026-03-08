<?php
include 'config.php';
echo "--- marketplaces table ---\n";
$stmt = $pdo->query("DESCRIBE marketplaces");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- checking for seller_marketplaces table ---\n";
$stmt = $pdo->query("SHOW TABLES LIKE 'seller_marketplaces'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- checking for product_channels table ---\n";
$stmt = $pdo->query("DESCRIBE product_channels");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
