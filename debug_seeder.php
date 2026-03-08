<?php
include 'config.php';
header('Content-Type: text/plain');

try {
    echo "CHECKING MISSING PRODUCTS\n";
    $names = [
        'Nike Victori One Slide',
        'Adidas Adilette Comfort',
        'Clarks Tilden Cap Oxford'
    ];
    
    foreach ($names as $name) {
        $stmt = $pdo->prepare("SELECT pb.id, pb.name, pb.category_id, c.name as cat_name 
                               FROM product_base pb 
                               LEFT JOIN categories c ON pb.category_id = c.id 
                               WHERE pb.name = ?");
        $stmt->execute([$name]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($res) {
            echo "FOUND: '{$res['name']}' in Category ID {$res['category_id']} ({$res['cat_name']})\n";
        } else {
            echo "MISSING: '$name'\n";
        }
    }
    
    // Check categories again
    $stmt = $pdo->query("SELECT id, name FROM categories");
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nCATEGORIES AVAILABLE:\n";
    foreach($cats as $c) echo "ID {$c['id']}: {$c['name']}\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
