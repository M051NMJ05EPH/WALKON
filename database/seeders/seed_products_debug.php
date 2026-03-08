<?php
/**
 * Seed Products DEBUG - Add sample shoes for all brands
 */
include 'config.php';

try {
    echo "<h1>Starting Debug Seed...</h1>";
    
    // Get seller ID
    $stmt = $pdo->query("SELECT id FROM sellers LIMIT 1");
    $seller = $stmt->fetch();
    
    if (!$seller) {
        $pdo->exec("INSERT INTO sellers (name, email, password, business_name) VALUES ('Demo Seller', 'demo@walkon.com', '" . password_hash('demo123', PASSWORD_DEFAULT) . "', 'WALKON Store')");
        $seller_id = $pdo->lastInsertId();
    } else {
        $seller_id = $seller['id'];
    }

    // Get all brands
    $brands = $pdo->query("SELECT id, name FROM brands")->fetchAll(PDO::FETCH_ASSOC);
    $categories = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($brands) || empty($categories)) {
        die("Missing brands or categories.");
    }

    // Simplified template (just one product per brand for test)
    $product_templates = [
        'Nike' => [['Air Max 270', 8999, 'Running Shoes']],
        'adidas' => [['Ultraboost 22', 12999, 'Running Shoes']],
        'PUMA' => [['RS-X', 7999, 'Sneakers']],
    ];
    // Use full list if this works

    $genders = ['Men', 'Women', 'Unisex'];
    $added = 0;

    foreach ($brands as $brand) {
        $brand_name = $brand['name'];
        $brand_id = $brand['id'];
        
        // Generate a generic product if not in template
        $products = [
            [$brand_name . ' Runner', 2999, 'Running Shoes'],
            [$brand_name . ' Sneaker', 2499, 'Sneakers']
        ];
        
        foreach ($products as $product) {
            $name = $product[0];
            $price = $product[1];
            $category_name = $product[2];
            
            // Find category
            $category_id = null;
            foreach ($categories as $cat) {
                 if (stripos($cat['name'], explode(' ', $category_name)[0]) !== false) {
                    $category_id = $cat['id'];
                    break;
                }
            }
            if (!$category_id) $category_id = $categories[0]['id'];
            
            // Check if product already exists
            $check = $pdo->prepare("SELECT id FROM product_base WHERE name = ?");
            $check->execute([$name]);
            if ($check->fetch()) continue;
            
            // Insert product_base
            $stmt = $pdo->prepare("INSERT INTO product_base (seller_id, category_id, name, status) VALUES (?, ?, ?, 'published')");
            $stmt->execute([$seller_id, $category_id, $name]);
            $product_id = $pdo->lastInsertId();
            
            // SKIP Descriptions
            // $pdo->prepare("INSERT INTO product_descriptions...
            
            // Insert price
            $pdo->prepare("INSERT INTO product_prices (product_id, price, min_price, max_price) VALUES (?, ?, ?, ?)")->execute([$product_id, $price, $price - 500, $price + 1000]);
            
            // SKIP Stock
             // $pdo->prepare("INSERT INTO product_stock...
            
            // Insert SKU
            $sku = strtoupper(substr($brand_name, 0, 3)) . '-' . $product_id;
            $pdo->prepare("INSERT INTO product_skus (product_id, sku) VALUES (?, ?)")->execute([$product_id, $sku]);
            
            // Insert specs
            $gender = $genders[array_rand($genders)];
            $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, gender, outer_material) VALUES (?, ?, ?, 'Synthetic')")
                ->execute([$product_id, $brand_id, $gender]);
            
            // Insert Media
            $image_url = 'https://via.placeholder.com/400x400';
            $pdo->prepare("INSERT INTO product_media (product_id, url, type, is_primary) VALUES (?, ?, 'image', 1)")->execute([$product_id, $image_url]);
            
            $added++;
        }
    }
    echo "Added $added products.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
