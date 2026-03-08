<?php
include 'config.php';

try {
    echo "<h1>Checking Broken Images...</h1>";
    
    $names = [
        'Puma Divecat v2',
        'Crocs Classic Clog Navy',
        'Timberland 6-Inch Premium'
    ];
    
    foreach ($names as $name) {
        $stmt = $pdo->prepare("SELECT pb.id, pb.name, pm.url 
                               FROM product_base pb 
                               LEFT JOIN product_media pm ON pb.id = pm.product_id 
                               WHERE pb.name = ?");
        $stmt->execute([$name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            echo "Product: <b>{$row['name']}</b> (ID: {$row['id']})<br>";
            echo "URL: " . htmlspecialchars($row['url']) . "<br>";
            
            if (strpos($row['url'], 'uploads/') !== false) {
                // Check if file exists
                $path = __DIR__ . '/' . $row['url'];
                if (file_exists($path)) {
                    echo "File Status: ✅ Exists (" . filesize($path) . " bytes)<br>";
                } else {
                    echo "File Status: ❌ Missing from disk<br>";
                }
            } else {
                echo "File Status: ⚠️ Remote URL (Not downloaded)<br>";
            }
            echo "<hr>";
        } else {
            echo "Product '$name' not found in DB.<hr>";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
