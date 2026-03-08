<?php
include 'config.php';

$seller_id = 1;

$categories = [
    'Boots', 'Formal Shoes', 'Running Shoes', 'Sandals & Slides', 'Sneakers', 'Sports'
];

$brands = [
    'adidas', 'ASICS', 'Bata', 'Clarks', 'Converse', 'Crocs', 
    'Dr. Martens', 'New Balance', 'Nike', 'PUMA', 'Reebok', 
    'Skechers', 'Sparx', 'Timberland', 'Under Armour', 'Vans'
];

$products = [
    [
        'name' => 'Elite Leather Oxford',
        'sku' => 'ELO-001',
        'price' => 5999.00,
        'cat' => 'Formal Shoes',
        'brand' => 'Clarks',
        'material' => 'Leather',
        'desc' => 'Premium handcrafted leather oxford shoes for high-tier professional settings.',
        'type' => 'Oxford'
    ],
    [
        'name' => 'Neo-Mesh Ultra Runner',
        'sku' => 'NUR-002',
        'price' => 4499.00,
        'cat' => 'Running Shoes',
        'brand' => 'adidas',
        'material' => 'Mesh',
        'desc' => 'Next-generation mesh technology providing unmatched breathability and support.',
        'type' => 'Running'
    ],
    [
        'name' => 'Synthetic Pro Court',
        'sku' => 'SPC-003',
        'price' => 2999.00,
        'cat' => 'Sneakers',
        'brand' => 'Nike',
        'material' => 'Synthetic',
        'desc' => 'High-durability synthetic sneakers optimized for urban style and comfort.',
        'type' => 'Court'
    ],
    [
        'name' => 'Rugged Timberland Boot',
        'sku' => 'RTB-004',
        'price' => 8999.00,
        'cat' => 'Boots',
        'brand' => 'Timberland',
        'material' => 'Leather',
        'desc' => 'Iconic waterproof leather boots designed for extreme durability and traction.',
        'type' => 'Boots'
    ],
    [
        'name' => 'Cloud-Mesh Sports Trainer',
        'sku' => 'CMT-005',
        'price' => 3499.00,
        'cat' => 'Sports',
        'brand' => 'PUMA',
        'material' => 'Mesh',
        'desc' => 'Lightweight sports trainer featuring cloud-mesh tech for maximum agility.',
        'type' => 'Trainer'
    ]
];

try {
    $pdo->beginTransaction();

    // 1. Categories
    echo "Syncing categories...\n";
    $cat_map = [];
    $stmt_cat = $pdo->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
    foreach ($categories as $cat) {
        $stmt_cat->execute([$cat]);
    }
    
    $res_cat = $pdo->query("SELECT id, name FROM categories")->fetchAll();
    foreach ($res_cat as $row) {
        $cat_map[$row['name']] = $row['id'];
    }

    // 2. Brands
    echo "Syncing brands...\n";
    $brand_map = [];
    $stmt_brand = $pdo->prepare("INSERT IGNORE INTO brands (name) VALUES (?)");
    foreach ($brands as $brand) {
        $stmt_brand->execute([$brand]);
    }
    
    $res_brand = $pdo->query("SELECT id, name FROM brands")->fetchAll();
    foreach ($res_brand as $row) {
        $brand_map[$row['name']] = $row['id'];
    }

    // 3. Products
    echo "Syncing products...\n";
    foreach ($products as $p) {
        $cat_id = $cat_map[$p['cat']] ?? null;
        $brand_id = $brand_map[$p['brand']] ?? null;
        
        // Base
        $stmt = $pdo->prepare("INSERT INTO product_base (seller_id, category_id, name, status) VALUES (?, ?, ?, 'published')");
        $stmt->execute([$seller_id, $cat_id, $p['name']]);
        $pid = $pdo->lastInsertId();

        // SKU
        $pdo->prepare("INSERT INTO product_skus (product_id, sku) VALUES (?, ?)")->execute([$pid, $p['sku']]);

        // Price
        $pdo->prepare("INSERT INTO product_prices (product_id, price) VALUES (?, ?)")->execute([$pid, $p['price']]);

        // Stock
        $pdo->prepare("INSERT INTO product_stock (product_id, quantity) VALUES (?, ?)")->execute([$pid, 50]);

        // Description
        $pdo->prepare("INSERT INTO product_descriptions (product_id, content) VALUES (?, ?)")->execute([$pid, $p['desc']]);

        // Specs
        $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, outer_material, shoe_type) VALUES (?, ?, ?, ?)")
            ->execute([$pid, $brand_id, $p['material'], $p['type']]);
            
        echo "Added: {$p['name']}\n";
    }

    $pdo->commit();
    echo "\nSuccess: All data seeded correctly.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
