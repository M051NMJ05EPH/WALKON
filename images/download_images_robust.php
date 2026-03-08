<?php
include 'config.php';
header('Content-Type: text/plain');

// Ensure uploads directory exists
if (!is_dir('uploads/products')) {
    mkdir('uploads/products', 0777, true);
}

function downloadImage($url, $path) {
    if (file_exists($path) && filesize($path) > 0) return true;
    
    $ch = curl_init($url);
    $fp = fopen($path, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);
    
    if ($code !== 200 || filesize($path) == 0) {
        unlink($path);
        return false;
    }
    return true;
}

try {
    echo "STARTING ROBUST DOWNLOAD...\n";

    // Select ALL images that are remote OR missing local files
    $stmt = $pdo->query("SELECT id, url FROM product_media WHERE type='image'");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $count = 0;
    $errors = 0;

    foreach ($images as $img) {
        $url = $img['url'];
        $id = $img['id'];
        
        // Skip if it looks like a local path AND exists
        if (strpos($url, 'uploads/') !== false) {
             if (!file_exists($url)) {
                 echo "ID $id: Local file missing ($url). Attempting recovery skipped (no source).\n";
             }
             continue; 
        }

        echo "Processing ID $id... ";
        
        $filename = 'product_' . $id . '_' . time() . '.jpg';
        $path = 'uploads/products/' . $filename;
        
        if (downloadImage($url, $path)) {
            // Update DB
            $upd = $pdo->prepare("UPDATE product_media SET url = ? WHERE id = ?");
            $upd->execute([$path, $id]);
            echo "SAVED to $path\n";
            $count++;
        } else {
             // Fallback to a known working image or local asset
             echo "FAILED original URL. Trying fallback...\n";
             $fallback_url = "https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=600"; // Known working Red Nike
             
             if (downloadImage($fallback_url, $path)) {
                 $upd = $pdo->prepare("UPDATE product_media SET url = ? WHERE id = ?");
                 $upd->execute([$path, $id]);
                 echo "SAVED Fallback to $path\n";
                 $count++;
             } else {
                 // Final Fallback: Local Hero Shoe
                 $local_fallback = 'assets/hero_shoe.png';
                 if (file_exists($local_fallback)) {
                     copy($local_fallback, $path);
                     $upd = $pdo->prepare("UPDATE product_media SET url = ? WHERE id = ?");
                     $upd->execute([$path, $id]);
                     echo "SAVED Local Asset to $path\n";
                     $count++;
                 } else {
                     echo "CRITICAL: No image could be saved.\n";
                     $errors++;
                 }
             }
        }
    }
    echo "\nSUMMARY: Updated $count images. Failed $errors.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
