<?php
session_start();
include 'config.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    echo "Invalid Product ID.";
    exit;
}

// Fetch Product Details
$stmt = $pdo->prepare("
    SELECT pb.*, pp.price, pp.max_price, ps.sku, c.name as category_name, sc.name as sub_category_name, b.name as brand_name,
           spec.heel_height, spec.outer_material, spec.season, spec.shoe_type, spec.occasion, spec.gender,
           pd.content as description
    FROM product_base pb
    LEFT JOIN product_prices pp ON pb.id = pp.product_id
    LEFT JOIN product_skus ps ON pb.id = ps.product_id
    LEFT JOIN categories c ON pb.category_id = c.id
    LEFT JOIN sub_categories sc ON pb.sub_category_id = sc.id
    LEFT JOIN product_specs spec ON pb.id = spec.product_id
    LEFT JOIN brands b ON spec.brand_id = b.id
    LEFT JOIN product_descriptions pd ON pb.id = pd.product_id
    WHERE pb.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "Product not found.";
    exit;
}

// Fetch Media
$stmt_media = $pdo->prepare("SELECT * FROM product_media WHERE product_id = ? ORDER BY is_primary DESC");
$stmt_media->execute([$product_id]);
$media = $stmt_media->fetchAll(PDO::FETCH_ASSOC);

// Fetch Sizes
$stmt_sizes = $pdo->prepare("SELECT size_value FROM product_sizes WHERE product_id = ?");
$stmt_sizes->execute([$product_id]);
$sizes = $stmt_sizes->fetchAll(PDO::FETCH_COLUMN);

// Fetch Colors
$stmt_colors = $pdo->prepare("SELECT color_name FROM product_colors WHERE product_id = ?");
$stmt_colors->execute([$product_id]);
$colors = $stmt_colors->fetchAll(PDO::FETCH_COLUMN);

