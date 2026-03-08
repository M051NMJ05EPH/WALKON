<?php
include 'config.php';

$products = [
    'Velocity Runner Pro' => [
        'sizes' => ['7', '8', '9', '10', '11'],
        'colors' => [
            ['Green', '#10b981'], ['Black', '#000000'], ['White', '#ffffff']
        ]
    ],
    'Reebok Nano X3' => [
        'sizes' => ['7', '8', '8.5', '9', '10', '11'],
        'colors' => [
            ['Black/Orange', '#ff4500'], ['White', '#ffffff'], ['Grey', '#808080']
        ]
    ],
    'Skechers Arch Fit' => [
        'sizes' => ['7', '8', '9', '10', '11'],
        'colors' => [
            ['Navy', '#000080'], ['Grey', '#808080'], ['Black', '#000000']
        ]
    ],
    'Sparx Power Running' => [
        'sizes' => ['6', '7', '8', '9', '10'],
        'colors' => [
            ['Red', '#ff0000'], ['Blue', '#0000ff'], ['Black', '#000000']
        ]
    ],
    'Timberland 6-Inch Premium' => [
        'sizes' => ['8', '9', '10', '11', '12'],
        'colors' => [
            ['Wheat', '#f5deb3'], ['Black', '#000000'], ['Brown', '#a52a2a']
        ]
    ],
    'Under Armour Curry 10' => [
        'sizes' => ['7', '8', '9', '10', '11', '12'],
        'colors' => [
            ['Red', '#ff0000'], ['Blue', '#0000ff'], ['White', '#ffffff']
        ]
    ],
    'Vans Old Skool Stackform' => [
        'sizes' => ['5', '6', '7', '8', '9', '10'],
        'colors' => [
            ['Black/White', '#000000'], ['All Black', '#1a1a1a'], ['White', '#ffffff']
        ]
    ]
];

try {
    $pdo->beginTransaction();

    foreach ($products as $name => $variants) {
        // Get ID
        $stmt = $pdo->prepare("SELECT id FROM product_base WHERE name = ?");
        $stmt->execute([$name]);
        $p = $stmt->fetch();

        if (!$p) {
            echo "Skipping $name: Not found.\n";
            continue;
        }
        $pid = $p['id'];

        // Clear existing
        $pdo->prepare("DELETE FROM product_sizes WHERE product_id = ?")->execute([$pid]);
        $pdo->prepare("DELETE FROM product_colors WHERE product_id = ?")->execute([$pid]);

        // Insert Sizes
        $stmt_size = $pdo->prepare("INSERT INTO product_sizes (product_id, size_value) VALUES (?, ?)");
        foreach ($variants['sizes'] as $size) {
            $stmt_size->execute([$pid, $size]);
        }

        // Insert Colors
        $stmt_color = $pdo->prepare("INSERT INTO product_colors (product_id, color_name, color_hex) VALUES (?, ?, ?)");
        foreach ($variants['colors'] as $color) {
            $stmt_color->execute([$pid, $color[0], $color[1]]);
        }

        echo "Updated variants for: $name (ID: $pid)\n";
    }

    $pdo->commit();
    echo "\nSuccess: All variants seeded.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
