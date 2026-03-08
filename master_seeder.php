<?php
include 'config.php';

function seedTable($pdo, $table, $data, $columns = ['name']) {
    $placeholders = str_repeat('?,', count($columns) - 1) . '?';
    $colList = implode(',', $columns);
    $stmt = $pdo->prepare("INSERT IGNORE INTO $table ($colList) VALUES ($placeholders)");
    $count = 0;
    foreach ($data as $item) {
        if (is_array($item)) {
            $stmt->execute(array_values($item));
        } else {
            $stmt->execute([$item]);
        }
        if ($stmt->rowCount() > 0) $count++;
    }
    echo "[$table] Seeded $count new records.\n";
}

// 1. Brands
$brands = [
    'Nike', 'Adidas', 'Puma', 'Reebok', 'New Balance', 'Asics', 'Skechers', 'Vans',
    'Converse', 'Under Armour', 'Fila', 'Brooks', 'Saucony', 'Hoka One One', 'Mizuno',
    'Bata', 'Woodland', 'Red Tape', 'Liberty', 'Metro', 'Dr. Martens', 'Timberland',
    'Clarks', 'Birkenstock', 'Crocs', 'Gucci', 'Prada', 'Balenciaga', 'Sparx'
];
seedTable($pdo, 'brands', $brands);

// 2. Materials
$materials = [
    'Leather', 'Suede', 'Mesh', 'Canvas', 'Synthetic', 'Rubber', 'Foam', 'Knit',
    'Nylon', 'Velvet', 'Gore-Tex', 'Nubuck', 'Patent Leather', 'Textile'
];
seedTable($pdo, 'materials', $materials);

// 3. Marketplaces (already has some, but let's ensure major ones)
$marketplaces = [
    ['name' => 'Amazon', 'website_url' => 'https://amazon.com'],
    ['name' => 'Flipkart', 'website_url' => 'https://flipkart.com'],
    ['name' => 'Myntra', 'website_url' => 'https://myntra.com'],
    ['name' => 'Ajio', 'website_url' => 'https://ajio.com'],
    ['name' => 'Nykaa Fashion', 'website_url' => 'https://nykaafashion.com'],
    ['name' => 'Shopify', 'website_url' => 'https://shopify.com'],
    ['name' => 'Instagram', 'website_url' => 'https://instagram.com'],
    ['name' => 'eBay', 'website_url' => 'https://ebay.com']
];
seedTable($pdo, 'marketplaces', $marketplaces, ['name', 'website_url']);

echo "Master seeding complete.\n";
?>