$main_image = !empty($media) ? $media[0]['url'] : 'https://via.placeholder.com/500';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <style>
        :root { --green: #16a34a; --gray-50: #f8fafc; --gray-900: #0f172a; }
        body { font-family: 'Inter', sans-serif; background: var(--gray-50); color: var(--gray-900); }
        
        .navbar { background: white; padding: 1rem 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.8rem; font-weight: 900; color: var(--gray-900); text-decoration:none; }
        .logo span { color: var(--green); }

        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; }
        
        .image-gallery { display: flex; gap: 20px; }
        .thumbnails { display: flex; flex-direction: column; gap: 15px; }
        .thumb { width: 80px; height: 80px; border-radius: 12px; cursor: pointer; object-fit: cover; border: 2px solid transparent; transition: 0.3s; }
        .thumb.active { border-color: var(--green); }
        .main-img-wrap { flex: 1; border-radius: 24px; overflow: hidden; background: white; box-shadow: 0 10px 40px rgba(0,0,0,0.05); }
        .main-img { width: 100%; height: 100%; object-fit: cover; display: block; }
        
        .product-info h1 { font-size: 2.5rem; font-weight: 800; line-height: 1.2; margin-bottom: 20px; }
        .brand-badge { display: inline-block; background: #dcfce7; color: #166534; padding: 6px 12px; border-radius: 50px; font-weight: 700; font-size: 0.9rem; margin-bottom: 15px; text-transform: uppercase; }
        
        .price-area { margin-bottom: 30px; }
        .price { font-size: 2.5rem; font-weight: 900; color: var(--green); }
        .old-price { font-size: 1.2rem; color: #94a3b8; text-decoration: line-through; margin-left: 10px; }
        
        .specs-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 30px; background: white; padding: 25px; border-radius: 16px; border: 1px solid #e2e8f0; }
        .spec-item { font-size: 0.95rem; }
        .spec-label { color: #64748b; display: block; font-size: 0.85rem; margin-bottom: 4px; }
        .spec-value { font-weight: 600; color: var(--gray-900); }
        
        .selector-group { margin-bottom: 25px; }
        .selector-label { font-weight: 600; margin-bottom: 10px; display: block; }
        .size-box { display: inline-block; padding: 10px 15px; border: 2px solid #e2e8f0; border-radius: 12px; text-align: center; font-weight: 600; margin-right: 10px; cursor: pointer; transition: 0.2s; min-width: 60px; }
        .size-box:hover, .size-box.active { border-color: var(--green); background: #f0fdf4; color: var(--green); }
        
        .color-dot { display: inline-block; width: 35px; height: 35px; border-radius: 50%; border: 2px solid #e2e8f0; margin-right: 12px; cursor: pointer; transition: 0.3s; position: relative; }
        .color-dot:hover, .color-dot.active { border-color: var(--green); transform: scale(1.1); }
        .color-dot.active::after { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 12px; text-shadow: 0 0 4px rgba(0,0,0,0.5); }
        
        .actions-group { display: flex; gap: 20px; margin-top: 30px; }
        .btn-cart { flex: 1.2; background: var(--green); color: white; border: none; padding: 18px 30px; border-radius: 50px; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 25px rgba(22,163,74,0.3); }
        .btn-buy { flex: 1; background: var(--gray-900); color: white; border: 2px solid var(--gray-900); padding: 18px 30px; border-radius: 50px; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-cart:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(22,163,74,0.4); }
        .btn-buy:hover { background: transparent; color: var(--gray-900); transform: translateY(-3px); }

        .about-section { margin-top: 40px; }
        .about-section h3 { font-size: 1.4rem; font-weight: 700; margin-bottom: 15px; }
        .about-section p { color: #475569; line-height: 1.8; }

        /* Footer Refined */
        footer {
          background: #05070A; border-top: 1px solid var(--gray-50);
          padding: 80px 0 40px; color: #fff;
          margin-top: 80px;
        }
        .footer-container {
            max-width: 1400px; margin: 0 auto; padding: 0 2rem;
            display: grid; grid-template-columns: 1.2fr 2fr; gap: 4rem;
        }
        
        /* Footer Card */
        .footer-card {
            background: #0f131f; /* Darker card background */
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 24px; padding: 3rem;
            display: flex; flex-direction: column; gap: 1.5rem;
        }
        .footer-logo {
            display: flex; align-items: center; gap: 10px; text-decoration: none;
        }
        .brand-text {
            font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; line-height: 1;
        }
        .footer-desc {
            color: #94a3b8; font-size: 0.95rem; line-height: 1.6; margin-bottom: 0.5rem;
        }
        
        .contact-info { display: flex; flex-direction: column; gap: 0.8rem; }
        .contact-item {
            display: flex; align-items: center; gap: 10px;
            color: #fff; font-size: 0.9rem;
        }
        .contact-item i { color: var(--green); width: 20px; }
        
        .social-links {
            display: flex; gap: 1rem; margin-top: 1rem;
        }
        .social-btn {
            width: 40px; height: 40px; border-radius: 10px;
            background: rgba(255,255,255,0.05);
            display: flex; align-items: center; justify-content: center;
            color: #94a3b8; text-decoration: none; transition: 0.3s;
        }
        .social-btn:hover {
            background: var(--green); color: #000; transform: translateY(-3px);
        }
        
        /* Footer Grid */
        .footer-nav-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;
        }
        
        .footer-col h4 {
            color: var(--green); font-size: 0.85rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.5rem;
        }
        
        .footer-links { list-style: none; padding: 0; margin: 0; }
        .footer-links li { margin-bottom: 1rem; }
        .footer-links a {
            color: #e2e8f0; text-decoration: none; font-size: 0.95rem;
            transition: 0.3s;
        }
        .footer-links a:hover { color: var(--green); padding-left: 5px; }

        @media (max-width: 1024px) {
            .footer-container { grid-template-columns: 1fr; }
            .footer-card { max-width: 500px; }
        }
        @media (max-width: 768px) {
            .footer-nav-grid { grid-template-columns: 1fr 1fr; }
             .container { grid-template-columns: 1fr; gap: 30px; }
            .image-gallery { flex-direction: column-reverse; }
            .thumbnails { flex-direction: row; overflow-x: auto; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
        <img src="assets/shoe_logo_green.png" alt="WalkOn Logo" style="height: 48px; width: auto;">
            <div style="font-family: 'Outfit', sans-serif; font-size: 26px; font-weight: 700; line-height: 1;"><span style="color:#fff">WALK</span><span style="color:#10b981">ON</span></div>
    </a>
    <div>
        <a href="index.php" style="text-decoration:none; color:var(--gray-900); font-weight:600;">Home</a>
        <a href="shop.php" style="text-decoration:none; color:var(--gray-900); font-weight:600; margin-left: 20px;">Shop</a>
    </div>
</nav>

<div class="container">
    <div class="gallery-wrapper">
        <div class="image-gallery">
            <div class="thumbnails">
                <?php foreach ($media as $idx => $m): ?>
                    <img src="<?php echo htmlspecialchars($m['url']); ?>" class="thumb <?php echo $idx === 0 ? 'active' : ''; ?>" onclick="changeImage(this.src, this)">
                <?php endforeach; ?>
            </div>
            <div class="main-img-wrap">
                <img src="<?php echo htmlspecialchars($main_image); ?>" id="mainImage" class="main-img">
            </div>
        </div>
    </div>

    <div class="product-info">
        <?php if ($product['brand_name']): ?>
            <span class="brand-badge"><?php echo htmlspecialchars($product['brand_name']); ?></span>
        <?php endif; ?>
        
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        
        <div class="price-area">
            <span class="price">₹<?php echo number_format($product['price']); ?></span>
            <?php if ($product['max_price']): ?>
                <span class="old-price">₹<?php echo number_format($product['max_price']); ?></span>
            <?php endif; ?>
        </div>

        <!-- SPECS BOX -->
        <div class="specs-grid">
            <div class="spec-item">
                <span class="spec-label">Category</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['category_name']); ?></span>
            </div>
            
            <?php if ($product['sub_category_name']): ?>
            <div class="spec-item">
                <span class="spec-label">Sub Category</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['sub_category_name']); ?></span>
            </div>
            <?php endif; ?>
            <div class="spec-item">
                <span class="spec-label">SKU</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['sku']); ?></span>
            </div>
            <?php if ($product['shoe_type']): ?>
            <div class="spec-item">
                <span class="spec-label">Type</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['shoe_type']); ?></span>
            </div>
            <?php endif; ?>


            <?php if ($product['heel_height']): ?>
            <div class="spec-item">
                <span class="spec-label">Heel Height</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['heel_height']); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($product['outer_material']): ?>
            <div class="spec-item">
                <span class="spec-label">Material</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['outer_material']); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($product['season']): ?>
            <div class="spec-item">
                <span class="spec-label">Season</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['season']); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($product['gender']): ?>
            <div class="spec-item">
                <span class="spec-label">Gender</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['gender']); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($colors)): ?>
        <div class="selector-group">
            <span class="selector-label">Available Colors</span>
            <div style="display: flex;">
                <?php foreach ($colors as $c): 
                    $hex = 'gray';
                    if(stripos($c, 'Black') !== false) $hex = '#000000';
                    elseif(stripos($c, 'Navy') !== false) $hex = '#000080';
                    elseif(stripos($c, 'Grey') !== false) $hex = '#808080';
                    elseif(stripos($c, 'White') !== false) $hex = '#ffffff';
                ?>
                    <div class="color-dot" style="background-color: <?= $hex ?>;" title="<?= htmlspecialchars($c) ?>" onclick="selectColor(this)"></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($sizes)): ?>
        <div class="selector-group">
            <span class="selector-label">Select Size</span>
            <?php foreach ($sizes as $s): ?>
                <span class="size-box" onclick="selectSize(this)"><?php echo htmlspecialchars($s); ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="actions-group">
            <button class="btn-cart" onclick="window.location.href='cart.php'">Add to Cart</button>
            <button class="btn-buy" onclick="window.location.href='checkout.php'">Buy Now</button>
        </div>

        <div class="about-section">
            <h3>About this item</h3>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
    </div>
</div>

</div>

</body>
</html>

<script>
    function changeImage(src, el) {
        document.getElementById('mainImage').src = src;
        document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
    }
    function selectSize(el) {
        document.querySelectorAll('.size-box').forEach(s => s.classList.remove('active'));
        el.classList.add('active');
    }
    function selectColor(el) {
        document.querySelectorAll('.color-dot').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
    }
</script>

</body>
</html>
