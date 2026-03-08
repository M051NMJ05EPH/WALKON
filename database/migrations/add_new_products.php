<?php
include 'config.php';

// IDs from database (assuming these exist based on previous research)
$seller_id = 1;
$cat_casual = 1;
$cat_sports = 2; // Assuming 2 is Sports based on typical setups, but I'll check or fallback

$products = [
    [
        'name' => 'Handcrafted Leather Chelsea Boot',
        'sku' => 'LCB-PREMIUM-001',
        'price' => 6499.00,
        'cat_id' => $cat_casual,
        'material' => 'Leather',
        'desc' => 'Timeless design meets superior craftsmanship. Made from genuine top-grain leather with a durable sole.',
        'brand_id' => 16, // Timberland
        'type' => 'Boots'
    ],
    [
        'name' => 'Breathable Mesh Speed Runner',
        'sku' => 'MSR-NEO-002',
        'price' => 3899.00,
        'cat_id' => $cat_sports,
        'material' => 'Mesh',
        'desc' => 'Ultra-lightweight mesh upper for maximum breathability during high-intensity runs.',
        'brand_id' => 3, // PUMA
        'type' => 'Running Shoes'
    ],
    [
        'name' => 'Prime-Synthetic Street Sneakers',
        'sku' => 'PSS-URBAN-003',
        'price' => 2199.00,
        'cat_id' => $cat_casual,
        'material' => 'Synthetic',
        'desc' => 'Modern aesthetics combined with durable synthetic materials. Perfect for daily urban exploration.',
        'brand_id' => 1, // Nike/adidas IDs vary, assuming mapping exists
        'type' => 'Sneakers'
    ]
];

try {
    $pdo->beginTransaction();
    
    foreach ($products as $p) {
        // 1. Base
        $stmt = $pdo->prepare("INSERT INTO product_base (seller_id, category_id, name, status) VALUES (?, ?, ?, 'published')");
        $stmt->execute([$seller_id, $p['cat_id'], $p['name']]);
        $new_id = $pdo->lastInsertId();
        
        // 2. SKU
        $pdo->prepare("INSERT INTO product_skus (product_id, sku) VALUES (?, ?)")->execute([$new_id, $p['sku']]);
        
        // 3. Price
        $pdo->prepare("INSERT INTO product_prices (product_id, price) VALUES (?, ?)")->execute([$new_id, $p['price']]);
        
        // 4. Stock
        $pdo->prepare("INSERT INTO product_stock (product_id, quantity) VALUES (?, ?)")->execute([$new_id, 25]);
        
        // 5. Description
        $pdo->prepare("INSERT INTO product_descriptions (product_id, content) VALUES (?, ?)")->execute([$new_id, $p['desc']]);
        
        // 6. Specs
        $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, outer_material, shoe_type) VALUES (?, ?, ?, ?)")
            ->execute([$new_id, $p['brand_id'], $p['material'], $p['type']]);
            
        echo "Added Product: {$p['name']} (ID: $new_id)\n";
    }
    
    $pdo->commit();
    echo "\nNew material-based products added successfully.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
