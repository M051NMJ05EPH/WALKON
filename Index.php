<?php
include 'config.php';

// Fetch Categories from Database
try {
    $stmt = $pdo->query("SELECT id, name, image_url as image FROM categories ORDER BY id");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<!-- Database Query Failed: " . $e->getMessage() . " -->";
    $categories = []; // Fallback to empty if failed
}

// Fetch Featured Products from Database
try {
    $stmt = $pdo->query("SELECT DISTINCT pb.id, pb.name, pp.price, c.name as category_name, b.name as brand_name,
        (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as primary_image,
        (SELECT url FROM product_media pm WHERE pm.product_id = pb.id LIMIT 1) as fallback_image
        FROM product_base pb
        LEFT JOIN product_prices pp ON pb.id = pp.product_id
        LEFT JOIN categories c ON pb.category_id = c.id
        LEFT JOIN product_skus ps ON pb.id = ps.product_id
        LEFT JOIN product_specs spec ON pb.id = spec.product_id
        LEFT JOIN brands b ON spec.brand_id = b.id
        WHERE pb.status = 'published' AND pp.price IS NOT NULL
        ORDER BY pb.created_at DESC LIMIT 6");
    $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<!-- Database Query Failed: " . $e->getMessage() . " -->";
    $featured_products = []; // Fallback to empty if failed
}

$featured_products_static = [
    ["name" => "Nike Air Max 270", "category" => "Men • Running", "price" => 149.99, "old_price" => 189.99, "img" => "https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"],
    ["name" => "Air Jordan 1 Retro", "category" => "Unisex • Lifestyle", "price" => 179.99, "old_price" => null, "img" => "https://images.unsplash.com/photo-1549298916-b41d501d3772?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"],
    ["name" => "Adidas Ultraboost", "category" => "Women • Running", "price" => 180.00, "old_price" => 220.00, "img" => "https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"],
    ["name" => "Timberland Premium", "category" => "Men • Boots", "price" => 198.00, "old_price" => null, "img" => "https://images.unsplash.com/photo-1520639889313-72721e0ab9ef?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"],
    ["name" => "Puma RS-X", "category" => "Men • Sneakers", "price" => 120.00, "old_price" => 150.00, "img" => "https://images.unsplash.com/photo-1608231387042-66d1773070a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"],
    ["name" => "New Balance 550", "category"  => "Unisex • Lifestyle", "price" => 129.99, "old_price" => null, "img" => "https://images.unsplash.com/photo-1539185441755-769473a23570?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"]
];

// Use database products if available, else fallback to static
if (empty($featured_products)) {
    $featured_products = $featured_products_static;
}

// Fetch Platform Features from Database
try {
    $stmt = $pdo->query("SELECT * FROM platform_features ORDER BY id ASC");
    $platform_features = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<!-- Features Query Failed: " . $e->getMessage() . " -->";
    $platform_features = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>WALKON - Luxury Footwear Platform</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
  
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
  
  <style>
    :root {
      /* Premium Green Palette */
      --primary: #10b981;       /* Emerald 500 */
      --primary-light: #34d399; /* Emerald 400 */
      --primary-dark: #059669;  /* Emerald 700 */
      --primary-dim: rgba(16, 185, 129, 0.1);
      
      --dark-bg: #0B0F19;
      --dark-card: #151B2B;
      --dark-border: #2A3241;
      
      --text-main: #F1F5F9;
      --text-muted: #94A3B8;
      
      --font-heading: 'Playfair Display', serif;
      --font-body: 'Inter', sans-serif;
    }

    * { margin:0; padding:0; box-sizing:border-box; }
    
    body { 
      font-family: var(--font-body); 
      background: var(--dark-bg); 
      color: var(--text-main); 
      line-height: 1.6; 
      overflow-x: hidden;
    }

    h1, h2, h3, h4, h5, h6 {
      font-family: var(--font-heading);
      color: var(--text-main);
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

    /* Buttons */
    .btn {
      padding: 1rem 2.5rem; border-radius: 50px; font-weight: 600;
      text-decoration: none; transition: all 0.3s; font-size: 0.95rem;
      letter-spacing: 0.5px; display: inline-flex; align-items: center; justify-content: center;
    }
    .btn-login {
      padding: 0.6rem 1.5rem; border-radius: 6px; border: 1px solid var(--primary);
      color: var(--primary); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;
    }
    .btn-primary { 
      background: var(--primary); color: #000; border: none;
      box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
    }
    .btn-primary:hover { 
      background: #34d399; transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(16, 185, 129, 0.5);
    }
    .btn-outline {
      background: transparent; border: 1px solid rgba(255,255,255,0.3);
      color: #fff; margin-left: 1rem;
    }
    .btn-outline:hover {
      border-color: #fff; background: rgba(255,255,255,0.05);
    }

    /* Hero Refined */
    .hero {
      position: relative; min-height: 85vh;
      background: radial-gradient(circle at 60% 50%, #0d1f18 0%, #0B0F19 60%);
      display: flex; align-items: center; justify-content: center;
      padding: 120px 2rem 50px; overflow: hidden;
    }
    .hero-container {
        max-width: 1400px; width: 100%; margin: 0 auto;
        display: grid; grid-template-columns: 1fr 1.2fr;
        align-items: center; gap: 4rem;
    }
    
    .hero-text { z-index: 2; }
    .hero-visual { 
        position: relative; z-index: 1; 
        display: flex; justify-content: center; align-items: center; 
        min-height: 500px; /* Ensure space for the rotated card */
    }

    /* Restoring Deleted Hero Text Styles */
    .hero-badge {
        display: inline-block; padding: 0.5rem 1rem;
        background: rgba(16, 185, 129, 0.15); color: var(--primary);
        border-radius: 30px; font-size: 0.85rem; font-weight: 600;
        margin-bottom: 1.5rem; border: 1px solid rgba(16, 185, 129, 0.3);
    }
    
    .hero h1 { 
      font-size: 4.5rem; font-weight: 800; margin-bottom: 1.5rem; 
      line-height: 1.1; letter-spacing: -1px; color: #ffffff;
    }
    .text-gradient {
      background: linear-gradient(135deg, #fff 30%, #94A3B8 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    
    .hero p { 
      font-size: 1.15rem; color: #e2e8f0; 
      max-width: 500px; margin-bottom: 2.5rem; font-weight: 400; line-height: 1.7;
    }

    /* The "Shadow Image" - Rotated Card Background */
    /* The "Shadow Image" - Spotlight Background */
    .hero-visual::before {
        content: '';
        position: absolute;
        width: 480px; height: 480px;
        background: radial-gradient(circle at 50% 20%, #1f2937 0%, #000000 70%);
        border-radius: 20px;
        transform: none; /* Removed rotation */
        z-index: -1;
        box-shadow: 0 0 50px rgba(0,0,0,0.5);
    }

    .floating-shoe {
        width: 100%; max-width: 420px;
        filter: drop-shadow(0 30px 30px rgba(0,0,0,0.5));
        transform: none; 
        animation: float 6s ease-in-out infinite;
    }
    
    .glow-effect {
        display: none; /* Removing old glow to prioritize spotlight */
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-15px); }
    }

    /* Bento Grid Categories */
    #categories { padding: 100px 2rem; background: #0B0F19; }
    
    .cat-grid {
      max-width: 1400px; margin: 0 auto;
      display: grid; grid-template-columns: repeat(4, 1fr);
      grid-template-rows:repeat(2, 280px);
      gap: 1.5rem;
    }
    
    /* First item spans 2x2 (Large Left) */
    .cat-card:nth-child(1) { grid-column: span 2; grid-row: span 2; }
    /* Second item spans 2 cols (Wide Top Right) */
    .cat-card:nth-child(2) { grid-column: span 2; }
    /* Others are 1x1 */
    .cat-card {
      background: var(--dark-card); border-radius: 16px;
      border: 1px solid var(--dark-border);
      position: relative; overflow: hidden; text-decoration: none;
      transition: all 0.4s ease;
    }
    
    /* Responsive Bento */
    @media (max-width: 900px) {
        .cat-grid { grid-template-columns: 1fr; grid-template-rows: auto; }
        .cat-card:nth-child(1), .cat-card:nth-child(2) { grid-column: auto; grid-row: auto; height: 300px; }
        .hero-container { grid-template-columns: 1fr; text-align: center; }
        .hero p { margin: 0 auto 2.5rem; }
        .hero-visual { margin-top: 3rem; }
    }
    
    .cat-card img {
      width: 100%; height: 100%; object-fit: cover;
      transition: transform 0.7s ease; opacity: 0.7;
    }
    .cat-card:hover { border-color: var(--primary); transform: translateY(-5px); }
    .cat-card:hover img { transform: scale(1.1); opacity: 0.5; }
    
    .cat-overlay {
      position: absolute; bottom: 0; left: 0; width: 100%;
      padding: 1.5rem;
      background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    }
    
    .cat-card:hover { border-color: var(--primary); transform: translateY(-5px); }
    .cat-card:hover img { transform: scale(1.1); opacity: 0.6; }
    
    .cat-title {
      font-family: var(--font-heading); font-size: 2rem;
      color: #fff; margin-bottom: 0.5rem;
      transform: translateY(20px); transition: 0.5s;
    }
    .cat-link-text {
      color: var(--primary); text-transform: uppercase;
      font-size: 0.8rem; letter-spacing: 2px;
      opacity: 0; transform: translateY(20px); transition: 0.5s 0.1s;
    }
    
    .cat-card:hover .cat-title,
    .cat-card:hover .cat-link-text {
      transform: translateY(0); opacity: 1;
    }

    /* Featured Products */
    #products { padding: 100px 2rem; background: #000; }
    
    .products-grid {
      max-width: 1400px; margin: 0 auto;
      display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
      gap: 2.5rem;
    }
    

    .product-card {
      background: var(--dark-card); border-radius: 16px;
      overflow: hidden; text-decoration: none;
      transition: all 0.4s ease; display: block;
      border: 1px solid var(--dark-border);
      position: relative;
    }
    
    .product-img-wrapper {
      position: relative;
      background: radial-gradient(circle at center, #1f2937, #111827);
      padding: 2rem;
      border-radius: 16px; margin: 10px;
      height: 300px; display: flex; align-items: center; justify-content: center;
      overflow: hidden;
    }
    
    .product-img-wrapper img {
      max-width: 100%; max-height: 100%;
      object-fit: contain;
      filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3));
      transition: transform 0.4s ease;
    }
    
    .product-card:hover .product-img-wrapper img {
      transform: scale(1.1) rotate(-3deg);
    }

    /* New Price Tag - Top Left */
    .product-price-tag {
        position: absolute; top: 12px; left: 12px;
        background: #10b981; color: #000;
        padding: 4px 12px; border-radius: 8px;
        font-weight: 700; font-size: 0.9rem;
        z-index: 10;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    .product-price-tag span { font-size: 0.75rem; font-weight: 600; margin-right: 2px; }

    /* Wishlist Button - Top Right */
    .wishlist-btn {
        position: absolute; top: 12px; right: 12px;
        width: 32px; height: 32px; border-radius: 50%;
        background: rgba(255,255,255,0.1);
        display: flex; align-items: center; justify-content: center;
        color: #fff; transition: 0.3s; z-index: 10;
    }
    .wishlist-btn:hover { background: #fff; color: #ef4444; }

    .product-meta { padding: 0.5rem 1.5rem 1.5rem; text-align: left; }
    
    .product-brand {
      color: #10b981; font-size: 0.75rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;
      display: block;
    }
    
    .product-title {
      font-family: var(--font-body); /* Modern clean font for title */
      font-weight: 600; font-size: 1.1rem; margin-bottom: 0;
      color: #fff; line-height: 1.4;
    }

    .section-header h2 {
      font-size: 2.5rem; margin-bottom: 1rem;
      position: relative; display: inline-block;
      color: #ffffff; font-weight: 700;
    }

    /* Footer Refined */
    footer {
      background: #05070A; border-top: 1px solid var(--dark-border);
      padding: 80px 0 40px; color: #fff;
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
        font-family: var(--font-heading); font-size: 24px; font-weight: 700; line-height: 1;
    }
    .footer-desc {
        color: #94a3b8; font-size: 0.95rem; line-height: 1.6; margin-bottom: 0.5rem;
    }
    
    .contact-info { display: flex; flex-direction: column; gap: 0.8rem; }
    .contact-item {
        display: flex; align-items: center; gap: 10px;
        color: #fff; font-size: 0.9rem;
    }
    .contact-item i { color: var(--primary); width: 20px; }
    
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
        background: var(--primary); color: #000; transform: translateY(-3px);
    }
    
    /* Footer Grid */
    .footer-nav-grid {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;
    }
    
    .footer-col h4 {
        color: #10b981; /* Force Green */
        font-size: 0.85rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.5rem;
    }
    
    .footer-links { list-style: none; padding: 0; margin: 0; }
    .footer-links li { margin-bottom: 1rem; }
    .footer-links a {
        color: #e2e8f0; text-decoration: none; font-size: 0.95rem;
        transition: 0.3s;
    }
    .footer-links a:hover { color: #10b981; padding-left: 5px; }

    @media (max-width: 1024px) {
        .footer-container { grid-template-columns: 1fr; }
        .footer-card { max-width: 500px; }
    }
    @media (max-width: 768px) {
        .footer-nav-grid { grid-template-columns: 1fr 1fr; }
    }
  </style>
</head>
<body>

<nav class="navbar">
  <div class="nav-container">
    <a href="index.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
      <img src="assets/shoe_logo_green.png" alt="WalkOn Logo" style="height: 32px; width: auto; filter: drop-shadow(0 0 10px rgba(16, 185, 129, 0.2));">
      <div style="font-family: 'Outfit', sans-serif; font-size: 26px; font-weight: 800; line-height: 1; letter-spacing: -0.5px; text-transform: uppercase;">
        <span style="color: #fff;">Walk</span><span style="color: #10b981;">on</span>
      </div>
    </a>
    <div class="nav-links">
      <a href="#categories">Categories</a>
      <a href="#features">Features</a>
      <a href="marketplaces.php">Marketplace</a>
      <a href="#products">Featured Products</a>
      <div style="display: flex; align-items: center; gap: 1.5rem; margin-left: 1rem;">
        <a href="login.php" style="text-decoration: none; color: #fff; font-weight: 600; font-size: 0.9rem;">Login</a>
        <a href="register.php" class="btn btn-primary" style="padding: 0.8rem 1.8rem; border-radius: 50px; font-size: 0.9rem; gap: 8px;">
          Start Selling <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i>
        </a>
      </div>
    </div>
  </div>
</nav>

<section class="hero">
  <div class="hero-container">
    <div class="hero-visual">
         <div class="glow-effect"></div>
         <img src="assets/hero_shoe.png" alt="Hero Shoe" class="floating-shoe">
    </div>
    <div class="hero-text">
        <span class="hero-badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: none; padding: 0.5rem 1.2rem;">Trusted by 2,500+ Luxury Shoe Brands</span>
        <h1>Step into the <br><span class="text-gradient">Next Dimension</span></h1>
        <p>The global multi-channel infrastructure for premium footwear. Sync your inventory across Amazon, Flipkart, shopify, TikTok, instagram, ebay and more with a single powerful dashboard.</p>
        <div class="hero-btns">
            <a href="shop.php" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1rem;">Explore the Platform</a>
            <a href="#products" class="btn btn-outline" style="border: none; color: #fff; padding: 1rem 2rem;">Browse Collection</a>
        </div>
    </div>
  </div>
</section>




<section id="categories">
  <div class="section-header">
    <h2>SHOP BY CATEGORY</h2>
    <div class="divider"></div>
  </div>
  
  <div class="cat-grid">
    <?php foreach($categories as $cat): ?>
      <a href="shop.php?category=<?= $cat['id'] ?>" class="cat-card">
        <img src="<?= $cat['image'] ?>" alt="<?= $cat['name'] ?>">
        <div class="cat-overlay">
          <h3 class="cat-title"><?= $cat['name'] ?></h3>
          <span class="cat-link-text">View Collection &rarr;</span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- Features Section -->
<section id="features" style="padding: 100px 0; background: #0B0F19;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 2rem;">
        <div class="features-box" style="background: #111827; border-radius: 24px; padding: 4rem 2rem; border: 1px solid #1f2937;">
            <div class="section-header" style="text-align: center; margin-bottom: 3rem;">
                <h2 style="color: #ffffff;">Why Leading Brands <br> Choose WalkOn</h2>
            </div>
            
            <div class="features-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 3rem; margin-top: 3rem;">
                
                <?php if(!empty($platform_features)): ?>
                    <?php foreach($platform_features as $feature): ?>
                    <div class="feature-card" style="text-align: center;">
                        <div class="icon-box" style="width: 60px; height: 60px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <i class="<?= htmlspecialchars($feature['icon']) ?>" style="font-size: 1.5rem; color: #10b981;"></i>
                        </div>
                        <h3 style="color: #fff; font-size: 1.25rem; margin-bottom: 1rem;"><?= htmlspecialchars($feature['title']) ?></h3>
                        <p style="color: #9ca3af; font-size: 0.95rem;"><?= htmlspecialchars($feature['description']) ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback if database empty -->
                    <div class="feature-card" style="text-align: center;">
                        <div class="icon-box" style="width: 60px; height: 60px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <i class="fas fa-layer-group" style="font-size: 1.5rem; color: #10b981;"></i>
                        </div>
                        <h3 style="color: #fff; font-size: 1.25rem; margin-bottom: 1rem;">Multi-Channel Sync</h3>
                        <p style="color: #9ca3af; font-size: 0.95rem;">Seamlessly sync inventory across all marketplaces in 5 minutes.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<section id="products">
  <div class="section-header" style="text-align: center; margin-bottom: 3rem;">
    <h5 style="color: #10b981; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 0.5rem; font-size: 0.9rem;">New Arrivals</h5>
    <h2 style="color: #ffffff; margin-bottom: 0;">Featured Products</h2>
  </div>

  <div class="products-grid">
    <?php foreach($featured_products as $p): ?>
      <a href="shop.php?search=<?= urlencode($p['name']) ?>" class="product-card">
        <div class="product-img-wrapper">
           <!-- Green Price Tag (Top Left) -->
           <div class="product-price-tag">
             <?php if($p['price']): ?>
                <span>₹</span><?= number_format($p['price']) ?>
             <?php else: ?>
                <span>Ask</span>
             <?php endif; ?>
           </div>

           <!-- Wishlist Button (Top Right) -->
           <div class="wishlist-btn" onclick="toggleWishlist(event, <?= $p['id'] ?: 0 ?>)">
               <i class="far fa-heart"></i>
           </div>

           <img src="<?= $p['primary_image'] ?: $p['fallback_image'] ?: 'https://via.placeholder.com/320x320?text=' . urlencode($p['name']) ?>" alt="<?= $p['name'] ?>">
        </div>
        
        <div class="product-meta">
          <!-- Brand Name (Green) -->
          <span class="product-brand"><?= $p['brand_name'] ?: ($p['category_name'] ?: 'WALKON') ?></span>
          
          <!-- Product Title (White) -->
          <h4 class="product-title"><?= $p['name'] ?></h4>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
  
  <div style="text-align:center; margin-top:5rem;">
    <a href="shop.php" class="btn btn-login" style="padding: 1rem 3rem;">View All Masterpieces</a>
  </div>
</section>

</section>

<script>
function toggleWishlist(event, productId) {
    event.preventDefault();
    event.stopPropagation();
    
    const btn = event.currentTarget;
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
                icon.classList.remove('far');
                icon.classList.add('fas');
                icon.style.color = '#ef4444';
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                icon.style.color = '';
            }
        } else if (data.message === 'User not logged in') {
            window.location.href = 'login.php';
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        console.error('Wishlist error:', err);
        alert('Could not update wishlist. Try again later.');
    });
}
</script>

<footer>
  <div class="footer-container">
    <!-- Left Card -->
    <div class="footer-card">
      <a href="index.php" class="footer-logo">
         <img src="assets/shoe_logo_green.png" alt="WalkOn" style="height: 32px;">
         <div class="brand-text" style="font-family: 'Outfit', sans-serif; text-transform: uppercase;"><span style="color:#fff">WALK</span><span style="color:#10b981">ON</span></div>
      </a>
      <p class="footer-desc">Elevating the global footwear industry with intelligent multi-channel technology. One inventory, infinite possibilities.</p>
      
      <div class="contact-info">
        <div class="contact-item"><i class="fas fa-envelope"></i> support@walkon.com</div>
        <div class="contact-item"><i class="fas fa-phone"></i> +91 90745 85775</div>
        <div class="contact-item"><i class="fas fa-map-marker-alt"></i> Kottayam, Kerala, India</div>
      </div>

      <div class="social-links">
        <a href="tel:+919074585775" class="social-btn"><i class="fas fa-phone-alt"></i></a>
        <a href="https://www.instagram.com/walkon" target="_blank" class="social-btn"><i class="fab fa-instagram"></i></a>
        <a href="https://wa.me/919074585775" target="_blank" class="social-btn"><i class="fab fa-whatsapp"></i></a>
        <a href="https://www.facebook.com/walkon" target="_blank" class="social-btn"><i class="fab fa-facebook-f"></i></a>
        <a href="https://www.linkedin.com/company/walkon" target="_blank" class="social-btn"><i class="fab fa-linkedin-in"></i></a>
        <a href="https://www.youtube.com/@walkon" target="_blank" class="social-btn"><i class="fab fa-youtube"></i></a>
      </div>
    </div>

    <!-- Right Columns -->
    <div class="footer-nav-grid">
      <div class="footer-col">
        <h4>NAVIGATION</h4>
        <ul class="footer-links">
          <li><a href="index.php">Home</a></li>
          <li><a href="#categories">Categories</a></li>
          <li><a href="#features">Features</a></li>
          <li><a href="marketplaces.php">Marketplace</a></li>
          <li><a href="#products">Featured Products</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>SHOES</h4>
        <ul class="footer-links">
           <li><a href="shop.php">All Products</a></li>
           <li><a href="shop.php?category=1">Boots</a></li>
           <li><a href="shop.php?category=2">Formal Shoes</a></li>
           <li><a href="shop.php?category=3">Running Shoes</a></li>
           <li><a href="shop.php?category=4">Sandals & Slides</a></li>
           <li><a href="shop.php?category=5">Sneakers</a></li>
           <li><a href="shop.php?category=6">Sports</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>BRANDS</h4>
        <ul class="footer-links">
          <li><a href="shop.php">All Brands</a></li>
          <li><a href="shop.php?brand=1">adidas</a></li>
          <li><a href="shop.php?brand=2">ASICS</a></li>
          <li><a href="shop.php?brand=3">Bata</a></li>
          <li><a href="shop.php?brand=4">Clarks</a></li>
          <li><a href="shop.php?brand=5">Converse</a></li>
          <li><a href="shop.php?brand=6">Crocs</a></li>
          <li><a href="shop.php?brand=7">Dr. Martens</a></li>
          <li><a href="shop.php?brand=8">New Balance</a></li>
        </ul>
      </div>
    </div>
  </div>
</footer>
</body>
</html>
