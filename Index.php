<?php
$categories = [
    [
        "name" => "Sneakers",
        "subcategories" => "Casual, Lifestyle, High-Top, Low-Top",
        "image" => "https://static.vecteezy.com/system/resources/previews/002/884/011/large_2x/product-shoot-of-nike-men-s-sport-running-shoe-on-white-background-nike-running-shoes-free-photo.JPG"  // Nike Sneakers
    ],
    [
        "name" => "Running Shoes",
        "subcategories" => "Trail, Road, Racing, Training",
        "image" => "https://m.media-amazon.com/images/I/71byI7vOrjL._AC_UY900_.jpg"  // Adidas Ultraboost
    ],
    [
        "name" => "Boots",
        "subcategories" => "Ankle, Chelsea, Combat, Hiking",
        "image" => "https://images.dsw.com/is/image/DSWShoes/579988_231_ss_09?impolicy=qlt-medium-high&imwidth=640&imdensity=1"  // Timberland Boot
    ],
    [
        "name" => "Sandals & Slides",
        "subcategories" => "Flip-Flops, Sliders, Sport Sandals",
        "image" => "https://www.shutterstock.com/image-photo/leather-black-sandals-birkenstocks-on-600nw-2357474737.jpg"  // Birkenstock Sandals
    ],
    [
        "name" => "Formal Shoes",
        "subcategories" => "Oxford, Derby, Loafers, Brogues",
        "image" => "https://media.istockphoto.com/id/1048397632/photo/male-black-leather-shoe-on-white-background-isolated-product.jpg?s=612x612&w=is&k=20&c=3exekhJfVltKKfaJbqAetKDvvHfg06AAQWQFazrbVSo="  // Black Formal Shoe
    ],
    [
        "name" => "Kids Shoes",
        "subcategories" => "Sneakers, School, Sandals",
        "image" => "https://media.istockphoto.com/id/522628236/photo/little-girl-sneakers-shoes.jpg?s=612x612&w=0&k=20&c=JT7FEcRZNzOaAwDoFldcp5iymPU8hJ8Yt-WFvKZpcvw="  // Kids Sneakers
    ]
];

$featured_products = [
    ["name" => "Nike Air Max 270", "category" => "Men • Running", "price" => 149.99, "old_price" => 189.99, "img" => "https://cdn11.bigcommerce.com/s-k0qaggtmuc/images/stencil/1280x1280/products/305/1471/nike-air-max-86-junior-golf-shoes-white__26148.1755318591.jpg?c=1"],  // Nike Air Max
    ["name" => "Air Jordan 1 Retro High", "category" => "Unisex • Lifestyle", "price" => 179.99, "old_price" => null, "img" => "https://static.nike.com/a/images/f_auto,cs_srgb/w_1920,c_limit/89c121fc-3d07-4de0-aef6-bcc9c2764a2c/air-jordan-1-2022-lost-and-found-chicago-the-inspiration-behind-the-design.jpg"],  // Air Jordan
    ["name" => "Adidas Ultraboost 23", "category" => "Women • Running", "price" => 180.00, "old_price" => 220.00, "img" => "https://assets.adidas.com/videos/b2085988566d45b2aca2618af509008a_d98c/IH4924_HM51.jpg"],  // Adidas Ultraboost
    ["name" => "Timberland Premium 6\"", "category" => "Men • Boots", "price" => 198.00, "old_price" => null, "img" => "https://images.dsw.com/is/image/DSWShoes/579988_231_ss_09?impolicy=qlt-medium-high&imwidth=640&imdensity=1"],  // Timberland
    ["name" => "Puma RS-X", "category" => "Men • Sneakers", "price" => 120.00, "old_price" => 150.00, "img" => "https://m.media-amazon.com/images/I/51ikyGG7F-L._AC_UY900_.jpg"],  // Puma RS-X
    ["name" => "New Balance 550", "category"  => "Unisex • Lifestyle", "price" => 129.99, "old_price" => null, "img" => "https://images.stockx.com/360/New-Balance-550-White-Nightwatch-Green/Images/New-Balance-550-White-Nightwatch-Green/Lv2/img01.jpg?w=480&q=60&dpr=1&updated_at=1664522569&h=320"]  // New Balance 550
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
        <li><a href="#pricing" style="color:#e2e8f0; text-decoration:none; font-weight:500; transition:color 0.2s;">Pricing</a></li>
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