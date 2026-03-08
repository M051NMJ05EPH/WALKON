<?php
include 'config.php';

$products = ['Charged Assert 10', 'Chelsea Boot'];
foreach ($products as $name) {
    echo "Checking product: $name\n";
    $stmt = $pdo->prepare("SELECT pb.id, pm.url, pm.id as media_id FROM product_base pb JOIN product_media pm ON pb.id = pm.product_id WHERE pb.name LIKE ?");
    $stmt->execute(["%$name%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($results)) {
        echo "No media found for $name\n";
    } else {
        foreach ($results as $row) {
            echo "ID: {$row['id']} | MediaID: {$row['media_id']} | URL: {$row['url']}\n";
        }
    }
    echo "-------------------\n";
}

// Also check all featured products logic from Index.php
echo "Checking all products currently marked as published:\n";
$stmt = $pdo->query("SELECT pb.id, pb.name, pm.url FROM product_base pb LEFT JOIN product_media pm ON pb.id = pm.product_id WHERE pb.status = 'published'");
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($all as $p) {
    if (empty($p['url']) || strpos($p['url'], 'placeholder') !== false || strpos($p['url'], 'via.placeholder') !== false) {
        echo "POTENTIAL BROKEN/MISSING IMAGE: ID: {$p['id']} | Name: {$p['name']} | URL: {$p['url']}\n";
    }
}
?>
