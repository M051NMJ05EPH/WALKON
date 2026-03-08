<?php
include 'config.php';
header('Content-Type: text/plain');

if (!is_dir('uploads/products')) mkdir('uploads/products', 0777, true);

// Rotating Fallbacks (High Quality)
$fallbacks = [
    'https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=600', // White/Color Sneaker
    'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=600', // Blue Sneaker
    'https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=600', // Jordan
    'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?q=80&w=600', // Green Shoe
    'https://images.unsplash.com/photo-1560769629-975e13f0c470?q=80&w=600', // Leather Shoe
    'https://images.unsplash.com/photo-1545127398-5aae4d57c668?q=80&w=600'  // Slide/Sandal
];

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
    echo "STARTING ROTATING DOWNLOADER...\n";

    $stmt = $pdo->query("SELECT id, url FROM product_media WHERE type='image'");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $fallback_idx = 0;

    foreach ($images as $img) {
        $url = $img['url'];
        $id = $img['id'];
        
        // Skip valid local
        if (strpos($url, 'uploads/') !== false && file_exists($url) && filesize($url) > 1000) continue;

        echo "Product $id: ";
        $filename = 'product_' . $id . '_' . time() . '.jpg';
        $path = 'uploads/products/' . $filename;
        
        // Try DB URL
        if (downloadImage($url, $path)) {
            $pdo->prepare("UPDATE product_media SET url=? WHERE id=?")->execute([$path, $id]);
            echo "SAVED Original\n";
        } else {
            // Try Rotating Fallback
            $f_url = $fallbacks[$fallback_idx % count($fallbacks)];
            echo "FAILED Original. Trying Fallback " . ($fallback_idx % count($fallbacks)) . "... ";
            
            if (downloadImage($f_url, $path)) {
                $pdo->prepare("UPDATE product_media SET url=? WHERE id=?")->execute([$path, $id]);
                echo "SAVED Fallback\n";
            } else {
                echo "FAILED Fallback. Using Local Hero Backup.\n";
                // Final Local Fallback
                if (file_exists('assets/hero_shoe.png')) {
                    copy('assets/hero_shoe.png', $path);
                    $pdo->prepare("UPDATE product_media SET url=? WHERE id=?")->execute([$path, $id]);
                }
            }
            $fallback_idx++; // Increment to ensure next failure gets different image
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
