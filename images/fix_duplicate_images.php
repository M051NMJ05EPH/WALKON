<?php
include 'config.php';

/**
 * Fix Duplicate Product Images
 * Assigns unique, diverse images to each product based on their brand and category
 */

// Unsplash collections for different shoe types
$shoe_queries = [
    'timberland boots' => 'brown-boots-leather',
    'clarks shoes' => 'brown-casual-shoes',
    'red tape shoes' => 'leather-oxford-shoes',
    'nike sneakers' => 'nike-running-shoes',
    'adidas shoes' => 'adidas-sneakers',
    'puma shoes' => 'puma-athletic-shoes',
    'skechers shoes' => 'skechers-comfort-shoes',
    'new balance' => 'new-balance-running',
    'converse' => 'converse-sneakers',
    'vans' => 'vans-skateboard-shoes',
    'boots' => 'leather-boots-fashion',
    'sneakers' => 'modern-sneakers',
    'running shoes' => 'athletic-running-shoes',
    'formal shoes' => 'formal-leather-shoes',
    'sandals' => 'leather-sandals',
    'sports' => 'sports-athletic-shoes'
];

try {
    // Get all products with their brands and categories
    $stmt = $pdo->query("
        SELECT pb.id, pb.name, b.name as brand_name, c.name as category_name,
               (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as current_image
        FROM product_base pb
        LEFT JOIN product_specs ps ON pb.id = ps.product_id
        LEFT JOIN brands b ON ps.brand_id = b.id
        LEFT JOIN categories c ON pb.category_id = c.id
        WHERE pb.status = 'published'
        ORDER BY pb.id
    ");
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Fixing Duplicate Product Images</h2>";
    echo "<p>Found " . count($products) . " products to update</p>";
    echo "<hr>";
    
    $updated = 0;
    $failed = 0;
    
    foreach ($products as $index => $product) {
        $product_id = $product['id'];
        $brand = strtolower($product['brand_name'] ?? '');
        $category = strtolower($product['category_name'] ?? '');
        $name = $product['name'];
        
        // Determine search query based on brand or category
        $search_query = '';
        
        // Priority 1: Brand-specific
        foreach ($shoe_queries as $key => $query) {
            if (stripos($brand, $key) !== false) {
                $search_query = $query;
                break;
            }
        }
        
        // Priority 2: Category-specific
        if (!$search_query) {
            foreach ($shoe_queries as $key => $query) {
                if (stripos($category, $key) !== false) {
                    $search_query = $query;
                    break;
                }
            }
        }
        
        // Priority 3: Generic based on product name
        if (!$search_query) {
            $search_query = str_replace(' ', '-', strtolower($name));
        }
        
        // Add variety by using index to get different images
        $page = ($index % 10) + 1; // Cycle through pages 1-10
        
        // Fetch image from Unsplash
        $unsplash_url = "https://source.unsplash.com/800x800/?$search_query&sig=$index";
        
        try {
            // Download and verify image
            $image_data = @file_get_contents($unsplash_url);
            
            if ($image_data && strlen($image_data) > 1000) {
                // Update product_media table
                $check = $pdo->prepare("SELECT id FROM product_media WHERE product_id = ? AND is_primary = 1");
                $check->execute([$product_id]);
                
                if ($check->rowCount() > 0) {
                    // Update existing
                    $update = $pdo->prepare("UPDATE product_media SET url = ? WHERE product_id = ? AND is_primary = 1");
                    $update->execute([$unsplash_url, $product_id]);
                } else {
                    // Insert new
                    $insert = $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary, type) VALUES (?, ?, 1, 'image')");
                    $insert->execute([$product_id, $unsplash_url]);
                }
                
                echo "<div style='margin: 10px 0; padding: 10px; background: #d4edda; border-radius: 5px;'>";
                echo "✅ <strong>Updated:</strong> $name (Brand: $brand, Category: $category)<br>";
                echo "🔍 Query: $search_query<br>";
                echo "🖼️ <img src='$unsplash_url' style='max-width: 150px; margin-top: 5px; border-radius: 8px;'>";
                echo "</div>";
                
                $updated++;
            } else {
                throw new Exception("Invalid image data");
            }
        } catch (Exception $e) {
            echo "<div style='margin: 10px 0; padding: 10px; background: #f8d7da; border-radius: 5px;'>";
            echo "❌ <strong>Failed:</strong> $name - " . $e->getMessage();
            echo "</div>";
            $failed++;
        }
        
        // Small delay to avoid rate limiting
        usleep(500000); // 0.5 second delay
    }
    
    echo "<hr>";
    echo "<h3>Summary</h3>";
    echo "<p>✅ Successfully Updated: <strong>$updated</strong></p>";
    echo "<p>❌ Failed: <strong>$failed</strong></p>";
    echo "<p><a href='shop.php' style='display: inline-block; padding: 10px 20px; background: #10b981; color: white; text-decoration: none; border-radius: 8px; margin-top: 10px;'>View Shop</a></p>";
    
} catch (PDOException $e) {
    echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border-radius: 8px;'>";
    echo "<h3>Database Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
