<?php
include 'config.php';
try {
    // Check if seller 1 exists
    $stmt = $pdo->prepare("SELECT id FROM sellers WHERE id = 1");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $pdo->exec("INSERT INTO sellers (id, name, email, password, business_name, phone, is_active) 
                   VALUES (1, 'WalkOn Admin', 'official@walkon.com', 'password123', 'WalkOn Official', '1234567890', 1)");
        echo "Default seller created.\n";
    } else {
        echo "Default seller already exists.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
