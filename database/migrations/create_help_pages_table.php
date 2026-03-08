<?php
include '../../config.php';

// Check connection
if (!isset($pdo)) {
    die("Database connection failed. Variable \$pdo is not set in config.php");
}

// Create help_pages table using PDO
$sql = "CREATE TABLE IF NOT EXISTS help_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    icon_class VARCHAR(50) NOT NULL,
    summary TEXT,
    content LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

try {
    $pdo->exec($sql);
    echo "Table 'help_pages' created successfully (or already exists).\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>
