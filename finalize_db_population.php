<?php
include 'config.php';

try {
    echo "<h1>Finalizing Database Population...</h1>";

    // 1. Add Elite Footwear Brands
    $brands = [
        ['Nike', 'https://upload.wikimedia.org/wikipedia/commons/a/a6/Logo_NIKE.svg'],
        ['Adidas', 'https://upload.wikimedia.org/wikipedia/commons/2/20/Adidas_Logo.svg'],
        ['Puma', 'https://upload.wikimedia.org/wikipedia/commons/8/88/Puma_Logo.svg'],
        ['Reebok', 'https://upload.wikimedia.org/wikipedia/commons/5/5f/Reebok_Logo.svg'],
        ['Skechers', ''],
        ['New Balance', ''],
        ['Asics', ''],
        ['Converse', ''],
        ['Vans', ''],
        ['Fila', ''],
        ['Bata', ''],
        ['Red Tape', ''],
        ['Sparx', ''],
        ['Crocs', ''],
        ['Birkenstock', ''],
        ['Timberland', ''],
        ['Gucci', ''],
        ['Prada', ''],
        ['Balenciaga', ''],
        ['Yeezy', '']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO brands (name, logo_url) VALUES (?, ?)");
    foreach ($brands as $b) {
        $stmt->execute($b);
    }
    echo "✅ Elite brands synced.<br>";

    // 2. Add Common Materials
    $pdo->exec("CREATE TABLE IF NOT EXISTS materials (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL UNIQUE)");
    $materials = ['Full Grain Leather', 'Nappa Leather', 'Suede', 'Nubuck', 'Patent Leather', 'Canvas', 'Mesh', 'Flyknit', 'Primeknit', 'Synthetic', 'Neoprene', 'Velvet', 'Satin', 'Denim', 'Recycled Polyester', 'Vegan Leather', 'Gore-Tex'];
    $stmt = $pdo->prepare("INSERT IGNORE INTO materials (name) VALUES (?)");
    foreach ($materials as $m) $stmt->execute([$m]);
    echo "✅ Premium materials added.<br>";

    // 3. Add Key Footwear Sizes
    $pdo->exec("CREATE TABLE IF NOT EXISTS sizes_ref (id INT AUTO_INCREMENT PRIMARY KEY, size_value VARCHAR(10) NOT NULL UNIQUE)");
    $sizes = ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11', 'UK 12', 'US 7', 'US 8', 'US 9', 'US 10', 'US 11', 'US 12', 'EU 39', 'EU 40', 'EU 41', 'EU 42', 'EU 43', 'EU 44', 'EU 45'];
    $stmt = $pdo->prepare("INSERT IGNORE INTO sizes_ref (size_value) VALUES (?)");
    foreach ($sizes as $s) $stmt->execute([$s]);
    echo "✅ Standard size reference table populated.<br>";

    echo "<h3>Database is now fully optimized for Elite Footwear Commerce.</h3>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>Error: " . $e->getMessage() . "</h3>";
}
?>
