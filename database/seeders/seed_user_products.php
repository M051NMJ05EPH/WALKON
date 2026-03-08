<?php
include 'config.php';

$target_email = 'mosinmjoseph2028@mca.ajce.in';

try {
    $pdo->exec("USE `walkon_shoes_v2` ");
    echo "<h1>🌱 Custom Seeding for $target_email</h1>";

    // 1. Find Seller
    $stmt = $pdo->prepare("SELECT id, name FROM sellers WHERE email = ?");
    $stmt->execute([$target_email]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seller) {
        // Try finding in users table first
        $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE email = ?");
        $stmt->execute([$target_email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            die("❌ Error: User with email $target_email not found. Please register first.");
        }

        // Create seller record for this user
        $name = $user['first_name'] . ' ' . $user['last_name'];
        $stmt = $pdo->prepare("INSERT INTO sellers (user_id, name, email, is_active) VALUES (?, ?, ?, 1)");
        $stmt->execute([$user['id'], $name, $target_email]);
        $seller_id = $pdo->lastInsertId();
        echo "✅ Seller record created for $name.<br>";
    } else {
        $seller_id = $seller['id'];
        echo "✅ Seller record found (ID: $seller_id).<br>";
    }

    // 2. Sample Data for 10 Products
    $products = [
        ['Nike Air Max Elite', 'Premium running shoes with air cushion', 1, 1, 12999, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff'],
        ['Adidas Ultraboost Pro', 'Energy return for world-class runners', 1, 2, 18999, 'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb'],
        ['Puma RS-X Tech', 'Retro-future design with tech vibes', 1, 3, 9999, 'https://images.unsplash.com/photo-1608231387042-66d1773070a5'],
        ['Jordan 1 Mid Emerald', 'Classic Jordan 1 in emerald green', 1, 4, 15499, 'https://images.unsplash.com/photo-1549298916-b41d501d3772'],
        ['Reebok Nano X4', 'The ultimate cross-training shoe', 2, 5, 11999, 'https://images.unsplash.com/photo-1579338559194-a162d19bf8ff'],
        ['New Balance 550 Retro', 'Vintage basketball-inspired lifestyle', 1, 6, 13999, 'https://images.unsplash.com/photo-1539185441755-769473a23570'],
        ['Vans Old Skool Pro', 'Classic skate shoe with pro features', 1, 7, 5999, 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77'],
        ['Converse Chuck 70 High', 'The legendary high-top canvas sneaker', 1, 8, 7499, 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a'],
        ['Timberland 6-Inch Boot', 'Rugged waterproof durability', 3, 9, 21999, 'https://images.unsplash.com/photo-1520639889313-72721e0ab9ef'],
        ['Bata Comfit Walk', 'Comfort combined with professional style', 4, 10, 3999, 'https://images.unsplash.com/photo-1518002171953-a080ee817e1f']
    ];

    // 3. Insert 10 Products
    for ($i = 0; $i < 10; $i++) {
        $p = $products[$i];
        
        // Insert product_base
        $stmt = $pdo->prepare("INSERT INTO product_base (seller_id, category_id, name, status, created_at) VALUES (?, ?, ?, 'published', NOW())");
        $stmt->execute([$seller_id, $p[2], $p[0]]);
        $product_id = $pdo->lastInsertId();

        // Insert product_prices
        $stmt = $pdo->prepare("INSERT INTO product_prices (product_id, price, max_price) VALUES (?, ?, ?)");
        $stmt->execute([$product_id, $p[4], $p[4] + 2000]);

        // Insert product_media
        $stmt = $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary) VALUES (?, ?, 1)");
        $stmt->execute([$product_id, $p[5]]);

        echo "✅ Added Product " . ($i+1) . ": <strong>" . $p[0] . "</strong> (ID: $product_id)<br>";
    }

    echo "<h3>Finished! 10 products are now live for $target_email.</h3>";

} catch (PDOException $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
?>
