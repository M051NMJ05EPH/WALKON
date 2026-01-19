<?php
include 'config.php';

$brands_to_add = [
    'Nike',
    'adidas', // Lowercase as per image
    'New Balance',
    'Skechers',
    'Under Armour',
    'Vans',
    'PUMA' // Uppercase as per image
];

try {
    foreach ($brands_to_add as $name) {
        // Check if exists (case-insensitive check for duplication prevention, but we want to update casing)
        $stmt = $pdo->prepare("SELECT id, name FROM brands WHERE name = ?");
        $stmt->execute([$name]);
        $existing = $stmt->fetch();

        if ($existing) {
            // If exists but casing is different (e.g. 'Adidas' vs 'adidas'), update it
            if ($existing['name'] !== $name) {
                $upd = $pdo->prepare("UPDATE brands SET name = ? WHERE id = ?");
                $upd->execute([$name, $existing['id']]);
                echo "Updated casing for: $name\n";
            } else {
                echo "Already exists: $name\n";
            }
        } else {
            // Check case-insensitive existence (e.g. 'adidas' vs 'Adidas')
            // If 'Adidas' exists, we want to update it to 'adidas'.
            // MySQL VARCHAR default collation is usually case-insensitive, so SELECT ... WHERE name = 'adidas' might find 'Adidas'.
            
            // Let's try to find it blindly first
             $stmt2 = $pdo->prepare("SELECT id, name FROM brands WHERE name LIKE ?");
             $stmt2->execute([$name]);
             $existing2 = $stmt2->fetch();
             
             if ($existing2) {
                 // Found a match (likely case-insensitive), update validation
                 if ($existing2['name'] !== $name) {
                     $upd = $pdo->prepare("UPDATE brands SET name = ? WHERE id = ?");
                     $upd->execute([$name, $existing2['id']]);
                     echo "Updated casing for: $name (was {$existing2['name']})\n";
                 } else {
                     echo "Already exists: $name\n";
                 }
             } else {
                // Really doesn't exist
                $ins = $pdo->prepare("INSERT INTO brands (name) VALUES (?)");
                $ins->execute([$name]);
                echo "Added: $name\n";
             }
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
