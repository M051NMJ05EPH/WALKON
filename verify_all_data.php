<?php
include 'config.php';

$product_names = [
    'Velocity Runner Pro',
    'Nike Air Jordan 1 High',
    'Reebok Nano X3',
    'Timberland 6-Inch Premium',
    'Under Armour Curry 10',
    'Skechers Arch Fit',
    'Sparx Power Running',
    'Vans Old Skool Stackform',
    'Nike Breathable Mesh Speed Runner',
    'Clarks CraftMaster II'
];

echo "=== COMPREHENSIVE DATA VERIFICATION ===\n\n";

foreach ($product_names as $name) {
    try {
        // Fetch Base Info
        $stmt = $pdo->prepare("
            SELECT pb.id, pb.name, pp.price, ps.sku, c.name as cat, b.name as brand
            FROM product_base pb
            LEFT JOIN product_prices pp ON pb.id = pp.product_id
            LEFT JOIN product_skus ps ON pb.id = ps.product_id
            LEFT JOIN categories c ON pb.category_id = c.id
            LEFT JOIN product_specs spec ON pb.id = spec.product_id
            LEFT JOIN brands b ON spec.brand_id = b.id
            WHERE pb.name = ?
        ");
        $stmt->execute([$name]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$p) {
            echo "❌ [MISSING] $name\n";
            continue;
        }

        echo "✅ [FOUND] {$p['name']} (ID: {$p['id']})\n";
        echo "   - SKU: {$p['sku']}\n";
        echo "   - Price: " . number_format($p['price']) . "\n";
        echo "   - Category: {$p['cat']}\n";
        echo "   - Brand: {$p['brand']}\n";

        // Fetch Media
        $media = $pdo->prepare("SELECT url FROM product_media WHERE product_id = ? ORDER BY is_primary DESC");
        $media->execute([$p['id']]);
        $urls = $media->fetchAll(PDO::FETCH_COLUMN);
        echo "   - Media: " . (count($urls) > 0 ? implode(", ", $urls) : "❌ No images") . "\n";

        // Fetch Sizes
        $sizes = $pdo->prepare("SELECT size_value FROM product_sizes WHERE product_id = ?");
        $sizes->execute([$p['id']]);
        $size_list = $sizes->fetchAll(PDO::FETCH_COLUMN);
        echo "   - Sizes: " . (count($size_list) > 0 ? implode(", ", $size_list) : "None") . "\n";

        // Fetch Colors
        $colors = $pdo->prepare("SELECT color_name FROM product_colors WHERE product_id = ?");
        $colors->execute([$p['id']]);
        $color_list = $colors->fetchAll(PDO::FETCH_COLUMN);
        echo "   - Colors: " . (count($color_list) > 0 ? implode(", ", $color_list) : "None") . "\n";

        echo "---------------------------------------------------\n";

    } catch (Exception $e) {
        echo "Error checking $name: " . $e->getMessage() . "\n";
    }
}
?>
