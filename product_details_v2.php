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
           pd.content as description,
           s.business_name as seller_name, s.id as seller_id
    FROM product_base pb
    LEFT JOIN product_prices pp ON pb.id = pp.product_id
    LEFT JOIN product_skus ps ON pb.id = ps.product_id
    LEFT JOIN categories c ON pb.category_id = c.id
    LEFT JOIN sub_categories sc ON pb.sub_category_id = sc.id
    LEFT JOIN product_specs spec ON pb.id = spec.product_id
    LEFT JOIN brands b ON spec.brand_id = b.id
    LEFT JOIN product_descriptions pd ON pb.id = pd.product_id
    LEFT JOIN sellers s ON pb.seller_id = s.id
    WHERE pb.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "Product not found.";
    exit;
}

// Fetch Media with Color info
$stmt_media = $pdo->prepare("SELECT * FROM product_media WHERE product_id = ? ORDER BY is_primary DESC");
$stmt_media->execute([$product_id]);
$media = $stmt_media->fetchAll(PDO::FETCH_ASSOC);

// Retrieve Color-Image Map for JS
$colorImageMap = [];
foreach ($media as $m) {
    if (!empty($m['color'])) {
        // Normalize color name for matching (simple lowercase)
        $colorKey = strtolower(trim($m['color']));
        if (!isset($colorImageMap[$colorKey])) {
             $colorImageMap[$colorKey] = $m['url'];
        }
    }
}

// Fetch Sizes
$stmt_sizes = $pdo->prepare("SELECT size_value FROM product_sizes WHERE product_id = ?");
$stmt_sizes->execute([$product_id]);
$sizes = $stmt_sizes->fetchAll(PDO::FETCH_COLUMN);

// Fetch Colors
// Fetch Colors
try {
    $stmt_colors = $pdo->prepare("SELECT color_name, color_code FROM product_colors WHERE product_id = ?");
    $stmt_colors->execute([$product_id]);
    $colorsRaw = $stmt_colors->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching colors: " . $e->getMessage() . "<br>SQL: SELECT color_name, color_code FROM product_colors WHERE product_id = " . $product_id);
}

// Format colors for display
$colors = [];
foreach($colorsRaw as $c) {
    $colors[$c['color_name']] = $c['color_code']; // Map name => code
}

