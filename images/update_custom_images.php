<?php
include 'config.php';

/**
 * Update Specific Products with Custom Uploaded Images
 */

// Define the mapping of products to their new images
$product_images = [
    'Timberland Bradstreet Chukka' => 'assets/products/timberland_bradstreet.jpg',
    'Clarks Un Costa Lace' => 'assets/products/clarks_uncosta.jpg',
    'Red Tape Leather Oxford' => 'assets/products/redtape_oxford.jpg'
];

echo "<h2>Updating Products with Custom Images</h2>";
echo "<style>
    body { font-family: 'Arial', sans-serif; padding: 20px; background: #f5f5f5; }
    .success { background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #28a745; }
    .error { background: #f8d7da; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #dc3545; }
    .image-preview { max-width: 200px; height: auto; border-radius: 8px; margin: 10px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .product-card { background: white; padding: 20px; margin: 15px 0; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h3 { color: #333; margin: 0 0 10px 0; }
    .brand { color: #10b981; font-size: 0.9em; font-weight: bold; text-transform: uppercase; }
</style>";

try {
    $updated_count = 0;
    $failed_count = 0;
    
    foreach ($product_images as $product_name => $image_path) {
        try {
            // Find the product by name (case-insensitive, partial match)
            $stmt = $pdo->prepare("
                SELECT pb.id, pb.name, b.name as brand_name
                FROM product_base pb
                LEFT JOIN product_specs ps ON pb.id = ps.product_id
                LEFT JOIN brands b ON ps.brand_id = b.id
                WHERE pb.name LIKE ?
                LIMIT 1
            ");
            $search_term = '%' . $product_name . '%';
            $stmt->execute([$search_term]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                // Try searching by brand name if product name doesn't match
                $brand_search = explode(' ', $product_name)[0]; // Get first word (brand)
                $stmt = $pdo->prepare("
                    SELECT pb.id, pb.name, b.name as brand_name
                    FROM product_base pb
                    LEFT JOIN product_specs ps ON pb.id = ps.product_id
                    LEFT JOIN brands b ON ps.brand_id = b.id
                    WHERE b.name LIKE ? OR pb.name LIKE ?
                    LIMIT 1
                ");
                $stmt->execute(['%' . $brand_search . '%', '%' . $brand_search . '%']);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if ($product) {
                $product_id = $product['id'];
                
                // Check if image file exists
                if (file_exists($image_path)) {
                    // Delete old primary images
                    $delete_old = $pdo->prepare("DELETE FROM product_media WHERE product_id = ? AND is_primary = 1");
                    $delete_old->execute([$product_id]);
                    
                    // Insert new primary image
                    $insert_new = $pdo->prepare("
                        INSERT INTO product_media (product_id, url, is_primary, type) 
                        VALUES (?, ?, 1, 'image')
                    ");
                    $insert_new->execute([$product_id, $image_path]);
                    
                    echo "<div class='product-card'>";
                    echo "<h3>✅ {$product['name']}</h3>";
                    echo "<div class='brand'>{$product['brand_name']}</div>";
                    echo "<p><strong>Image Updated:</strong> $image_path</p>";
                    echo "<img src='$image_path' class='image-preview' alt='{$product['name']}'>";
                    echo "</div>";
                    
                    $updated_count++;
                } else {
                    throw new Exception("Image file not found: $image_path");
                }
            } else {
                throw new Exception("Product not found: $product_name");
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<h3>❌ Failed: $product_name</h3>";
            echo "<p>Error: " . $e->getMessage() . "</p>";
            echo "</div>";
            $failed_count++;
        }
    }
    
    echo "<hr style='margin: 30px 0;'>";
    echo "<div style='background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>";
    echo "<h3>📊 Update Summary</h3>";
    echo "<p>✅ <strong>Successfully Updated:</strong> $updated_count products</p>";
    echo "<p>❌ <strong>Failed:</strong> $failed_count products</p>";
    echo "<br>";
    echo "<a href='shop.php' style='display: inline-block; padding: 12px 24px; background: #10b981; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;'>View Shop →</a> ";
    echo "<a href='Index.php' style='display: inline-block; padding: 12px 24px; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin-left: 10px;'>View Homepage →</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>Database Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
