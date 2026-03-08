<?php
include 'config.php';

try {
    echo "<h1>🚀 Taxonomy Upgrade Initiated</h1><hr>";

    // 1. Add New Categories
    echo "<h3>1. Adding New Categories...</h3>";
    $newCategories = [
        ['Formal Shoes', 'c:/xampp/htdocs/WALKON-rough/assets/formal_shoes_category.png', 'Premium leather shoes for formal occasions.'], // Will update image path later if needed or rely on local
        ['Casual Shoes', 'c:/xampp/htdocs/WALKON-rough/assets/casual_shoes_category.png', 'Stylish and comfortable shoes for everyday wear.']
    ];

    // Check if image files exist, otherwise use placeholders or the ones generated
    // Note: I will use the generated image paths if available, or placeholders.
    // Correction: I should copy the generated images to assets first, but for now I'll use the names I plan to save them as.
    
    $stmCat = $pdo->prepare("INSERT IGNORE INTO categories (name, image_url, description) VALUES (?, ?, ?)");
    
    // We need to map category names to IDs for subcategories
    $catMap = []; 
    // Fetch all categories first to populate map
    $stmt = $pdo->query("SELECT id, name FROM categories");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $catMap[$row['name']] = $row['id'];
    }

    foreach ($newCategories as $nc) {
        if (!isset($catMap[$nc[0]])) {
            // Use the images we generated earlier (I will move them to assets folder in a separate step, here we reference the target path)
            // Ideally URL should be relative 'assets/...'
            $imgUrl = 'assets/' . strtolower(str_replace(' ', '_', $nc[0])) . '_category.png';
            $stmCat->execute([$nc[0], $imgUrl, $nc[2]]);
            $catMap[$nc[0]] = $pdo->lastInsertId();
            echo "✅ Added Category: {$nc[0]}<br>";
        } else {
            echo "ℹ️ Category {$nc[0]} already exists.<br>";
        }
    }

    // 2. Add New Brands
    echo "<h3>2. Adding New Brands...</h3>";
    $newBrands = [
        'Converse', 'Fila', 'Skechers', 'Under Armour', 'Crocs', 'Asian', 'Campus', 'Sparx'
    ];
    $stmBrand = $pdo->prepare("INSERT IGNORE INTO brands (name, logo_url) VALUES (?, ?)");
    
    foreach ($newBrands as $brand) {
        // Simple placeholder logo for now, or fetch from a reliable source if possible.
        // For efficiency, using a placeholder service text.
        $logo = "https://via.placeholder.com/100x50?text=" . urlencode($brand);
        $stmBrand->execute([$brand, $logo]);
        // Check if row was inserted
        if ($stmBrand->rowCount() > 0) {
            echo "✅ Added Brand: $brand<br>";
        } else {
             echo "ℹ️ Brand $brand already exists.<br>";
        }
    }

    // 3. Add Subcategories
    echo "<h3>3. Adding Subcategories...</h3>";
    $subStructure = [
        'Sneakers' => ['High-Top', 'Low-Top', 'Slip-On', 'Luxury', 'Canvas'],
        'Boots' => ['Chelsea', 'Combat', 'Hiking', 'Chukkas', 'Ankle Boots'],
        'Running Shoes' => ['Road Running', 'Trail Running', 'Performance', 'Cushioned'],
        'Sports' => ['Basketball', 'Tennis', 'Football', 'Training', 'Walking'],
        'Formal Shoes' => ['Oxfords', 'Derbys', 'Loafers', 'Brogues', 'Monk Straps'],
        'Casual Shoes' => ['Espadrilles', 'Boat Shoes', 'Mules', 'Sandals', 'Slides']
    ];

    $stmSub = $pdo->prepare("INSERT IGNORE INTO sub_categories (category_id, name) VALUES (?, ?)");

    foreach ($subStructure as $catName => $subs) {
        if (isset($catMap[$catName])) {
            $catId = $catMap[$catName];
            foreach ($subs as $subName) {
                $stmSub->execute([$catId, $subName]);
                if ($stmSub->rowCount() > 0) {
                    echo "  - Added $subName to $catName<br>";
                }
            }
        } else {
            echo "⚠️ Parent Category $catName not found, skipping subcategories.<br>";
        }
    }

    echo "<hr><h3>✅ Taxonomy Upgrade Complete!</h3>";
    echo "<a href='shop.php'>Go to Shop</a>";

} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage());
}
?>
