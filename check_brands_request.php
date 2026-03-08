<?php
include 'config.php';
$brands_to_check = ['adidas', 'ASICS', 'Bata', 'Clarks', 'Converse', 'Crocs', 'Dr. Martens', 'New Balance', 'Nike'];
echo "Checking brands:\n";
foreach ($brands_to_check as $brand_name) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM brands WHERE name LIKE ?");
        $stmt->execute(['%' . $brand_name . '%']);
        $brand = $stmt->fetch();
        if ($brand) {
            echo "Found " . $brand_name . " (ID: " . $brand['id'] . ")\n";
            // Check products
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_base WHERE brand_id = ?");
            $stmt->execute([$brand['id']]);
            $count = $stmt->fetchColumn();
            echo "  Products: " . $count . "\n";
        } else {
            echo "Not found: " . $brand_name . "\n";
        }
    } catch (Exception $e) {
        echo "Error checking " . $brand_name . ": " . $e->getMessage() . "\n";
    }
}
?>
