<?php
include 'config.php';

// The list of brands to KEEP
$allowed_brands = [
    'Nike',
    'adidas',
    'New Balance',
    'Skechers',
    'Under Armour',
    'Vans',
    'PUMA'
];

// Create placeholders for the allowed brands
$placeholders = implode(',', array_fill(0, count($allowed_brands), '?'));

try {
    // Delete brands NOT in the allowed list
    $sql = "DELETE FROM brands WHERE name NOT IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($allowed_brands);
    
    echo "Removed " . $stmt->rowCount() . " brands that were not in the allowed list.\n";
    
    // List remaining brands
    $stmt = $pdo->query("SELECT name FROM brands ORDER BY name");
    $remaining = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Remaining Brands:\n" . implode(", ", $remaining);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
