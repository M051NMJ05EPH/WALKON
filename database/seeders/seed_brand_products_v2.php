<?php
include 'config.php';

$seller_id = 1;

// Fetch maps for dynamic ID lookup
$cat_map = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_KEY_PAIR);
$brand_map = $pdo->query("SELECT id, name FROM brands")->fetchAll(PDO::FETCH_KEY_PAIR);

$cat_id_by_name = array_flip($cat_map);
$brand_id_by_name = array_flip($brand_map);

$products = [
    [
        'name' => 'Reebok Nano X3',
        'sku' => 'RBK-NX3-001',
        'price' => 12999.00,
        'cat' => 'Sports',
        'brand' => 'Reebok',
        'material' => 'Flexweave',
        'desc' => 'The official shoe of fitness. Versatile, durable, and built for performance.',
        'type' => 'Training',
        'img' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'name' => 'Skechers Arch Fit',
        'sku' => 'SKX-AF-001',
        'price' => 7499.00,
        'cat' => 'Sneakers',
        'brand' => 'Skechers',
        'material' => 'Mesh',
        'desc' => 'Podiatrist-designed arch support. Ultimate comfort for all-day wear.',
        'type' => 'Lifestyle',
        'img' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'name' => 'Sparx Power Running',
        'sku' => 'SPX-PR-001',
        'price' => 2499.00,
        'cat' => 'Sports',
        'brand' => 'Sparx',
        'material' => 'Synthetic',
        'desc' => 'High-performance running shoes designed for maximum durability.',
        'type' => 'Running',
        'img' => 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'name' => 'Timberland 6-Inch Premium',
        'sku' => 'TBL-6IN-P-001',
        'price' => 18999.00,
        'cat' => 'Boots',
        'brand' => 'Timberland',
        'material' => 'Waterproof Leather',
        'desc' => 'The iconic waterproof boot that started it all. Rugged and refined.',
        'type' => 'Boot',
        'img' => 'https://images.unsplash.com/photo-1520639889313-72721e0ab9ef?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'name' => 'Under Armour Curry 10',
        'sku' => 'UA-C10-001',
        'price' => 15999.00,
        'cat' => 'Sports',
        'brand' => 'Under Armour',
        'material' => 'UA Warp',
        'desc' => 'Stephen Curry signature shoe. Unmatched grip and stability on the court.',
        'type' => 'Basketball',
        'img' => 'uploads/ua_curry_10.png'
    ],
    [
        'name' => 'Vans Old Skool Stackform',
        'sku' => 'VNS-OSS-001',
        'price' => 6499.00,
        'cat' => 'Sneakers',
        'brand' => 'Vans',
        'material' => 'Suede & Canvas',
        'desc' => 'The classic Old Skool elevated with a platform sole.',
        'type' => 'Skate',
        'img' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=800&q=80'
    ]
];

try {
    $pdo->beginTransaction();

    foreach ($products as $p) {
        $cat_id = $cat_id_by_name[$p['cat']] ?? null;
        $brand_id = $brand_id_by_name[$p['brand']] ?? null;

        if (!$cat_id || !$brand_id) {
            echo "Skipping {$p['name']}: Category '{$p['cat']}' or Brand '{$p['brand']}' not found.\n";
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
        $pdo->prepare("INSERT INTO product_stock (product_id, quantity) VALUES (?, ?)")->execute([$pid, 75]);

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
    echo "\nSuccess: Second set of brand products seeded correctly.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
