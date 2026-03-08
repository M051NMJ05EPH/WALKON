<?php
session_start();
include 'config.php';

$_SESSION['user_id'] = 1;

// Seed an item
$pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (1, 126)")->execute(); // Reebok Nano X3

// Run Query
$stmt = $pdo->prepare("
    SELECT w.id as wishlist_id, pb.name, pp.price
    FROM wishlist w
    JOIN product_base pb ON w.product_id = pb.id
    LEFT JOIN product_prices pp ON pb.id = pp.product_id
    WHERE w.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($products) . " items:\n";
foreach ($products as $p) {
    echo "- " . $p['name'] . "\n";
}

// Cleanup
$pdo->prepare("DELETE FROM wishlist WHERE user_id = 1 AND product_id = 126")->execute();
?>
