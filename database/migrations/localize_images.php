<?php
include 'config.php';

// Ensure directory exists
$targetDir = __DIR__ . '/../../uploads/products/';
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

echo "<h1>📥 Localizing Product Images</h1><hr>";

// Fetch all media with external URLs
$stmt = $pdo->query("SELECT id, product_id, url FROM product_media WHERE url LIKE 'http%'");
$mediaItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updatedCount = 0;
$failedCount = 0;

foreach ($mediaItems as $item) {
    $url = $item['url'];
    $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
    if (!$ext) $ext = 'jpg'; // Default if cleaning fails
    
    // Clean extension (remove query params if any leaked)
    $ext = explode('?', $ext)[0];
    
    // Generate new filename
    $newFilename = "product_{$item['product_id']}_" . uniqid() . ".$ext";
    $localPath = $targetDir . $newFilename;
    $dbPath = "uploads/products/$newFilename";

    // Download
    echo "DTO: Product {$item['product_id']}... ";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For dev env
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 && $data) {
        if (file_put_contents($localPath, $data)) {
            // Update DB
            $update = $pdo->prepare("UPDATE product_media SET url = ? WHERE id = ?");
            $update->execute([$dbPath, $item['id']]);
            echo "✅ Saved as $dbPath<br>";
            $updatedCount++;
        } else {
            echo "❌ Write Failed<br>";
            $failedCount++;
        }
    } else {
        echo "❌ Download Failed (HTTP $httpCode)<br>";
        $failedCount++;
    }
}

echo "<hr><h3>🎉 Synchronization Complete</h3>";
echo "<strong>$updatedCount</strong> images localized.<br>";
echo "<strong>$failedCount</strong> failures.<br>";
echo "<a href='../../shop.php'>Return to Shop</a>";
?>
