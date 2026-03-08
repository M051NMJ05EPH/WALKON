<?php
require 'config.php';

// Disable foreign key checks for seeding
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

$brands_map = [
    "Asics" => 14,
    "Balenciaga" => 36,
    "Bata" => 8,
    "Birkenstock" => 32,
    "Brooks" => 20,
    "Clarks" => 31,
    "Converse" => 17,
    "Crocs" => 33,
    "Dr. Martens" => 29,
    "Fila" => 19,
    "Gucci" => 34,
    "Hoka One One" => 22,
    "Jordan" => 4,
    "Liberty" => 27,
    "Metro" => 28,
    "Mizuno" => 23,
    "New Balance" => 6
];

$products_data = [
    "Asics" => [
        ["name" => "Gel-Kayano 30", "price" => 160.00, "cat" => 4, "sub" => 16, "img" => "https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=1000&auto=format&fit=crop", "desc" => "Premium stability running shoes for maximum comfort and support."],
        ["name" => "Gel-Nimbus 25", "price" => 160.00, "cat" => 4, "sub" => 13, "img" => "https://images.unsplash.com/photo-1584735175315-9d5df23860e6?q=80&w=1000&auto=format&fit=crop", "desc" => "Highly cushioned road running shoe for plush comfort."]
    ],
    "Balenciaga" => [
        ["name" => "Triple S Sneaker", "price" => 1100.00, "cat" => 1, "sub" => 2, "img" => "https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?q=80&w=1000&auto=format&fit=crop", "desc" => "Iconic oversized sneaker defineing the dad-shoe trend."],
        ["name" => "Speed Trainer", "price" => 950.00, "cat" => 1, "sub" => 3, "img" => "https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?q=80&w=1000&auto=format&fit=crop", "desc" => "Sock-like knit sneaker with a technical sole."]
    ],
    "Bata" => [
        ["name" => "City Formal Derby", "price" => 45.00, "cat" => 5, "sub" => 18, "img" => "https://images.unsplash.com/photo-1533867617858-e7b97e060509?q=80&w=1000&auto=format&fit=crop", "desc" => "Classic leather derby shoes for everyday professional wear."],
        ["name" => "Comfort Walk Loafer", "price" => 35.00, "cat" => 5, "sub" => 19, "img" => "https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?q=80&w=1000&auto=format&fit=crop", "desc" => "Easy-to-wear slip-on loafers for ultimate comfort."]
    ],
    "Birkenstock" => [
        ["name" => "Arizona Soft Footbed", "price" => 140.00, "cat" => 6, "sub" => 21, "img" => "https://images.unsplash.com/photo-1603487788427-d31d4024220c?q=80&w=1000&auto=format&fit=crop", "desc" => "The classic two-strap sandal with legendary cork footbed."],
        ["name" => "Boston Clog", "price" => 155.00, "cat" => 6, "sub" => 22, "img" => "https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=1000&auto=format&fit=crop", "desc" => "Versatile clogs that can be worn for work or leisure."]
    ],
    "Brooks" => [
        ["name" => "Ghost 15", "price" => 140.00, "cat" => 4, "sub" => 13, "img" => "https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=1000&auto=format&fit=crop", "desc" => "Balanced cushioning and smooth transitions for neutral runners."],
        ["name" => "Adrenaline GTS 23", "price" => 140.00, "cat" => 4, "sub" => 16, "img" => "https://images.unsplash.com/photo-1560769629-975ec94e6a86?q=80&w=1000&auto=format&fit=crop", "desc" => "Stability focused running shoe with GuideRails support system."]
    ],
    "Clarks" => [
        ["name" => "Desert Boot", "price" => 150.00, "cat" => 2, "sub" => 7, "img" => "https://images.unsplash.com/photo-1520639889313-75198e705476?q=80&w=1000&auto=format&fit=crop", "desc" => "Original desert boots in premium suede."],
        ["name" => "Wallabee", "price" => 160.00, "cat" => 6, "sub" => 22, "img" => "https://images.unsplash.com/photo-1560769629-975ec94e6a86?q=80&w=1000&auto=format&fit=crop", "desc" => "Classic moccasin construction in durable leather."]
    ],
    "Converse" => [
        ["name" => "Chuck Taylor All Star Hi", "price" => 65.00, "cat" => 1, "sub" => 1, "img" => "https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=1000&auto=format&fit=crop", "desc" => "The timeless high-top canvas sneaker."],
        ["name" => "Chuck 70 Low", "price" => 80.00, "cat" => 1, "sub" => 2, "img" => "https://images.unsplash.com/photo-1460353581641-37baddab0fa2?q=80&w=1000&auto=format&fit=crop", "desc" => "Premium materials and vintage details for modern comfort."]
    ],
    "Crocs" => [
        ["name" => "Classic Clog", "price" => 50.00, "cat" => 6, "sub" => 21, "img" => "https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=1000&auto=format&fit=crop", "desc" => "Original foam clog for lightweight comfort and versatility."],
        ["name" => "LiteRide 360", "price" => 65.00, "cat" => 6, "sub" => 23, "img" => "https://images.unsplash.com/photo-1603487788427-d31d4024220c?q=80&w=1000&auto=format&fit=crop", "desc" => "Enhanced cushioning for active comfort."]
    ],
    "Dr. Martens" => [
        ["name" => "1460 8-Eye Boot", "price" => 170.00, "cat" => 2, "sub" => 6, "img" => "https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=1000&auto=format&fit=crop", "desc" => "The original Dr. Martens boot with air-cushioned sole."],
        ["name" => "1461 Smooth Shoe", "price" => 130.00, "cat" => 5, "sub" => 18, "img" => "https://images.unsplash.com/photo-1512374382149-4332c6c0211d?q=80&w=1000&auto=format&fit=crop", "desc" => "Classic 3-eye shoe in smooth leather."]
    ],
    "Fila" => [
        ["name" => "Disruptor II", "price" => 75.00, "cat" => 1, "sub" => 2, "img" => "https://images.unsplash.com/photo-1552346154-21d328109a27?q=80&w=1000&auto=format&fit=crop", "desc" => "Bold, chunky sneaker with sawtooth sole."],
        ["name" => "Ray Tracer", "price" => 80.00, "cat" => 1, "sub" => 3, "img" => "https://images.unsplash.com/photo-1628413993904-94ecb60f1239?q=80&w=1000&auto=format&fit=crop", "desc" => "Technical heritage sneaker with mixed materials."]
    ],
    "Gucci" => [
        ["name" => "Ace Sneaker", "price" => 720.00, "cat" => 1, "sub" => 2, "img" => "https://images.unsplash.com/photo-1562183241-b937e95585b6?q=80&w=1000&auto=format&fit=crop", "desc" => "Embroidered low-top sneaker in premium leather."],
        ["name" => "Horsebit Loafer", "price" => 920.00, "cat" => 5, "sub" => 19, "img" => "https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=1000&auto=format&fit=crop", "desc" => "Timeless luxury loafers with iconic horsebit detail."]
    ],
    "Hoka One One" => [
        ["name" => "Bondi 8", "price" => 165.00, "cat" => 4, "sub" => 13, "img" => "https://images.unsplash.com/photo-1584735175315-9d5df23860e6?q=80&w=1000&auto=format&fit=crop", "desc" => "Max cushioned road shoe for ultimate plush ride."],
        ["name" => "Speedgoat 5", "price" => 155.00, "cat" => 4, "sub" => 14, "img" => "https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=1000&auto=format&fit=crop", "desc" => "Top-tier trail running shoe for technical terrain."]
    ],
    "Jordan" => [
        ["name" => "Air Jordan 1 Retro High", "price" => 180.00, "cat" => 1, "sub" => 1, "img" => "https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?q=80&w=1000&auto=format&fit=crop", "desc" => "The sneaker that started it all in its original high-top form."],
        ["name" => "Air Jordan 4 OG", "price" => 210.00, "cat" => 1, "sub" => 1, "img" => "https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?q=80&w=1000&auto=format&fit=crop", "desc" => "Classic silhouette with flight cushioning and support."]
    ],
    "Liberty" => [
        ["name" => "Aha Sneakers", "price" => 30.00, "cat" => 1, "sub" => 2, "img" => "https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?q=80&w=1000&auto=format&fit=crop", "desc" => "Affordable and stylish sneakers for every day."],
        ["name" => "Force 10 Sports", "price" => 40.00, "cat" => 3, "sub" => 12, "img" => "https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=1000&auto=format&fit=crop", "desc" => "Durable sports shoes for active lifestyle."]
    ],
    "Metro" => [
        ["name" => "Metro Formal Oxford", "price" => 55.00, "cat" => 5, "sub" => 17, "img" => "https://images.unsplash.com/photo-1449247709967-d4461a6a6103?q=80&w=1000&auto=format&fit=crop", "desc" => "Sleek leather oxfords for formal occasions."],
        ["name" => "Casual Loafer XL", "price" => 45.00, "cat" => 6, "sub" => 24, "img" => "https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?q=80&w=1000&auto=format&fit=crop", "desc" => "Comfortable loafers for casual weekend outings."]
    ],
    "Mizuno" => [
        ["name" => "Wave Rider 27", "price" => 140.00, "cat" => 4, "sub" => 13, "img" => "https://images.unsplash.com/photo-1584735175315-9d5df23860e6?q=80&w=1000&auto=format&fit=crop", "desc" => "Dynamic running shoe with signature Wave Plate technology."],
        ["name" => "Wave Inspire 19", "price" => 140.00, "cat" => 4, "sub" => 16, "img" => "https://images.unsplash.com/photo-1539185441755-769473a23570?q=80&w=1000&auto=format&fit=crop", "desc" => "Supportive running shoe for overpronation control."]
    ],
    "New Balance" => [
        ["name" => "Fresh Foam 1080v12", "price" => 160.00, "cat" => 4, "sub" => 13, "img" => "https://images.unsplash.com/photo-1539185441755-769473a23570?q=80&w=1000&auto=format&fit=crop", "desc" => "Premium cushioning for a smooth and comfortable run."],
        ["name" => "990v6 Core", "price" => 200.00, "cat" => 1, "sub" => 2, "img" => "https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?q=80&w=1000&auto=format&fit=crop", "desc" => "Classic American-made sneaker with unmatched support."]
    ]
];

