<?php
include 'config.php';

try {
    // Add gender column if it doesn't exist
    $pdo->exec("ALTER TABLE product_specs ADD COLUMN gender VARCHAR(50) AFTER brand_id");
    echo "Added 'gender' column to product_specs table.\n";
} catch (PDOException $e) {
    // Ignore error if column already exists (SQLSTATE 42S21)
    if ($e->getCode() == '42S21') { 
        echo "Column 'gender' already exists.\n";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
