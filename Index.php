<?php
$categories = [
    [
        "name" => "Sneakers",
        "subcategories" => "Casual, Lifestyle, High-Top, Low-Top",
        "image" => "https://images.unsplash.com/photo-1608231387042-66d1773070a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80"  // Black/White Sneaker (Unsplash)
    ],
    [
        "name" => "Running Shoes",
        "subcategories" => "Trail, Road, Racing, Training",
        "image" => "https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80"  // Red Running Shoe (Unsplash) - Reliable
    ],
    [
        "name" => "Boots",
        "subcategories" => "Ankle, Chelsea, Combat, Hiking",
        "image" => "data:image/jpeg;base64,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"  // Red Tape Style Tan Boot
    ],
    [
        "name" => "Sandals & Slides",
        "subcategories" => "Flip-Flops, Sliders, Sport Sandals",
        "image" => "https://images.unsplash.com/photo-1543163521-1bf539c55dd2?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"  // Blue Sandals (Reliable)
    ],
    [
        "name" => "Formal Shoes",
        "subcategories" => "Oxford, Derby, Loafers, Brogues",
        "image" => "https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"  // Classic Leather Shoe (Fixed)
    ],
    [
        "name" => "Kids Shoes",
        "subcategories" => "Sneakers, School, Sandals",
        "image" => "https://images.unsplash.com/photo-1514989940723-e8e51635b782?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80"  // Kids Shooting Star (Unsplash)
    ]
];

