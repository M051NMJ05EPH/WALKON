<?php
include 'config.php';

/**
 * Download and Save Product Images Locally
 * This fixes the broken Unsplash links by downloading images and saving them locally
 */

// Create images directory if it doesn't exist
$image_dir = 'assets/products';
if (!is_dir($image_dir)) {
    mkdir($image_dir, 0777, true);
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Downloading Product Images</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .progress { background: #e0e0e0; border-radius: 8px; height: 30px; margin: 20px 0; overflow: hidden; }
        .progress-bar { background: linear-gradient(to right, #10b981, #34d399); height: 100%; line-height: 30px; text-align: center; color: white; font-weight: bold; transition: width 0.3s; }
        .product { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; display: flex; align-items: center; gap: 15px; }
        .product img { max-width: 100px; border-radius: 8px; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
    </style>
</head>
<body>
    <h2>📥 Downloading and Saving Product Images Locally</h2>";

try {
    // Get all products
    $stmt = $pdo->query("
        SELECT pb.id, pb.name, b.name as brand_name, c.name as category_name
        FROM product_base pb
        LEFT JOIN product_specs ps ON pb.id = ps.product_id
        LEFT JOIN brands b ON ps.brand_id = b.id
        LEFT JOIN categories c ON pb.category_id = c.id
        WHERE pb.status = 'published'
        ORDER BY pb.id
    ");
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total = count($products);
    
    echo "<p>Found <strong>$total products</strong> to update</p>";
    echo "<div class='progress'><div class='progress-bar' id='progressBar' style='width: 0%'>0%</div></div>";
    echo "<div id='status'></div>";
    echo "<script>
        function updateProgress(current, total) {
            const percent = Math.round((current / total) * 100);
            document.getElementById('progressBar').style.width = percent + '%';
            document.getElementById('progressBar').innerText = percent + '%';
        }
    </script>";
    
    $updated = 0;
    $failed = 0;
    
    foreach ($products as $index => $product) {
        $current = $index + 1;
        echo "<script>updateProgress($current, $total);</script>";
        flush();
        
        $product_id = $product['id'];
        $brand = $product['brand_name'] ?? 'shoe';
        $category = $product['category_name'] ?? 'footwear';
        $name = $product['name'];
        
        // Create search query
        $search = strtolower(str_replace(' ', '-', $brand . ' ' . $category));
        
        // Use Unsplash Source API with better parameters
        $width = 800;
        $height = 800;
        $unsplash_url = "https://source.unsplash.com/{$width}x{$height}/?{$search}&sig=" . ($index + time());
        
        try {
            // Download image with timeout
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'follow_location' => true
                ]
            ]);
            
            $image_data = @file_get_contents($unsplash_url, false, $context);
            
            if ($image_data && strlen($image_data) > 5000) {
                // Save locally
                $filename = 'product_' . $product_id . '_' . time() . '.jpg';
                $local_path = $image_dir . '/' . $filename;
                
                if (file_put_contents($local_path, $image_data)) {
                    // Update database
                    $db_path = $local_path;
                    
                    // Delete old images
                    $pdo->prepare("DELETE FROM product_media WHERE product_id = ? AND is_primary = 1")->execute([$product_id]);
                    
                    // Insert new image
                    $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary, type) VALUES (?, ?, 1, 'image')")->execute([$product_id, $db_path]);
                    
                    echo "<div class='product'>
                        <img src='$db_path' alt='$name'>
                        <div>
                            <strong class='success'>✅ $name</strong>
                            <div style='color: #666; font-size: 0.9em;'>$brand - $category</div>
                            <div style='color: #999; font-size: 0.8em;'>Saved: $filename</div>
                        </div>
                    </div>";
                    
                    $updated++;
                } else {
                    throw new Exception("Failed to save file");
                }
            } else {
                throw new Exception("Invalid image data");
            }
            
        } catch (Exception $e) {
            echo "<div class='product'>
                <div>
                    <strong class='error'>❌ $name</strong>
                    <div style='color: #999; font-size: 0.9em;'>Error: " . $e->getMessage() . "</div>
                </div>
            </div>";
            $failed++;
        }
        
        // Small delay
        usleep(300000); // 0.3 seconds
        flush();
    }
    
    echo "<hr>";
    echo "<div style='background: white; padding: 20px; border-radius: 12px; margin: 20px 0;'>";
    echo "<h3>📊 Download Complete!</h3>";
    echo "<p>✅ Successfully saved: <strong>$updated</strong> images</p>";
    echo "<p>❌ Failed: <strong>$failed</strong> images</p>";
    echo "<br>";
    echo "<a href='shop.php' style='display: inline-block; padding: 12px 24px; background: #10b981; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;'>View Shop →</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; color: #721c24;'>";
    echo "<h3>Database Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>
