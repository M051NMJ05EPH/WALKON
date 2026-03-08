<?php
include 'config.php';
header('Content-Type: text/plain');

try {
    echo "CATEGORY STATS REPORT\n";
    echo "=====================\n";
    
    $sql = "SELECT c.id, c.name, COUNT(pb.id) as product_count, 
            SUM(CASE WHEN pm.url IS NOT NULL THEN 1 ELSE 0 END) as image_count
            FROM categories c
            LEFT JOIN product_base pb ON c.id = pb.category_id
            LEFT JOIN product_media pm ON pb.id = pm.product_id AND pm.is_primary = 1
            GROUP BY c.id, c.name";
            
    $stmt = $pdo->query($sql);
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($stats as $row) {
        echo str_pad($row['name'], 20) . 
             " | Products: " . str_pad($row['product_count'], 5) . 
             " | Images: " . $row['image_count'] . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
