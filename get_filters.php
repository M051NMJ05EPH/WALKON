<?php
include 'config.php';
$categories = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''")->fetchAll(PDO::FETCH_COLUMN);
$statuses = $pdo->query("SELECT DISTINCT status FROM products")->fetchAll(PDO::FETCH_COLUMN);
$channels = ['Amazon', 'Shopify', 'TikTok', 'eBay', 'Facebook', 'Instagram']; // Standard list based on my icon map

echo json_encode([
    'categories' => $categories,
    'statuses' => $statuses,
    'channels' => $channels
]);
?>
