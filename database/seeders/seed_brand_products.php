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
        'name' => 'Adidas Ultraboost Light',
        'sku' => 'ADI-UB-L-001',
        'price' => 18999.00,
        'cat' => 'Running Shoes',
        'brand' => 'adidas',
        'material' => 'Primeknit',
        'desc' => 'Experience epic energy with the new Ultraboost Light, our lightest Ultraboost ever.',
        'type' => 'Running',
        'img' => 'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'name' => 'Nike Air Jordan 1 High',
        'sku' => 'NIKE-AJ1-H-001',
        'price' => 15995.00,
        'cat' => 'Sneakers',
        'brand' => 'Nike',
        'material' => 'Leather',
        'desc' => 'The iconic sneaker that defined a generation. Premium leather and classic design.',
        'type' => 'Basketball',
        'img' => 'https://images.unsplash.com/photo-1597248881519-db089d3744a0?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'name' => 'ASICS Gel-Kayano 29',
        'sku' => 'ASC-GK29-001',
        'price' => 14999.00,
        'cat' => 'Running Shoes',
        'brand' => 'ASICS',
        'material' => 'Engineered Mesh',
        'desc' => 'Stable and supportive running shoe for long-distance comfort.',
        'type' => 'Running',
        'img' => 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'name' => 'Bata Premium Derby',
        'sku' => 'BATA-PRM-DB-001',
        'price' => 4999.00,
        'cat' => 'Formal Shoes',
        'brand' => 'Bata',
        'material' => 'Full Grain Leather',
        'desc' => 'Handcrafted formal shoes for the modern professional.',
        'type' => 'Derby',
        'img' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'name' => 'Clarks CraftMaster II',
        'sku' => 'CLK-CM2-001',
        'price' => 8999.00,
        'cat' => 'Formal Shoes',
        'brand' => 'Clarks',
        'material' => 'Suede',
        'desc' => 'A timeless silhouette with Clarks signature comfort technology.',
        'type' => 'Oxford',
        'img' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'name' => 'Converse Chuck 70 Vintage',
        'sku' => 'CON-C70-V-001',
        'price' => 5999.00,
        'cat' => 'Sneakers',
        'brand' => 'Converse',
        'material' => 'Heavy Canvas',
        'desc' => 'The heritage classic with modern cushioning and vintage details.',
        'type' => 'Hi-Top',
        'img' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'name' => 'Crocs Classic Lined Clog',
        'sku' => 'CRC-CLC-001',
        'price' => 3995.00,
        'cat' => 'Sandals & Slides',
        'brand' => 'Crocs',
        'material' => 'Croslite',
        'desc' => 'The warm and fuzzy version of the iconic classic clog.',
        'type' => 'Clog',
        'img' => 'https://images.unsplash.com/photo-1595341888016-a392ef81b7de?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'name' => 'Dr. Martens 1460 Smooth',
        'sku' => 'DM-1460-S-001',
        'price' => 16999.00,
        'cat' => 'Boots',
        'brand' => 'Dr. Martens',
        'material' => 'Smooth Leather',
        'desc' => 'The original 8-eye boot. Instantly recognizable, built to last.',
        'type' => 'Boot',
        'img' => 'https://images.unsplash.com/photo-1511556820780-d912e42b4980?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'name' => 'New Balance 990v6',
        'sku' => 'NB-990-V6-001',
        'price' => 19999.00,
        'cat' => 'Sneakers',
        'brand' => 'New Balance',
        'material' => 'Suede & Mesh',
        'desc' => 'Made in USA quality with FuelCell foam technology for ultimate comfort.',
        'type' => 'Lifestyle',
        'img' => 'https://images.unsplash.com/photo-1539185441755-769473a23570?auto=format&fit=crop&w=800&q=80'
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
        $pdo->prepare("INSERT INTO product_stock (product_id, quantity) VALUES (?, ?)")->execute([$pid, 50]);

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
    echo "\nSuccess: Brand specific products seeded correctly.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
