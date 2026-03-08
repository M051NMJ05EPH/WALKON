<?php
include __DIR__ . '/../../config.php';

echo "Updating user roles to include entrepreneur and customer...\n";

try {
    // Modify the role column to include new roles
    $sql = "ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'store_owner', 'entrepreneur', 'staff', 'customer') NOT NULL DEFAULT 'customer'";
    $pdo->exec($sql);
    echo "Successfully updated role column with new values.\n";

    // Verify the change
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nCurrent role column configuration:\n";
    print_r($col);
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