$main_image = !empty($media) ? $media[0]['url'] : 'https://via.placeholder.com/500';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - WALKON Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <style>
        :root { 
            --primary: #10b981; 
            --primary-hover: #059669;
            --bg: #FFFFFF;
            --gray-50: #f8fafc; 
            --gray-100: #f1f5f9;
            --gray-900: #0B0F19; 
            --text-main: #1e293b;
            --text-light: #64748b;
        }
        
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit',sans-serif; }
        body { background: var(--bg); color: var(--text-main); }
        
        /* Navbar */
        .navbar {
          background: #05070a;
          backdrop-filter: blur(20px);
          position: fixed; width: 100%; top: 0; z-index: 1000;
          border-bottom: 1px solid rgba(255, 255, 255, 0.05);
          height: 80px;
        }
        .nav-container {
          max-width: 1400px; margin: 0 auto; padding: 0 2rem; height: 100%;
          display: flex; justify-content: space-between; align-items: center;
        }
        
        .nav-links { display: flex; align-items: center; gap: 2.5rem; }
        .nav-links a { 
          text-decoration: none; font-weight: 500; font-size: 0.9rem;
          color: #e2e8f0; letter-spacing: 0.3px;
          transition: all 0.3s ease;
          position: relative;
        }
        .nav-links a:not(.btn)::after {
          content: ''; position: absolute; width: 0; height: 1px;
          bottom: -4px; left: 0; background: var(--primary);
          transition: width 0.3s ease;
        }
        .nav-links a:not(.btn):hover::after { width: 100%; }
        .nav-links a:hover { color: var(--primary); }

        /* Buttons matching Index.php */
        .btn {
          padding: 1rem 2.5rem; border-radius: 50px; font-weight: 600;
          text-decoration: none; transition: all 0.3s; font-size: 0.95rem;
          letter-spacing: 0.5px; display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-primary { 
          background: var(--primary); color: #000; border: none;
          box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
        }
        .btn-primary:hover { 
          background: #34d399; transform: translateY(-3px);
          box-shadow: 0 10px 30px rgba(16, 185, 129, 0.5);
        }

        .container { max-width: 1400px; margin: 120px auto 40px; padding: 0 40px; display: grid; grid-template-columns: 1fr 1fr; gap: 80px; }
        
        /* Gallery */
        .gallery-container { display: flex; gap: 20px; }
        .thumbnails { display: flex; flex-direction: column; gap: 15px; }
        .thumb-box { width: 80px; height: 80px; border-radius: 12px; cursor: pointer; border: 2px solid transparent; transition: 0.3s; overflow: hidden; background: var(--gray-50); display: flex; align-items: center; justify-content: center; }
        .thumb-box img { max-width: 90%; max-height: 90%; object-fit: contain; }
        .thumb-box.active { border-color: var(--primary); }
        
        .main-img-wrap { flex: 1; border-radius: 32px; background: var(--gray-50); display: flex; align-items: center; justify-content: center; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.02); }
        .main-img { max-width: 100%; max-height: 500px; object-fit: contain; filter: drop-shadow(0 20px 40px rgba(0,0,0,0.1)); }
        
        /* Info */
        .brand-badge { display: inline-block; background: #dcfce7; color: #166534; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.8rem; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
        .product-title { font-size: 3rem; font-weight: 800; color: var(--gray-900); margin-bottom: 15px; letter-spacing: -1px; }
        .price-tag { font-size: 3rem; font-weight: 900; color: var(--primary); margin-bottom: 40px; }
        
        /* Specs Table Style */
        .specs-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1px; background: var(--gray-100); border-radius: 20px; overflow: hidden; border: 1px solid var(--gray-100); margin-bottom: 40px; }
        .spec-item { background: white; padding: 20px 25px; }
        .spec-label { display: block; font-size: 0.8rem; color: var(--text-light); text-transform: capitalize; margin-bottom: 5px; }
        .spec-value { font-weight: 700; font-size: 1rem; color: var(--gray-900); }
        
        /* Selectors */
        .section-label { font-weight: 700; font-size: 1.1rem; margin-bottom: 20px; display: block; color: var(--gray-900); }
        .selector-row { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 40px; }
        
        /* Color Selector */
        .color-option { width: 48px; height: 48px; border-radius: 50%; border: 3px solid white; cursor: pointer; transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1); position: relative; box-shadow: 0 0 0 1.5px #e2e8f0; }
        .color-option:hover { transform: scale(1.1); box-shadow: 0 4px 14px rgba(0,0,0,0.18), 0 0 0 2px var(--primary); }
        .color-option.active { transform: scale(1.18); box-shadow: 0 6px 20px rgba(0,0,0,0.2), 0 0 0 2.5px var(--primary); }
        .color-option.active::after { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 13px; text-shadow: 0 1px 4px rgba(0,0,0,0.5); }
        .color-name-label { font-size: 0.85rem; font-weight: 600; color: var(--text-light); margin-top: -28px; margin-bottom: 28px; transition: opacity 0.2s ease, transform 0.2s ease; letter-spacing: 0.3px; min-height: 1.2em; }
        
        /* Size Selector */
        .size-btn { padding: 12px 25px; border: 1px solid #e2e8f0; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; background: white; color: var(--text-main); font-size: 1rem; }
        .size-btn:hover { border-color: var(--primary); color: var(--primary); }
        .size-btn.active { background: var(--primary); color: white; border-color: var(--primary); transform: translateY(-3px); box-shadow: 0 8px 20px rgba(16,185,129,0.25); }
        
        /* Actions */
        .actions { display: flex; gap: 20px; margin-bottom: 60px; }
        .btn-cart { flex: 1.2; background: var(--primary); color: white; border: none; padding: 22px; border-radius: 16px; font-size: 1.2rem; font-weight: 800; cursor: pointer; transition: 0.4s; box-shadow: 0 15px 35px rgba(16,185,129,0.2); }
        .btn-buy { flex: 1; background: var(--gray-900); color: white; border: none; padding: 22px; border-radius: 16px; font-size: 1.2rem; font-weight: 800; cursor: pointer; transition: 0.4s; }
        .btn-cart:hover { background: var(--primary-hover); transform: translateY(-5px); box-shadow: 0 20px 45px rgba(16,185,129,0.3); }
        .btn-buy:hover { background: #1a2233; transform: translateY(-5px); }

        /* Description */
        .about-item h3 { font-size: 1.5rem; font-weight: 800; margin-bottom: 20px; color: var(--gray-900); }
        .about-item p { color: var(--text-light); line-height: 1.8; font-size: 1.1rem; }

        @media (max-width: 1024px) {
            .container { grid-template-columns: 1fr; gap: 40px; }
            .gallery-container { flex-direction: column-reverse; }
            .thumbnails { flex-direction: row; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
            <img src="assets/shoe_logo_green.png" alt="WalkOn Logo" style="height: 60px; width: auto; filter: drop-shadow(0 0 10px rgba(16, 185, 129, 0.2));">
            <div style="font-family: 'Outfit', sans-serif; font-size: 36px; font-weight: 800; line-height: 1; letter-spacing: -0.5px; text-transform: uppercase;">
                <span style="color: #fff;">Walk</span><span style="color: #10b981;">on</span>
            </div>
        </a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="shop.php">Shop</a>
            <a href="dashboard.php">Dashboard</a>
            <div style="display: flex; align-items: center; gap: 1.5rem; margin-left: 1rem;">
                <a href="login.php" style="text-decoration: none; color: #fff; font-weight: 600; font-size: 0.9rem;">Login</a>
                <a href="start_selling.php" class="btn btn-primary" style="padding: 0.8rem 1.8rem; border-radius: 50px; font-size: 0.9rem; gap: 8px;">
                    Start Selling <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container">
    <!-- LEFT: GALLERY -->
    <div class="gallery-container">
        <div class="thumbnails">
            <?php foreach ($media as $idx => $m): ?>
                <div class="thumb-box <?php echo $idx === 0 ? 'active' : ''; ?>" onclick="changeImage('<?php echo htmlspecialchars($m['url']); ?>', this)">
                    <img src="<?php echo htmlspecialchars($m['url']); ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <div class="main-img-wrap">
            <img src="<?php echo htmlspecialchars($main_image); ?>" id="mainImage" class="main-img">
        </div>
    </div>

    <!-- RIGHT: PRODUCT INFO -->
    <div class="info-container">
        <span class="brand-badge"><?php echo htmlspecialchars($product['brand_name'] ?: 'WALKON'); ?></span>
        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
        <div class="price-tag">₹<?php echo number_format($product['price'] ?: 15995); ?></div>

        <div class="specs-grid">
            <div class="spec-item">
                <span class="spec-label">Category</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['category_name'] ?: 'Sneakers'); ?></span>
            </div>
            <div class="spec-item">
                <span class="spec-label">SKU</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['sku'] ?: 'NIKE-AJ1-H-001'); ?></span>
            </div>
            <div class="spec-item">
                <span class="spec-label">Type</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['shoe_type'] ?: 'Basketball'); ?></span>
            </div>
            <div class="spec-item">
                <span class="spec-label">Material</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['outer_material'] ?: 'Leather'); ?></span>
            </div>
        </div>

        <!-- NEW: COLOR SELECTOR -->
        <span class="section-label">Select Color</span>
        <div id="colorNameLabel" class="color-name-label">—</div>
        <div class="selector-row" style="margin-top: 10px;">
            <?php if (!empty($colors)): ?>
                <?php foreach ($colors as $name => $code): ?>
                    <div class="color-option" 
                         style="background-color: <?= htmlspecialchars($code) ?>;" 
                         title="<?= htmlspecialchars($name) ?>" 
                         data-color-name="<?= htmlspecialchars($name) ?>"
                         onclick="selectColor(this)">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback: 7 curated colors -->
                <div class="color-option" style="background-color: #111111;" title="Jet Black" data-color-name="Jet Black" onclick="selectColor(this)"></div>
                <div class="color-option" style="background-color: #F5F0E8;" title="Ivory White" data-color-name="Ivory White" onclick="selectColor(this)"></div>
                <div class="color-option" style="background-color: #1B2A4A;" title="Midnight Navy" data-color-name="Midnight Navy" onclick="selectColor(this)"></div>
                <div class="color-option" style="background-color: #2D6A4F;" title="Forest Green" data-color-name="Forest Green" onclick="selectColor(this)"></div>
                <div class="color-option" style="background-color: #C0392B;" title="Crimson Red" data-color-name="Crimson Red" onclick="selectColor(this)"></div>
                <div class="color-option" style="background-color: #C9A84C;" title="Royal Gold" data-color-name="Royal Gold" onclick="selectColor(this)"></div>
                <div class="color-option" style="background-color: #3A86C8;" title="Sky Blue" data-color-name="Sky Blue" onclick="selectColor(this)"></div>
            <?php endif; ?>
        </div>

        <!-- NEW: SIZE SELECTOR -->
        <span class="section-label">Select Size</span>
        <div class="selector-row">
            <?php 
            $display_sizes = !empty($sizes) ? $sizes : ['UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11'];
            foreach ($display_sizes as $s): ?>
                <button class="size-btn" onclick="selectSize(this)"><?php echo htmlspecialchars($s); ?></button>
            <?php endforeach; ?>
        </div>

        <div class="actions">
            <button class="btn-cart" onclick="location.href='cart.php'">Add to Cart</button>
            <button class="btn-buy" onclick="location.href='checkout.php'">Buy Now</button>
        </div>
        
        <!-- SELLER INFO & API CONNECTION -->
        <div class="seller-card" style="margin-bottom: 40px; border-radius: 24px; overflow: hidden; border: 1px solid var(--gray-100);">
            <div class="seller-section" style="padding: 25px; background: var(--gray-50); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--gray-100);">
                 <div>
                     <span style="font-size: 0.85rem; color: var(--text-light); display: block; margin-bottom: 4px;">Sold by</span>
                     <strong style="font-size: 1.2rem; color: var(--gray-900);"><?php echo htmlspecialchars($product['seller_name'] ?: 'WalkOn Official Store'); ?></strong>
                 </div>
                 <div style="display: flex; gap: 10px;">
                     <?php if (isset($_SESSION['seller_id']) && $_SESSION['seller_id'] == $product['seller_id']): ?>
                         <a href="edit_listing.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: white; background: var(--gray-900); font-weight: 700; font-size: 0.95rem; padding: 10px 24px; border-radius: 50px; transition: 0.3s; display: flex; align-items: center; gap: 8px;">
                             <i class="fas fa-edit"></i> Edit Listing
                         </a>
                     <?php endif; ?>
                     <a href="shop.php?seller=<?php echo $product['seller_id']; ?>" style="text-decoration: none; color: var(--primary); font-weight: 700; font-size: 0.95rem; border: 2px solid var(--primary); padding: 10px 24px; border-radius: 50px; transition: 0.3s;" onmouseover="this.style.background='var(--primary)'; this.style.color='white'" onmouseout="this.style.background='transparent'; this.style.color='var(--primary)'">
                         Visit Store <i class="fas fa-arrow-right" style="margin-left: 5px;"></i>
                     </a>
                 </div>
            </div>
            
            <!-- API Connection Banner -->
            <div class="api-connection-banner" style="background: linear-gradient(135deg, #0f172a 0%, #151b2b 100%); padding: 25px; color: white;">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                    <div style="width: 45px; height: 45px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                        <i class="fas fa-plug" style="font-size: 1.2rem;"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 1.1rem; font-weight: 700; margin: 0;">Multi-Channel API Sync</h4>
                        <p style="font-size: 0.85rem; color: #94a3b8; margin: 0;">Connect this product to Amazon, Shopify & more.</p>
                    </div>
                </div>
                <a href="ai_channel_sync.php?product_id=<?php echo $product_id; ?>" style="display: block; width: 100%; padding: 12px; background: var(--primary); color: #000; text-align: center; text-decoration: none; border-radius: 12px; font-weight: 800; font-size: 0.95rem; transition: 0.3s; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(16, 185, 129, 0.3)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(16, 185, 129, 0.2)';">
                    Connect Channel API <i class="fas fa-link" style="margin-left: 8px; font-size: 0.8rem;"></i>
                </a>
            </div>
        </div>

        <div class="about-item">
            <h3>About this item</h3>
            <p><?php echo nl2br(htmlspecialchars($product['description'] ?: 'The iconic sneaker that defined a generation. Premium leather and classic design.')); ?></p>
        </div>
    </div>
