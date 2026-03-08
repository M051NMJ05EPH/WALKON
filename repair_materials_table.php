<?php
include 'config.php';

try {
    // 1. Create table
    $sql = "CREATE TABLE IF NOT EXISTS materials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "Materials table created successfully.<br>";

    // 2. Seed data
    $defaults = [
        'Leather', 'Canvas', 'Mesh', 'Suede', 'Synthetic', 
        'Rubber', 'Foam', 'Knit', 'Nylon', 'Velvet'
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO materials (name) VALUES (?)");
    
    foreach ($defaults as $mat) {
        $stmt->execute([$mat]);
    }

    echo "Materials seeded successfully.<br>";
    echo "Done.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
