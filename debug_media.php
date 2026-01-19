<?php
include 'config.php';

echo "--- Product Media URLs ---\n";
// Join with product_base to identify products
$stmt = $pdo->query("SELECT pb.name, pm.url, pm.type 
                     FROM product_media pm 
                     JOIN product_base pb ON pm.product_id = pb.id 
                     LIMIT 20");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $r) {
    echo "Product: {$r['name']}\n";
    echo "URL: {$r['url']}\n";
    echo "Type: {$r['type']}\n";
    echo "----------------\n";
}

echo "\n--- Products WITHOUT Media ---\n";
$stmt = $pdo->query("SELECT pb.id, pb.name 
                     FROM product_base pb 
                     LEFT JOIN product_media pm ON pb.id = pm.product_id 
                     WHERE pm.id IS NULL");
$missing = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($missing as $m) {
    echo "ID: {$m['id']} - {$m['name']}\n";
}
?>
