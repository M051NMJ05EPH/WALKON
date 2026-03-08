<?php
include 'config.php';

try {
    echo "<h1>Seeding Missing Categories...</h1>";

    $seller = $pdo->query("SELECT id FROM sellers LIMIT 1")->fetch();
    $seller_id = $seller['id'] ?? 1;

    $products = [
        // Sandals & Slides (Currently 0)
        [
            'name' => 'Nike Victori One Slide',
            'category' => 'Sandals & Slides',
            'brand' => 'Nike',
            'price' => 1595,
            'image' => 'https://images.unsplash.com/photo-1545127398-5aae4d57c668?q=80&w=600&auto=format&fit=crop'
        ],
        [
            'name' => 'Adidas Adilette Comfort',
            'category' => 'Sandals & Slides',
            'brand' => 'adidas',
            'price' => 2499,
            'image' => 'https://images.unsplash.com/photo-1603808033192-082d6919d3e1?q=80&w=600&auto=format&fit=crop'
        ],
        [
            'name' => 'Crocs Classic Clog Navy',
            'category' => 'Sandals & Slides',
            'brand' => 'Crocs',
            'price' => 2995,
            'image' => 'https://images.unsplash.com/photo-1545127398-5aae4d57c668?q=80&w=600&auto=format&fit=crop'
        ],
         [
            'name' => 'Puma Divecat v2',
            'category' => 'Sandals & Slides',
            'brand' => 'PUMA',
            'price' => 1299,
            'image' => 'https://images.unsplash.com/photo-1621251347629-d5c22e4d284f?q=80&w=600&auto=format&fit=crop'
        ],
        
        // Formal Shoes (Currently 1)
        [
            'name' => 'Clarks Tilden Cap Oxford',
            'category' => 'Formal Shoes',
            'brand' => 'Clarks',
            'price' => 4999,
            'image' => 'https://images.unsplash.com/photo-1478146896981-b80fe4634763?q=80&w=600&auto=format&fit=crop'
        ],
         [
            'name' => 'Hush Puppies Leather Slip-On',
            'category' => 'Formal Shoes',
            'brand' => 'Bata',
            'price' => 5999,
            'image' => 'https://images.unsplash.com/photo-1533867617858-e7b97e060509?q=80&w=600&auto=format&fit=crop'
        ],
         [
            'name' => 'Red Tape Monk Strap',
            'category' => 'Formal Shoes',
            'brand' => 'Red Tape',
            'price' => 3495,
            'image' => 'https://images.unsplash.com/photo-1550523498-84223f0343a4?q=80&w=600&auto=format&fit=crop'
        ],
        
        // Boots (Currently 3)
         [
            'name' => 'Woodland Camel Outdoor',
            'category' => 'Boots',
            'brand' => 'Woodland',
            'price' => 4295,
            'image' => 'https://images.unsplash.com/photo-1520639889313-72721e0ab9ef?q=80&w=600&auto=format&fit=crop'
        ],
        [
            'name' => 'Timberland 6-Inch Premium',
            'category' => 'Boots',
            'brand' => 'Timberland',
            'price' => 16000,
            'image' => 'https://images.unsplash.com/photo-1576203955615-592f802d2aa8?q=80&w=600&auto=format&fit=crop'
        ]
    ];

    foreach ($products as $p) {
        $brand_name = $p['brand'];
        
        // Get/Create Brand (Simplified)
        $brand_stmt = $pdo->prepare("SELECT id FROM brands WHERE name=?");
        $brand_stmt->execute([$brand_name]);
        $brand_id = $brand_stmt->fetchColumn();
        if (!$brand_id) {
            $pdo->prepare("INSERT INTO brands (name) VALUES (?)")->execute([$brand_name]);
            $brand_id = $pdo->lastInsertId();
        }

        // Get Category
        $cat_stmt = $pdo->prepare("SELECT id FROM categories WHERE name LIKE ?");
        $cat_stmt->execute([$p['category'] . '%']);
        $cat_id = $cat_stmt->fetchColumn() ?: 1;

        // Check Loop
        $check = $pdo->prepare("SELECT id FROM product_base WHERE name = ?");
        $check->execute([$p['name']]);
        if ($check->fetch()) continue;

        // Insert
        $ins = $pdo->prepare("INSERT INTO product_base (seller_id, category_id, name, status, created_at) VALUES (?, ?, ?, 'published', NOW())");
        $ins->execute([$seller_id, $cat_id, $p['name']]);
        $pid = $pdo->lastInsertId();

        // Price
        $pdo->prepare("INSERT INTO product_prices (product_id, price, max_price) VALUES (?, ?, ?)")->execute([$pid, $p['price'], $p['price']*1.2]);
        
        // Specs
        $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, gender, outer_material) VALUES (?, ?, 'Men', 'Leather')")->execute([$pid, $brand_id]);

        // Image
        $pdo->prepare("INSERT INTO product_media (product_id, url, type, is_primary) VALUES (?, ?, 'image', 1)")->execute([$pid, $p['image']]);
        
        echo "Added: {$p['name']}<br>";
    }

    echo "<h3>Balancing Complete!</h3>";
    echo "<a href='download_images_locally.php'>Run Image Downloader Next</a>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