$featured_products = [
    ["name" => "Nike Air Max 270", "category" => "Men • Running", "price" => 149.99, "old_price" => 189.99, "img" => "https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"],  // Nike Air Max Style
    ["name" => "Air Jordan 1 Retro", "category" => "Unisex • Lifestyle", "price" => 179.99, "old_price" => null, "img" => "https://images.unsplash.com/photo-1549298916-b41d501d3772?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"],  // Air Jordan Style
    ["name" => "Adidas Ultraboost", "category" => "Women • Running", "price" => 180.00, "old_price" => 220.00, "img" => "data:image/jpeg;base64,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"],  // Ultraboost (Uploaded)
    ["name" => "Timberland Premium", "category" => "Men • Boots", "price" => 198.00, "old_price" => null, "img" => "data:image/jpeg;base64,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"],  // Red Tape Style Tan Boot
    ["name" => "Puma RS-X", "category" => "Men • Sneakers", "price" => 120.00, "old_price" => 150.00, "img" => "https://images.unsplash.com/photo-1608231387042-66d1773070a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"],  // Puma Style
    ["name" => "New Balance 550", "category"  => "Unisex • Lifestyle", "price" => 129.99, "old_price" => null, "img" => "https://images.unsplash.com/photo-1539185441755-769473a23570?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"]  // New Balance Style
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>WALKON - Shoe Multi-Channel E-Commerce Platform</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
  <style>
    :root {
      --green: #16a34a;
      --green-light: #22c55e;
      --green-dark: #15803d;
      --gray-50: #f8fafc;
      --gray-900: #0f172a;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: 'Inter', sans-serif; background: var(--gray-50); color: var(--gray-900); line-height: 1.6; }

    /* Navbar */
    .navbar {
      background: white; position: fixed; width: 100%; top: 0; z-index: 1000;
      box-shadow: 0 4px 30px rgba(0,0,0,0.08); height: 80px;
    }
    .nav-container {
      max-width: 1400px; margin: 0 auto; padding: 0 2rem; height: 100%;
      display: flex; justify-content: space-between; align-items: center;
    }
    .logo { font-size: 2.6rem; font-weight: 900; color: var(--green); }
    .logo span { color: var(--green-light); }
    .nav-links a { margin-left: 2rem; text-decoration: none; font-weight: 600; color: var(--gray-900); }
    .nav-links a:hover { color: var(--green); }
    .btn {
      padding: 0.9rem 2rem; border-radius: 12px; font-weight: 700;
      text-decoration: none; transition: all 0.3s; font-size: 1rem;
    }
    .btn-primary { background: var(--green); color: white; }
    .btn-primary:hover { background: var(--green-dark); transform: translateY(-3px); }

    /* Hero */
    .hero {
      background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
      color: white; text-align: center; padding: 160px 2rem 100px;
    }
    .hero h1 { font-size: 4.8rem; font-weight: 900; margin-bottom: 1rem; }
    .hero p { font-size: 1.4rem; max-width: 900px; margin: 0 auto 2.5rem; opacity: 0.95; }

    /* Sections */
    .container { max-width: 1400px; margin: 0 auto; padding: 0 2rem; }
    .section-title { text-align: center; font-size: 2.8rem; font-weight: 800; margin: 4rem 0 3rem; }

    /* Categories Grid */
    .cat-grid {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem; margin-bottom: 4rem;
    }
    .cat-card {
      background: white; border-radius: 24px; overflow: hidden;
      box-shadow: 0 15px 35px rgba(0,0,0,0.1); transition: 0.4s;
    }
    .cat-card:hover { transform: translateY(-12px); box-shadow: 0 25px 50px rgba(22,163,74,0.2); }
    .cat-card img { width: 100%; height: 220px; object-fit: cover; }
    .cat-info { padding: 1.8rem; text-align: center; }
    .cat-info h3 { font-size: 1.6rem; color: var(--green); margin-bottom: 0.5rem; }
    .cat-info p { color: #64748b; font-size: 0.95rem; }

    /* Products Grid */
    .product-grid {
      display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2.5rem;
    }
    .product-card {
      background: white; border-radius: 24px; overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.08); transition: 0.4s;
    }
    .product-card:hover { transform: translateY(-10px); box-shadow: 0 25px 50px rgba(22,163,74,0.18); }
    .product-card img { width: 100%; height: 260px; object-fit: cover; }
    .product-info { padding: 1.5rem; }
    .product-info h4 { font-size: 1.3rem; margin-bottom: 0.4rem; }
    .product-info p { color: #64748b; font-size: 0.95rem; margin-bottom: 0.8rem; }
    .price { font-size: 1.5rem; font-weight: 700; color: var(--green); }
    .old-price { text-decoration: line-through; color: #94a3b8; margin-left: 0.5rem; }

    footer { background: var(--gray-900); color: white; text-align: center; padding: 4rem 2rem; font-size: 1.1rem; }

    @media (max-width: 768px) {
      .hero h1 { font-size: 3.2rem; }
      .nav-links { display: none; }
    }
    /* Favorite Button Design (Dribbble Style) */
  .favorite-btn {
    position: absolute;
    top: 18px;
    right: 18px;
    z-index: 10;
    width: 48px;
    height: 48px;
    background: white;
    border-radius: 50%;
    border: none;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    cursor: pointer;
    font-size: 1.4rem;
    color: #94a3b8;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .favorite-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
  }
  .favorite-btn.active {
    color: #ef4444;
    transform: scale(1.2);
  }
</style>

<script>
  function toggleFavorite(btn) {
    btn.classList.toggle('active');
    if (btn.classList.contains('active')) {
      btn.innerHTML = '♥'; // Filled heart
      setTimeout(() => btn.style.transform = 'scale(1)', 300);
    } else {
      btn.innerHTML = '♡'; // Outline heart
    }
  }
  
</script>
  </style>
</head>
<body>

 <!-- Updated Navbar with New Premium WALKON Logo -->
<nav class="navbar">
  <div class="nav-container">
    
    <!-- NEW PROFESSIONAL WALKON LOGO (2025 Edition) -->
    <a href="index.php" aria-label="WALKON - Home">
      <svg class="logo-svg" width="210" height="70" viewBox="0 0 210 70" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <linearGradient id="walkon-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#16a34a"/>
            <stop offset="100%" stop-color="#22c55e"/>
          </linearGradient>
          <filter id="shadow" x="-50%" y="-50%" width="200%" height="200%">
            <feDropShadow dx="0" dy="4" stdDeviation="4" flood-color="#000000" flood-opacity="0.15"/>
          </filter>
        </defs>

        <!-- Stylish Running Shoe Icon -->
        <g transform="translate(10,15)" filter="url(#shadow)">
          <path d="M18 35 Q5 22, 18 12 Q38 18, 32 38 Q26 50, 18 35 Z" 
                fill="url(#walkon-gradient)" opacity="0.98"/>
          <path d="M32 38 Q45 25, 32 12" 
                fill="none" stroke="#15803d" stroke-width="9" stroke-linecap="round"/>
          <path d="M18 35 H44" 
                stroke="#15803d" stroke-width="9" stroke-linecap="round"/>
          <circle cx="18" cy="35" r="6" fill="#15803d"/>
          <path d="M44 35 Q52 28, 52 20" 
                fill="none" stroke="#15803d" stroke-width="7" stroke-linecap="round"/>
        </g>

        <!-- Bold & Modern Text -->
        <text x="75" y="45" 
              font-family="Inter, system-ui, -apple-system, sans-serif" 
              font-size="38" 
              font-weight="900" 
              fill="#0f172a"
              letter-spacing="-1">WALK</text>
        <text x="150" y="45" 
              font-family="Inter, system-ui, -apple-system, sans-serif" 
              font-size="38" 
              font-weight="900" 
              fill="url(#walkon-gradient)"
              letter-spacing="-1">ON</text>
      </svg>
    </a>

    <!-- Navigation Links (unchanged) -->
    <div class="nav-links">
      <a href="#categories">Categories</a>
      <a href="#products">Products</a>
      <a href="login.php" class="btn" style="background:#f8fafc; color:var(--green); border:2px solid var(--green);">
        Seller Login
      </a>
      <a href="register.php" class="btn btn-primary">
        Start Selling Shoes
      </a>
    </div>
  </div>
</nav>

  <!-- Hero -->
  <section class="hero">
    <h1>WALKON Shoes</h1>
    <p>The #1 Multi-Channel E-Commerce Platform for Shoe Brands & Retailers<br>
       Sell on Amazon · Shopify · Instagram · TikTok Shop · eBay · Your Store — All Synced</p>
    <a href="register.php" class="btn btn-primary" style="font-size:1.4rem; padding:1.2rem 3rem; border-radius:50px;">
      Start Free 14-Day Trial
    </a>
    <p style="margin-top:1.5rem; font-size:1.1rem; opacity:0.9; font-weight:500;">
      <i class="fas fa-check-circle" style="color:#a7f3d0; margin-right:8px;"></i>No credit card required &nbsp;•&nbsp; 
      <i class="fas fa-check-circle" style="color:#a7f3d0; margin-right:8px;"></i>Cancel anytime &nbsp;•&nbsp; 
      <i class="fas fa-check-circle" style="color:#a7f3d0; margin-right:8px;"></i>Full Access
    </p>
  </section>





  <!-- SHOP BY CATEGORY -->
<section id="categories" style="padding:80px 0;background:#f8fafc;">
  <div class="container" style="max-width:1400px;margin:0 auto;padding:0 2rem;">
    <h2 style="text-align:center;font-size:2.8rem;font-weight:800;margin-bottom:4rem;color:#0f172a;">
      Shop by Category
    </h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:2.5rem;">
      <?php foreach($categories as $cat): ?>
        <div style="background:white;border-radius:28px;overflow:hidden;box-shadow:0 15px 35px rgba(0,0,0,0.08);position:relative;transition:0.4s;">
          <button class="favorite-btn" onclick="toggleFavorite(this)">♡</button>
          <img src="<?= $cat['image'] ?>" alt="<?= $cat['name'] ?>" style="width:100%;height:280px;object-fit:cover;">
          <div style="padding:1.8rem;text-align:center;">
            <h3 style="font-size:1.6rem;font-weight:700;color:#16a34a;"><?= $cat['name'] ?></h3>
            <p style="color:#64748b;"><?= $cat['subcategories'] ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

 <!-- FEATURED PRODUCTS -->
<section id="products" style="padding:80px 0;background:white;">
  <div class="container" style="max-width:1400px;margin:0 auto;padding:0 2rem;">
    <h2 style="text-align:center;font-size:2.8rem;font-weight:800;margin-bottom:4rem;color:#0f172a;">
      Featured Products
    </h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:2.5rem;">
      <?php foreach($featured_products as $p): 
        $has_sale = $p['old_price'] > $p['price'];
        $discount = $has_sale ? round((($p['old_price'] - $p['price']) / $p['old_price']) * 100) : 0;
      ?>
        <div style="background:white;border-radius:28px;overflow:hidden;box-shadow:0 20px 40px rgba(0,0,0,0.12);position:relative;transition:0.4s;">
          <?php if($has_sale): ?>
            <div style="position:absolute;top:18px;left:18px;background:#ef4444;color:white;padding:10px 20px;border-radius:50px;font-weight:800;font-size:0.95rem;z-index:10;">
              Sale
            </div>
          <?php endif; ?>

          <button class="favorite-btn" onclick="toggleFavorite(this)">♡</button>

          <div style="height:360px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f9fafb;">
            <img src="<?= $p['img'] ?>" alt="<?= $p['name'] ?>" style="max-width:92%;max-height:92%;object-fit:contain;">
          </div>

          <div style="padding:2rem;text-align:center;">
            <h3 style="font-size:1.5rem;font-weight:800;margin-bottom:0.5rem;"><?= $p['name'] ?></h3>
            <p style="color:#6b7280;"><?= $p['category'] ?></p>
            <div style="display:flex;align-items:center;justify-content:center;gap:12px;margin:1rem 0;">
              <span style="font-size:2rem;font-weight:900;color:#16a34a;">₹<?= number_format($p['price'],0) ?></span>
              <?php if($has_sale): ?>
                <span style="font-size:1.2rem;color:#94a3b8;text-decoration:line-through;">₹<?= number_format($p['old_price'],0) ?></span>
                <span style="background:#fef3c7;color:#92400e;padding:6px 14px;border-radius:12px;font-weight:700;">-<?= $discount ?>%</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

 <footer style="background-color:#0f172a; color:#e2e8f0; padding:3rem 1rem; font-family:system-ui, -apple-system, sans-serif;">
  <div style="max-width:1200px; margin:0 auto; display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:2rem;">
    
    <!-- Logo + Description -->
    <div>
      <svg width="180" height="50" viewBox="0 0 190 60" xmlns="http://www.w3.org/2000/svg" style="margin-bottom:1rem;">
        <defs><linearGradient id="g"><stop offset="0%" stop-color="#16a34a"/><stop offset="100%" stop-color="#22c55e"/></linearGradient></defs>
        <g transform="translate(8,10)"><path d="M18 35 Q5 22,18 12 Q38 18,32 38 Q26 50,18 35 Z" fill="url(#g)"/><path d="M32 38 Q45 25,32 12" fill="none" stroke="#15803d" stroke-width="8" stroke-linecap="round"/><path d="M18 35 H44" stroke="#15803d" stroke-width="8" stroke-linecap="round"/><circle cx="18" cy="35" r="6" fill="#15803d"/></g>
        <text x="70" y="38" font-size="34" font-weight="900" fill="white">WALK</text>
        <text x="140" y="38" font-size="34" font-weight="900" fill="url(#g)">ON</text>
      </svg>
      <p style="opacity:0.8; line-height:1.7; font-size:0.95rem;">
        The #1 Multi-Channel E-Commerce Platform for Shoe Brands & Sellers.<br>
        Sell on Amazon, Flipkart, Shopify, Instagram, TikTok Shop, eBay & your own store — all synced in real-time.
      </p>
    </div>

    <!-- Quick Links -->
    <div>
      <h4 style="font-size:1.3rem; font-weight:700; margin-bottom:1.5rem; color:#22c55e;">Quick Links</h4>
      <ul style="list-style:none; padding:0; margin:0; line-height:2.2;">
        <li><a href="#categories" style="color:#e2e8f0; text-decoration:none; font-weight:500; transition:color 0.2s;">Categories</a></li>
        <li><a href="#products" style="color:#e2e8f0; text-decoration:none; font-weight:500; transition:color 0.2s;">Featured Products</a></li>
        <li><a href="#how-it-works" style="color:#e2e8f0; text-decoration:none; font-weight:500; transition:color 0.2s;">How It Works</a></li>
        <li><a href="login.php" style="color:#e2e8f0; text-decoration:none; font-weight:500; transition:color 0.2s;">Seller Login</a></li>
        <li><a href="register.php" style="color:#e2e8f0; text-decoration:none; font-weight:500; transition:color 0.2s;">Start Selling</a></li>
        <li><a href="pricing.php" style="color:#e2e8f0; text-decoration:none; font-weight:500; transition:color 0.2s;">Pricing</a></li>
      </ul>
    </div>

    <!-- Supported Channels -->
    <div>
      <h4 style="font-size:1.3rem; font-weight:700; margin-bottom:1.5rem; color:#22c55e;">Supported Channels</h4>
      <ul style="list-style:none; padding:0; margin:0; line-height:2.2; columns:2; column-gap:2rem;">
        <li style="opacity:0.9;">Amazon</li>
        <li style="opacity:0.9;">Flipkart</li>
        <li style="opacity:0.9;">Shopify</li>
        <li style="opacity:0.9;">WooCommerce</li>
        <li style="opacity:0.9;">TikTok Shop</li>
        <li style="opacity:0.9;">Instagram Shopping</li>
        <li style="opacity:0.9;">eBay</li>
        <li style="opacity:0.9;">Myntra</li>
      </ul>
    </div>

    <!-- Contact & Social -->
    <div>
      <h4 style="font-size:1.3rem; font-weight:700; margin-bottom:1.5rem; color:#22c55e;">Get in Touch</h4>
      <p style="opacity:0.8; margin-bottom:0.5rem;">support@walkon.com</p>
      <p style="opacity:0.8; margin-bottom:1.5rem;">+91 9074585775</p>
      
      <div style="display:flex; gap:1rem;">
        <a href="#" aria-label="Facebook" style="color:#e2e8f0; font-size:1.5rem;">&#x1F464;</a> <!-- Placeholder icons -->
        <a href="#" aria-label="Instagram" style="color:#e2e8f0; font-size:1.5rem;">&#x1F464;</a>
        <a href="#" aria-label="Twitter" style="color:#e2e8f0; font-size:1.5rem;">&#x1F464;</a>
        <a href="#" aria-label="LinkedIn" style="color:#e2e8f0; font-size:1.5rem;">&#x1F464;</a>
      </div>
    </div>
  </div>

  <hr style="border:1px solid #334155; margin:3rem 0;">

  <p style="text-align:center; opacity:0.7; font-size:0.9rem;">
    © 2025 WALKON Technologies Pvt. Ltd. • All rights reserved.<br>
    Made with ❤️ for shoe sellers who want to walk the world.
  </p>
</footer>