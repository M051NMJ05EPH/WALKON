<?php
include 'config.php';

try {
    echo "<h1>Updating Product Images...</h1>";

    // Image Mapping (Brand -> Array of Images)
    $brand_images = [
        'Nike' => [
            'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=600&auto=format&fit=crop', // Red Nike
            'https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?q=80&w=600&auto=format&fit=crop', // Nike Air
            'https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=600&auto=format&fit=crop'  // Blue Nike
        ],
        'adidas' => [
            'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?q=80&w=600&auto=format&fit=crop', // Ultraboost
            'https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=600&auto=format&fit=crop', // Superstar style
            'https://images.unsplash.com/photo-1518002171953-a080ee32bed3?q=80&w=600&auto=format&fit=crop'
        ],
        'PUMA' => [
            'https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=600&auto=format&fit=crop', // Reuse generic sneaker
            'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?q=80&w=600&auto=format&fit=crop'  // Green sneaker
        ],
        'New Balance' => [
            'https://images.unsplash.com/photo-1539185441755-769473a23570?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?q=80&w=600&auto=format&fit=crop'
        ],
        'Reebok' => [
            'https://images.unsplash.com/photo-1579338559194-a162d19bd842?q=80&w=600&auto=format&fit=crop'
        ],
        'Timberland' => [
            'https://images.unsplash.com/photo-1520639889313-72721e0ab9ef?q=80&w=600&auto=format&fit=crop' // Classic Boot
        ],
        'Dr. Martens' => [
           'https://images.unsplash.com/photo-1655998632622-c328f579997e?q=80&w=600&auto=format&fit=crop' // Boots
        ],
        'Converse' => [
            'https://images.unsplash.com/photo-1607522370275-f14206abe5d3?q=80&w=600&auto=format&fit=crop' // High tops
        ],
        'Vans' => [
            'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?q=80&w=600&auto=format&fit=crop' // Classic Vans
        ],
         'Crocs' => [
            'https://images.unsplash.com/photo-1603808033192-082d6919d3e1?q=80&w=600&auto=format&fit=crop' 
        ],
        'Generic' => [
             'https://images.unsplash.com/photo-1560769629-975e13f0c470?q=80&w=600&auto=format&fit=crop',
             'https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=600&auto=format&fit=crop'
        ]
    ];

    // Get all products joined with brand info
    $sql = "SELECT pb.id, pb.name, b.name as brand_name 
            FROM product_base pb 
            LEFT JOIN product_specs ps ON pb.id = ps.product_id
            LEFT JOIN brands b ON ps.brand_id = b.id";
    
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<p>Found " . count($products) . " products to update.</p>";

    foreach ($products as $p) {
        $brand = $p['brand_name'] ?? 'Generic';
        
        // Determine image to use
        if (isset($brand_images[$brand])) {
            // Pick a random image from the brand's specific array
            $url = $brand_images[$brand][array_rand($brand_images[$brand])];
        } else {
            // Fallback to generic
            $url = $brand_images['Generic'][array_rand($brand_images['Generic'])];
        }

        // Update product_media
        // Check if entry exists
        $check = $pdo->prepare("SELECT id FROM product_media WHERE product_id = ?");
        $check->execute([$p['id']]);
        
        if ($check->fetch()) {
            $upd = $pdo->prepare("UPDATE product_media SET url = ? WHERE product_id = ?");
            $upd->execute([$url, $p['id']]);
             echo "Updated ID {$p['id']} ($brand) with new image.<br>";
        } else {
            $ins = $pdo->prepare("INSERT INTO product_media (product_id, url, type, is_primary) VALUES (?, ?, 'image', 1)");
            $ins->execute([$p['id'], $url]);
             echo "Inserted ID {$p['id']} ($brand) with new image.<br>";
        }
    }

    echo "<h3>Image Update Complete!</h3>";
    echo "<a href='shop.php'>Go to Shop</a>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
