<?php
include 'config.php';

// Ensure uploads directory exists
if (!is_dir('uploads/products')) {
    mkdir('uploads/products', 0777, true);
}

try {
    echo "<h1>Downloading Images Locally...</h1>";

    $stmt = $pdo->query("SELECT id, url FROM product_media WHERE type='image'");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($images as $img) {
        $url = $img['url'];
        $id = $img['id'];
        
        // Skip if already local
        if (strpos($url, 'uploads/') !== false) continue;

        echo "Processing ID $id... ";
        
        $content = @file_get_contents($url);
        if ($content) {
            $filename = 'product_' . $id . '_' . time() . '.jpg';
            $path = 'uploads/products/' . $filename;
            file_put_contents($path, $content);
            
            // Update DB
            $upd = $pdo->prepare("UPDATE product_media SET url = ? WHERE id = ?");
            $upd->execute([$path, $id]);
            echo "✅ Saved to $path<br>";
        } else {
            echo "❌ Failed to download $url<br>";
        }
    }
    echo "<h3>Download Complete!</h3>";
    echo "<a href='shop.php'>Check Shop</a>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
