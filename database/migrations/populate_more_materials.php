<?php
include 'config.php';

try {
    echo "<h1>🧵 Populating Extended Product Materials</h1><hr>";

    // Fetch all products
    $sql = "SELECT pb.id, pb.name, c.name as category_name 
            FROM product_base pb 
            LEFT JOIN categories c ON pb.category_id = c.id";
    $products = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $stmtSpec = $pdo->prepare("UPDATE product_specs SET outer_material = ? WHERE product_id = ?");

    // available materials for random distribution within categories to ensure variety
    $casualMaterials = ['Canvas', 'Denim', 'Hemp', 'Suede', 'Corduroy'];
    $formalMaterials = ['Full-Grain Leather', 'Patent Leather', 'Suede', 'Velvet', 'Calfskin'];
    $sportMaterials = ['Flyknit', 'Mesh', 'Nylon', 'Synthetic', 'Neoprene'];
    $bootMaterials = ['Gore-Tex', 'Nubuck', 'Rubber', 'Full-Grain Leather'];

    foreach ($products as $p) {
        $material = 'Synthetic'; 
        $name = strtolower($p['name']);
        $cat = strtolower($p['category_name'] ?? '');

        // --- Logic with Random Variety ---
        
        if ($cat === 'formal shoes') {
            // Randomly assign premium materials if no specific keyword
            $material = $formalMaterials[array_rand($formalMaterials)];
            if (strpos($name, 'suede') !== false) $material = 'Suede';
            if (strpos($name, 'patent') !== false) $material = 'Patent Leather';
        } 
        elseif ($cat === 'boots') {
            $material = $bootMaterials[array_rand($bootMaterials)];
            if (strpos($name, 'timberland') !== false) $material = 'Nubuck';
            if (strpos($name, 'hiking') !== false) $material = 'Gore-Tex';
        } 
        elseif ($cat === 'casual shoes') {
            $material = $casualMaterials[array_rand($casualMaterials)];
            if (strpos($name, 'crocs') !== false) $material = 'Croslite';
        }
        elseif ($cat === 'running shoes' || $cat === 'sports') {
            $material = $sportMaterials[array_rand($sportMaterials)];
            if (strpos($name, 'knit') !== false) $material = 'Flyknit';
            if (strpos($name, 'air') !== false) $material = 'Mesh';
        }
        elseif ($cat === 'sneakers') {
            // Mix of leather and canvas
            $material = (rand(0,1) == 0) ? 'Leather' : 'Canvas';
            if (strpos($name, 'yeezy') !== false) $material = 'Primeknit';
            if (strpos($name, 'jordan') !== false) $material = 'Full-Grain Leather';
            if (strpos($name, 'suede') !== false) $material = 'Suede';
        }

        // Update
        $stmtSpec->execute([$material, $p['id']]);
        echo "Product ID {$p['id']} ({$p['name']}) -> <strong>$material</strong><br>";
    }

    echo "<hr><h3>✅ Extended Materials Populated!</h3>";
    echo "<a href='../../shop.php'>Go to Shop</a>";

} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage());
}
?>
