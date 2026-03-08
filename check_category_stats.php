<?php
include 'config.php';

try {
    echo "<h1>Category Distribution Check</h1>";
    
    $sql = "SELECT c.name, COUNT(pb.id) as product_count, 
            SUM(CASE WHEN pm.url IS NOT NULL THEN 1 ELSE 0 END) as image_count
            FROM categories c
            LEFT JOIN product_base pb ON c.id = pb.category_id
            LEFT JOIN product_media pm ON pb.id = pm.product_id AND pm.is_primary = 1
            GROUP BY c.id, c.name";
            
    $stmt = $pdo->query($sql);
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'><tr><th>Category</th><th>Products</th><th>Images</th></tr>";
    foreach ($stats as $row) {
        echo "<tr>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['product_count'] . "</td>";
        echo "<td>" . $row['image_count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
