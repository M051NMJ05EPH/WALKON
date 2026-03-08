<?php
include 'config.php';

try {
    echo "<h1>Seeding Sample Products...</h1>";

    // 1. Get Seller ID
    $seller = $pdo->query("SELECT id FROM sellers LIMIT 1")->fetch();
    if (!$seller) {
        $pdo->exec("INSERT INTO sellers (name, email, password, business_name) VALUES ('WalkOn Official', 'admin@walkon.com', 'hashed_pass', 'WalkOn Inc.')");
        $sellerId = $pdo->lastInsertId();
    } else {
        $sellerId = $seller['id'];
    }

    // 2. Sample Products Data (Matching categories)
    $products = [
        [
            "name" => "Air Jordan 1 High OG", "category" => "Sneakers", "price" => 16999,
            "img" => "https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=500&q=80"
        ],
        [
            "name" => "Nike Air Max 90", "category" => "Sneakers", "price" => 11999,
            "img" => "https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=500&q=80"
        ],
        [
            "name" => "Timberland Premium 6-Inch", "category" => "Boots", "price" => 18999,
            "img" => "https://images.unsplash.com/photo-1520639889313-72721e0ab9ef?auto=format&fit=crop&w=500&q=80"
        ],
        [
            "name" => "Chelsea Boot Classic", "category" => "Boots", "price" => 12500,
            "img" => "https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=500&q=80"
        ],
        [
            "name" => "Adidas Ultraboost 22", "category" => "Running Shoes", "price" => 17999,
            "img" => "https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?auto=format&fit=crop&w=500&q=80"
        ],
        [
            "name" => "Nike Pegasus 40", "category" => "Running Shoes", "price" => 11499,
            "img" => "https://images.unsplash.com/photo-1595341888016-a392ef81b7de?auto=format&fit=crop&w=500&q=80"
        ],
        [
            "name" => "Puma Future Ultimate", "category" => "Sports", "price" => 8999,
            "img" => "https://images.unsplash.com/photo-1511886929837-354d827aae26?auto=format&fit=crop&w=500&q=80"
        ],
        [
            "name" => "Under Armour Curry 10", "category" => "Sports", "price" => 13999,
            "img" => "https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=500&q=80"
        ]
    ];

    foreach ($products as $p) {
        $cat = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $cat->execute([$p['category']]);
        $catId = $cat->fetchColumn();

        if ($catId) {
            // Check if product exists
            $check = $pdo->prepare("SELECT id FROM product_base WHERE name = ?");
            $check->execute([$p['name']]);
            
            if (!$check->fetch()) {
                // Insert Product Base
                $stmt = $pdo->prepare("INSERT INTO product_base (seller_id, category_id, name, status) VALUES (?, ?, ?, 'published')");
                $stmt->execute([$sellerId, $catId, $p['name']]);
                $prodId = $pdo->lastInsertId();

                // Insert Price
                $stmtPrice = $pdo->prepare("INSERT INTO product_prices (product_id, price) VALUES (?, ?)");
                $stmtPrice->execute([$prodId, $p['price']]);

                // Insert Media
                $stmtMedia = $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary) VALUES (?, ?, 1)");
                $stmtMedia->execute([$prodId, $p['img']]);

                echo "Inserted Product: {$p['name']}<br>";
            } else {
                echo "Product already exists: {$p['name']}<br>";
            }
        }
    }

    echo "<h3>Product Seeding Complete!</h3>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>Error: " . $e->getMessage() . "</h3>";
}
?>
