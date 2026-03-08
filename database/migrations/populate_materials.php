<?php
include 'config.php';

try {
    echo "<h1>🧵 Populating Product Materials</h1><hr>";

    // Fetch all products with their categories
    $sql = "SELECT pb.id, pb.name, c.name as category_name 
            FROM product_base pb 
            LEFT JOIN categories c ON pb.category_id = c.id";
    $products = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $stmtSpec = $pdo->prepare("UPDATE product_specs SET outer_material = ? WHERE product_id = ?");

    foreach ($products as $p) {
        $material = 'Synthetic'; // Default
        $name = strtolower($p['name']);
        $cat = strtolower($p['category_name'] ?? '');

        // Logic for Material Assignment
        if ($cat === 'formal shoes') {
            $material = 'Leather';
            if (strpos($name, 'suede') !== false) $material = 'Suede';
        } 
        elseif ($cat === 'boots') {
            $material = 'Leather';
            if (strpos($name, 'hiking') !== false) $material = 'Waterproof Mesh';
            if (strpos($name, 'timberland') !== false) $material = 'Nubuck Leather';
        } 
        elseif ($cat === 'casual shoes') {
            $material = 'Canvas';
            if (strpos($name, 'loafer') !== false) $material = 'Suede';
            if (strpos($name, 'mule') !== false) $material = 'Leather';
            if (strpos($name, 'crocs') !== false) $material = 'Croslite';
        }
        elseif ($cat === 'running shoes') {
            $material = 'Mesh';
            if (strpos($name, 'knit') !== false) $material = 'Primeknit';
        }
        elseif ($cat === 'sneakers') {
            $material = 'Leather'; // Commmon for Jordans, AF1
            if (strpos($name, 'yeezy') !== false) $material = 'Primeknit';
            if (strpos($name, 'canvas') !== false || strpos($name, 'converse') !== false || strpos($name, 'vans') !== false) $material = 'Canvas';
            if (strpos($name, 'mesh') !== false) $material = 'Mesh';
        }
        elseif ($cat === 'sports') {
             $material = 'Synthetic';
             if (strpos($name, 'leather') !== false) $material = 'Leather';
        }

        // Specific overrides based on brand keywords if needed
        if (strpos($name, 'crocs') !== false) $material = 'Croslite';

        // Update
        $stmtSpec->execute([$material, $p['id']]);
        echo "Product ID {$p['id']} ({$p['name']}) -> <strong>$material</strong><br>";
    }

    echo "<hr><h3>✅ Materials Populated Successfully!</h3>";
    echo "<a href='../../shop.php'>Go to Shop</a>";

} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage());
}
?>
