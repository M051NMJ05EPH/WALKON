<?php
include 'config.php';
try {
    $stmt = $pdo->query("SELECT pb.name, pm.url, pm.is_primary FROM product_base pb JOIN product_media pm ON pb.id = pm.product_id");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['name'] . " -> " . $row['url'] . " (" . ($row['is_primary'] ? "Primary" : "Secondary") . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
