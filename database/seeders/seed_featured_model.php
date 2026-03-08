<?php
include 'config.php';

try {
    echo "<h1>Seeding Featured Products Model...</h1>";

    // Specific products from the screenshot
    $products = [
        [
            'brand' => 'Nike',
            'name' => 'Nike Air Max 270',
            'price' => 11995,
            'category' => 'Sports',
            'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=600&auto=format&fit=crop' // Red/White Nike
        ],
        [
            'brand' => 'Bata',
            'name' => 'Bata Premium Derby',
            'price' => 4999,
            'category' => 'Formal Shoes',
            'image' => 'https://images.unsplash.com/photo-1614252369475-531eba835eb1?q=80&w=600&auto=format&fit=crop' // Brown Formal
        ],
        [
            'brand' => 'ASICS',
            'name' => 'ASICS Gel-Kayano 29',
            'price' => 14000,
            'category' => 'Sports',
            'image' => 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?q=80&w=600&auto=format&fit=crop' // Green Running Shoe
        ],
        [
            'brand' => 'New Balance',
            'name' => 'New Balance 990v6',
            'price' => 19999,
            'category' => 'Sneakers',
            'image' => 'https://images.unsplash.com/photo-1539185441755-769473a23570?q=80&w=600&auto=format&fit=crop' // Black/Grey NB
        ],
        [
            'brand' => 'Nike',
            'name' => 'Nike Air Jordan 1 High',
            'price' => 17995,
            'category' => 'Sneakers',
            'image' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=600&auto=format&fit=crop' // Jordan 1
        ],
        [
            'brand' => 'Dr. Martens',
            'name' => 'Dr. Martens 1460 Smooth',
            'price' => 15999,
            'category' => 'Boots',
            'image' => 'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?q=80&w=600&auto=format&fit=crop' // Doc Martens
        ]
    ];

    // Get Seller ID
    $seller = $pdo->query("SELECT id FROM sellers LIMIT 1")->fetch();
    $seller_id = $seller['id'] ?? 1;

    foreach ($products as $p) {
        $brand_name = $p['brand'];
        
        // 1. Get/Create Brand
        $stmt = $pdo->prepare("SELECT id FROM brands WHERE name = ?");
        $stmt->execute([$brand_name]);
        $brand = $stmt->fetch();
        if (!$brand) {
            $pdo->prepare("INSERT INTO brands (name) VALUES (?)")->execute([$brand_name]);
            $brand_id = $pdo->lastInsertId();
        } else {
            $brand_id = $brand['id'];
        }

        // 2. Get Category
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name LIKE ?");
        $stmt->execute([$p['category'] . '%']); // Match "Sports" or "Sports Shoes"
        $cat = $stmt->fetch();
        $cat_id = $cat['id'] ?? 1; // Fallback

        // 3. Insert/Update Product
        $check = $pdo->prepare("SELECT id FROM product_base WHERE name = ?");
        $check->execute([$p['name']]);
        $existing = $check->fetch();

        if ($existing) {
            $pid = $existing['id'];
            // Update ensure it's published and recent
            $pdo->prepare("UPDATE product_base SET status='published', created_at=NOW() WHERE id=?")->execute([$pid]);
            echo "Updated {$p['name']}<br>";
        } else {
            $ins = $pdo->prepare("INSERT INTO product_base (seller_id, category_id, name, status, created_at) VALUES (?, ?, ?, 'published', NOW())");
            $ins->execute([$seller_id, $cat_id, $p['name']]);
            $pid = $pdo->lastInsertId();
            echo "Created {$p['name']}<br>";
        }

        // 4. Update Price
        $pdo->prepare("DELETE FROM product_prices WHERE product_id=?")->execute([$pid]);
        $pdo->prepare("INSERT INTO product_prices (product_id, price, max_price) VALUES (?, ?, ?)")->execute([$pid, $p['price'], $p['price'] + 2000]);

        // 5. Update Spec (Brand Link)
        $pdo->prepare("DELETE FROM product_specs WHERE product_id=?")->execute([$pid]);
        $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, gender, outer_material) VALUES (?, ?, 'Unisex', 'Premium Leather')")->execute([$pid, $brand_id]);

        // 6. Update Image
        $pdo->prepare("DELETE FROM product_media WHERE product_id=?")->execute([$pid]);
        $pdo->prepare("INSERT INTO product_media (product_id, url, type, is_primary) VALUES (?, ?, 'image', 1)")->execute([$pid, $p['image']]);
    }

    echo "<h3>Featured Products Seeded!</h3>";
    echo "<a href='index.php'>Go to Home</a>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
