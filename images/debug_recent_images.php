<?php
include 'config.php';

try {
    echo "<h1>Latest Products Check</h1>";
    
    $stmt = $pdo->query("SELECT pb.id, pb.name, pm.url 
                         FROM product_base pb 
                         LEFT JOIN product_media pm ON pb.id = pm.product_id 
                         ORDER BY pb.id DESC LIMIT 20");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'><tr><th>ID</th><th>Name</th><th>URL</th><th>Status</th></tr>";
    
    foreach ($rows as $row) {
        $status = "Remote";
        if (strpos($row['url'], 'uploads/') !== false) {
            $path = __DIR__ . '/' . $row['url'];
            if (file_exists($path)) {
                $status = "✅ Local (" . round(filesize($path)/1024, 1) . " KB)";
            } else {
                $status = "❌ Missing File";
            }
        } elseif (empty($row['url'])) {
            $status = "⚠️ No Image";
        }
        
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>" . substr($row['url'], 0, 30) . "...</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
