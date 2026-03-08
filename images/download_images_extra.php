<?php
include 'config.php';
header('Content-Type: text/plain');

if (!is_dir('uploads/products')) mkdir('uploads/products', 0777, true);

function downloadImage($url, $path) {
    if (file_exists($path) && filesize($path) > 1000) return true;
    
    $ch = curl_init($url);
    $fp = fopen($path, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);
    
    if ($code !== 200 || filesize($path) < 1000) {
        @unlink($path);
        return false;
    }
    return true;
}

try {
    echo "STARTING EXTRA IMAGE DOWNLOADER...\n";

    // Select ONLY secondary images that are likely remote
    $stmt = $pdo->query("SELECT id, url FROM product_media WHERE is_primary = 0");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($images as $img) {
        $url = $img['url'];
        $id = $img['id'];
        
        if (strpos($url, 'uploads/') !== false) continue;

        echo "Media ID $id: ";
        $filename = 'gallery_' . $id . '_' . time() . '.jpg';
        $path = 'uploads/products/' . $filename;
        
        if (downloadImage($url, $path)) {
            $pdo->prepare("UPDATE product_media SET url=? WHERE id=?")->execute([$path, $id]);
            echo "SAVED to $path\n";
        } else {
            echo "FAILED main. Using fallback.\n";
            // Simple local fallback for gallery
            if (file_exists('assets/hero_shoe.png')) {
                copy('assets/hero_shoe.png', $path);
                $pdo->prepare("UPDATE product_media SET url=? WHERE id=?")->execute([$path, $id]);
                echo "SAVED Local Fallback\n";
            }
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
