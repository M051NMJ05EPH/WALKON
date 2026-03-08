<?php
session_start();
// Version Check: Force Refresh 1
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
           s.business_name as seller_name, s.id as seller_id,
           pst.quantity as available_quantity
    FROM product_base pb
    LEFT JOIN product_prices pp ON pb.id = pp.product_id
    LEFT JOIN product_skus ps ON pb.id = ps.product_id
    LEFT JOIN categories c ON pb.category_id = c.id
    LEFT JOIN sub_categories sc ON pb.sub_category_id = sc.id
    LEFT JOIN product_specs spec ON pb.id = spec.product_id
    LEFT JOIN brands b ON spec.brand_id = b.id
    LEFT JOIN product_descriptions pd ON pb.id = pd.product_id
    LEFT JOIN sellers s ON pb.seller_id = s.id
    LEFT JOIN product_stock pst ON pb.id = pst.product_id
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

// Check if in wishlist
$is_in_wishlist = false;
if (isset($_SESSION['user_id'])) {
    $stmt_wish = $pdo->prepare("SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt_wish->execute([$_SESSION['user_id'], $product_id]);
    $is_in_wishlist = (bool)$stmt_wish->fetch();
}

// Recently Viewed logic
if (!isset($_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'] = [];
}
// Remove if already exists to move to front
if (($key = array_search($product_id, $_SESSION['recently_viewed'])) !== false) {
    unset($_SESSION['recently_viewed'][$key]);
}
// Add to front
array_unshift($_SESSION['recently_viewed'], $product_id);
// Keep only latest 10
$_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 10);
// Count initial views logic
$ip = $_SERVER['REMOTE_ADDR'];
try {
    $stmt = $pdo->prepare("INSERT INTO product_views (product_id, ip_address, view_count, last_viewed) 
                          VALUES (?, ?, 1, NOW()) 
                          ON DUPLICATE KEY UPDATE view_count = view_count + 1, last_viewed = NOW()");
    $stmt->execute([$product_id, $ip]);
} catch(PDOException $e) {}

// Get Cart Total
$cart_total = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt_cart = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
        $stmt_cart->execute([$_SESSION['user_id']]);
        $cart_total = $stmt_cart->fetchColumn() ?: 0;
    } catch(PDOException $e) {}
}

// Fetch Reviews & Ratings
$stmt_reviews = $pdo->prepare("
    SELECT r.*, u.first_name, u.last_name 
    FROM product_reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$stmt_reviews->execute([$product_id]);
$reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);

// Calculate Average Rating
$avg_rating = 0;
$total_reviews = count($reviews);
if ($total_reviews > 0) {
    $sum = 0;
    foreach ($reviews as $r) $sum += $r['rating'];
    $avg_rating = round($sum / $total_reviews, 1);
}

