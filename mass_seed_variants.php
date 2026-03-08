<?php
include 'config.php';

try {
    echo "<h1>Mass Seeding Variants...</h1>";
    
    // 1. Get all products
    $products = $pdo->query("SELECT id FROM product_base")->fetchAll(PDO::FETCH_COLUMN);
    
    $size_options = ['6', '7', '8', '9', '10', '11', '12'];
    $color_options = [
        ['Black', '#000000'],
        ['White', '#ffffff'],
        ['Navy Blue', '#000080'],
        ['Grey', '#808080'],
        ['Red', '#ff0000'],
        ['Emerald Green', '#10b981']
    ];
    
    $processed = 0;
    
    foreach ($products as $pid) {
        // Check if already has sizes
        $has_sizes = $pdo->query("SELECT COUNT(*) FROM product_sizes WHERE product_id = $pid")->fetchColumn();
        if (!$has_sizes) {
            $num_sizes = rand(3, 5);
            $selected_sizes = array_rand(array_flip($size_options), $num_sizes);
            if (!is_array($selected_sizes)) $selected_sizes = [$selected_sizes];
            sort($selected_sizes);
            
            $stmt = $pdo->prepare("INSERT INTO product_sizes (product_id, size_value) VALUES (?, ?)");
            foreach ($selected_sizes as $sz) {
                $stmt->execute([$pid, $sz]);
            }
        }
        
        // Check if already has colors
        $has_colors = $pdo->query("SELECT COUNT(*) FROM product_colors WHERE product_id = $pid")->fetchColumn();
        if (!$has_colors) {
            $num_colors = rand(1, 3);
            $selected_colors = array_rand($color_options, $num_colors);
            if (!is_array($selected_colors)) $selected_colors = [$selected_colors];
            
            $stmt = $pdo->prepare("INSERT INTO product_colors (product_id, color_name, color_hex) VALUES (?, ?, ?)");
            foreach ($selected_colors as $idx) {
                $c = $color_options[$idx];
                $stmt->execute([$pid, $c[0], $c[1]]);
            }
        }
        
        $processed++;
        if ($processed % 10 === 0) echo "Processed $processed products...\n";
    }
    
    echo "<h3>Success: Variants seeded for $processed products!</h3>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
