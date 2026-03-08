<?php
include __DIR__ . '/../../config.php';

echo "Modifying 'role' column in users table...\n";

try {
    // Modify the column to the new ENUM definition
    // We add 'user' to the list temporarily if needed to map old values, 
    // but the requirement implies strict new roles. 
    // Let's first map any existing 'user' roles to 'store_owner' effectively by updating the ENUM.
    // However, direct modification might fail if data differs.
    // Safe approach: Change to text, update values, then change to new ENUM.
    
    // 1. Change to VARCHAR to be safe
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role VARCHAR(50)");
    
    // 2. Update existing values
    // Map 'user' -> 'store_owner'
    // Map 'admin' -> 'admin' (stays same)
    $pdo->exec("UPDATE users SET role = 'store_owner' WHERE role = 'user' OR role IS NULL OR role = ''");
    
    // 3. Apply new ENUM schema
    $sql = "ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'store_owner', 'inventory_manager', 'staff') NOT NULL DEFAULT 'store_owner'";
    $pdo->exec($sql);
    
    echo "Successfully modified 'role' column schema.\n";

    // Verify
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($col);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
