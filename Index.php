<?php
include 'config.php';

// Fetch all Categories for Search & Bento
try {
    // For Bento Grid (keeping specialized list)
    $target_categories = ['Sneakers', 'Boots', 'Sports', 'Running Shoes', 'Formal Shoes', 'Casual Shoes'];
    $placeholders = str_repeat('?,', count($target_categories) - 1) . '?';
    $stmt = $pdo->prepare("SELECT id, name, image_url as image FROM categories WHERE name IN ($placeholders) ORDER BY FIELD(name, 'Sneakers', 'Boots', 'Sports', 'Running Shoes', 'Formal Shoes', 'Casual Shoes')");
    $stmt->execute($target_categories);
    $bento_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // For Search Dropdown (all categories)
    $stmt_all = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $all_categories = $stmt_all->fetchAll(PDO::FETCH_ASSOC);

    // For Brand Filter
    $stmt_brands = $pdo->query("SELECT id, name FROM brands ORDER BY name LIMIT 15");
    $search_brands = $stmt_brands->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<!-- Database Query Failed: " . $e->getMessage() . " -->";
    $bento_categories = $all_categories = $search_brands = [];
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
    ["id" => 1, "name" => "Nike Air Max 270", "category_name" => "Running", "brand_name" => "Nike", "price" => 14999, "primary_image" => "https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80", "fallback_image" => ""],
    ["id" => 2, "name" => "Air Jordan 1 Retro", "category_name" => "Lifestyle", "brand_name" => "Jordan", "price" => 17999, "primary_image" => "https://images.unsplash.com/photo-1549298916-b41d501d3772?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80", "fallback_image" => ""],
    ["id" => 3, "name" => "Adidas Ultraboost", "category_name" => "Running", "brand_name" => "Adidas", "price" => 18000, "primary_image" => "https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80", "fallback_image" => ""],
    ["id" => 4, "name" => "Timberland Premium", "category_name" => "Boots", "brand_name" => "Timberland", "price" => 19800, "primary_image" => "https://images.unsplash.com/photo-1520639889313-72721e0ab9ef?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80", "fallback_image" => ""],
    ["id" => 5, "name" => "Puma RS-X", "category_name" => "Sneakers", "brand_name" => "Puma", "price" => 12000, "primary_image" => "https://images.unsplash.com/photo-1608231387042-66d1773070a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80", "fallback_image" => ""],
    ["id" => 6, "name" => "New Balance 550", "category_name" => "Lifestyle", "brand_name" => "New Balance", "price" => 12999, "primary_image" => "https://images.unsplash.com/photo-1539185441755-769473a23570?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80", "fallback_image" => ""]
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
      /* Premium Blue Palette */
      --primary: #2563eb;       /* Royal Blue */
      --primary-light: #60a5fa; /* Sky Blue */
      --primary-dark: #1e40af;  /* Deep Navy */
      --primary-dim: rgba(37, 99, 235, 0.1);
      
      --light-bg: #f0f9ff;      /* Light Blue Background */
      --light-card: #ffffff;    /* Keep cards white for contrast */
      --light-border: #bae6fd;  /* Soft Blue Border */
      
      --text-main: #0f172a;
      --text-muted: #64748b;
      
      --font-heading: 'Inter', sans-serif;
      --font-body: 'Inter', sans-serif;
    }

    * { margin:0; padding:0; box-sizing:border-box; }
    
    body { 
      font-family: var(--font-body); 
      background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 50%, #e0f2fe 100%);
      background-attachment: fixed;
      color: var(--text-main); 
      line-height: 1.6; 
      overflow-x: hidden;
    }

    h1, h2, h3, h4, h5, h6 {
      font-family: var(--font-heading);
      color: var(--text-main);
    }


    .navbar {
      background: linear-gradient(135deg, #10b981 0%, #2563eb 100%);
      backdrop-filter: blur(24px);
      position: fixed; width: 100%; top: 0; z-index: 1000;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      height: auto;
      /* padding removed from navbar container, utility bar adds space */
    }

    .nav-main-bar {
      max-width: 1400px; margin: 0 auto; padding: 12px 2rem;
      display: grid; grid-template-columns: 220px 1fr auto;
      align-items: center; gap: 3rem;
    }
    
    .nav-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
    .nav-logo img { height: 48px; width: auto; }
    .logo-text { font-family: 'Outfit', sans-serif; font-size: 1.8rem; font-weight: 800; line-height: 1; letter-spacing: -0.5px; text-transform: uppercase; }

    /* Search Bar Refined */
    .nav-search-wrap { position: relative; width: 100%; max-width: 700px; justify-self: center; }
    .nav-search-inner {
      position: relative; background: #f1f5f9;
      border: 1px solid var(--light-border); border-radius: 12px;
      display: flex; align-items: center; padding: 2px;
      transition: all 0.3s ease;
    }
    .nav-search-inner:focus-within {
      background: #ffffff; border-color: var(--primary);
      box-shadow: 0 0 20px rgba(37, 99, 235, 0.15);
    }
    .search-cat-dropdown {
      padding: 10px 16px; border: none; background: transparent; color: var(--text-main);
      font-size: 0.85rem; font-weight: 600; cursor: pointer;
      border-right: 1px solid var(--light-border); outline: none;
    }
    .search-input {
      flex: 1; background: transparent; border: none; padding: 10px 18px;
      color: var(--text-main); font-size: 0.95rem; outline: none;
    }
    .search-input::placeholder { color: #64748b; }
    .search-btn {
      background: transparent; border: none; padding: 10px 16px;
      color: #ffffff; cursor: pointer; font-size: 1.1rem; transition: 0.2s;
    }
    .search-btn:hover { transform: scale(1.1); }

    .filter-toggle-btn {
      background: transparent; border: none; padding: 10px 16px;
      color: var(--text-muted); cursor: pointer; font-size: 1rem; transition: 0.2s;
      border-left: 1px solid var(--light-border);
    }
    .filter-toggle-btn:hover { color: var(--primary); transform: scale(1.1); }
    .filter-toggle-btn.active { color: var(--primary); background: rgba(37, 99, 235, 0.05); }

    /* Search Filters Panel */
    .search-filters-panel {
      position: absolute; top: calc(100% + 10px); left: 0; right: 0;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: 16px; border: 1px solid var(--light-border);
      padding: 1.5rem; display: none; /* Toggle this via JS */
      box-shadow: 0 15px 40px rgba(0,0,0,0.1);
      z-index: 1001;
      grid-template-columns: repeat(3, 1fr); gap: 1.5rem;
    }
    .search-filters-panel.active { display: grid; animation: slideDown 0.3s ease-out; }

    @keyframes slideDown {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .filter-group-label {
      display: block; font-size: 0.75rem; font-weight: 700;
      color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;
      margin-bottom: 0.75rem;
    }
    .filter-select {
      width: 100%; padding: 8px 12px; border-radius: 8px;
      border: 1px solid var(--light-border); background: #ffffff;
      font-size: 0.85rem; color: var(--text-main); outline: none;
    }
    .filter-select:focus { border-color: var(--primary); }
    
    .verify-toggle-wrap {
      display: flex; align-items: center; justify-content: space-between;
      padding: 8px 12px; background: #f8fafc; border-radius: 8px;
      border: 1px solid var(--light-border);
    }
    .verify-toggle-text { font-size: 0.85rem; font-weight: 600; color: var(--text-main); }

    /* Autocomplete Suggestions */
    .search-suggestions {
      position: absolute; top: calc(100% + 5px); left: 0; right: 0;
      background: #ffffff; border-radius: 12px;
      border: 1px solid var(--light-border);
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      z-index: 1002; display: none; overflow: hidden;
    }
    .suggestion-item {
      padding: 12px 20px; cursor: pointer; transition: 0.2s;
      font-size: 0.9rem; color: var(--text-main);
      display: flex; align-items: center; gap: 12px;
    }
    .suggestion-item:hover { background: #f0f9ff; color: var(--primary); }
    .suggestion-item i { color: #64748b; font-size: 0.8rem; }
    
    .filter-footer {
      grid-column: span 3; display: flex; justify-content: flex-end;
      gap: 1rem; padding-top: 1rem; margin-top: 0.5rem;
      border-top: 1px solid var(--light-border);
    }
    .filter-reset-btn {
      background: transparent; border: none; color: #ef4444;
      font-size: 0.8rem; font-weight: 700; cursor: pointer;
      text-transform: uppercase; letter-spacing: 0.5px;
    }
    .filter-apply-btn {
      background: var(--primary); color: #ffffff; border: none;
      padding: 6px 16px; border-radius: 8px; font-size: 0.8rem;
      font-weight: 700; cursor: pointer; transition: 0.2s;
    }
    .filter-apply-btn:hover { background: var(--primary-dark); }

    .nav-actions { display: flex; align-items: center; gap: 2rem; }
    
    .nav-action-item {
      display: flex; flex-direction: column; align-items: center;
      text-decoration: none; color: #ffffff; gap: 4px; transition: 0.3s;
      position: relative;
    }
    .nav-action-item i { font-size: 1.2rem; transition: transform 0.3s; color: #ffffff; }
    .nav-action-item span { font-size: 0.75rem; font-weight: 600; color: rgba(255,255,255,0.8); }
    .nav-action-item:hover { transform: translateY(-2px); }
    .nav-action-item:hover i { color: #ffffff; }
    .nav-action-item:hover span { color: #ffffff; }
    
    .cart-badge {
      position: absolute; top: -5px; right: -8px;
      background: #f97316; color: #ffffff;
      font-size: 0.65rem; font-weight: 800; min-width: 18px; height: 18px;
      border-radius: 50%; display: flex; align-items: center; justify-content: center;
      border: 2px solid var(--primary);
    }


    @media (max-width: 1024px) {
      .nav-main-bar { grid-template-columns: auto 1fr auto; gap: 1.5rem; }
      .search-cat-dropdown { display: none; }
      .nav-main-bar { padding: 10px 1rem; }
      .search-filters-panel { grid-template-columns: repeat(2, 1fr); }
    }
    
    @media (max-width: 768px) {
      .nav-main-bar { grid-template-columns: auto auto; }
      .nav-search-wrap { grid-column: span 2; order: 2; margin-top: 10px; max-width: 100%; }
      .search-filters-panel { grid-template-columns: 1fr; }
      .filter-footer { grid-column: span 1; }
      .nav-actions { gap: 1.2rem; }
      .nav-logo img { height: 36px; }
      .logo-text { font-size: 1.4rem; }
      .navbar { padding-bottom: 12px; }
    }

    /* Hero Slider Refined */
    .hero {
      position: relative; min-height: 90vh;
      display: block;
      padding: 0; overflow: hidden;
    }
    
    .hero-slider {
        position: relative;
        width: 100%;
        height: 90vh;
    }

    .hero-slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        transition: opacity 1s ease-in-out;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        background-size: cover;
        background-position: center;
        padding: 0 2rem;
    }

    .hero-slide.active {
        opacity: 1;
        z-index: 10;
    }

    .hero-slide::before {
      content: "";
      position: absolute; inset: 0;
      background: linear-gradient(to right, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 50%, transparent 100%);
      z-index: 1;
    }

    .hero-container {
        max-width: 1400px; width: 100%; margin: 0 auto;
        position: relative;
        z-index: 2;
    }
    
    .hero-text { max-width: 650px; }

    .hero-badge {
        display: inline-block; padding: 0.5rem 1.2rem;
        background: rgba(255, 255, 255, 0.1); color: #ffffff;
        border-radius: 30px; font-size: 0.85rem; font-weight: 600;
        margin-bottom: 1.5rem; border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(5px);
    }
    
    .hero h1 { 
      font-size: 4.5rem; font-weight: 800; margin-bottom: 1.5rem; 
      line-height: 1.1; letter-spacing: -1px; color: #ffffff;
    }
    .text-gradient {
      background: linear-gradient(135deg, #60a5fa 30%, #2563eb 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    
    .hero p { 
      font-size: 1.15rem; color: rgba(255, 255, 255, 0.9); 
      max-width: 550px; margin-bottom: 2.5rem; font-weight: 400; line-height: 1.7;
    }

    /* Slider Pagination Dots */
    .slider-dots {
        position: absolute;
        bottom: 40px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 12px;
        z-index: 100;
        background: rgba(0, 0, 0, 0.2);
        padding: 10px 20px;
        border-radius: 30px;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .dot {
        width: 12px;
        height: 12px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .dot.active {
        background: var(--primary);
        transform: scale(1.3);
        border-color: rgba(255, 255, 255, 0.5);
        box-shadow: 0 0 15px var(--primary);
    }
    .dot:hover {
        background: rgba(255, 255, 255, 0.8);
    }

    /* The "Shadow Image" - Dark Square Box with Video */
    .hero-video-box {
        position: absolute;
        width: 440px; height: 440px;
        background: #05070a;
        border: 12px solid rgba(255, 255, 255, 0.4);
        border-radius: 20px;
        z-index: -1;
        box-shadow: 0 30px 60px rgba(0,0,0,0.1), inset 0 0 0 2px rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-video-box video {
        width: 150%; height: 150%;
        object-fit: cover;
        opacity: 0.5;
        filter: brightness(0.8) contrast(1.2);
    }

    .video-vignette {
        position: absolute; inset: 0;
        background: radial-gradient(circle at center, transparent 30%, rgba(0,0,0,0.7) 100%);
        z-index: 1;
    }

    .floating-shoe {
        width: 100%; max-width: 420px;
        filter: drop-shadow(0 40px 50px rgba(0,0,0,0.8));
        transform: rotate(-12deg); 
        animation: float 6s ease-in-out infinite;
        z-index: 2;
    }
    
    .glow-effect {
        display: none; /* Removing old glow to prioritize spotlight */
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-15px); }
    }

    /* Bento Grid Categories - Model Inspired */
    #categories { padding: 100px 2rem; background: transparent; }
    
    .cat-grid {
      max-width: 1400px; margin: 0 auto;
      display: grid; grid-template-columns: repeat(4, 1fr);
      grid-template-rows: repeat(2, 320px);
      gap: 1.5rem;
    }
    
    /* Bento Layout: 1 Large Left, 1 Wide Top Right, 2 Small Bottom Right */
    .cat-card:nth-child(1) { grid-column: span 2; grid-row: span 2; }
    .cat-card:nth-child(2) { grid-column: span 2; }
    .cat-card:nth-child(3), .cat-card:nth-child(4) { grid-column: span 1; }

    .cat-card {
      background: #0f172a; border-radius: 32px; /* Smoother rounding */
      border: 1px solid rgba(255,255,255,0.08); /* More defined border */
      position: relative; overflow: hidden; text-decoration: none;
      transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 15px 35px rgba(0,0,0,0.4);
    }
    
    .cat-card img {
      width: 100%; height: 100%; object-fit: cover;
      transition: transform 0.8s ease; opacity: 0.8;
      filter: saturate(1.1) brightness(0.9);
    }
    
    .cat-card:hover { 
      border-color: var(--primary); 
      transform: translateY(-5px); 
      box-shadow: 0 0 25px rgba(16, 185, 129, 0.4), 0 15px 45px rgba(0,0,0,0.6); 
    }
    .cat-card:hover img { transform: scale(1.08); opacity: 0.6; }
    
    .cat-overlay {
      position: absolute; inset: 0;
      padding: 2rem;
      background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.2) 50%, transparent 100%);
      display: flex; flex-direction: column; justify-content: flex-end;
    }

    .cat-title {
      font-family: 'Inter', sans-serif; font-size: 1.8rem; font-weight: 800;
      color: var(--primary-light); 
      margin-bottom: 0.2rem;
      text-transform: capitalize;
    }
    .cat-badge {
        font-size: 0.75rem; color: #94A3B8; text-transform: capitalize;
        margin-bottom: 0.2rem; font-weight: 600; letter-spacing: 0.5px;
        opacity: 0.8;
    }

    /* Featured Products */
    #products { padding: 100px 2rem; background: transparent; }
    
    .products-grid {
      max-width: 1400px; margin: 0 auto;
      display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
      gap: 2.5rem;
    }
    

    .product-card {
      background: var(--light-card); border-radius: 24px;
      overflow: hidden; text-decoration: none;
      transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
      display: block;
      border: 1px solid var(--light-border);
      position: relative;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 50px rgba(37, 99, 235, 0.1);
        border-color: var(--primary-light);
    }
    
    .product-img-wrapper {
      position: relative;
      background: radial-gradient(circle at 30% 30%, #eff6ff, #f8fafc);
      padding: 2.5rem;
      margin: 12px;
      border-radius: 18px;
      height: 320px; display: flex; align-items: center; justify-content: center;
      overflow: hidden;
    }
    
    .product-img-wrapper img {
      max-width: 100%; max-height: 100%;
      object-fit: contain;
      filter: drop-shadow(0 20px 40px rgba(0,0,0,0.4));
      transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .product-card:hover .product-img-wrapper img {
      transform: scale(1.12) rotate(-5deg);
    }

    /* Premium Price Tag */
    .product-price-tag {
        position: absolute; top: 16px; left: 16px;
        background: var(--primary); color: #fff;
        padding: 6px 14px; border-radius: 10px;
        font-weight: 800; font-size: 0.95rem;
        z-index: 10;
        box-shadow: 0 8px 16px rgba(37, 99, 235, 0.2);
        display: flex; align-items: center; gap: 2px;
    }
    .product-price-tag span { font-size: 0.75rem; opacity: 0.9; }

    /* Floating Header Style for Category Section */
    .section-header { text-align: center; margin-bottom: 4rem; }
    .section-header h2 {
      font-family: 'Inter', sans-serif; font-size: 3rem; font-weight: 800;
      color: var(--text-main); letter-spacing: -1px; line-height: 1; margin: 0;
    }

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
      color: var(--primary); font-size: 0.75rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;
      display: block;
    }
    
    .product-title {
      font-family: var(--font-body); /* Modern clean font for title */
      font-weight: 600; font-size: 1.1rem; margin-bottom: 0;
      color: var(--text-main); line-height: 1.4;
    }

    .section-header h2 {
      font-size: 2.5rem; margin-bottom: 1rem;
      position: relative; display: inline-block;
      color: var(--text-main); font-weight: 700;
    }

    /* Footer Refined */
    footer {
      background: #05070a; border-top: 1px solid rgba(255,255,255,0.05);
      padding: 80px 0 40px; color: #ffffff;
    }
    .footer-container {
        max-width: 1400px; margin: 0 auto; padding: 0 2rem;
        display: grid; grid-template-columns: 1.2fr 2fr; gap: 4rem;
    }
    
    /* Footer Card */
    .footer-card {
        background: #0a0e17; /* Deep black card background */
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 24px; padding: 3rem;
        display: flex; flex-direction: column; gap: 1.5rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    .footer-logo {
        display: flex; align-items: center; gap: 10px; text-decoration: none;
    }
    .brand-text {
        font-family: var(--font-heading); font-size: 24px; font-weight: 700; line-height: 1;
    }
    .footer-desc {
        color: rgba(255,255,255,0.8); font-size: 0.95rem; line-height: 1.6; margin-bottom: 0.5rem;
    }
    
    .contact-info { display: flex; flex-direction: column; gap: 0.8rem; }
    .contact-item {
        display: flex; align-items: center; gap: 10px;
        color: #ffffff; font-size: 0.9rem;
    }
    .contact-item i { color: var(--primary); width: 20px; }
    
    .social-links {
        display: flex; gap: 1rem; margin-top: 1rem;
    }
    .social-btn {
        width: 40px; height: 40px; border-radius: 10px;
        background: rgba(255,255,255,0.05);
        display: flex; align-items: center; justify-content: center;
        color: #ffffff; text-decoration: none; transition: 0.3s;
    }
    .social-btn:hover {
        background: var(--primary); color: #fff; transform: translateY(-3px);
    }
    
    /* Footer Grid */
    .footer-nav-grid {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;
    }
    
    .footer-col h4 {
        color: #10b981; 
        font-size: 0.85rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.5rem;
    }
    
    .footer-links { list-style: none; padding: 0; margin: 0; }
    .footer-links li { margin-bottom: 1rem; }
    .footer-links a {
        color: #ffffff; text-decoration: none; font-size: 0.95rem;
        transition: 0.3s;
    }
    .footer-links a:hover { color: #10b981; padding-left: 5px; }

    /* Features Section - Model Inspired */
    #features { padding: 80px 2rem; background: transparent; }
    .features-box {
      max-width: 1400px; margin: 0 auto;
      background: #f8fafc; border-radius: 32px;
      padding: 5rem 3rem; border: 1px solid var(--light-border);
      box-shadow: 0 10px 40px rgba(0,0,0,0.02);
    }
    .features-box h2 {
      font-family: 'Inter', sans-serif; font-size: 2.5rem; font-weight: 800;
      color: var(--text-main); text-align: center; margin-bottom: 4rem; line-height: 1.2;
    }
    .features-grid {
      display: grid; grid-template-columns: repeat(3, 1fr); gap: 4rem;
    }
    .feature-card { text-align: center; }
    .feature-icon {
      width: 56px; height: 56px; background: var(--primary-dim);
      border-radius: 14px; display: flex; align-items: center; justify-content: center;
      margin: 0 auto 1.5rem; color: var(--primary); font-size: 1.5rem;
      transition: all 0.3s ease;
    }
    .feature-card:hover .feature-icon {
      background: var(--primary); color: #fff; transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
    }
    .feature-card h3 {
      font-family: 'Inter', sans-serif; color: var(--text-main); font-size: 1.25rem;
      font-weight: 700; margin-bottom: 1rem;
    }
    .feature-card p {
      color: var(--text-muted); font-size: 0.95rem; line-height: 1.7;
      max-width: 300px; margin: 0 auto;
    }

    @media (max-width: 1024px) {
      .features-grid { grid-template-columns: repeat(2, 1fr); gap: 3rem; }
    }
    @media (max-width: 768px) {
      .features-grid { grid-template-columns: 1fr; }
      .features-box { padding: 4rem 2rem; }
    }

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

  <!-- Main Interaction Bar -->
  <div class="nav-main-bar">
    <!-- Logo -->
    <a href="index.php" class="nav-logo">
      <img src="assets/shoe_logo_green.png" alt="WalkOn Logo">
      <div class="logo-text">
        <span style="color: #ffffff;">WALK</span><span style="color: #10b981;">ON</span>
      </div>
    </a>

    <!-- Search Bar -->
    <div class="nav-search-wrap">
      <form action="shop.php" method="GET" class="nav-search-inner" id="navSearchForm">
        <select name="category" class="search-cat-dropdown">
          <option value="all">Categories</option>
          <?php foreach($all_categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
          <?php endforeach; ?>
        </select>
        <input type="text" name="search" id="mainSearchInput" class="search-input" placeholder="Search for Products, Brands and More" autocomplete="off">
        
        <div class="search-suggestions" id="searchSuggestions"></div>

        <button type="button" class="filter-toggle-btn" onclick="toggleSearchFilters()" title="Advanced Filters">
          <i class="fas fa-sliders-h"></i>
        </button>

        <button type="submit" class="search-btn">
          <i class="fas fa-search"></i>
        </button>

        <!-- Advanced Filter Panel -->
        <div class="search-filters-panel" id="searchFilters">
          <div class="filter-group">
            <span class="filter-group-label"><i class="fas fa-tag"></i> Brand</span>
            <select name="brand" class="filter-select">
              <option value="0">All Brands</option>
              <?php foreach($search_brands as $b): ?>
                <option value="<?= $b['id'] ?>"><?= $b['name'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="filter-group">
            <span class="filter-group-label"><i class="fas fa-user-friends"></i> Gender</span>
            <select name="gender" class="filter-select">
              <option value="">Any Gender</option>
              <option value="Men">Men</option>
              <option value="Women">Women</option>
              <option value="Kids">Kids</option>
              <option value="Unisex">Unisex</option>
            </select>
          </div>

          <div class="filter-group">
            <span class="filter-group-label"><i class="fas fa-star"></i> Special</span>
            <div class="verify-toggle-wrap">
              <span class="verify-toggle-text">Verified Only</span>
              <input type="checkbox" name="verified_only" value="1" style="width: 18px; height: 18px; accent-color: var(--primary);">
            </div>
          </div>

          <div class="filter-footer">
            <button type="button" class="filter-reset-btn" onclick="resetFilters()">Reset All</button>
            <button type="submit" class="filter-apply-btn">Apply Filters</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Actions -->
    <div class="nav-actions">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="dashboard.php" class="nav-action-item">
          <i class="fas fa-user-circle"></i>
          <span>Account</span>
        </a>
        <a href="logout.php" class="nav-action-item">
          <i class="fas fa-sign-out-alt"></i>
          <span>Logout</span>
        </a>
      <?php else: ?>
        <a href="login.php" class="nav-action-item">
          <i class="far fa-user"></i>
          <span>Login</span>
        </a>
      <?php endif; ?>



      <a href="cart.php" class="nav-action-item">
        <div style="position: relative;">
          <i class="fas fa-shopping-cart"></i>
          <div class="cart-badge">0</div>
        </div>
        <span>Cart</span>
      </a>

    </div>
  </div>
</nav>

<section class="hero">
  <div class="hero-slider">
    <!-- Slide 1: Nike Collection -->
    <div class="hero-slide active" style="background-image: url('https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=2070&auto=format&fit=crop');">
      <div class="hero-container">
        <div class="hero-text">
            <span class="hero-badge">Nike Collection</span>
            <h1>Step into the <br><span class="text-gradient">Next Dimension</span></h1>
            <p>The global multi-channel infrastructure for premium footwear. Sync your inventory across Amazon, Flipkart, shopify, and more.</p>
            <div class="hero-btns">
                <a href="shop.php?brand=nike" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1rem; background: #2563eb; border: none; color:#fff; text-decoration:none; border-radius:10px; font-weight:700;">Explore Nike</a>
            </div>
        </div>
      </div>
    </div>

    <!-- Slide 2: Puma Lifestyle -->
    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=2074&auto=format&fit=crop');">
      <div class="hero-container">
        <div class="hero-text">
            <span class="hero-badge">Puma Lifestyle</span>
            <h1>Step into the <br><span class="text-gradient">Next Dimension</span></h1>
            <p>The global multi-channel infrastructure for premium footwear. Sync your inventory across Amazon, Flipkart, shopify, and more.</p>
            <div class="hero-btns">
                <a href="shop.php?brand=puma" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1rem; background: #2563eb; border: none; color:#fff; text-decoration:none; border-radius:10px; font-weight:700;">Explore Puma</a>
            </div>
        </div>
      </div>
    </div>

    <!-- Slide 3: New Balance Classic -->
    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1539185441755-769473a23570?q=80&w=2071&auto=format&fit=crop');">
      <div class="hero-container">
        <div class="hero-text">
            <span class="hero-badge">New Balance Classic</span>
            <h1>Step into the <br><span class="text-gradient">Next Dimension</span></h1>
            <p>The global multi-channel infrastructure for premium footwear. Sync your inventory across Amazon, Flipkart, shopify, and more.</p>
            <div class="hero-btns">
                <a href="shop.php?brand=new_balance" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1rem; background: #2563eb; border: none; color:#fff; text-decoration:none; border-radius:10px; font-weight:700;">Explore NB</a>
            </div>
        </div>
      </div>
    </div>

    <!-- Slide 4: Sparx Dynamic -->
    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1560769629-975ec94e6a86?q=80&w=1964&auto=format&fit=crop');">
      <div class="hero-container">
        <div class="hero-text">
            <span class="hero-badge">Sparx Dynamic</span>
            <h1>Step into the <br><span class="text-gradient">Next Dimension</span></h1>
            <p>The global multi-channel infrastructure for premium footwear. Sync your inventory across Amazon, Flipkart, shopify, and more.</p>
            <div class="hero-btns">
                <a href="shop.php?brand=sparx" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1rem; background: #2563eb; border: none; color:#fff; text-decoration:none; border-radius:10px; font-weight:700;">Explore Sparx</a>
            </div>
        </div>
      </div>
    </div>

    <!-- Slide 5: Global Casual Hub -->
    <div class="hero-slide" style="background-image: url('assets/casual_hero.png');">
      <div class="hero-container">
        <div class="hero-text">
            <span class="hero-badge">Global Casual Hub</span>
            <h1>Step into the <br><span class="text-gradient">Next Dimension</span></h1>
            <p>The global multi-channel infrastructure for premium footwear. Sync your inventory across Amazon, Flipkart, shopify, and more.</p>
            <div class="hero-btns">
                <a href="shop.php" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1rem; background: #2563eb; border: none; color:#fff; text-decoration:none; border-radius:10px; font-weight:700;">Explore All</a>
            </div>
        </div>
      </div>
    </div>

    <!-- Controls (Dots) -->
    <div class="slider-dots" id="sliderDots">
      <div class="dot active" onclick="goToSlide(0)"></div>
      <div class="dot" onclick="goToSlide(1)"></div>
      <div class="dot" onclick="goToSlide(2)"></div>
      <div class="dot" onclick="goToSlide(3)"></div>
      <div class="dot" onclick="goToSlide(4)"></div>
    </div>
  </div>
</section>




<section id="categories">
  <div class="section-header">
    <h2>Shop by Category</h2>
  </div>
  
  <div class="cat-grid">
    <?php foreach($bento_categories as $index => $cat): ?>
      <a href="shop.php?category=<?= $cat['id'] ?>" class="cat-card">
        <img src="<?= $cat['image'] ?>" alt="<?= $cat['name'] ?>">
        <div class="cat-overlay">
          <span class="cat-badge"><?= $index === 0 ? 'Featured Collection' : 'Explore' ?></span>
          <h3 class="cat-title"><?= $cat['name'] ?></h3>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- Features Section - Refined Bento-Style -->
<section id="features">
    <div class="features-box">
        <h2>Why Leading Brands <br> Choose WalkOn</h2>
        
        <div class="features-grid">
            <!-- Feature 1 -->
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h3>Multi-Channel Sync</h3>
                <p>Instant inventory and order synchronization across 15+ global marketplaces.</p>
            </div>
            
            <!-- Feature 2 -->
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Smart Analytics</h3>
                <p>Deep insights into your sales performance with AI-driven forecasting.</p>
            </div>
            
            <!-- Feature 3 -->
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3>Auto-Pricing</h3>
                <p>Stay competitive with real-time price matching algorithms.</p>
            </div>
        </div>
    </div>
</section>

<section id="products">
  <div class="section-header" style="text-align: center; margin-bottom: 3rem;">
    <h5 style="color: #10b981; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 0.5rem; font-size: 0.9rem;">New Arrivals</h5>
    <h2 style="color: var(--text-main); margin-bottom: 0;">Featured Products</h2>
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

<!-- Marketplace Hub Section - Premium Multi-Channel Showcase -->
<section id="marketplaces" style="padding: 100px 2rem; background: transparent;">
    <div class="features-box" style="border-color: var(--light-border); background: #ffffff;">
        <div style="text-align: center; margin-bottom: 4rem;">
            <span style="color: var(--primary); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; font-size: 0.8rem;">Global Infrastructure</span>
            <h2 style="margin-top: 1rem;">Seamless Single-Inventory <br> <span class="text-gradient">Multi-Channel Sync</span></h2>
            <p style="margin: 1.5rem auto; color: var(--text-muted); max-width: 600px;">Connect your store to the world's most powerful marketplaces. One dashboard, infinite reach.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 2rem; align-items: center; justify-items: center; opacity: 0.8;">
            <!-- Simulating Logos with Premium Iconography/Typography -->
            <div style="text-align: center; filter: grayscale(1); transition: 0.3s;" onmouseover="this.style.filter='grayscale(0)';" onmouseout="this.style.filter='grayscale(1)';">
                <i class="fab fa-amazon" style="font-size: 2.5rem; color: #ff9900; margin-bottom: 0.5rem;"></i>
                <div style="font-weight: 700; font-size: 0.7rem; letter-spacing: 1px;">AMAZON</div>
            </div>
            <div style="text-align: center; filter: grayscale(1); transition: 0.3s;" onmouseover="this.style.filter='grayscale(0)';" onmouseout="this.style.filter='grayscale(1)';">
                <i class="fas fa-shopping-bag" style="font-size: 2.5rem; color: #2874f0; margin-bottom: 0.5rem;"></i>
                <div style="font-weight: 700; font-size: 0.7rem; letter-spacing: 1px;">FLIPKART</div>
            </div>
            <div style="text-align: center; filter: grayscale(1); transition: 0.3s;" onmouseover="this.style.filter='grayscale(0)';" onmouseout="this.style.filter='grayscale(1)';">
                <i class="fab fa-shopify" style="font-size: 2.5rem; color: #96bf48; margin-bottom: 0.5rem;"></i>
                <div style="font-weight: 700; font-size: 0.7rem; letter-spacing: 1px;">SHOPIFY</div>
            </div>
            <div style="text-align: center; filter: grayscale(1); transition: 0.3s;" onmouseover="this.style.filter='grayscale(0)';" onmouseout="this.style.filter='grayscale(1)';">
                <i class="fab fa-tiktok" style="font-size: 2.5rem; color: #000; margin-bottom: 0.5rem;"></i>
                <div style="font-weight: 700; font-size: 0.7rem; letter-spacing: 1px;">TIKTOK SHOP</div>
            </div>
            <div style="text-align: center; filter: grayscale(1); transition: 0.3s;" onmouseover="this.style.filter='grayscale(0)';" onmouseout="this.style.filter='grayscale(1)';">
                <i class="fab fa-ebay" style="font-size: 2.5rem; color: #e53238; margin-bottom: 0.5rem;"></i>
                <div style="font-weight: 700; font-size: 0.7rem; letter-spacing: 1px;">EBAY</div>
            </div>
            <div style="text-align: center; filter: grayscale(1); transition: 0.3s;" onmouseover="this.style.filter='grayscale(0)';" onmouseout="this.style.filter='grayscale(1)';">
                <i class="fab fa-instagram" style="font-size: 2.5rem; color: #c13584; margin-bottom: 0.5rem;"></i>
                <div style="font-weight: 700; font-size: 0.7rem; letter-spacing: 1px;">INSTAGRAM</div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 5rem;">
            <a href="start_selling.php" class="btn btn-primary" style="padding: 1rem 2.5rem; box-shadow: 0 10px 30px rgba(37, 99, 235, 0.2);">Become a Partner Seller</a>
        </div>
    </div>
</section>

<!-- Footer Section -->
<footer>
    <div class="footer-container">
        <!-- Left Card: Branding + Contact -->
        <div class="footer-card">
            <a href="Index.php" class="footer-logo">
                <img src="assets/shoe_logo_green.png" alt="WalkOn Logo" style="height: 50px; width: auto; filter: drop-shadow(0 0 8px rgba(16, 185, 129, 0.2));">
                <div class="brand-text">
                    <span style="color: #ffffff;">WALK</span><span style="color: #10b981;">ON</span>
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
            <!-- Navigation Column -->
            <div class="footer-col">
                <h4>NAVIGATION</h4>
                <ul class="footer-links">
                    <li><a href="Index.php">Home</a></li>
                    <li><a href="#categories">Categories</a></li>
                    <li><a href="shop.php">Shop</a></li>
                    <li><a href="sellers.php">Our Sellers</a></li>
                    <li><a href="marketplaces.php">Marketplace</a></li>
                </ul>
            </div>
            
            <!-- Shops Column -->
            <div class="footer-col">
                <h4>SHOPS</h4>
                <ul class="footer-links">
                    <li><a href="shop.php">All Products</a></li>
                    <li><a href="shop.php?category=2">Boots</a></li>
                    <li><a href="shop.php?category=5">Formal Shoes</a></li>
                    <li><a href="shop.php?category=4">Running Shoes</a></li>
                    <li><a href="shop.php?category=6">Sandals & Slides</a></li>
                    <li><a href="shop.php?category=1">Sneakers</a></li>
                    <li><a href="shop.php?category=3">Sports</a></li>
                </ul>
            </div>
            
            <!-- Brands Column -->
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

<script>
function toggleSearchFilters() {
    const panel = document.getElementById('searchFilters');
    const btn = document.querySelector('.filter-toggle-btn');
    panel.classList.toggle('active');
    btn.classList.toggle('active');
    
    // Close panel when clicking outside
    if(panel.classList.contains('active')) {
        const closeHandler = (e) => {
            if(!panel.contains(e.target) && !btn.contains(e.target)) {
                panel.classList.remove('active');
                btn.classList.remove('active');
                document.removeEventListener('click', closeHandler);
            }
        };
        setTimeout(() => document.addEventListener('click', closeHandler), 10);
    }
}

function resetFilters() {
    const form = document.getElementById('navSearchForm');
    form.querySelector('[name="brand"]').value = "0";
    form.querySelector('[name="gender"]').value = "";
    form.querySelector('[name="verified_only"]').checked = false;
    form.querySelector('[name="category"]').value = "all";
}

// Autocomplete Logic
const searchInput = document.getElementById('mainSearchInput');
const suggestionsBox = document.getElementById('searchSuggestions');

searchInput.addEventListener('input', function() {
    const query = this.value.trim();
    if (query.length < 2) {
        suggestionsBox.style.display = 'none';
        return;
    }

    fetch(`get_suggestions.php?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                suggestionsBox.innerHTML = '';
                data.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'suggestion-item';
                    div.innerHTML = `<i class="fas fa-search"></i> ${item}`;
                    div.onclick = () => {
                        searchInput.value = item;
                        suggestionsBox.style.display = 'none';
                        document.getElementById('navSearchForm').submit();
                    };
                    suggestionsBox.appendChild(div);
                });
                suggestionsBox.style.display = 'block';
            } else {
                suggestionsBox.style.display = 'none';
            }
        });
});

// Hide suggestions when clicking outside
document.addEventListener('click', (e) => {
    if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
        suggestionsBox.style.display = 'none';
    }
});

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
// Hero Slider Logic
let currentSlide = 0;
const slides = document.querySelectorAll('.hero-slide');
const totalSlides = slides.length;

function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    currentSlide = (index + totalSlides) % totalSlides;
    slides[currentSlide].classList.add('active');
    
    // Update dots
    const dots = document.querySelectorAll('.dot');
    dots.forEach(dot => dot.classList.remove('active'));
    if(dots[currentSlide]) {
        dots[currentSlide].classList.add('active');
    }
}

function moveSlide(step) {
    showSlide(currentSlide + step);
    resetAutoAdvance();
}

function goToSlide(index) {
    showSlide(index);
    resetAutoAdvance();
}

let slideInterval = setInterval(() => moveSlide(1), 5000);

function resetAutoAdvance() {
    clearInterval(slideInterval);
    slideInterval = setInterval(() => moveSlide(1), 5000);
}
</script>


<?php include 'includes/chatbot.php'; ?>
</body>
</html>
