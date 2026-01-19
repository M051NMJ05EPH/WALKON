<?php
include 'config.php';

$brands = [
    'adidas', 'ASICS', 'Bata', 'Clarks', 'Converse', 'Crocs', 
    'Dr. Martens', 'New Balance', 'Nike', 'PUMA', 'Reebok', 
    'Skechers', 'Sparx', 'Timberland', 'Under Armour', 'Vans'
];

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO brands (name) VALUES (?)");
    foreach ($brands as $brand) {
        $stmt->execute([$brand]);
        echo "Added/Verified brand: $brand\n";
    }
    
    $pdo->commit();
    echo "\nAll brands synced successfully.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
