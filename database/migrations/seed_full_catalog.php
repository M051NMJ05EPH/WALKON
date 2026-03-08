<?php
include 'config.php';

// Disable foreign key checks to avoid ordering issues during massive inserts
$pdo->exec("SET FOREIGN_KEY_CHECKS=0");

function getCategoryId($pdo, $name) {
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$name]);
    return $stmt->fetchColumn();
}

function getBrandId($pdo, $name) {
    $stmt = $pdo->prepare("SELECT id FROM brands WHERE name = ?");
    $stmt->execute([$name]);
    return $stmt->fetchColumn();
}

function createProduct($pdo, $data) {
    // 1. Base
    $stmt = $pdo->prepare("INSERT INTO product_base (seller_id, category_id, name, status, created_at) VALUES (1, ?, ?, 'published', NOW())");
    $stmt->execute([$data['cat_id'], $data['name']]);
    $pid = $pdo->lastInsertId();

    // 2. Price
    $stmt = $pdo->prepare("INSERT INTO product_prices (product_id, price, min_price, max_price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$pid, $data['price'], $data['price'] * 0.9, $data['price'] * 1.1]);

    // 3. Stock
    $pdo->prepare("INSERT INTO product_stock (product_id, quantity) VALUES (?, 50)")->execute([$pid]);

    // 4. Media
    $stmt = $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary) VALUES (?, ?, 1)");
    $stmt->execute([$pid, $data['image']]);

    // 5. Specs (Brand & Material)
    $stmt = $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, outer_material, gender) VALUES (?, ?, ?, ?)");
    $stmt->execute([$pid, $data['brand_id'], $data['material'], $data['gender']]);
    
    // 6. Colors (Default set)
    $colors = [['Black','#000'], ['White','#FFF']];
    if (isset($data['colors'])) $colors = $data['colors'];
    
    $stmtCol = $pdo->prepare("INSERT INTO product_colors (product_id, color_name, color_code) VALUES (?, ?, ?)");
    foreach($colors as $c) {
        $stmtCol->execute([$pid, $c[0], $c[1]]);
    }

    echo "✅ Added: {$data['name']} <br>";
}

try {
    echo "<h1>👟 Seeding Full Catalog...</h1><hr>";

    // IDs
    $catFormal = getCategoryId($pdo, 'Formal Shoes');
    $catCasual = getCategoryId($pdo, 'Casual Shoes');
    $catSneakers = getCategoryId($pdo, 'Sneakers');
    $catBoots = getCategoryId($pdo, 'Boots');
    $catSports = getCategoryId($pdo, 'Sports');

    $brandBata = getBrandId($pdo, 'Bata');
    $brandCrocs = getBrandId($pdo, 'Crocs');
    $brandConverse = getBrandId($pdo, 'Converse');
    $brandSkechers = getBrandId($pdo, 'Skechers');
    $brandFila = getBrandId($pdo, 'Fila');
    $brandUA = getBrandId($pdo, 'Under Armour');
    $brandAsian = getBrandId($pdo, 'Asian');
    $brandSparx = getBrandId($pdo, 'Sparx');

    $products = [
        // FORMAL
        [
            'name' => 'Bata Premium Oxford Black',
            'cat_id' => $catFormal,
            'brand_id' => $brandBata,
            'price' => 3499,
            'image' => 'https://images.unsplash.com/photo-1614252369475-531eba835eb1?w=800',
            'material' => 'Patent Leather', 
            'gender' => 'Men',
            'colors' => [['Black', '#000'], ['Brown', '#8B4513']]
        ],
        [
            'name' => 'Classic Leather Derby',
            'cat_id' => $catFormal,
            'brand_id' => $brandBata, // Generic fallback
            'price' => 2999,
            'image' => 'https://images.unsplash.com/photo-1478146896981-b80fe463b330?w=800',
            'material' => 'Full-Grain Leather',
            'gender' => 'Men'
        ],
        // CASUAL
        [
            'name' => 'Converse Chuck Taylor All Star',
            'cat_id' => $catCasual, // Or sneakers, but can fit casual
            'brand_id' => $brandConverse,
            'price' => 4999,
            'image' => 'https://images.unsplash.com/photo-1607522370275-f14206abe5d3?w=800',
            'material' => 'Canvas',
            'gender' => 'Unisex',
            'colors' => [['Classic Black', '#000'], ['Red', '#F00'], ['White', '#FFF']]
        ],
        [
            'name' => 'Crocs Classic Clog Navy',
            'cat_id' => $catCasual,
            'brand_id' => $brandCrocs,
            'price' => 2495,
            'image' => 'https://images.unsplash.com/photo-1603808033192-082d6919d3e1?w=800', // Placeholder for clog-like visual
            'material' => 'Croslite',
            'gender' => 'Unisex',
            'colors' => [['Navy', '#000080'], ['Green', '#008000']]
        ],
        [
            'name' => 'Skechers Go Walk Evolution',
            'cat_id' => $catCasual,
            'brand_id' => $brandSkechers,
            'price' => 5499,
            'image' => 'https://images.unsplash.com/photo-1512374382149-233c48b6303a?w=800',
            'material' => 'Mesh',
            'gender' => 'Women'
        ],
        // SPORTS / SNEAKERS
        [
            'name' => 'Fila Disruptor II Premium',
            'cat_id' => $catSneakers,
            'brand_id' => $brandFila,
            'price' => 6999,
            'image' => 'https://images.unsplash.com/photo-1555274175-6f9f43af4305?w=800', // Chunky sneaker
            'material' => 'Synthethic Leather',
            'gender' => 'Women',
            'colors' => [['White', '#FFF'], ['Pink', '#FFC0CB']]
        ],
        [
            'name' => 'Under Armour Curry Flow 9',
            'cat_id' => $catSports,
            'brand_id' => $brandUA,
            'price' => 13999,
            'image' => 'https://images.unsplash.com/photo-1518002171953-a080ee802e12?w=800',
            'material' => 'Warp Knit',
            'gender' => 'Men',
            'colors' => [['Blue', '#00F'], ['Yellow', '#FF0']]
        ],
        [
            'name' => 'Sparx SM-654 Running',
            'cat_id' => $catSports,
            'brand_id' => $brandSparx,
            'price' => 1299,
            'image' => 'https://images.unsplash.com/photo-1579338559194-a162d19bf842?w=800',
            'material' => 'Mesh',
            'gender' => 'Men',
            'colors' => [['Grey', '#808080'], ['Black', '#000']]
        ],
        [
            'name' => 'Asian Tarzan-11',
            'cat_id' => $catCasual,
            'brand_id' => $brandAsian,
            'price' => 899,
            'image' => 'https://images.unsplash.com/photo-1560769629-975e13f0c470?w=800',
            'material' => 'Canvas',
            'gender' => 'Men'
        ]
    ];

    foreach ($products as $p) {
        if ($p['cat_id'] && $p['brand_id']) {
            createProduct($pdo, $p);
        } else {
            echo "⚠️ Skipping {$p['name']} - Missing Cat/Brand ID<br>";
        }
    }

    echo "<hr><h3>✅ Catalog Seeded!</h3> <a href='../../shop.php'>Go to Shop</a>";

} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage());
}
?>