$seller_id = 1;

try {
    foreach ($products_data as $brand_name => $products) {
        $brand_id = $brands_map[$brand_name];
        echo "Processing brand: $brand_name (ID: $brand_id)\n";
        
        foreach ($products as $p) {
            // 1. Insert into product_base
            $stmt = $pdo->prepare("INSERT INTO product_base (seller_id, category_id, sub_category_id, name, status) VALUES (?, ?, ?, ?, 'published')");
            $stmt->execute([$seller_id, $p['cat'], $p['sub'], $p['name']]);
            $product_id = $pdo->lastInsertId();
            
            // 2. Insert into product_prices
            $stmt = $pdo->prepare("INSERT INTO product_prices (product_id, price, min_price, max_price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$product_id, $p['price'], $p['price'] * 0.9, $p['price'] * 1.5]);
            
            // 3. Insert into product_skus
            $sku = strtoupper(substr($brand_name, 0, 2)) . "-" . strtoupper(substr($p['name'], 0, 3)) . "-" . rand(1000, 9999);
            $stmt = $pdo->prepare("INSERT INTO product_skus (product_id, sku) VALUES (?, ?)");
            $stmt->execute([$product_id, $sku]);
            
            // 4. Insert into product_media
            $stmt = $pdo->prepare("INSERT INTO product_media (product_id, url, type, is_primary) VALUES (?, ?, 'image', 1)");
            $stmt->execute([$product_id, $p['img']]);
            
            // 5. Insert into product_specs
            $stmt = $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, gender, shoe_type) VALUES (?, ?, 'Unisex', 'Footwear')");
            $stmt->execute([$product_id, $brand_id]);
            
            // 6. Insert into product_descriptions
            $stmt = $pdo->prepare("INSERT INTO product_descriptions (product_id, content) VALUES (?, ?)");
            $stmt->execute([$product_id, $p['desc']]);
            
            // 7. Insert into product_stock
            $stmt = $pdo->prepare("INSERT INTO product_stock (product_id, quantity) VALUES (?, ?)");
            $stmt->execute([$product_id, rand(50, 200)]);
            
            // 8. Insert into product_channels
            $channels = ['WALKON', 'Direct'];
            foreach ($channels as $chan) {
                $stmt = $pdo->prepare("INSERT INTO product_channels (product_id, channel_name) VALUES (?, ?)");
                $stmt->execute([$product_id, $chan]);
            }
            
            echo " - Added product: {$p['name']} (ID: $product_id, SKU: $sku)\n";
        }
    }
    
    echo "\nSeeding completed successfully!\n";
} catch (Exception $e) {
    echo "Error during seeding: " . $e->getMessage() . "\n";
} finally {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
}
