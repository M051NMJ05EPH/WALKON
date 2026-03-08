<?php
include 'config.php';

$products = ['Premium 6-Inch Boot', 'Power Running Shoe', 'Sparx Men\'s SM-734'];
foreach($products as $name) {
    $stmt = $pdo->prepare("SELECT id, name FROM product_base WHERE name LIKE ?");
    $stmt->execute(["%$name%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Results for $name:\n";
    print_r($results);
    echo "\n";
}
?>
