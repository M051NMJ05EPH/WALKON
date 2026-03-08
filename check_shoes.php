<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';

$names = ['Metro Formal Oxford', 'LiteRide 360'];

foreach ($names as $name) {
    echo "Checking for '$name'...\n";
    $stmt = $pdo->prepare("SELECT * FROM product_base WHERE name LIKE ?");
    $stmt->execute(["%$name%"]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($rows) > 0) {
        foreach ($rows as $row) {
            echo "Found: ID " . $row['id'] . " - " . $row['name'] . "\n";
            
            // Check images
            // Assuming product_media uses product_id as FK? and maybe url/media_url column?
            // I'll assume 'product_id' and 'media_url' based on previous attempt, but I will check schema in parallel tool call.
            // Wait, I should wait for schema check result before writing this if I wasn't sure.
            // But I can guess standard names or update later.
            // Let's assume standard names for now, relying on recent 'check_shoes.php' logic.
            // Actually previous code used 'media_url'.
            
            $media_stmt = $pdo->prepare("SELECT * FROM product_media WHERE product_id = ?");
            $media_stmt->execute([$row['id']]); // 'id' from product_base
            $media_rows = $media_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($media_rows) > 0) {
                foreach($media_rows as $m) {
                    // I'll dump the whole row to be safe if I don't know the column name for URL
                    print_r($m);
                }
            } else {
                echo "  No images found.\n";
            }
        }
    } else {
        echo "Not found.\n";
    }
    echo "-------------------\n";
}
?>
