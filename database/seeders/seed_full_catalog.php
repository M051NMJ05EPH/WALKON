<?php
include 'config.php';

try {
    echo "<h1>Seeding Complete Catalog...</h1>";
    
    $seller = $pdo->query("SELECT id FROM sellers LIMIT 1")->fetch();
    $seller_id = $seller['id'] ?? 1;

    // Brand -> Products Map (Distinct Images for everyone)
    $catalog = [
        'Nike' => [
            ['name' => 'Nike Air Zoom Pegasus', 'price' => 9995, 'cat' => 'Running Shoes', 'img' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=600'],
            ['name' => 'Nike Dunk Low Retro', 'price' => 8295, 'cat' => 'Sneakers', 'img' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=600']
        ],
        'adidas' => [
            ['name' => 'Adidas Forum Low', 'price' => 9999, 'cat' => 'Sneakers', 'img' => 'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?q=80&w=600'],
            ['name' => 'Adidas NMD_R1', 'price' => 12999, 'cat' => 'Running Shoes', 'img' => 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=600']
        ],
        'PUMA' => [
            ['name' => 'Puma Suede Classic', 'price' => 6999, 'cat' => 'Sneakers', 'img' => 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=600'], 
            ['name' => 'Puma Velocity Nitro', 'price' => 10999, 'cat' => 'Running Shoes', 'img' => 'https://images.unsplash.com/photo-1579338559194-a162d19bd842?q=80&w=600'],
            ['name' => 'Puma Divecat v2', 'price' => 2999, 'cat' => 'Sandals & Slides', 'img' => 'https://images.unsplash.com/photo-1603808041750-51c880a5f0c4?q=80&w=600'] // Fixed Legacy
        ],
        'New Balance' => [
            ['name' => 'Granddad Shoe 530', 'price' => 8999, 'cat' => 'Sneakers', 'img' => 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?q=80&w=600'],
            ['name' => 'NB Fresh Foam', 'price' => 14999, 'cat' => 'Sports', 'img' => 'https://images.unsplash.com/photo-1539185441755-769473a23570?q=80&w=600']
        ],
        'Reebok' => [
            ['name' => 'Reebok Club C 85', 'price' => 5599, 'cat' => 'Sneakers', 'img' => 'https://images.unsplash.com/photo-1579338559194-a162d19bd842?q=80&w=600'],
            ['name' => 'Reebok Nano X3', 'price' => 11999, 'cat' => 'Sports', 'img' => 'https://images.unsplash.com/photo-1550523498-84223f0343a4?q=80&w=600']
        ],
        'Vans' => [
            ['name' => 'Vans Old Skool', 'price' => 5499, 'cat' => 'Sneakers', 'img' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?q=80&w=600'],
            ['name' => 'Vans Sk8-Hi', 'price' => 6499, 'cat' => 'Sneakers', 'img' => 'https://images.unsplash.com/photo-1607522370275-f14206abe5d3?q=80&w=600']
        ],
        'Converse' => [
            ['name' => 'Chuck 70 High Top', 'price' => 5999, 'cat' => 'Sneakers', 'img' => 'https://images.unsplash.com/photo-1607522370275-f14206abe5d3?q=80&w=600'],
            ['name' => 'Run Star Hike', 'price' => 7499, 'cat' => 'Boots', 'img' => 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=600']
        ],
        'Dr. Martens' => [
            ['name' => 'Jadon Platform Boot', 'price' => 18999, 'cat' => 'Boots', 'img' => 'https://images.unsplash.com/photo-1655998632622-c328f579997e?q=80&w=600'],
            ['name' => '1461 Smooth Leather', 'price' => 13999, 'cat' => 'Formal Shoes', 'img' => 'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?q=80&w=600']
        ],
        'Timberland' => [
            ['name' => 'Earthkeepers Boot', 'price' => 14000, 'cat' => 'Boots', 'img' => 'https://images.unsplash.com/photo-1520639889313-72721e0ab9ef?q=80&w=600'],
            ['name' => 'Timberland 6-Inch Premium', 'price' => 16000, 'cat' => 'Boots', 'img' => 'https://images.unsplash.com/photo-1605034313761-73ea4a0cfbf3?q=80&w=600'] // Fixed Legacy
        ],
        'Crocs' => [
            ['name' => 'Classic Clog White', 'price' => 2995, 'cat' => 'Sandals & Slides', 'img' => 'https://images.unsplash.com/photo-1545127398-5aae4d57c668?q=80&w=600'],
            ['name' => 'Crocs Classic Clog Navy', 'price' => 2995, 'cat' => 'Sandals & Slides', 'img' => 'https://images.unsplash.com/photo-1603808041750-51c880a5f0c4?q=80&w=600'] // Fixed Legacy
        ],
        'Clarks' => [
            ['name' => 'Desert Boot', 'price' => 9999, 'cat' => 'Boots', 'img' => 'https://images.unsplash.com/photo-1478146896981-b80fe4634763?q=80&w=600']
        ],
        'Skechers' => [
            ['name' => 'Go Walk 6', 'price' => 5499, 'cat' => 'Walking Shoes', 'img' => 'https://images.unsplash.com/photo-1560769629-975e13f0c470?q=80&w=600']
        ],
        'ASICS' => [
            ['name' => 'Novablast 3', 'price' => 13999, 'cat' => 'Running Shoes', 'img' => 'https://images.unsplash.com/photo-1550523498-84223f0343a4?q=80&w=600']
        ],
        'Under Armour' => [
            ['name' => 'UA Curry Flow', 'price' => 15999, 'cat' => 'Sports', 'img' => 'https://images.unsplash.com/photo-1560769629-975e13f0c470?q=80&w=600']
        ],
         'Bata' => [
            ['name' => 'Bata Comfit', 'price' => 2499, 'cat' => 'Formal Shoes', 'img' => 'https://images.unsplash.com/photo-1533867617858-e7b97e060509?q=80&w=600']
        ]
    ];

    foreach ($catalog as $brand_name => $items) {
        // 1. Get/Create Brand
        $stmt = $pdo->prepare("SELECT id FROM brands WHERE name = ?");
        $stmt->execute([$brand_name]);
        $brand_id = $stmt->fetchColumn();
        if (!$brand_id) {
            $pdo->prepare("INSERT INTO brands (name) VALUES (?)")->execute([$brand_name]);
            $brand_id = $pdo->lastInsertId();
        }

        foreach ($items as $p) {
            // 2. Get/Create Category (Generic match)
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name LIKE ?");
            $stmt->execute([$p['cat'] . '%']);
            $cat_id = $stmt->fetchColumn() ?: 1;

            // 3. Upsert Product
            $check = $pdo->prepare("SELECT id FROM product_base WHERE name = ?");
            $check->execute([$p['name']]);
            $existing = $check->fetch();

            if ($existing) {
                $pid = $existing['id'];
                // Update Image if changed
                $pdo->prepare("UPDATE product_media SET url = ? WHERE product_id = ?")->execute([$p['img'], $pid]);
                echo "Updated {$p['name']}<br>";
            } else {
                $ins = $pdo->prepare("INSERT INTO product_base (seller_id, category_id, name, status, created_at) VALUES (?, ?, ?, 'published', NOW())");
                $ins->execute([$seller_id, $cat_id, $p['name']]);
                $pid = $pdo->lastInsertId();
                echo "Created {$p['name']}<br>";

                // Price
                $pdo->prepare("INSERT INTO product_prices (product_id, price, max_price) VALUES (?, ?, ?)")->execute([$pid, $p['price'], $p['price']*1.2]);
                
                // Specs
                $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, gender) VALUES (?, ?, 'Unisex')")->execute([$pid, $brand_id]);

                // Image
                $pdo->prepare("INSERT INTO product_media (product_id, url, type, is_primary) VALUES (?, ?, 'image', 1)")->execute([$pid, $p['img']]);
            }
        }
    }
    
    echo "<h3>Full Catalog Seeded!</h3>";
    echo "<a href='download_images_rotator.php'>Run Rotating Downloader Next</a>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