</div>

<script>
    // Color Image Map from PHP
    const colorImageMap = <?php echo json_encode($colorImageMap); ?>;

    function changeImage(src, el) {
        document.getElementById('mainImage').src = src;
        if(el) {
            document.querySelectorAll('.thumb-box').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
        }
    }
    
    function selectColor(el) {
        document.querySelectorAll('.color-option').forEach(c => c.classList.remove('active'));
        el.classList.add('active');

        // Update color name label
        const colorName = el.getAttribute('data-color-name') || el.getAttribute('title') || '';
        const label = document.getElementById('colorNameLabel');
        if (label && colorName) {
            label.style.opacity = '0';
            label.style.transform = 'translateY(-4px)';
            setTimeout(() => {
                label.textContent = colorName;
                label.style.opacity = '1';
                label.style.transform = 'translateY(0)';
            }, 150);
        }

        // Check if there's a specific image for this color
        if (colorName) {
            const key = colorName.toLowerCase().trim();
            if(colorImageMap[key]) {
                const newSrc = colorImageMap[key];
                document.getElementById('mainImage').src = newSrc;
                document.querySelectorAll('.thumb-box img').forEach(img => {
                    if(img.src.includes(newSrc)) {
                       img.parentElement.click();
                    }
                });
            }
        }
    }
    
    function selectSize(el) {
        document.querySelectorAll('.size-btn').forEach(s => s.classList.remove('active'));
        el.classList.add('active');
    }
</script>

</body>
</html>
