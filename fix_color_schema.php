<?php
require_once 'config.php';

try {
    echo "Checking 'product_colors' schema...\n";
    $columns = $pdo->query("DESCRIBE product_colors")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('color_code', $columns)) {
        echo "Adding 'color_code' column...\n";
        $pdo->exec("ALTER TABLE product_colors ADD COLUMN color_code VARCHAR(10) DEFAULT '#000000'");
        echo "Column added.\n";
    } else {
        echo "'color_code' exists.\n";
    }

    // Re-seed colors for 122 just to be sure
    echo "Re-seeding colors for Product 122...\n";
    $pdo->exec("DELETE FROM product_colors WHERE product_id = 122");
    
    $colors = [
        ['Navy Blue', '#000080'],
        ['Red', '#FF0000']
    ];
    $stmt = $pdo->prepare("INSERT INTO product_colors (product_id, color_name, color_code) VALUES (122, ?, ?)");
    foreach ($colors as $c) {
        $stmt->execute($c);
    }
    echo "Colors seeded.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
