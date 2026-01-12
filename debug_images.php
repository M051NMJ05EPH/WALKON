<?php
include 'config.php';
$stmt = $pdo->query("SELECT product_name, images FROM products");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
file_put_contents('debug_images.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Dumped " . count($data) . " products to debug_images.json";
?>
