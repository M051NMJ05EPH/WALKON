<?php
include 'config.php';

try {
    echo "<h1>Fixing Product Details Data...</h1>";

    // 1. Create product_sizes if not exists
    $sql_sizes = "CREATE TABLE IF NOT EXISTS product_sizes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        size_value VARCHAR(50) NOT NULL,
        FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_sizes);
    echo "✅ product_sizes table created.<br>";

    // 2. Create product_colors if not exists
    $sql_colors = "CREATE TABLE IF NOT EXISTS product_colors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        color_name VARCHAR(50) NOT NULL,
        color_hex VARCHAR(20) DEFAULT '#000000',
        FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_colors);
    echo "✅ product_colors table created.<br>";

    // 3. Clear existing simple data to avoid duplicates (optional, but safer for seeding)
    $pdo->exec("TRUNCATE TABLE product_sizes");
    $pdo->exec("TRUNCATE TABLE product_colors");
    // Don't truncate media, we want to keep primary images, just add new ones.
    // Actually, to be safe against duplicates, let's delete NON-primary images first
    $pdo->exec("DELETE FROM product_media WHERE is_primary = 0");

    // 4. Seed Data based on Category
    $products = $pdo->query("SELECT id, category_id, name FROM product_base")->fetchAll();

    $extra_images_pool = [
        'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?q=80&w=600', // Green
        'https://images.unsplash.com/photo-1605034313761-73ea4a0cfbf3?q=80&w=600', // Timberland-ish
        'https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=600', // Jordan
        'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=600', // Blue
        'https://images.unsplash.com/photo-1584735175315-9d5df23860e6?q=80&w=600', // Red
        'https://images.unsplash.com/photo-1552346154-21d32810aba3?q=80&w=600'  // Sneakers
    ];

    foreach ($products as $p) {
        $pid = $p['id'];
        
        // SEED SIZES
        $sizes = ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10'];
        foreach ($sizes as $s) {
            $pdo->prepare("INSERT INTO product_sizes (product_id, size_value) VALUES (?, ?)")->execute([$pid, $s]);
        }

        // SEED COLORS
        $colors = [
            ['Red', '#ef4444'], 
            ['Black', '#0B0F19'], 
            ['White', '#ffffff']
        ];
        foreach ($colors as $c) {
            $pdo->prepare("INSERT INTO product_colors (product_id, color_name, color_hex) VALUES (?, ?, ?)")
                ->execute([$pid, $c[0], $c[1]]);
        }

        // SEED SECONDARY IMAGES (Add 2 random extra images)
        // Shuffle pool to get random ones
        shuffle($extra_images_pool);
        $seconds = array_slice($extra_images_pool, 0, 2);
        
        foreach ($seconds as $url) {
            $pdo->prepare("INSERT INTO product_media (product_id, url, type, is_primary) VALUES (?, ?, 'image', 0)")
                ->execute([$pid, $url]);
        }
    }

    echo "✅ Seeded sizes, colors, and secondary images for " . count($products) . " products.<br>";
    echo "<a href='download_images_extra.php'>Run Extra Image Downloader</a>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
