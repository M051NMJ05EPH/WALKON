<?php
include 'config.php';

$seller_id = 1;

// Fetch maps for dynamic ID lookup
$cat_map = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_KEY_PAIR);
$brand_map = $pdo->query("SELECT id, name FROM brands")->fetchAll(PDO::FETCH_KEY_PAIR);

// Function to inverse map
$cat_id_by_name = array_flip($cat_map);
$brand_id_by_name = array_flip($brand_map);

$extra_products = [
    [
        'name' => 'Classic Canvas Old Skool',
        'sku' => 'VANS-OS-001',
        'price' => 4599.00,
        'cat' => 'Sneakers',
        'brand' => 'Vans',
        'material' => 'Canvas',
        'desc' => 'The iconic sidestripe skate shoe that started it all.',
        'type' => 'Skate',
        'img' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'name' => 'Original Comfort Clog',
        'sku' => 'CROC-CL-002',
        'price' => 2495.00,
        'cat' => 'Sandals & Slides',
        'brand' => 'Crocs',
        'material' => 'Synthetic',
        'desc' => 'The iconic clog that started a comfort revolution around the world.',
        'type' => 'Clog',
        'img' => 'https://images.unsplash.com/photo-1595341888016-a392ef81b7de?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'name' => 'Bata Heritage Derby',
        'sku' => 'BATA-DB-003',
        'price' => 3299.00,
        'cat' => 'Formal Shoes',
        'brand' => 'Bata',
        'material' => 'Leather',
        'desc' => 'Timeless leather derbys for everyday business elegance.',
        'type' => 'Derby',
        'img' => 'https://images.unsplash.com/photo-1533867617858-e7b97e060509?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'name' => 'Air Max Infinity 2',
        'sku' => 'NIKE-AM-004',
        'price' => 7995.00,
        'cat' => 'Running Shoes',
        'brand' => 'Nike',
        'material' => 'Mesh',
        'desc' => 'Futuristic look with a cushioned heel and toggle lacing system.',
        'type' => 'Running',
        'img' => 'https://images.unsplash.com/photo-1514989940723-e8e51635b782?auto=format&fit=crop&w=800&q=80'
    ]
];

try {
    $pdo->beginTransaction();

    foreach ($extra_products as $p) {
        $cat_id = $cat_id_by_name[$p['cat']] ?? null;
        $brand_id = $brand_id_by_name[$p['brand']] ?? null;

        if (!$cat_id || !$brand_id) {
            echo "Skipping {$p['name']}: Category or Brand not found.\n";
            continue;
        }

        // Base
        $stmt = $pdo->prepare("INSERT INTO product_base (seller_id, category_id, name, status) VALUES (?, ?, ?, 'published')");
        $stmt->execute([$seller_id, $cat_id, $p['name']]);
        $pid = $pdo->lastInsertId();

        // SKU
        $pdo->prepare("INSERT INTO product_skus (product_id, sku) VALUES (?, ?)")->execute([$pid, $p['sku']]);

        // Price
        $pdo->prepare("INSERT INTO product_prices (product_id, price) VALUES (?, ?)")->execute([$pid, $p['price']]);

        // Stock
        $pdo->prepare("INSERT INTO product_stock (product_id, quantity) VALUES (?, ?)")->execute([$pid, 100]);

        // Description
        $pdo->prepare("INSERT INTO product_descriptions (product_id, content) VALUES (?, ?)")->execute([$pid, $p['desc']]);

        // Specs
        $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, outer_material, shoe_type) VALUES (?, ?, ?, ?)")
            ->execute([$pid, $brand_id, $p['material'], $p['type']]);

        // Media
        $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary) VALUES (?, ?, 1)")
            ->execute([$pid, $p['img']]);

        echo "Added: {$p['name']}\n";
    }

    $pdo->commit();
    echo "\nSuccess: Extra diverse products seeded correctly.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
