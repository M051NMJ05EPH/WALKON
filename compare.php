<?php
include 'config.php';

$ids_str = $_GET['ids'] ?? '';
$ids = array_filter(array_map('intval', explode(',', $ids_str)));

if (empty($ids)) {
    header("Location: shop.php");
    exit;
}

try {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("
        SELECT pb.id, pb.name, pp.price, pp.max_price, b.name as brand_name,
        spec.gender, spec.heel_height, spec.outer_material, spec.shoe_type,
        (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as image
        FROM product_base pb
        LEFT JOIN product_prices pp ON pb.id = pp.product_id
        LEFT JOIN product_specs spec ON pb.id = spec.product_id
        LEFT JOIN brands b ON spec.brand_id = b.id
        WHERE pb.id IN ($placeholders)
    ");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching comparison data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Comparison - WALKON</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #10b981;
            --bg: #020617;
            --card-bg: #0f172a;
            --border: rgba(255,255,255,0.08);
            --text-muted: #94a3b8;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        body { background: var(--bg); color: white; padding: 50px 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h1 { font-size: 2.5rem; font-weight: 800; }
        .back-btn { color: var(--primary); text-decoration: none; font-weight: 700; display: flex; align-items: center; gap: 8px; }

        .compare-table { width: 100%; border-collapse: collapse; background: var(--card-bg); border-radius: 20px; overflow: hidden; }
        .compare-table th, .compare-table td { padding: 25px; border: 1px solid var(--border); text-align: left; }
        .compare-table th { background: rgba(255,255,255,0.02); color: var(--text-muted); font-weight: 600; width: 200px; }
        
        .product-header-cell { text-align: center !important; }
        .product-img { width: 150px; height: 150px; object-fit: contain; margin-bottom: 15px; background: #000; border-radius: 15px; padding: 10px; }
        .product-name { font-size: 1.2rem; font-weight: 700; margin-bottom: 5px; }
        .product-brand { color: var(--primary); font-size: 0.8rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
        
        .price-val { font-size: 1.4rem; font-weight: 800; color: var(--primary); }
        .old-price { text-decoration: line-through; color: var(--text-muted); font-size: 1rem; margin-left: 8px; }
        
        .feat-val { font-weight: 500; font-size: 1rem; color: #f1f5f9; }
        .btn-buy { display: block; background: var(--primary); color: #000; text-decoration: none; padding: 12px; border-radius: 10px; font-weight: 700; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Product Comparison</h1>
            <a href="shop.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Shop</a>
        </div>

        <table class="compare-table">
            <thead>
                <tr>
                    <th>Attributes</th>
                    <?php foreach ($products as $p): ?>
                        <td class="product-header-cell">
                            <img src="<?= htmlspecialchars($p['image'] ?? 'assets/shoe_placeholder.png') ?>" class="product-img">
                            <div class="product-brand"><?= htmlspecialchars($p['brand_name'] ?? 'FOOTWEAR') ?></div>
                            <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                            <a href="product_detail.php?id=<?= $p['id'] ?>" class="btn-buy">View Details</a>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>Price</th>
                    <?php foreach ($products as $p): ?>
                        <td>
                            <span class="price-val">₹<?= number_format($p['price']) ?></span>
                            <?php if ($p['max_price'] > $p['price']): ?>
                                <span class="old-price">₹<?= number_format($p['max_price']) ?></span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th>Brand</th>
                    <?php foreach ($products as $p): ?>
                        <td class="feat-val"><?= htmlspecialchars($p['brand_name'] ?? 'N/A') ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th>Gender</th>
                    <?php foreach ($products as $p): ?>
                        <td class="feat-val"><?= htmlspecialchars($p['gender'] ?? 'Unisex') ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th>Material</th>
                    <?php foreach ($products as $p): ?>
                        <td class="feat-val"><?= htmlspecialchars($p['outer_material'] ?? 'N/A') ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th>Style</th>
                    <?php foreach ($products as $p): ?>
                        <td class="feat-val"><?= htmlspecialchars($p['shoe_type'] ?? 'N/A') ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th>Heel Height</th>
                    <?php foreach ($products as $p): ?>
                        <td class="feat-val"><?= htmlspecialchars($p['heel_height'] ?? 'N/A') ?></td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
