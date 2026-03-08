<?php
/**
 * seed_7_colors.php
 * Adds all 7 standard WALKON colors to every product in the database.
 * Run once via browser: http://localhost/WALKON-rough/seed_7_colors.php
 */

include 'config.php';

$colors = [
    ['Jet Black',     '#111111'],
    ['Ivory White',   '#F5F0E8'],
    ['Midnight Navy', '#1B2A4A'],
    ['Forest Green',  '#2D6A4F'],
    ['Crimson Red',   '#C0392B'],
    ['Royal Gold',    '#C9A84C'],
    ['Sky Blue',      '#3A86C8'],
];

// Ensure color_code column exists (in case older schema only has color_name)
try {
    $cols = $pdo->query("DESCRIBE product_colors")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('color_code', $cols)) {
        $pdo->exec("ALTER TABLE product_colors ADD COLUMN color_code VARCHAR(20) DEFAULT '#000000'");
        echo "<p style='color:green'>✅ Added <code>color_code</code> column to product_colors.</p>";
    }
} catch (PDOException $e) {
    die("<p style='color:red'>❌ Schema check failed: " . $e->getMessage() . "</p>");
}

// Fetch all product IDs
$products = $pdo->query("SELECT id, name FROM product_base ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

if (empty($products)) {
    die("<p style='color:orange'>⚠️ No products found in product_base.</p>");
}

$stmtDelete = $pdo->prepare("DELETE FROM product_colors WHERE product_id = ?");
$stmtInsert = $pdo->prepare("INSERT INTO product_colors (product_id, color_name, color_code) VALUES (?, ?, ?)");

$updated = 0;
$skipped = 0;
$errors  = 0;

echo "<!DOCTYPE html><html><head>
    <title>Seed 7 Colors</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; }
        h1 { color: #10b981; }
        .product-row { padding: 8px 14px; border-radius: 8px; margin: 4px 0; font-size: 0.9rem; }
        .ok  { background: #ecfdf5; color: #065f46; border-left: 4px solid #10b981; }
        .err { background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444; }
        .swatches { display: flex; gap: 6px; margin-top: 6px; }
        .dot { width: 18px; height: 18px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 1px #ccc; display: inline-block; }
        .summary { background: #f0fdf4; border: 2px solid #10b981; border-radius: 12px; padding: 20px; margin-top: 30px; }
    </style>
</head><body>";

echo "<h1>🎨 Seeding 7 Colors to All Products</h1>";
echo "<p>Processing <strong>" . count($products) . "</strong> products...</p><hr>";

foreach ($products as $p) {
    $pid  = $p['id'];
    $name = htmlspecialchars($p['name']);

    try {
        // Remove old colors for this product
        $stmtDelete->execute([$pid]);

        // Insert all 7 colors
        foreach ($colors as [$colorName, $colorCode]) {
            $stmtInsert->execute([$pid, $colorName, $colorCode]);
        }

        // Build swatch preview
        $swatches = '';
        foreach ($colors as [$cn, $cc]) {
            $swatches .= "<span class='dot' style='background:{$cc}' title='{$cn}'></span>";
        }

        echo "<div class='product-row ok'>
                <strong>#$pid</strong> — $name
                <div class='swatches'>$swatches</div>
              </div>";
        $updated++;

    } catch (PDOException $e) {
        echo "<div class='product-row err'>
                ❌ <strong>#$pid</strong> — $name: " . htmlspecialchars($e->getMessage()) . "
              </div>";
        $errors++;
    }
}

echo "<div class='summary'>
    <h2>✅ Done!</h2>
    <p>✔ Updated: <strong>$updated products</strong></p>
    " . ($errors ? "<p>❌ Errors: <strong>$errors</strong></p>" : "") . "
    <p style='margin-top:12px;'>
        <a href='product_detail.php?id=1' style='background:#10b981;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:700;'>
            👟 View Product #1
        </a>
        &nbsp;
        <a href='shop.php' style='background:#0f172a;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:700;'>
            🛒 Go to Shop
        </a>
    </p>
</div>
</body></html>";
?>
