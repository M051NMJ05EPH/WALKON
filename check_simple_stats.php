<?php
include 'config.php';
header('Content-Type: text/plain');

try {
    echo "SIMPLE STATS REPORT\n";
    
    $sql = "SELECT c.id, c.name, COUNT(pb.id) as count 
            FROM categories c
            LEFT JOIN product_base pb ON c.id = pb.category_id
            GROUP BY c.id, c.name";
            
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($rows as $r) {
        echo "{$r['name']} (ID {$r['id']}): {$r['count']}\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