// Fetch Recently Viewed Products details
$recent_products = [];
if (!empty($_SESSION['recently_viewed'])) {
    // Exclude current product
    $recent_ids = array_filter($_SESSION['recently_viewed'], function($id) use ($product_id) {
        return $id != $product_id;
    });
    
    if (!empty($recent_ids)) {
        $ids_placeholder = implode(',', array_fill(0, count($recent_ids), '?'));
        $stmt_recent = $pdo->prepare("
            SELECT pb.id, pb.name, pp.price, b.name as brand_name,
                   (SELECT url FROM product_media WHERE product_id = pb.id ORDER BY is_primary DESC LIMIT 1) as image_url
            FROM product_base pb
            LEFT JOIN product_prices pp ON pb.id = pp.product_id
            LEFT JOIN product_specs spec ON pb.id = spec.product_id
            LEFT JOIN brands b ON spec.brand_id = b.id
            WHERE pb.id IN ($ids_placeholder)
            ORDER BY FIELD(pb.id, " . implode(',', $recent_ids) . ")
            LIMIT 6
        ");
        $stmt_recent->execute(array_values($recent_ids));
        $recent_products = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - WALKON Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
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
            --font-heading: 'Playfair Display', serif;
            --dark-border: #2A3241;
        }
        
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit',sans-serif; }
        body { 
            font-family: 'Outfit', sans-serif; 
            background: var(--bg); 
            color: var(--text-main);
            margin: 0; padding: 0;
            line-height: 1.6;
        }

        /* Back Button */
        .back-btn-container {
            max-width: 1400px;
            margin: 100px auto 0;
            padding: 0 2rem;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-main);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .back-btn:hover {
            color: var(--primary);
            transform: translateX(-5px);
        }
        
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

        /* Cart Badge Styles matching Index.php */
        .cart-badge {
          position: absolute; top: -5px; right: -8px;
          background: #f97316; color: #ffffff;
          font-size: 0.65rem; font-weight: 800; min-width: 18px; height: 18px;
          border-radius: 50%; display: flex; align-items: center; justify-content: center;
          border: 2px solid var(--primary);
        }

        /* Footer Refined */
        footer {
          background: #05070A; border-top: 1px solid var(--dark-border);
          padding: 80px 0 40px; color: #fff;
          margin-top: 100px;
        }
        .footer-container {
            max-width: 1400px; margin: 0 auto; padding: 0 2rem;
            display: grid; grid-template-columns: 1.2fr 2fr; gap: 4rem;
        }
        
        .footer-card {
            background: #0f131f; 
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 24px; padding: 3rem;
            display: flex; flex-direction: column; gap: 1.5rem;
        }
        .footer-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-text { font-family: var(--font-heading); font-size: 24px; font-weight: 700; line-height: 1; }
        .footer-desc { color: rgba(255,255,255,0.8); font-size: 0.95rem; line-height: 1.6; margin-bottom: 0.5rem; }
        
        .contact-info { display: flex; flex-direction: column; gap: 0.8rem; }
        .contact-item { display: flex; align-items: center; gap: 10px; color: #fff; font-size: 0.9rem; }
        .contact-item i { color: var(--primary); width: 20px; }
        
        .social-links { display: flex; gap: 1rem; margin-top: 1rem; }
        .social-btn {
            width: 40px; height: 40px; border-radius: 10px;
            background: rgba(255,255,255,0.05);
            display: flex; align-items: center; justify-content: center;
            color: #94a3b8; text-decoration: none; transition: 0.3s;
        }
        .social-btn:hover { background: var(--primary); color: #000; transform: translateY(-3px); }
        
        .footer-nav-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
        .footer-col h4 {
            color: #10b981; font-size: 0.85rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.5rem;
        }
        .footer-links { list-style: none; padding: 0; margin: 0; }
        .footer-links li { margin-bottom: 1rem; }
        .footer-links a { color: #ffffff; text-decoration: none; font-size: 0.95rem; transition: 0.3s; }
        .footer-links a:hover { color: #10b981; padding-left: 5px; }

        /* Wishlist Button Styles */
        .btn-wishlist {
            width: 65px; height: 65px; border-radius: 16px;
            border: 2px solid #e2e8f0; background: white;
            color: var(--text-light); font-size: 1.5rem;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: 0.3s;
        }
        .btn-wishlist:hover { border-color: #ef4444; color: #ef4444; }
        .btn-wishlist.active { border-color: #ef4444; color: #ef4444; }
        .btn-wishlist.active i { font-weight: 900; }

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
        .btn-outline:hover { background: rgba(5, 7, 10, 0.05); transform: translateY(-3px); }

        /* Description */
        .description-tabs { border-bottom: 1px solid var(--gray-100); margin-top: 60px; display: flex; gap: 40px; margin-bottom: 40px; }
        .tab-trigger { padding: 15px 0; font-weight: 700; color: var(--text-light); cursor: pointer; position: relative; font-size: 1.1rem; }
        .tab-trigger.active { color: var(--gray-900); }
        .tab-trigger.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 3px; background: var(--primary); }

        /* Zoom Effect */
        .main-img-wrap { position: relative; overflow: hidden; cursor: crosshair; }
        .zoom-lens { position: absolute; border: 1px solid var(--dark-border); width: 150px; height: 150px; background: rgba(255,255,255,0.2); display: none; pointer-events: none; border-radius: 50%; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .zoom-result { position: absolute; top: 0; right: -500px; width: 450px; height: 450px; border: 1px solid var(--gray-100); background-repeat: no-repeat; background-color: white; display: none; z-index: 100; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); }

        /* Trust Blocks */
        .trust-panel { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 40px; }
        .trust-card { background: var(--gray-50); padding: 20px; border-radius: 16px; display: flex; align-items: center; gap: 15px; border: 1px solid transparent; transition: 0.3s; }
        .trust-card:hover { border-color: var(--primary); background: #fff; }
        .trust-icon { width: 40px; height: 40px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.2rem; }
        .trust-info h4 { font-size: 0.9rem; font-weight: 700; margin-bottom: 2px; }
        .trust-info p { font-size: 0.75rem; color: var(--text-light); margin: 0; }

        /* Pincode Check */
        .delivery-check { background: var(--gray-100); padding: 25px; border-radius: 20px; margin-bottom: 40px; }
        .check-header { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; font-weight: 700; font-size: 0.95rem; }
        .check-input-wrap { display: flex; gap: 10px; }
        .check-input { flex: 1; padding: 12px 20px; border-radius: 12px; border: 1px solid #e2e8f0; font-weight: 600; outline: none; }
        .btn-check { padding: 12px 25px; background: var(--gray-900); color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; }

        /* Modals */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; display: none; align-items: center; justify-content: center; backdrop-filter: blur(10px); }
        .modal-content { background: white; width: 90%; max-width: 600px; border-radius: 30px; padding: 40px; position: relative; animation: slideUp 0.4s ease; }
        @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .close-modal { position: absolute; top: 25px; right: 25px; font-size: 1.5rem; cursor: pointer; color: var(--text-light); }

        <!-- END NEW MODALS -->

        <!-- Buy Action Blocks -->
        .btn-alert { width: 100%; padding: 15px; border-radius: 12px; border: 1px dashed var(--gray-900); background: transparent; color: var(--gray-900); font-weight: 700; margin-bottom: 30px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-alert:hover { background: var(--gray-900); color: white; border-style: solid; }

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
            <a href="wishlist.php">Wishlist</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
            <?php endif; ?>
            
            <!-- Cart Button -->
            <a href="cart.php" style="display: flex; align-items: center; gap: 6px;">
                <div style="position: relative;">
                    <i class="fas fa-shopping-cart" style="font-size: 1.1rem; color: #e2e8f0;"></i>
                    <div class="cart-badge" id="navCartBadge"><?= $cart_total ?></div>
                </div>
            </a>

            <div style="display: flex; align-items: center; gap: 1.5rem; margin-left: 1rem;">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" style="text-decoration: none; color: #fff; font-weight: 600; font-size: 0.9rem;">Logout</a>
                <?php else: ?>
                    <a href="login.php" style="text-decoration: none; color: #fff; font-weight: 600; font-size: 0.9rem;">Login</a>
                <?php endif; ?>
                <a href="start_selling.php" class="btn btn-primary" style="padding: 0.8rem 1.8rem; border-radius: 50px; font-size: 0.9rem; gap: 8px;">
                    Start Selling <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="back-btn-container">
    <a href="javascript:history.back()" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

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
        <div class="main-img-wrap" id="imgWrap" onmousemove="zoom(event)" onmouseleave="hideZoom()">
            <img src="<?php echo htmlspecialchars($main_image); ?>" id="mainImage" class="main-img">
            <div id="lens" class="zoom-lens"></div>
            <div id="zoomResult" class="zoom-result"></div>
            
            <button onclick="open360Modal()" style="position: absolute; bottom: 20px; left: 20px; background: rgba(0,0,0,0.6); color: white; border: none; padding: 10px 20px; border-radius: 50px; font-weight: 700; font-size: 0.8rem; cursor: pointer; backdrop-filter: blur(5px); display: flex; align-items: center; gap: 8px; z-index: 10;">
                <i class="fas fa-arrows-spin"></i> 360° VIEW
            </button>
        </div>
    </div>

    <!-- RIGHT: PRODUCT INFO -->
    <div class="info-container">
        <span class="brand-badge"><?php echo htmlspecialchars($product['brand_name'] ?: 'WALKON'); ?></span>
        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
        <div class="price-tag" style="margin-bottom: 15px;">₹<?php echo number_format($product['price'] ?: 15995); ?></div>
        
        <div class="stock-status" style="margin-bottom: 40px; font-weight: 700; font-size: 1.1rem; color: <?php echo ($product['available_quantity'] > 0) ? 'var(--primary)' : '#dc2626'; ?>;">
            <?php if ($product['available_quantity'] > 0): ?>
                <i class="fas fa-check-circle"></i> In Stock (<?php echo intval($product['available_quantity']); ?> available)
            <?php else: ?>
                <i class="fas fa-times-circle"></i> Out of Stock
            <?php endif; ?>
        </div>

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

        <!-- NEW: SIZE SELECTOR & GUIDE -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <span class="section-label" style="margin: 0;">Select Size</span>
            <button onclick="openModal('sizeModal')" style="background: none; border: none; color: var(--primary); font-weight: 700; font-size: 0.85rem; cursor: pointer; text-decoration: underline;">Size Guide & Fit Finder</button>
        </div>
        <div class="selector-row">
            <?php 
            $display_sizes = !empty($sizes) ? $sizes : ['UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11'];
            foreach ($display_sizes as $s): ?>
                <button class="size-btn" onclick="selectSize(this)"><?php echo htmlspecialchars($s); ?></button>
            <?php endforeach; ?>
        </div>

        <div class="delivery-check">
            <div class="check-header">
                <i class="fas fa-truck-fast" style="color: var(--primary);"></i> Check Delivery & COD
            </div>
            <div class="check-input-wrap">
                <input type="text" class="check-input" placeholder="Enter Pincode (e.g. 686001)" id="pincode">
                <button class="btn-check" onclick="checkPincode()">CHECK</button>
            </div>
            <div id="pincodeResult" style="font-size: 0.85rem; margin-top: 10px; font-weight: 600; display: none;"></div>
        </div>

        <button class="btn-alert" onclick="setPriceAlert()">
            <i class="fas fa-bell"></i> Notify me on Price Drop or Restock
        </button>

        <!-- QUANTITY SELECTOR -->
        <div class="quantity-selector" style="margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
            <span class="section-label" style="margin: 0; min-width: 80px;">Quantity</span>
            <div style="display: flex; align-items: center; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; height: 48px; background: white;">
                <button onclick="decrementQty()" style="width: 45px; height: 100%; background: transparent; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-main); font-weight: 500;">-</button>
                <input type="number" id="buyQty" value="1" min="1" max="<?= intval($product['available_quantity'] ?: 0) ?>" style="width: 50px; height: 100%; border: none; text-align: center; font-size: 1.1rem; font-weight: 700; color: var(--gray-900); outline: none; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; pointer-events: none;" readonly>
                <button onclick="incrementQty()" style="width: 45px; height: 100%; background: transparent; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-main); font-weight: 500;">+</button>
            </div>
            <span style="font-size: 0.85rem; color: var(--text-light);">(Max: <?= intval($product['available_quantity'] ?: 0) ?> available)</span>
        </div>

        <div class="actions">
            <?php if ($product['available_quantity'] > 0): ?>
                <button class="btn-cart" id="addToCartBtn" onclick="addToCart(<?= $product_id ?>)">Add to Cart</button>
            <?php else: ?>
                <button class="btn-cart" style="background:#94a3b8; cursor:not-allowed;" disabled>Out of Stock</button>
            <?php endif; ?>
            <button class="btn-wishlist <?= $is_in_wishlist ? 'active' : '' ?>" id="wishlistBtn" onclick="toggleWishlist(<?= $product_id ?>)" title="Add to Wishlist">
                <i class="<?= $is_in_wishlist ? 'fas' : 'far' ?> fa-heart"></i>
            </button>
            <?php if ($product['available_quantity'] > 0): ?>
                <button class="btn-buy" id="payProceedBtn" onclick="proceedPayment(this)">Buy Now</button>
            <?php else: ?>
                <button class="btn-buy" style="background:#cbd5e1; color:#64748b; cursor:not-allowed;" disabled>Unavailable</button>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'store'): ?>
            <!-- Stock for Reselling Action -->
            <div style="margin-bottom: 40px;">
                <button id="stockBtn" 
                        onclick="stockThisProduct(<?= $product_id ?>)"
                        style="width: 100%; padding: 20px; border-radius: 16px; border: 2px solid var(--primary); background: transparent; color: var(--primary); font-size: 1.1rem; font-weight: 800; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 12px;">
                    <i class="fas fa-boxes"></i> Stock This Product for My Store
                </button>
                <p style="font-size: 0.8rem; color: var(--text-light); text-align: center; margin-top: 10px;">
                    Create your own listing of this product to manage price and inventory.
                </p>
            </div>
        <?php endif; ?>
        
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

        <!-- DESCRIPTION & REVIEWS TABS -->
        <div class="description-tabs">
            <div class="tab-trigger active" onclick="switchTab(this, 'desc')">Description</div>
            <div class="tab-trigger" onclick="switchTab(this, 'specs')">Specifications</div>
            <div class="tab-trigger" onclick="switchTab(this, 'reviews')">Reviews (<?php echo $total_reviews; ?>)</div>
            <div class="tab-trigger" onclick="switchTab(this, 'qa')">Q&A</div>
        </div>

        <div id="tab-desc" class="tab-content">
            <h3 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 20px; color: var(--gray-900);">About this item</h3>
            <p style="color: var(--text-light); line-height: 1.8; font-size: 1.1rem;"><?php echo nl2br(htmlspecialchars($product['description'] ?: 'The iconic sneaker that defined a generation. Premium leather and classic design.')); ?></p>
        </div>

        <div id="tab-specs" class="tab-content" style="display: none;">
            <div class="specs-grid">
                 <!-- Repeated specs or detailed ones -->
                 <div class="spec-item"><span class="spec-label">Gender</span><span class="spec-value"><?= $product['gender'] ?></span></div>
                 <div class="spec-item"><span class="spec-label">Season</span><span class="spec-value"><?= $product['season'] ?></span></div>
                 <div class="spec-item"><span class="spec-label">Occasion</span><span class="spec-value"><?= $product['occasion'] ?></span></div>
                 <div class="spec-item"><span class="spec-label">Heel Height</span><span class="spec-value"><?= $product['heel_height'] ?></span></div>
            </div>
        </div>

        <div id="tab-reviews" class="tab-content" style="display: none;">
            <div style="display: flex; gap: 40px; margin-bottom: 40px; align-items: center;">
                <div style="text-align: center;">
                    <div style="font-size: 3.5rem; font-weight: 900; color: var(--gray-900);"><?php echo $avg_rating; ?></div>
                    <div style="color: #fbbf24; font-size: 1.2rem;">
                        <?php
                        for($i=1; $i<=5; $i++) {
                            if($i <= $avg_rating) echo '<i class="fas fa-star"></i>';
                            elseif($i - 0.5 <= $avg_rating) echo '<i class="fas fa-star-half-alt"></i>';
                            else echo '<i class="far fa-star"></i>';
                        }
                        ?>
                    </div>
                    <div style="font-size: 0.85rem; color: var(--text-light); margin-top: 5px;">Based on <?php echo $total_reviews; ?> reviews</div>
                </div>
                <button class="btn-check" style="background: var(--primary);" onclick="location.href='write_review.php?id=<?= $product_id ?>'">Write a Review</button>
            </div>
            
            <div class="reviews-list">
                <?php if ($total_reviews > 0): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item" style="border-bottom: 1px solid var(--gray-100); padding-bottom: 20px; margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <div>
                                    <strong style="font-size: 1.1rem;"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></strong>
                                    <span style="color: #fbbf24; margin-left: 10px;">
                                        <?php for($i=0; $i<$review['rating']; $i++) echo '<i class="fas fa-star"></i>'; ?>
                                    </span>
                                </div>
                                <span style="font-size: 0.85rem; color: var(--text-light);"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                            </div>
                            <p style="color: var(--text-main); line-height: 1.6;"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-light);">No reviews yet. Be the first to review this product!</p>
                <?php endif; ?>
            </div>
        </div>

        <div id="tab-qa" class="tab-content" style="display: none;">

            <style>
                /* ── Q&A Section ── */
                .qa-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 14px; }
                .qa-header h3 { font-size: 1.4rem; font-weight: 800; color: var(--gray-900); margin: 0; }
                .qa-count-badge { background: var(--primary); color: #fff; font-size: 0.7rem; font-weight: 800; padding: 3px 10px; border-radius: 50px; vertical-align: middle; margin-left: 8px; }

                /* Search bar */
                .qa-search-wrap { position: relative; width: 260px; }
                .qa-search-wrap input {
                    width: 100%; padding: 10px 16px 10px 38px;
                    border: 1.5px solid var(--gray-100);
                    border-radius: 50px; font-size: 0.85rem;
                    background: #f9fafb; color: var(--gray-900);
                    outline: none; font-family: 'Outfit', sans-serif;
                    transition: 0.2s;
                }
                .qa-search-wrap input:focus { border-color: var(--primary); background: #fff; }
                .qa-search-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 0.85rem; }

                /* Ask box */
                .qa-ask-box {
                    background: linear-gradient(135deg, #f0fdf9 0%, #ecfdf5 100%);
                    border: 1.5px solid rgba(16,185,129,0.2);
                    border-radius: 20px; padding: 24px; margin-bottom: 32px;
                }
                .qa-ask-box h4 { font-size: 1rem; font-weight: 800; color: var(--gray-900); margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
                .qa-ask-box h4 i { color: var(--primary); }
                .qa-ask-box p { font-size: 0.82rem; color: var(--text-light); margin-bottom: 14px; }
                .qa-textarea {
                    width: 100%; padding: 14px 16px;
                    border: 1.5px solid rgba(16,185,129,0.25);
                    border-radius: 14px; font-size: 0.9rem;
                    font-family: 'Outfit', sans-serif;
                    resize: vertical; min-height: 80px;
                    background: #fff; color: var(--gray-900);
                    outline: none; transition: 0.2s;
                    box-sizing: border-box;
                }
                .qa-textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }
                .qa-ask-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 12px; flex-wrap: wrap; gap: 10px; }
                .qa-tags { display: flex; gap: 6px; flex-wrap: wrap; }
                .qa-tag {
                    background: rgba(16,185,129,0.1); color: var(--primary);
                    font-size: 0.72rem; font-weight: 700; padding: 4px 10px;
                    border-radius: 50px; cursor: pointer; border: 1px solid rgba(16,185,129,0.2);
                    transition: 0.2s; user-select: none;
                }
                .qa-tag:hover, .qa-tag.active { background: var(--primary); color: #fff; }
                .qa-submit-btn {
                    background: var(--primary); color: #fff;
                    border: none; padding: 11px 26px; border-radius: 50px;
                    font-size: 0.88rem; font-weight: 800;
                    cursor: pointer; display: flex; align-items: center; gap: 8px;
                    transition: 0.2s; font-family: 'Outfit', sans-serif;
                }
                .qa-submit-btn:hover { background: #059669; transform: translateY(-1px); }

                /* Q&A list */
                .qa-list { display: flex; flex-direction: column; gap: 20px; }
                .qa-item {
                    border: 1.5px solid var(--gray-100);
                    border-radius: 18px; overflow: hidden;
                    transition: 0.2s;
                }
                .qa-item:hover { border-color: rgba(16,185,129,0.2); box-shadow: 0 4px 16px rgba(0,0,0,0.05); }
                .qa-question {
                    background: #f9fafb; padding: 18px 22px;
                    display: flex; align-items: flex-start; gap: 14px;
                    border-bottom: 1px solid var(--gray-100);
                }
                .qa-q-icon {
                    width: 34px; height: 34px; flex-shrink: 0;
                    background: var(--primary); color: #fff;
                    border-radius: 10px; display: flex; align-items: center; justify-content: center;
                    font-weight: 900; font-size: 0.9rem;
                }
                .qa-q-text { font-size: 0.95rem; font-weight: 700; color: var(--gray-900); line-height: 1.5; }
                .qa-q-meta { font-size: 0.75rem; color: var(--text-light); margin-top: 3px; }
                .qa-answer { padding: 18px 22px; display: flex; align-items: flex-start; gap: 14px; }
                .qa-a-icon {
                    width: 34px; height: 34px; flex-shrink: 0;
                    background: #f3f4f6; color: #6b7280;
                    border-radius: 10px; display: flex; align-items: center; justify-content: center;
                    font-weight: 900; font-size: 0.9rem;
                }
                .qa-a-icon.seller-answer { background: rgba(16,185,129,0.1); color: var(--primary); }
                .qa-a-body { flex: 1; }
                .qa-a-name { font-size: 0.75rem; font-weight: 800; color: var(--text-light); margin-bottom: 4px; display: flex; align-items: center; gap: 6px; }
                .qa-seller-badge {
                    background: var(--primary); color: #fff;
                    font-size: 0.6rem; padding: 2px 7px; border-radius: 50px;
                    font-weight: 800; letter-spacing: 0.5px;
                }
                .qa-a-text { font-size: 0.9rem; color: var(--gray-900); line-height: 1.7; }
                .qa-a-footer { display: flex; align-items: center; gap: 16px; margin-top: 10px; }
                .qa-helpful-btn {
                    background: none; border: 1.5px solid var(--gray-100);
                    border-radius: 50px; padding: 5px 14px;
                    font-size: 0.75rem; font-weight: 700; color: var(--text-light);
                    cursor: pointer; display: flex; align-items: center; gap: 6px;
                    transition: 0.2s; font-family: 'Outfit', sans-serif;
                }
                .qa-helpful-btn:hover, .qa-helpful-btn.voted {
                    background: #ecfdf5; border-color: var(--primary); color: var(--primary);
                }
                .qa-helpful-btn i { font-size: 0.8rem; }

                /* Empty / loading state */
                .qa-empty { text-align: center; padding: 40px 20px; }
                .qa-empty i { font-size: 2.5rem; color: #d1d5db; margin-bottom: 12px; display: block; }
                .qa-empty p { color: var(--text-light); font-size: 0.9rem; }

                /* Filter chips */
                .qa-filters { display: flex; gap: 8px; margin-bottom: 22px; flex-wrap: wrap; }
                .qa-filter-chip {
                    background: #f3f4f6; color: var(--text-light);
                    border: 1.5px solid transparent; border-radius: 50px;
                    padding: 6px 16px; font-size: 0.78rem; font-weight: 700;
                    cursor: pointer; transition: 0.2s; font-family: 'Outfit', sans-serif;
                    user-select: none;
                }
                .qa-filter-chip:hover, .qa-filter-chip.active {
                    background: #ecfdf5; border-color: var(--primary); color: var(--primary);
                }
            </style>

            <!-- Header row -->
            <div class="qa-header">
                <h3>
                    Community Q&amp;A
                    <span class="qa-count-badge" id="qaCountBadge">4</span>
                </h3>
                <div class="qa-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="qaSearchInput" placeholder="Search questions..." oninput="filterQA(this.value)">
                </div>
            </div>

            <!-- Ask a question box -->
            <div class="qa-ask-box">
                <h4><i class="fas fa-question-circle"></i> Ask a Question</h4>
                <p>Our seller & community will answer within 24 hours.</p>
                <textarea class="qa-textarea" id="qaQuestionInput" placeholder="e.g. Is this shoe true to size? Can I use it for outdoor running?"></textarea>
                <div class="qa-ask-footer">
                    <div class="qa-tags" id="qaTags">
                        <span class="qa-tag" onclick="appendTag(this, 'Sizing')">Sizing</span>
                        <span class="qa-tag" onclick="appendTag(this, 'Material')">Material</span>
                        <span class="qa-tag" onclick="appendTag(this, 'Comfort')">Comfort</span>
                        <span class="qa-tag" onclick="appendTag(this, 'Shipping')">Shipping</span>
                        <span class="qa-tag" onclick="appendTag(this, 'Return')">Return</span>
                    </div>
                    <button class="qa-submit-btn" onclick="submitQAQuestion()">
                        <i class="fas fa-paper-plane"></i> Post Question
                    </button>
                </div>
            </div>

            <!-- Filter chips -->
            <div class="qa-filters">
                <button class="qa-filter-chip active" onclick="filterQAByType('all', this)">All Questions</button>
                <button class="qa-filter-chip" onclick="filterQAByType('seller', this)">Answered by Seller</button>
                <button class="qa-filter-chip" onclick="filterQAByType('sizing', this)">Sizing</button>
                <button class="qa-filter-chip" onclick="filterQAByType('material', this)">Material</button>
            </div>

            <!-- Q&A List -->
            <div class="qa-list" id="qaList">

                <!-- Q1 -->
                <div class="qa-item" data-type="sizing">
                    <div class="qa-question">
                        <div class="qa-q-icon">Q</div>
                        <div>
                            <div class="qa-q-text">Is this shoe true to size? I usually wear UK 9. Should I go for the same size or size up?</div>
                            <div class="qa-q-meta"><i class="fas fa-user-circle"></i> Rahul M. &nbsp;·&nbsp; 3 days ago &nbsp;·&nbsp; <span style="color:var(--primary);">Sizing</span></div>
                        </div>
                    </div>
                    <div class="qa-answer">
                        <div class="qa-a-icon seller-answer">A</div>
                        <div class="qa-a-body">
                            <div class="qa-a-name">
                                WalkOn Official Store
                                <span class="qa-seller-badge">SELLER</span>
                            </div>
                            <div class="qa-a-text">Hi Rahul! Yes, this model runs true to size. We recommend sticking with your regular UK 9. However, if you have wide feet, you may want to size up by half a size for extra comfort during longer wear sessions.</div>
                            <div class="qa-a-footer">
                                <button class="qa-helpful-btn" onclick="voteHelpful(this)">
                                    <i class="fas fa-thumbs-up"></i> Helpful <span class="vote-count">(14)</span>
                                </button>
                                <span style="font-size:0.75rem;color:var(--text-light);">2 days ago</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Q2 -->
                <div class="qa-item" data-type="material">
                    <div class="qa-question">
                        <div class="qa-q-icon">Q</div>
                        <div>
                            <div class="qa-q-text">What is the outer material? Is it genuine leather or synthetic? How does it hold up in light rain?</div>
                            <div class="qa-q-meta"><i class="fas fa-user-circle"></i> Priya K. &nbsp;·&nbsp; 1 week ago &nbsp;·&nbsp; <span style="color:var(--primary);">Material</span></div>
                        </div>
                    </div>
                    <div class="qa-answer">
                        <div class="qa-a-icon seller-answer">A</div>
                        <div class="qa-a-body">
                            <div class="qa-a-name">
                                WalkOn Official Store
                                <span class="qa-seller-badge">SELLER</span>
                            </div>
                            <div class="qa-a-text">Great question! The outer is made from premium full-grain leather which is water-resistant to a degree — it'll handle light drizzle well. For heavy rain, we'd suggest applying a leather conditioner/waterproof spray. The sole is rubber, ensuring good grip on wet surfaces.</div>
                            <div class="qa-a-footer">
                                <button class="qa-helpful-btn" onclick="voteHelpful(this)">
                                    <i class="fas fa-thumbs-up"></i> Helpful <span class="vote-count">(8)</span>
                                </button>
                                <span style="font-size:0.75rem;color:var(--text-light);">6 days ago</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Q3 — Community answered -->
                <div class="qa-item" data-type="sizing">
                    <div class="qa-question">
                        <div class="qa-q-icon">Q</div>
                        <div>
                            <div class="qa-q-text">Can this be used for long-distance running or is it more suited for casual daily wear?</div>
                            <div class="qa-q-meta"><i class="fas fa-user-circle"></i> Aakash R. &nbsp;·&nbsp; 2 weeks ago &nbsp;·&nbsp; <span style="color:var(--primary);">Comfort</span></div>
                        </div>
                    </div>
                    <div class="qa-answer">
                        <div class="qa-a-icon">A</div>
                        <div class="qa-a-body">
                            <div class="qa-a-name">Community Member — Deepak V.</div>
                            <div class="qa-a-text">I've been using these for 10km runs and they're fantastic! The midsole cushioning is excellent. I wouldn't call them a replacement for dedicated marathon shoes, but for everyday runs up to 15km they're very comfortable and supportive.</div>
                            <div class="qa-a-footer">
                                <button class="qa-helpful-btn" onclick="voteHelpful(this)">
                                    <i class="fas fa-thumbs-up"></i> Helpful <span class="vote-count">(21)</span>
                                </button>
                                <span style="font-size:0.75rem;color:var(--text-light);">11 days ago</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Q4 -->
                <div class="qa-item" data-type="seller">
                    <div class="qa-question">
                        <div class="qa-q-icon">Q</div>
                        <div>
                            <div class="qa-q-text">What is the return policy if the shoe doesn't fit? Can I exchange for a different size?</div>
                            <div class="qa-q-meta"><i class="fas fa-user-circle"></i> Meena S. &nbsp;·&nbsp; 3 weeks ago &nbsp;·&nbsp; <span style="color:var(--primary);">Return</span></div>
                        </div>
                    </div>
                    <div class="qa-answer">
                        <div class="qa-a-icon seller-answer">A</div>
                        <div class="qa-a-body">
                            <div class="qa-a-name">
                                WalkOn Official Store
                                <span class="qa-seller-badge">SELLER</span>
                            </div>
                            <div class="qa-a-text">Absolutely! We offer a hassle-free 30-day return and size exchange policy. Simply raise a return request through your "My Orders" page and we'll arrange a free pickup. Replacement will be dispatched within 3–5 business days once we receive the returned item.</div>
                            <div class="qa-a-footer">
                                <button class="qa-helpful-btn" onclick="voteHelpful(this)">
                                    <i class="fas fa-thumbs-up"></i> Helpful <span class="vote-count">(6)</span>
                                </button>
                                <span style="font-size:0.75rem;color:var(--text-light);">20 days ago</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /.qa-list -->

            <script>
            // ── Q&A JS ──────────────────────────────────────────────
            function voteHelpful(btn) {
                if (btn.classList.contains('voted')) return;
                btn.classList.add('voted');
                const span = btn.querySelector('.vote-count');
                const current = parseInt(span.textContent.replace(/\D/g,''));
                span.textContent = '(' + (current + 1) + ')';
                btn.querySelector('i').style.color = 'var(--primary)';
            }

            function appendTag(el, text) {
                el.classList.toggle('active');
                const inp = document.getElementById('qaQuestionInput');
                if (el.classList.contains('active')) {
                    inp.value = (inp.value.trim() ? inp.value.trim() + ' ' : '') + '[' + text + '] ';
                }
                inp.focus();
            }

            function filterQA(query) {
                const items = document.querySelectorAll('#qaList .qa-item');
                let count = 0;
                items.forEach(item => {
                    const text = item.innerText.toLowerCase();
                    const show = text.includes(query.toLowerCase());
                    item.style.display = show ? '' : 'none';
                    if (show) count++;
                });
                document.getElementById('qaCountBadge').textContent = count;
            }

            function filterQAByType(type, chip) {
                document.querySelectorAll('.qa-filter-chip').forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                const items = document.querySelectorAll('#qaList .qa-item');
                let count = 0;
                items.forEach(item => {
                    const dtype = item.getAttribute('data-type');
                    const show = type === 'all' || dtype === type;
                    item.style.display = show ? '' : 'none';
                    if (show) count++;
                });
                document.getElementById('qaCountBadge').textContent = count;
            }

            function submitQAQuestion() {
                const inp = document.getElementById('qaQuestionInput');
                const text = inp.value.trim();
                if (!text) { inp.style.borderColor = '#ef4444'; inp.focus(); return; }
                inp.style.borderColor = '';

                // Build new Q card
                const list  = document.getElementById('qaList');
                const now   = 'Just now';
                const card  = document.createElement('div');
                card.className = 'qa-item';
                card.setAttribute('data-type', 'all');
                card.innerHTML = `
                    <div class="qa-question">
                        <div class="qa-q-icon">Q</div>
                        <div>
                            <div class="qa-q-text">${text.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
                            <div class="qa-q-meta"><i class="fas fa-user-circle"></i> You &nbsp;·&nbsp; ${now}</div>
                        </div>
                    </div>
                    <div class="qa-answer">
                        <div class="qa-a-icon">A</div>
                        <div class="qa-a-body">
                            <div class="qa-a-name">Awaiting answer…</div>
                            <div class="qa-a-text" style="color:var(--text-light);font-style:italic;">Our seller or a community member will respond within 24 hours. You'll be notified by email.</div>
                        </div>
                    </div>`;

                list.prepend(card);
                card.style.animation = 'fadeInUp 0.4s ease';
                inp.value = '';
                document.querySelectorAll('.qa-tag').forEach(t => t.classList.remove('active'));

                const badge = document.getElementById('qaCountBadge');
                badge.textContent = parseInt(badge.textContent) + 1;

                // Scroll into view
                card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            </script>
        </div>
    </div>
</div>

<?php if (!empty($recent_products)): ?>
<section class="recently-viewed" style="max-width: 1400px; margin: 80px auto; padding: 0 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px;">
        <div>
            <span style="color: var(--primary); font-weight: 800; font-size: 0.85rem; letter-spacing: 2px; text-transform: uppercase;">Based on your activity</span>
            <h2 style="font-size: 2.2rem; font-weight: 900; color: var(--gray-900); margin-top: 5px;">Recently Viewed</h2>
        </div>
        <a href="shop.php" style="color: var(--text-light); text-decoration: none; font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-light)'">
            Explore All <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <style>
        .recent-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 25px; }
        .recent-card {
            background: #fff; border: 1px solid var(--gray-100); border-radius: 20px; padding: 15px;
            text-decoration: none; color: inherit; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex; flex-direction: column; position: relative;
        }
        .recent-card:hover { transform: translateY(-8px); border-color: var(--primary); box-shadow: 0 15px 35px rgba(0,0,0,0.06); }
        .recent-img-box {
            width: 100%; height: 180px; background: var(--gray-50); border-radius: 14px;
            display: flex; align-items: center; justify-content: center; padding: 15px; margin-bottom: 15px;
        }
        .recent-img-box img { max-width: 100%; max-height: 100%; object-fit: contain; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.08)); }
        .recent-brand { font-size: 0.65rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; display: block; }
        .recent-name { font-size: 0.95rem; font-weight: 700; color: var(--gray-900); margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
        .recent-price { font-size: 1.1rem; font-weight: 900; color: var(--gray-900); }
    </style>

    <div class="recent-grid">
        <?php foreach ($recent_products as $rp): ?>
            <a href="product_detail.php?id=<?= $rp['id'] ?>" class="recent-card">
                <div class="recent-img-box">
                    <img src="<?= htmlspecialchars($rp['image_url'] ?: 'https://via.placeholder.com/300') ?>" alt="<?= htmlspecialchars($rp['name']) ?>">
                </div>
                <span class="recent-brand"><?= htmlspecialchars($rp['brand_name'] ?: 'WALKON') ?></span>
                <h3 class="recent-name"><?= htmlspecialchars($rp['name']) ?></h3>
                <div class="recent-price">₹<?= number_format($rp['price']) ?></div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
    </section>
<?php endif; ?>

<!-- NOTE: The Razorpay UI will open automatically on top of the screen when 'Buy Now' is clicked. -->



<!-- 360° IMMERSIVE MODAL — REDESIGNED SPLIT-PANEL FORMAT -->
<style>
    /* ── 360 Modal Shell ── */
    #modal360 { align-items: center; justify-content: center; }

    .modal-360-wrap {
        display: flex;
        width: 92vw;
        max-width: 1060px;
        height: 88vh;
        max-height: 680px;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 40px 100px rgba(0,0,0,0.6);
        position: relative;
        font-family: 'Outfit', sans-serif;
    }

    /* ── LEFT SIDEBAR ── */
    .v360-sidebar {
        width: 270px;
        flex-shrink: 0;
        background: linear-gradient(160deg, #ffffff 0%, #eef5ff 100%);
        display: flex;
        flex-direction: column;
        padding: 32px 24px;
        gap: 0;
        border-right: 1px solid #c7dcff;
        position: relative;
        z-index: 2;
    }
    .v360-sidebar::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #10b981, #06d6a0, #3b82f6);
    }

    .v360-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(16,185,129,0.12);
        border: 1px solid rgba(16,185,129,0.3);
        color: #10b981;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        padding: 5px 12px;
        border-radius: 50px;
        margin-bottom: 20px;
        width: fit-content;
    }
    .v360-badge i { font-size: 0.7rem; animation: spin360badge 3s linear infinite; }
    @keyframes spin360badge { to { transform: rotate(360deg); } }

    .v360-title {
        font-size: 1.5rem;
        font-weight: 900;
        color: #1e293b;
        line-height: 1.25;
        margin-bottom: 6px;
    }
    .v360-sub {
        font-size: 0.78rem;
        color: #64748b;
        margin-bottom: 28px;
        line-height: 1.5;
    }

    .v360-divider {
        height: 1px;
        background: #c7dcff;
        margin-bottom: 22px;
    }

    /* Controls */
    .v360-ctrl-label {
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: #475569;
        margin-bottom: 10px;
    }

    .v360-ctrl-btns {
        display: flex;
        gap: 8px;
        margin-bottom: 22px;
    }
    .v360-btn {
        flex: 1;
        background: #ffffff;
        border: 1px solid #c7dcff;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 10px 6px;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        transition: 0.2s;
        box-shadow: 0 2px 5px rgba(37, 99, 235, 0.05);
    }
    .v360-btn:hover { background: #f0f6ff; border-color: #60a5fa; color: #2563eb; transform: translateY(-2px); }
    .v360-btn i { font-size: 1rem; }

    /* Rotation slider */
    .v360-slider-wrap { margin-bottom: 22px; }
    .v360-angle-display {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    .v360-angle-val {
        font-size: 1.1rem;
        font-weight: 800;
        color: #10b981;
    }
    .v360-slider {
        width: 100%;
        -webkit-appearance: none;
        appearance: none;
        height: 4px;
        border-radius: 4px;
        background: linear-gradient(90deg, #10b981 var(--pct, 50%), rgba(255,255,255,0.1) var(--pct, 50%));
        outline: none;
        cursor: pointer;
    }
    .v360-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 18px; height: 18px;
        background: #10b981;
        border-radius: 50%;
        border: 3px solid #0f172a;
        box-shadow: 0 0 0 2px #10b981;
        cursor: grab;
    }

    /* Tips */
    .v360-tips {
        margin-top: auto;
        background: #f8fafc;
        border: 1px solid #c7dcff;
        border-radius: 14px;
        padding: 14px;
    }
    .v360-tip-row {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.75rem;
        color: #64748b;
        margin-bottom: 8px;
        line-height: 1.4;
    }
    .v360-tip-row:last-child { margin-bottom: 0; }
    .v360-tip-row i { color: #334155; font-size: 0.85rem; flex-shrink: 0; }

    /* close btn */
    .v360-close {
        position: absolute;
        top: 18px; right: 18px;
        width: 34px; height: 34px;
        background: #ffffff;
        border: 1px solid #c7dcff;
        border-radius: 50%;
        color: #64748b;
        font-size: 1.1rem;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: 0.2s;
        z-index: 10;
        line-height: 1;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.1);
    }
    .v360-close:hover { background: #fee2e2; color: #ef4444; border-color: #fca5a5; }

    /* ── RIGHT VIEWPORT ── */
    .v360-viewport {
        flex: 1;
        position: relative;
        background: radial-gradient(ellipse at 60% 40%, #ffffff 0%, #e0eeff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: grab;
        overflow: hidden;
    }
    .v360-viewport:active { cursor: grabbing; }

    /* Grid overlay */
    .v360-grid {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(16,185,129,0.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(16,185,129,0.04) 1px, transparent 1px);
        background-size: 40px 40px;
        pointer-events: none;
    }

    /* Spotlight glow */
    .v360-glow {
        position: absolute;
        width: 420px; height: 420px;
        background: radial-gradient(circle, rgba(37,99,235,0.06) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
        transition: left 0.05s, top 0.05s;
    }

    /* Rotation ring */
    .v360-ring {
        position: absolute;
        bottom: 40px;
        left: 50%;
        transform: translateX(-50%);
        width: 180px; height: 20px;
        border-bottom: 2px solid rgba(16,185,129,0.25);
        border-radius: 50%;
        pointer-events: none;
    }
    .v360-ring::after {
        content: '';
        position: absolute;
        bottom: -4px; left: 50%; transform: translateX(-50%);
        width: 8px; height: 8px;
        background: #10b981;
        border-radius: 50%;
        box-shadow: 0 0 12px #10b981;
    }

    /* HUD bar */
    .v360-hud {
        position: absolute;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(14px);
        border: 1px solid #c7dcff;
        padding: 9px 22px;
        border-radius: 50px;
        color: #1e293b;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 1px;
        pointer-events: none;
        white-space: nowrap;
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.1);
    }
    .v360-hud i { color: #10b981; }
    .v360-hud-dot {
        width: 6px; height: 6px;
        background: #10b981;
        border-radius: 50%;
        animation: pulse360 1.6s ease-in-out infinite;
    }
    @keyframes pulse360 {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.4; transform: scale(0.7); }
    }

    /* The shoe image */
    #modelWrapper360 {
        width: 62%;
        max-width: 380px;
        transform-style: preserve-3d;
        transition: transform 0.08s linear;
        transform: rotateY(-20deg);
        user-select: none;
        position: relative;
        z-index: 2;
    }
    #model360Img {
        width: 100%;
        filter: drop-shadow(0 40px 60px rgba(0,0,0,0.5)) drop-shadow(0 0 30px rgba(16,185,129,0.08));
        pointer-events: none;
        user-select: none;
        -webkit-user-drag: none;
    }

    /* Scanlines (subtle texture) */
    .v360-scanlines {
        position: absolute;
        inset: 0;
        background: repeating-linear-gradient(
            0deg,
            transparent,
            transparent 3px,
            rgba(0,0,0,0.07) 3px,
            rgba(0,0,0,0.07) 4px
        );
        pointer-events: none;
        z-index: 1;
    }

    /* ── Responsive ── */
    @media (max-width: 700px) {
        .modal-360-wrap { flex-direction: column; height: 96vh; max-height: none; width: 96vw; }
        .v360-sidebar { width: 100%; padding: 20px; flex-direction: row; flex-wrap: wrap; gap: 12px; height: auto; }
        .v360-tips { display: none; }
        .v360-viewport { min-height: 320px; }
    }
</style>

<div id="modal360" class="modal-overlay" onclick="if(event.target===this)closeModal('modal360')">
    <div class="modal-360-wrap">

        <!-- ── LEFT SIDEBAR ── -->
        <div class="v360-sidebar">

            <div class="v360-badge">
                <i class="fas fa-arrows-spin"></i> 360° View
            </div>

            <div class="v360-title">Immersive<br>Product View</div>
            <div class="v360-sub">Interact with the model by dragging or using the slider below.</div>

            <div class="v360-divider"></div>

            <!-- Quick actions -->
            <div class="v360-ctrl-label">Quick Actions</div>
            <div class="v360-ctrl-btns">
                <button class="v360-btn" onclick="reset360Rotation()" title="Reset">
                    <i class="fas fa-undo"></i>
                    Reset
                </button>
                <button class="v360-btn" onclick="autoSpin360()" id="spinToggleBtn" title="Auto Spin">
                    <i class="fas fa-play"></i>
                    Auto
                </button>
                <button class="v360-btn" onclick="toggle360Zoom()" title="Zoom">
                    <i class="fas fa-search-plus"></i>
                    Zoom
                </button>
            </div>

            <!-- Rotation slider -->
            <div class="v360-slider-wrap">
                <div class="v360-ctrl-label">Manual Rotation</div>
                <div class="v360-angle-display">
                    <span style="color:#64748b;font-size:0.75rem;font-weight:600;">Angle</span>
                    <span class="v360-angle-val" id="v360AngleLabel">-20°</span>
                </div>
                <input type="range" class="v360-slider" id="v360SliderInput"
                       min="-180" max="180" value="-20"
                       oninput="onSliderInput(this.value)">
            </div>

            <div class="v360-divider"></div>

            <!-- Tips -->
            <div class="v360-tips">
                <div class="v360-ctrl-label" style="margin-bottom:12px;">Tips</div>
                <div class="v360-tip-row">
                    <i class="fas fa-mouse-pointer"></i>
                    Drag left/right to rotate the model freely
                </div>
                <div class="v360-tip-row">
                    <i class="fas fa-sliders-h"></i>
                    Use the slider for precise angle control
                </div>
                <div class="v360-tip-row">
                    <i class="fas fa-play-circle"></i>
                    Hit Auto Spin for a continuous 360° preview
                </div>
            </div>
        </div>

        <!-- ── RIGHT VIEWPORT ── -->
        <div class="v360-viewport" id="view360Container">
            <div class="v360-grid"></div>
            <div class="v360-glow" id="v360Glow"></div>
            <div class="v360-scanlines"></div>

            <div id="modelWrapper360">
                <img id="model360Img" src="<?php echo htmlspecialchars($main_image); ?>" alt="360 view">
            </div>

            <div class="v360-ring"></div>

            <div class="v360-hud">
                <div class="v360-hud-dot"></div>
                <i class="fas fa-hand-pointer"></i>
                DRAG TO ROTATE
            </div>

            <!-- Close button inside viewport -->
            <button class="v360-close" onclick="closeModal('modal360')">&times;</button>
        </div>

    </div>
</div>

<div id="sizeModal" class="modal-overlay" onclick="if(event.target===this)closeModal('sizeModal')">
    <div class="modal-content" style="max-width: 500px; padding: 32px; border-radius: 24px;">
        <span class="close-modal" onclick="closeModal('sizeModal')" style="top: 20px; right: 24px; font-size: 1.8rem;">&times;</span>
        
        <div style="text-align: center; margin-bottom: 24px;">
            <h2 style="font-weight: 800; color: var(--gray-900); margin-bottom: 8px; font-size: 1.5rem;">Size Guide</h2>
            <p style="color: var(--text-light); font-size: 0.95rem;">Find your perfect fit across global standards</p>
        </div>

        <style>
            .size-table { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 28px; border: 1px solid var(--gray-100); border-radius: 12px; overflow: hidden; }
            .size-table th { background: #f9fafb; padding: 14px; text-align: center; font-weight: 700; color: var(--gray-900); font-size: 0.85rem; border-bottom: 1px solid var(--gray-100); }
            .size-table td { padding: 14px; text-align: center; color: var(--text-main); font-size: 0.9rem; border-bottom: 1px solid var(--gray-100); transition: 0.2s; }
            .size-table tr:last-child td { border-bottom: none; }
            .size-table tr:hover td { background: #f0fdf9; color: var(--primary); font-weight: 700; }
            
            .fit-finder-card {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                padding: 20px 24px; border-radius: 16px;
                color: white; text-align: center;
                box-shadow: 0 10px 25px rgba(16,185,129,0.25);
                position: relative; overflow: hidden;
            }
            .fit-finder-card::before {
                content: ''; position: absolute; top: -50%; left: -50%;
                width: 200%; height: 200%;
                background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
                pointer-events: none;
            }
            .fit-finder-icon {
                font-size: 1.5rem; margin-bottom: 10px;
                display: inline-block; animation: pulseSize 2s infinite;
            }
            @keyframes pulseSize { 0%,100%{transform:scale(1);} 50%{transform:scale(1.1);} }
        </style>

        <table class="size-table">
            <thead>
                <tr>
                    <th>UK / India</th>
                    <th>US</th>
                    <th>EU</th>
                    <th>Length (CM)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>6</td><td>7</td><td>40</td><td>24.6</td></tr>
                <tr><td>7</td><td>8</td><td>41</td><td>25.4</td></tr>
                <tr><td>8</td><td>9</td><td>42.5</td><td>26.2</td></tr>
                <tr><td>9</td><td>10</td><td>44</td><td>27.1</td></tr>
                <tr><td>10</td><td>11</td><td>45</td><td>27.9</td></tr>
                <tr><td>11</td><td>12</td><td>46</td><td>28.8</td></tr>
            </tbody>
        </table>

        <!-- AI Fit Finder -->
        <div class="fit-finder-card">
            <div class="fit-finder-icon"><i class="fas fa-ruler-combined"></i></div>
            <h4 style="margin: 0 0 6px; font-weight: 800; font-size: 1.1rem;">WalkOn Fit Finder™</h4>
            <p style="margin: 0; font-size: 0.9rem; opacity: 0.95; line-height: 1.5;">
                Based on your past purchase of <b>Adidas Ultraboost (UK 9)</b>,<br>
                we recommend <span style="font-weight:800; text-decoration:underline;">UK 9</span> for this model.
            </p>
        </div>
    </div>
</div>

<script>
    // Color Image Map from PHP
    const colorImageMap = <?php echo json_encode($colorImageMap); ?>;

    function changeImage(src, el) {
        document.getElementById('mainImage').src = src;
        // Also update zoom result background
        document.getElementById('zoomResult').style.backgroundImage = "url('" + src + "')";
        
        if(el) {
            document.querySelectorAll('.thumb-box').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
        }
    }

    // ZOOM LOGIC
    function zoom(e) {
        const wrap = document.getElementById('imgWrap');
        const img = document.getElementById('mainImage');
        const lens = document.getElementById('lens');
        const res = document.getElementById('zoomResult');
        
        lens.style.display = 'block';
        res.style.display = 'block';
        res.style.backgroundImage = "url('" + img.src + "')";
        
        const rect = wrap.getBoundingClientRect();
        let x = e.pageX - rect.left - window.scrollX;
        let y = e.pageY - rect.top - window.scrollY;
        
        // Prevent lens from going out
        if (x > img.width - lens.offsetWidth/2) x = img.width - lens.offsetWidth/2;
        if (x < lens.offsetWidth/2) x = lens.offsetWidth/2;
        if (y > img.height - lens.offsetHeight/2) y = img.height - lens.offsetHeight/2;
        if (y < lens.offsetHeight/2) y = lens.offsetHeight/2;
        
        lens.style.left = (x - lens.offsetWidth/2) + 'px';
        lens.style.top = (y - lens.offsetHeight/2) + 'px';
        
        const fx = res.offsetWidth / lens.offsetWidth;
        const fy = res.offsetHeight / lens.offsetHeight;
        
        res.style.backgroundSize = (img.width * fx) + 'px ' + (img.height * fy) + 'px';
        res.style.backgroundPosition = "-" + ((x - lens.offsetWidth/2) * fx) + "px -" + ((y - lens.offsetHeight/2) * fy) + "px";
    }

    function hideZoom() {
        document.getElementById('lens').style.display = 'none';
        document.getElementById('zoomResult').style.display = 'none';
    }

    // MODAL LOGIC
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    
    function open360Modal() {
        const mainImgSrc = document.getElementById('mainImage').src;
        document.getElementById('model360Img').src = mainImgSrc;
        openModal('modal360');
    }

    // ===== 360° INTERACTIVE LOGIC (NEW) =====
    let isRotationDragging = false;
    let startRotationX;
    let currentRotation = -20;
    let spinAnimId = null;
    let isSpinning = false;
    let is360Zoomed = false;

    function get360Elements() {
        return {
            wrapper: document.getElementById('modelWrapper360'),
            container: document.getElementById('view360Container'),
            slider: document.getElementById('v360SliderInput'),
            label: document.getElementById('v360AngleLabel'),
        };
    }

    function apply360Rotation(deg) {
        const { wrapper, slider, label } = get360Elements();
        currentRotation = deg;
        if (wrapper) wrapper.style.transform = `rotateY(${deg}deg)`;
        if (slider) {
            slider.value = deg;
            const pct = ((deg + 180) / 360 * 100).toFixed(1) + '%';
            slider.style.setProperty('--pct', pct);
        }
        if (label) label.textContent = Math.round(deg) + '°';
    }

    // Slider control
    function onSliderInput(val) {
        if (isSpinning) stopAutoSpin();
        apply360Rotation(parseFloat(val));
    }

    // Reset
    function reset360Rotation() {
        if (isSpinning) stopAutoSpin();
        apply360Rotation(-20);
    }

    // Auto Spin
    function autoSpin360() {
        if (isSpinning) { stopAutoSpin(); return; }
        isSpinning = true;
        const btn = document.getElementById('spinToggleBtn');
        if (btn) { btn.innerHTML = '<i class="fas fa-pause"></i> Stop'; btn.style.color = '#10b981'; btn.style.borderColor = 'rgba(16,185,129,0.4)'; }
        function tick() {
            currentRotation += 0.6;
            apply360Rotation(currentRotation);
            spinAnimId = requestAnimationFrame(tick);
        }
        spinAnimId = requestAnimationFrame(tick);
    }

    function stopAutoSpin() {
        isSpinning = false;
        cancelAnimationFrame(spinAnimId);
        const btn = document.getElementById('spinToggleBtn');
        if (btn) { btn.innerHTML = '<i class="fas fa-play"></i> Auto'; btn.style.color = ''; btn.style.borderColor = ''; }
    }

    // Zoom toggle
    function toggle360Zoom() {
        const wrapper = document.getElementById('modelWrapper360');
        if (!wrapper) return;
        is360Zoomed = !is360Zoomed;
        wrapper.style.width = is360Zoomed ? '85%' : '62%';
        wrapper.style.transition = 'width 0.3s ease';
    }

    window.addEventListener('load', () => {
        const { wrapper, container } = get360Elements();

        if (container) {
            // Mouse drag
            container.addEventListener('mousedown', (e) => {
                isRotationDragging = true;
                startRotationX = e.pageX;
                if (isSpinning) stopAutoSpin();
            });
            window.addEventListener('mousemove', (e) => {
                if (!isRotationDragging) return;
                const delta = e.pageX - startRotationX;
                apply360Rotation(currentRotation + delta * 0.7);
                startRotationX = e.pageX;
            });
            window.addEventListener('mouseup', () => { isRotationDragging = false; });

            // Touch drag
            container.addEventListener('touchstart', (e) => {
                isRotationDragging = true;
                startRotationX = e.touches[0].pageX;
                if (isSpinning) stopAutoSpin();
            }, { passive: true });
            window.addEventListener('touchmove', (e) => {
                if (!isRotationDragging) return;
                const delta = e.touches[0].pageX - startRotationX;
                apply360Rotation(currentRotation + delta * 0.7);
                startRotationX = e.touches[0].pageX;
            }, { passive: true });
            window.addEventListener('touchend', () => { isRotationDragging = false; });

            // Glow follows mouse
            container.addEventListener('mousemove', (e) => {
                const glow = document.getElementById('v360Glow');
                if (!glow) return;
                const rect = container.getBoundingClientRect();
                glow.style.left = (e.clientX - rect.left - 210) + 'px';
                glow.style.top  = (e.clientY - rect.top  - 210) + 'px';
            });
        }
    });

    // TAB LOGIC
    function switchTab(el, tabId) {
        document.querySelectorAll('.tab-trigger').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
        el.classList.add('active');
        document.getElementById('tab-' + tabId).style.display = 'block';
    }

    // PINCODE LOGIC
    function checkPincode() {
        const pin = document.getElementById('pincode').value;
        const res = document.getElementById('pincodeResult');
        if (pin.length < 6) { showToast('Input Required', 'Enter valid pincode'); return; }
        
        res.style.display = 'block';
        res.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking availability...';
        
        setTimeout(() => {
            res.innerHTML = '<span style="color: #16a34a;"><i class="fas fa-check-circle"></i> Delivery available by tomorrow! COD & Express Pickup enabled.</span>';
        }, 800);
    }

    // ALERT LOGIC
    // ===== TOAST NOTIFICATION LOGIC =====
    // Inject Styles
    const toastStyle = document.createElement('style');
    toastStyle.innerHTML = `
        .toast-container {
            position: fixed; top: 100px; right: 20px; z-index: 10000;
            display: flex; flex-direction: column; gap: 10px; pointer-events: none;
        }
        .toast {
            min-width: 320px; max-width: 400px; padding: 16px 20px; border-radius: 16px;
            background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.05);
            display: flex; align-items: flex-start; gap: 14px;
            transform: translateX(120%); transition: transform 0.5s cubic-bezier(0.19, 1, 0.22, 1);
            border-left: 5px solid #10b981; font-family: 'Outfit', sans-serif;
            pointer-events: auto;
        }
        .toast.show { transform: translateX(0); }
        .toast-icon {
            width: 28px; height: 28px; background: rgba(16,185,129,0.15);
            color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem; flex-shrink: 0; margin-top: 2px;
        }
        .toast-content { flex: 1; }
        .toast-title { font-size: 0.95rem; font-weight: 700; color: #111827; margin-bottom: 4px; }
        .toast-msg { font-size: 0.85rem; color: #6b7280; line-height: 1.5; }
        .toast-close { cursor: pointer; color: #9ca3af; transition: 0.2s; font-size: 1.2rem; line-height: 1; padding: 0 4px; }
        .toast-close:hover { color: #ef4444; }
    `;
    document.head.appendChild(toastStyle);

    // Create Container
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container';
    document.body.appendChild(toastContainer);

    function showToast(title, message) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = `
            <div class="toast-icon"><i class="fas fa-check"></i></div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-msg">${message}</div>
            </div>
            <div class="toast-close" onclick="this.parentElement.classList.remove('show'); setTimeout(()=>this.parentElement.remove(), 500)">&times;</div>
        `;
        toastContainer.appendChild(toast);
        
        // Trigger animation
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 500);
        }, 5000);
    }

    function setPriceAlert() {
        showToast('Alert Set Successfully!', "We'll notify you on WhatsApp/Email when the price drops or the product is back in stock.");
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
            const fallbackImageMap = {
                'jet black': 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=600&auto=format&fit=crop&q=80',
                'ivory white': 'https://images.unsplash.com/photo-1595950653106-6c9ebd614c3a?w=600&auto=format&fit=crop&q=80',
                'midnight navy': 'https://plus.unsplash.com/premium_photo-1682125177822-63c27a3830ea?w=600&auto=format&fit=crop&q=80',
                'forest green': 'https://images.unsplash.com/photo-1588600878108-578307a3cc9d?w=600&auto=format&fit=crop&q=80',
                'crimson red': 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop&q=80',
                'royal gold': 'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=600&auto=format&fit=crop&q=80',
                'sky blue': 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=600&auto=format&fit=crop&q=80'
            };

            let newSrc = null;
            if(colorImageMap[key]) {
                newSrc = colorImageMap[key];
            } else if (fallbackImageMap[key]) {
                newSrc = fallbackImageMap[key];
            }

            if(newSrc) {
                document.getElementById('mainImage').src = newSrc;
                const zoomBox = document.getElementById('zoomResult');
                if(zoomBox) zoomBox.style.backgroundImage = "url('" + newSrc + "')";

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

    function stockThisProduct(productId) {
        const btn = document.getElementById('stockBtn');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        btn.disabled = true;

        fetch('stock_product.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.style.background = 'var(--primary)';
                btn.style.color = '#000';
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Success! View in My Inventory';
                btn.onclick = () => window.location.href = 'my_listings.php';
            } else {
                showToast('Stock Status', data.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error('Error:', err);
            showToast('Error', 'Stocking failed. Please try again.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    function toggleWishlist(productId) {
        const btn = document.getElementById('wishlistBtn');
        const icon = btn.querySelector('i');
        
        fetch('toggle_wishlist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.action === 'added') {
                    btn.classList.add('active');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                } else {
                    btn.classList.remove('active');
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
            } else {
                if(data.message && data.message.includes('login')) window.location.href = 'login.php';
                else showToast('Wishlist', data.message || 'Error updating wishlist');
            }
        })
        .catch(err => {
            console.error('Wishlist error:', err);
            showToast('Error', 'Could not update wishlist. Try again later.');
        });
    }

    const maxQty = <?= intval($product['available_quantity'] ?: 0) ?>;
    
    function incrementQty() {
        const input = document.getElementById('buyQty');
        let val = parseInt(input.value) || 1;
        if (val < maxQty) {
            input.value = val + 1;
            updatePayModalAmount();
        } else {
            showToast('Limit Reached', 'You cannot select more than the available stock (' + maxQty + ').');
        }
    }
    
    function decrementQty() {
        const input = document.getElementById('buyQty');
        let val = parseInt(input.value) || 1;
        if (val > 1) {
            input.value = val - 1;
            updatePayModalAmount();
        }
    }
    
    function addToCart(productId) {
        const btn = document.getElementById('addToCartBtn');
        const qty = parseInt(document.getElementById('buyQty').value) || 1;
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        btn.disabled = true;

        fetch('api/add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId, quantity: qty })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.innerHTML = '<i class="fas fa-check"></i> Added to Cart!';
                btn.style.background = '#059669'; // Darker green
                showToast('Success', 'Item added to your cart.');
                
                // Update badge
                const badge = document.getElementById('navCartBadge');
                if (badge && data.cart_total !== undefined) {
                    badge.textContent = data.cart_total;
                    
                    // Simple pop animation for feedback
                    badge.style.transform = 'scale(1.5)';
                    setTimeout(() => badge.style.transform = 'scale(1)', 300);
                }

                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    btn.style.background = '';
                }, 2000);
            } else {
                if(data.message && data.message.includes('login')) window.location.href = 'login.php';
                else showToast('Error', data.message || 'Failed to add item to cart');
                
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error('Error:', err);
            showToast('Error', 'Could not communicate with the server.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    // ===== RAZORPAY CHECKOUT LOGIC =====
    function updatePayModalAmount() {
        // Function no longer needed for updating mock UI, but kept empty so decrementQty/incrementQty doesn't throw JS error
    }

    function proceedPayment(btn) {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        btn.disabled = true;

        const price = <?= floatval($product['price'] ?: 15995) ?>;
        const qty = parseInt(document.getElementById('buyQty').value) || 1;
        const total = price * qty;
        const amountInPaise = Math.round(total * 100);

        // Fetch Order ID from backend
        fetch('api/create_razorpay_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                amount: total,
                product_id: <?= intval($product_id) ?>,
                qty: qty
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.order_id) {
                var options = {
                    "key": "rzp_test_SJT6Nr8fIlTpbw", // USER PROVIDED TEST KEY
                    "amount": amountInPaise, 
                    "currency": "INR",
                    "name": "WALKON Premium",
                    "description": "<?= htmlspecialchars($product['name'] ?? 'Footwear Purchase') ?>",
                    "image": "assets/shoe_logo_green.png",
                    "order_id": data.order_id,
                    "handler": function (response){
                        // Verify payment in backend
                        fetch('api/verify_razorpay_payment.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_order_id: response.razorpay_order_id,
                                razorpay_signature: response.razorpay_signature
                            })
                        })
                        .then(res => res.json())
                        .then(verifyData => {
                            if(verifyData.success) {
                                showToast('Success', 'Payment completed via Razorpay!');
                                window.location.href = 'my_orders.php';
                            } else {
                                showToast("Verification Failed", verifyData.message);
                                btn.innerHTML = originalText;
                                btn.disabled = false;
                            }
                        }).catch(err => {
                            showToast("Server Error", "Payment verification failed.");
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        });
                    },
                    "prefill": {
                        "name": "<?php echo isset($_SESSION['user_id']) ? 'User' : 'Guest'; ?>",
                        "email": "customer@walkon.com",
                        "contact": "9000000000"
                    },
                    "theme": {
                        "color": "#2563eb"
                    },
                    "modal": {
                        "ondismiss": function(){
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        }
                    }
                };
                var rzp1 = new Razorpay(options);
                rzp1.on('payment.failed', function (response){
                    showToast("Payment Failed", response.error.description);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
                rzp1.open();
            } else {
                showToast("Error", "Could not initialize Razorpay: " + (data.message || 'Unknown Error'));
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }).catch(err => {
            console.error(err);
            showToast("Network Error", "Could not initialize payment.");
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
    // ===== END RAZORPAY CHECKOUT LOGIC =====

    // Check for Tab in URL Hash or Query
    window.addEventListener('load', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('review') === 'success' || window.location.hash === '#tab-reviews') {
            const reviewTab = document.querySelector('.tab-trigger[onclick*="reviews"]');
            if (reviewTab) reviewTab.click();
            if (urlParams.get('review') === 'success') {
                showToast('Review Submitted', 'Thank you for sharing your experience!');
            }
        }
    });

    function openReviewModal() {
        location.href = 'write_review.php?id=<?= $product_id ?>';
    }
</script>

<!-- Ready for the Next Step? CTA -->
<div style="max-width: 1400px; margin: 80px auto; padding: 0 2rem;">
    <div style="background: #0B0F19; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 24px; padding: 40px 60px; display: flex; align-items: center; justify-content: space-between; gap: 40px; flex-wrap: wrap; box-shadow: 0 20px 40px rgba(0,0,0,0.1); position: relative; overflow: hidden;">
        <!-- Subtle Glow -->
        <div style="position: absolute; top: -50%; left: -20%; width: 300px; height: 300px; background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%); pointer-events: none;"></div>
        
        <div style="position: relative; z-index: 2;">
            <h2 style="color: #fff; font-size: 1.8rem; font-weight: 800; margin-bottom: 10px; letter-spacing: -0.5px;">Ready for the Next Step?</h2>
            <p style="color: #94a3b8; font-size: 1rem; opacity: 0.8;">Move from Channel Sync to Global Distribution planning.</p>
        </div>
        
        <div style="position: relative; z-index: 2;">
            <a href="ai_virtual_showroom.php" class="btn" style="background: var(--primary); color: #000; padding: 1.2rem 2.5rem; border-radius: 14px; font-weight: 800; display: inline-flex; align-items: center; gap: 12px; transition: 0.3s; text-decoration: none; border: none;">
                Next: AI Virtual Showroom <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Footer Section -->
<footer>
    <div class="footer-container">
        <!-- Left Card: Branding + Contact -->
        <div class="footer-card">
            <a href="Index.php" class="footer-logo">
                <img src="assets/shoe_logo_green.png" alt="WalkOn Logo" style="height: 50px; width: auto; filter: drop-shadow(0 0 8px rgba(16, 185, 129, 0.2));">
                <div class="brand-text">
                    <span style="color: #fff;">WALK</span><span style="color: #10b981;">ON</span>
                </div>
            </a>
            
            <p class="footer-desc">
                Elevating the global footwear industry with intelligent multi-channel technology. Five networking infinite possibilities.
            </p>
            
            <div class="contact-info">
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>support@walkon.com</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>+91 90745 85775</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Kottayam, Kerala, India</span>
                </div>
            </div>
            
            <div class="social-links">
                <a href="https://twitter.com/walkon" target="_blank" class="social-btn"><i class="fab fa-twitter"></i></a>
                <a href="https://instagram.com/walkon" target="_blank" class="social-btn"><i class="fab fa-instagram"></i></a>
                <a href="https://wa.me/919074585775" target="_blank" class="social-btn"><i class="fab fa-whatsapp"></i></a>
                <a href="tel:+919074585775" class="social-btn"><i class="fas fa-phone"></i></a>
                <a href="https://facebook.com/walkon" target="_blank" class="social-btn"><i class="fab fa-facebook"></i></a>
                <a href="https://linkedin.com/company/walkon" target="_blank" class="social-btn"><i class="fab fa-linkedin"></i></a>
                <a href="https://youtube.com/@walkon" target="_blank" class="social-btn"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        
        <!-- Right: Navigation Grid -->
        <div class="footer-nav-grid">
            <div class="footer-col">
                <h4>NAVIGATION</h4>
                <ul class="footer-links">
                    <li><a href="Index.php">Home</a></li>
                    <li><a href="shop.php">Shop</a></li>
                    <li><a href="marketplaces.php">Marketplace</a></li>
                    <li><a href="sellers.php">Our Sellers</a></li>
                    <li><a href="wishlist.php">Wishlist</a></li>
                </ul>
            </div>
            
            <div class="footer-col">
                <h4>SHOPS</h4>
                <ul class="footer-links">
                    <li><a href="shop.php">All Products</a></li>
                    <li><a href="shop.php?category=2">Boots</a></li>
                    <li><a href="shop.php?category=5">Formal Shoes</a></li>
                    <li><a href="shop.php?category=4">Running Shoes</a></li>
                    <li><a href="shop.php?category=6">Sandals & Slides</a></li>
                </ul>
            </div>
            
            <div class="footer-col">
                <h4>BRANDS</h4>
                <ul class="footer-links">
                    <li><a href="shop.php">All Brands</a></li>
                    <li><a href="shop.php?brand=1">adidas</a></li>
                    <li><a href="shop.php?brand=3">Bata</a></li>
                    <li><a href="shop.php?brand=8">New Balance</a></li>
                    <li><a href="shop.php?brand=11">Nike</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<?php include 'includes/chatbot.php'; ?>
</body>
</html>
