<?php
session_start();
include 'config.php';

$_SESSION['user_id'] = 1; // Simulate logged-in user

// Same query as wishlist.php
$stmt = $pdo->prepare("
    SELECT w.id as wishlist_id, pb.name, pp.price
    FROM wishlist w
    JOIN product_base pb ON w.product_id = pb.id
    LEFT JOIN product_prices pp ON pb.id = pp.product_id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($products) . " items in wishlist for User 1:\n";
foreach ($products as $p) {
    echo "- " . $p['name'] . " (Price: " . $p['price'] . ")\n";
}
?>
