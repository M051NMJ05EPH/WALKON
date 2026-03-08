<?php
include 'config.php';
$stmt = $pdo->query("SELECT id, product_id, url FROM product_media LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
