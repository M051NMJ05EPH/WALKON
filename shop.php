<?php
session_start();
include 'config.php';

// 1. Get Filters
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$brand_id = isset($_GET['brand']) ? intval($_GET['brand']) : 0;
$outer_material = isset($_GET['material']) ? $_GET['material'] : '';
$gender = isset($_GET['gender']) ? $_GET['gender'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// 2. Fetch Filter Data
try {
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $brands = $pdo->query("SELECT id, name FROM brands ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $materials = $pdo->query("SELECT DISTINCT outer_material FROM product_specs WHERE outer_material IS NOT NULL AND outer_material != ''")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = $brands = $materials = [];
}

// 3. Build Product Query
$sql = "SELECT DISTINCT pb.id, pb.name, pp.price, pp.max_price, c.name as category_name, b.name as brand_name,
        (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as primary_image,
        (SELECT url FROM product_media pm WHERE pm.product_id = pb.id LIMIT 1) as fallback_image
        FROM product_base pb
        LEFT JOIN product_prices pp ON pb.id = pp.product_id
        LEFT JOIN categories c ON pb.category_id = c.id
        LEFT JOIN product_specs spec ON pb.id = spec.product_id
        LEFT JOIN brands b ON spec.brand_id = b.id
        WHERE pb.status = 'published'";

$params = [];

if ($category_id > 0) {
    $sql .= " AND pb.category_id = ?";
    $params[] = $category_id;
}
if ($brand_id > 0) {
    $sql .= " AND spec.brand_id = ?";
    $params[] = $brand_id;
}
if (!empty($outer_material)) {
    $sql .= " AND spec.outer_material = ?";
    $params[] = $outer_material;
}
if (!empty($gender)) {
    $sql .= " AND spec.gender = ?";
    $params[] = $gender;
}
if (!empty($search_query)) {
    $sql .= " AND (pb.name LIKE ?)";
    $params[] = "%$search_query%";
}

// Sorting
switch ($sort) {
    case 'price_low': $sql .= " ORDER BY pp.price ASC"; break;
    case 'price_high': $sql .= " ORDER BY pp.price DESC"; break;
    default: $sql .= " ORDER BY pb.created_at DESC"; break;
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - WALKON Premium Marketplace</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --bg: #030712;
            --card-bg: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #f9fafb;
            --text-muted: #9ca3af;
            --glass: rgba(17, 24, 39, 0.8);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Outfit', sans-serif; 
            background: var(--bg); 
            color: var(--text-main);
            line-height: 1.6;
        }

        .container { max-width: 1400px; margin: 0 auto; padding: 0 2rem; }

        /* Navbar (Sleek) */
        .navbar {
            background: var(--glass);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 1rem 0;
            position: sticky; top: 0; z-index: 100;
        }
        .nav-inner { display: flex; justify-content: space-between; align-items: center; }
        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo i {
            color: var(--primary);
        }

        .shop-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 3rem;
            margin-top: 3rem;
            align-items: start;
        }

        /* Sidebar Filters */
        .sidebar {
            position: sticky; top: 100px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 2rem;
        }
        .sidebar-section { margin-bottom: 2.5rem; }
        .sidebar-section h3 { 
            font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1.5px;
            color: var(--text-muted); margin-bottom: 1.25rem; font-weight: 700;
        }
        .filter-list { list-style: none; }
        .filter-item { margin-bottom: 0.75rem; }
        .filter-link { 
            text-decoration: none; color: var(--text-muted); font-size: 0.95rem;
            display: flex; justify-content: space-between; align-items: center;
            transition: 0.3s;
        }
        .filter-link:hover, .filter-link.active { color: var(--primary); }
        .filter-link.active { font-weight: 600; }

        /* Search Bar */
        .search-box {
            position: relative; margin-bottom: 2rem;
        }
        .search-box input {
            width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem;
            background: rgba(255,255,255,0.05); border: 1px solid var(--border);
            border-radius: 12px; color: white; outline: none; transition: 0.3s;
        }
        .search-box input:focus { border-color: var(--primary); }
        .search-box i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); }

        /* Clear Filters */
        .clear-btn {
            display: block; width: 100%; padding: 0.75rem; text-align: center;
            background: rgba(239, 68, 68, 0.1); color: #ef4444;
            border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 0.9rem;
            margin-top: 1rem; transition: 0.3s;
        }
        .clear-btn:hover { background: rgba(239, 68, 68, 0.2); }

        /* Main Content */
        .results-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 2rem;
        }
        .results-header h1 { font-size: 2rem; font-weight: 800; }
        
        .sort-select {
            background: var(--card-bg); border: 1px solid var(--border);
            color: white; padding: 0.5rem 1rem; border-radius: 10px; outline: none;
        }

        .product-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem;
        }
        .product-card {
            background: var(--card-bg); border: 1px solid var(--border);
            border-radius: 24px; padding: 1.25rem; text-decoration: none; color: white;
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .product-card:hover { 
            border-color: var(--primary); transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }
        .img-wrap {
            height: 240px; background: #0f172a; border-radius: 16px;
            margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        .img-wrap img { width: 100%; height: 100%; object-fit: contain; }
        
        .brand { font-size: 0.75rem; color: var(--primary); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.25rem; }
        .name { font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem; height: 1.4em; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
        .price-box { display: flex; align-items: center; gap: 10px; }
        .price { font-size: 1.25rem; font-weight: 800; color: white; }
        .old-price { text-decoration: line-through; color: var(--text-muted); font-size: 0.9rem; }

        .no-results {
            text-align: center; padding: 100px 0; grid-column: 1 / -1;
        }
        .no-results i { font-size: 4rem; color: var(--border); margin-bottom: 1rem; }


        @media (max-width: 1024px) {
            .shop-layout { grid-template-columns: 1fr; }
            .sidebar { position: static; margin-bottom: 2rem; }
        }

        /* PREMIUM FOOTER STYLES */
        :root {
            --footer-green: #10b981;
            --footer-bg: #05070A;
            --footer-border: #2A3241;
        }
        /* Footer Refined */
        footer {
          background: #05070A !important; border-top: 1px solid var(--footer-border) !important;
          padding: 80px 0 40px !important; color: #fff !important;
          margin-top: 100px;
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
            font-family: 'Outfit', sans-serif; font-size: 24px; font-weight: 700; line-height: 1;
        }
        .footer-desc {
            color: #94a3b8; font-size: 0.95rem; line-height: 1.6; margin-bottom: 0.5rem;
        }
        
        .contact-info { display: flex; flex-direction: column; gap: 0.8rem; }
        .contact-item {
            display: flex; align-items: center; gap: 10px;
            color: #fff; font-size: 0.9rem;
        }
        .contact-item i { color: var(--footer-green); width: 20px; }
        
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
            background: var(--footer-green); color: #000; transform: translateY(-3px);
        }
        
        /* Footer Grid */
        .footer-nav-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;
        }
        
        .footer-col h4 {
            color: var(--footer-green); font-size: 0.85rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.5rem;
        }
        
        .footer-links { list-style: none; padding: 0; margin: 0; }
        .footer-links li { margin-bottom: 1rem; }
        .footer-links a {
            color: #e2e8f0; text-decoration: none; font-size: 0.95rem;
            transition: 0.3s;
        }
        .footer-links a:hover { color: var(--footer-green); padding-left: 5px; }

        @media (max-width: 1024px) {
            .footer-container { grid-template-columns: 1fr; }
            .footer-card { max-width: 500px; }
        }
        @media (max-width: 768px) {
            .footer-nav-grid { grid-template-columns: 1fr 1fr; }
        }
        
        @media (max-width: 900px) {
          .footer-grid { grid-template-columns: 1fr 1fr; gap: 3rem; }
        }
        @media (max-width: 600px) {
          .footer-grid { grid-template-columns: 1fr; }
          .footer-bottom { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container nav-inner">
        <a href="index.php" class="logo" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
            <img src="assets/shoe_logo_green.png" alt="WalkOn Logo" style="height: 48px; width: auto;">
            <div style="font-family: 'Outfit', sans-serif; font-size: 32px; font-weight: 700; line-height: 1;">
                <span style="color: #fff;">WALK</span><span style="color: #10b981;">ON</span>
            </div>
        </a>
        <div style="display:flex; gap: 2rem;">
            <a href="index.php" style="color:white; text-decoration:none; font-weight:500;">Home</a>
            <a href="wishlist.php" style="color:white; text-decoration:none; font-weight:500;">Wishlist</a>
            <a href="dashboard.php" style="color:var(--text-muted); text-decoration:none; font-weight:500;">Dashboard</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="shop-layout">
        <aside class="sidebar">
            <div class="search-box">
                <form action="shop.php" method="GET">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search_query) ?>">
                </form>
            </div>

            <div class="sidebar-section">
                <h3>Categories</h3>
                <ul class="filter-list">
                    <li class="filter-item">
                        <a href="shop.php" class="filter-link <?= $category_id == 0 ? 'active' : '' ?>">All Collections</a>
                    </li>
                    <?php foreach($categories as $cat): ?>
                        <li class="filter-item">
                            <a href="shop.php?category=<?= $cat['id'] ?>&brand=<?= $brand_id ?>" class="filter-link <?= $category_id == $cat['id'] ? 'active' : '' ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sidebar-section">
                <h3>Brands</h3>
                <ul class="filter-list">
                    <li class="filter-item">
                        <a href="shop.php?category=<?= $category_id ?>" class="filter-link <?= $brand_id == 0 ? 'active' : '' ?>">All Brands</a>
                    </li>
                    <?php foreach($brands as $b): ?>
                        <li class="filter-item">
                            <a href="shop.php?brand=<?= $b['id'] ?>&category=<?= $category_id ?>" class="filter-link <?= $brand_id == $b['id'] ? 'active' : '' ?>">
                                <?= htmlspecialchars($b['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sidebar-section">
                <h3>Gender</h3>
                <ul class="filter-list">
                    <li class="filter-item">
                        <a href="shop.php?category=<?= $category_id ?>&brand=<?= $brand_id ?>" class="filter-link <?= empty($gender) ? 'active' : '' ?>">All Genders</a>
                    </li>
                    <?php 
                    $gender_options = ['Men', 'Women', 'Boys', 'Girls', 'Kids', 'Babies', 'Unisex'];
                    foreach($gender_options as $g): 
                    ?>
                        <li class="filter-item">
                            <a href="shop.php?gender=<?= urlencode($g) ?>&category=<?= $category_id ?>&brand=<?= $brand_id ?>" class="filter-link <?= $gender == $g ? 'active' : '' ?>">
                                <?= $g ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sidebar-section">
                <h3>Material</h3>
                <ul class="filter-list">
                    <li class="filter-item">
                        <a href="shop.php?category=<?= $category_id ?>&brand=<?= $brand_id ?>" class="filter-link <?= empty($outer_material) ? 'active' : '' ?>">Any Material</a>
                    </li>
                    <?php foreach($materials as $m): ?>
                        <li class="filter-item">
                            <a href="shop.php?material=<?= urlencode($m) ?>&category=<?= $category_id ?>&brand=<?= $brand_id ?>" class="filter-link <?= $outer_material == $m ? 'active' : '' ?>">
                                <?= htmlspecialchars($m) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php if($category_id || $brand_id || $outer_material || $gender || $search_query): ?>
                <a href="shop.php" class="clear-btn">Clear All Filters</a>
            <?php endif; ?>
        </aside>

        <main>
            <div class="results-header">
                <div>
                    <h1 style="color:var(--text-muted); font-size: 0.8rem; text-transform:uppercase; letter-spacing:1px; margin-bottom: 0.5rem;">Marketplace</h1>
                    <h2 style="font-size: 2.2rem; letter-spacing: -1px; margin: 0;">
                        <?php 
                        if(!empty($search_query)) echo 'Search: "' . htmlspecialchars($search_query) . '"';
                        else echo 'Premium Selection';
                        ?>
                    </h2>
                </div>
                <form action="shop.php" method="GET" id="sortForm">
                    <input type="hidden" name="category" value="<?= $category_id ?>">
                    <input type="hidden" name="brand" value="<?= $brand_id ?>">
                    <input type="hidden" name="gender" value="<?= htmlspecialchars($gender) ?>">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search_query) ?>">
                    <select name="sort" class="sort-select" onchange="document.getElementById('sortForm').submit()">
                        <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                    </select>
                </form>
            </div>

            <div class="product-grid">
                <?php if (empty($products)): ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>No matches found</h3>
                        <p style="color:var(--text-muted);">Try adjusting your filters or search query.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($products as $idx => $p): 
                        $img = $p['primary_image'] ?? $p['fallback_image'] ?? 'https://via.placeholder.com/400?text=No+Image';
                        $is_active = (isset($_GET['search']) && $_GET['search'] == $p['name']) || ($idx === 0 && empty($search_query));
                    ?>
                        <div class="product-card" id="card-<?= $p['id'] ?>" style="<?= $is_active ? 'border-color: var(--primary); box-shadow: 0 0 20px rgba(16, 185, 129, 0.15);' : '' ?>">
                            <!-- Wishlist Button -->
                            <div class="wishlist-btn" onclick="toggleWishlist(event, <?= $p['id'] ?>)" style="position: absolute; top: 12px; right: 12px; z-index: 10; width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: #fff; cursor: pointer; transition: 0.3s;">
                                <i class="far fa-heart"></i>
                            </div>
                            
                            <a href="product_details.php?id=<?= $p['id'] ?>" style="text-decoration:none; color:inherit; display:block;">
                                <div class="img-wrap" style="background: #0a0f1d;">
                                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                                </div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 1px; margin-bottom: 0.25rem;">
                                    <?= htmlspecialchars($p['category_name'] ?? 'Footwear') ?>
                                </div>
                                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.75rem; color: white;">
                                    <?= htmlspecialchars($p['name']) ?>
                                </h3>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="price-box">
                                        <span style="font-size: 1.2rem; font-weight: 800; color: var(--primary);">₹<?= number_format($p['price']) ?></span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<footer>
  <div class="footer-container">
    <!-- Left Card -->
    <div class="footer-card">
      <a href="index.php" class="footer-logo">
         <img src="assets/shoe_logo_green.png" alt="WalkOn" style="height: 32px;">
         <div class="brand-text" style="font-family: 'Outfit', sans-serif;"><span style="color:#fff">WALK</span><span style="color:#10b981">ON</span></div>
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
          <li><a href="shop.php">Shop</a></li>
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
