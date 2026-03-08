<?php
session_start();
include 'config.php';

// Auth & Seller Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?intended_role=seller");
    exit();
}

$email = $_SESSION['email'];
$seller_id = $_SESSION['seller_id'] ?? null;

// Initialize variables to avoid undefined warnings if try block fails
$marketplaces = [];
$all_categories = [];

try {
    if (!$seller_id) {
        $stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
        $stmt_seller->execute([$email]);
        $seller = $stmt_seller->fetch();
        $seller_id = $seller ? $seller['id'] : -1;
        if ($seller_id != -1) $_SESSION['seller_id'] = $seller_id;
    }

    // Fetch categories for filtering
    $stmt_cats = $pdo->query("SELECT DISTINCT category FROM marketplaces WHERE is_active = 1");
    $all_categories = $stmt_cats->fetchAll(PDO::FETCH_COLUMN);

    // Fetch all active marketplaces with connection status for THIS seller
    $stmt = $pdo->prepare("SELECT m.*, 
                        sm.status as connection_status,
                        (SELECT COUNT(*) FROM product_channels pc 
                         JOIN product_base pb ON pc.product_id = pb.id
                         WHERE pc.channel_name = m.name AND pb.seller_id = ?) as product_count
                        FROM marketplaces m 
                        LEFT JOIN seller_marketplaces sm ON m.id = sm.marketplace_id AND sm.seller_id = ?
                        WHERE m.is_active = 1 
                        ORDER BY m.display_order");
    $stmt->execute([$seller_id, $seller_id]);
    $marketplaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    // Fail gracefully with empty arrays
    $marketplaces = [];
    $all_categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplaces - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --primary-glow: rgba(16, 185, 129, 0.4);
            --bg: #030712;
            --card-bg: #0f172a;
            --text-main: #f9fafb;
            --text-muted: #9ca3af;
            --border: rgba(255, 255, 255, 0.05);
            --glass: rgba(15, 23, 42, 0.8);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            overflow-x: hidden;
            line-height: 1.6;
        }

        .container { max-width: 1300px; margin: 0 auto; padding: 0 2rem; }

        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 1.25rem 5%;
            z-index: 1000;
            background: var(--glass);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }
        
        .nav-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            letter-spacing: -1px;
            flex-shrink: 0;
        }

        .logo img {
            transition: transform 0.3s ease;
        }
        
        .logo:hover img {
            transform: scale(1.05);
        }

        /* Hero Section */
        .hero {
            padding: 180px 0 80px;
            text-align: center;
        }

        .hero h1 {
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: -2px;
            margin-bottom: 1.5rem;
            background: linear-gradient(to bottom, #fff 40%, rgba(255,255,255,0.6));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 700px;
            margin: 0 auto 3rem;
        }

        /* Search Section Premium */
        .search-wrapper {
            max-width: 600px;
            margin: 0 auto 50px;
            position: relative;
            z-index: 10;
        }

        .search-bar {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            padding: 1.2rem 2rem 1.2rem 3.5rem;
            color: white;
            font-size: 1.1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .search-bar::placeholder {
            color: rgba(255, 255, 255, 0.3);
            font-weight: 300;
        }

        .search-bar:focus {
            outline: none;
            background: rgba(16, 185, 129, 0.05); /* Very subtle green tint */
            border-color: var(--primary);
            box-shadow: 
                0 0 0 4px rgba(16, 185, 129, 0.1),
                0 20px 40px -10px rgba(0, 0, 0, 0.5);
            transform: translateY(-2px);
        }

        .search-icon {
            position: absolute;
            left: 1.4rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.4);
            font-size: 1.2rem;
            pointer-events: none;
            transition: 0.3s;
        }

        .search-bar:focus + .search-icon, /* If icon is after input */
        .search-wrapper:focus-within .search-icon {
            color: var(--primary);
        }
        
        /* Category Pills */
        .category-filter {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }

        .cat-pill {
            padding: 8px 24px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 50px;
            color: var(--text-muted);
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .cat-pill:hover, .cat-pill.active {
            background: var(--primary);
            color: #000;
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        /* Marketplace Grid */
        .marketplace-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
            margin-bottom: 100px;
        }

        .marketplace-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 2.5rem;
            text-decoration: none;
            color: white;
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .marketplace-card:hover {
            border-color: var(--primary);
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.15);
        }

        .marketplace-logo {
            height: 60px;
            width: auto;
            max-width: 180px;
            margin-bottom: 1.5rem;
            object-fit: contain;
            filter: brightness(0) invert(1);
            opacity: 0.9;
            transition: 0.3s;
        }

        .marketplace-card:hover .marketplace-logo {
            filter: none;
            opacity: 1;
        }

        .marketplace-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .marketplace-card p {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .marketplace-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            font-weight: 600;
            font-size: 0.9rem;
            margin-top: auto;
        }

        .marketplace-link i {
            transition: 0.3s;
        }

        .marketplace-card:hover .marketplace-link i {
            transform: translateX(5px);
        }

        /* Inventory Badge */
        .inventory-badge {
            background: rgba(16, 185, 129, 0.1);
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .status-dot {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            color: #34d399;
        }

        .status-dot::before {
            content: '';
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.8);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.5); opacity: 0.5; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Interaction Elements */
        .btn-connect {
            padding: 10px 20px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: rgba(255,255,255,0.05);
            color: #fff;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-connect:hover {
            border-color: var(--primary);
            background: rgba(16, 185, 129, 0.1);
        }

        .btn-connect.connected {
            background: var(--primary);
            color: #000;
            border-color: var(--primary);
        }

        .btn-manage {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: rgba(255,255,255,0.02);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
            text-decoration: none;
        }

        .btn-manage:hover {
            color: #fff;
            background: rgba(255,255,255,0.05);
            border-color: var(--text-muted);
        }

        /* Stats Section */
        .stats {
            background: rgba(255,255,255,0.02);
            padding: 60px 0;
            border-radius: 32px;
            border: 1px solid var(--border);
            margin-bottom: 80px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            text-align: center;
        }

        .stat-item h2 {
            font-size: 3rem;
            font-weight: 900;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-item p {
            color: var(--text-muted);
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .marketplace-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Footer Refined */
        footer {
          background: #05070A !important; border-top: 1px solid var(--border) !important;
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
            text-align: left;
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
            text-align: left;
        }
        
        .footer-col h4 {
            color: var(--primary); font-size: 0.85rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.5rem;
        }
        
        .footer-links { list-style: none; padding: 0; margin: 0; }
        .footer-links li { margin-bottom: 1rem; }
        .footer-links a {
            color: #e2e8f0; text-decoration: none; font-size: 0.95rem;
            transition: 0.3s;
        }
        .footer-links a:hover { color: var(--primary); padding-left: 5px; }

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
    <div class="container nav-inner">
        <a href="index.php" class="logo" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
            <img src="assets/shoe_logo_green.png" alt="WalkOn Logo" style="height: 32px; width: auto;">
            <span style="font-family: 'Outfit', sans-serif; font-size: 24px; font-weight: 700; color: white; letter-spacing: -0.5px;">WALK<span style="color:#10b981">ON</span></span>
        </a>
        <div style="display: flex; align-items: center; gap: 2rem;">
            <a href="index.php" style="color: var(--text-muted); text-decoration: none; font-weight: 500; transition: 0.3s;">Home</a>
            <a href="shop.php" style="color: var(--text-muted); text-decoration: none; font-weight: 500; transition: 0.3s;">Shop</a>
            <a href="dashboard.php" style="color: var(--text-muted); text-decoration: none; font-weight: 500; transition: 0.3s;">Dashboard</a>
            <a href="start_selling.php" style="background: var(--primary); color: #000; padding: 0.6rem 1.2rem; border-radius: 50px; text-decoration: none; font-weight: 700; font-size: 0.85rem; transition: 0.3s; display: flex; align-items: center; gap: 5px;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                Start Selling <i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i>
            </a>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="container" style="position: relative; z-index: 1;">
        <!-- Background Glow -->
        <div style="position: absolute; top: -150px; left: 50%; transform: translateX(-50%); width: 600px; height: 400px; background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, transparent 70%); pointer-events: none; z-index: -1;"></div>
        
        <div style="margin-bottom: 2rem;">
            <span style="color: var(--primary); font-weight: 700; letter-spacing: 2px; font-size: 0.9rem; text-transform: uppercase;">Empowering Footwear Giants</span>
        </div>

        <h1 style="font-size: clamp(3rem, 5vw, 4.5rem); line-height: 1.1; margin-bottom: 1.5rem; max-width: 900px; margin-left: auto; margin-right: auto;">
            The Global <span style="font-family: 'Playfair Display', serif; font-style: italic;">Multi-Channel</span><br>
            Commerce Infrastructure.
        </h1>
        
        <p style="font-size: 1.1rem; color: #94a3b8; max-width: 700px; margin: 0 auto 3rem; line-height: 1.8;">
            List once, sell anywhere. Our intelligent sync technology connects your inventory to 15+ marketplaces including Amazon, Shopify, and TikTok Shop with real-time automation.
        </p>
        
        <div style="margin-bottom: 5rem;">
            <a href="start_selling.php" style="background: var(--primary); color: #000; padding: 1rem 2.5rem; border-radius: 50px; font-weight: 700; font-size: 1rem; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 0 20px rgba(16, 185, 129, 0.3); transition: 0.3s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 30px rgba(16, 185, 129, 0.4)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 0 20px rgba(16, 185, 129, 0.3)'">
                Scale My Brand Now <i class="fas fa-rocket"></i>
            </a>
        </div>
    </div>
</section>

<section class="container" style="margin-top: -20px;">
    <!-- Premium Stats Card -->
    <div class="stats" style="background: #0B0F19; border: 1px solid rgba(255,255,255,0.05); padding: 4rem 2rem; border-radius: 30px; position: relative; overflow: hidden; margin-bottom: 80px;">
        <!-- Subtle bg pattern -->
        <div style="position: absolute; inset: 0; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 30px 30px; opacity: 0.5;"></div>
        
        <div class="stats-grid" style="position: relative; z-index: 2;">
            <div class="stat-item">
                <h2 style="color: var(--primary); font-size: 3rem; margin-bottom: 10px;">15+</h2>
                <div style="font-size: 0.85rem; letter-spacing: 1px; text-transform: uppercase; color: #64748b; font-weight: 700;">Marketplaces</div>
            </div>
            <div class="stat-item">
                <h2 style="color: var(--primary); font-size: 3rem; margin-bottom: 10px;">2500+</h2>
                <div style="font-size: 0.85rem; letter-spacing: 1px; text-transform: uppercase; color: #64748b; font-weight: 700;">Trusted Brands</div>
            </div>
            <div class="stat-item">
                <h2 style="color: var(--primary); font-size: 3rem; margin-bottom: 10px;">99<span style="font-size: 1.5rem">%</span></h2>
                <div style="font-size: 0.85rem; letter-spacing: 1px; text-transform: uppercase; color: #64748b; font-weight: 700;">Uptime Guarantee</div>
            </div>
        </div>
    </div>

    <!-- Search & Filters (Moved below Hero) -->
    <div style="max-width: 900px; margin: 0 auto 60px;">
        <div class="search-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="marketplaceSearch" class="search-bar" placeholder="Find a channel to connect (e.g. Amazon, Myntra)..." onkeyup="filterMarketplaces()">
        </div>

        <div class="category-filter">
            <div class="cat-pill active" onclick="filterCategory('all', this)">All Channels</div>
            <?php foreach($all_categories as $cat): ?>
                <div class="cat-pill" onclick="filterCategory('<?= strtolower($cat) ?>', this)"><?= $cat ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function toggleView(view) {
            const grid = document.getElementById('marketGrid');
            const table = document.getElementById('marketTable');
            const btns = document.querySelectorAll('.toggle-btn');
            
            // Save state
            localStorage.setItem('marketplaceView', view);
            
            // Remove animation classes to restart them
            grid.classList.remove('fade-in');
            table.classList.remove('fade-in');
            void grid.offsetWidth; // Force reflow
            void table.offsetWidth; 

            if (view === 'grid') {
                grid.style.display = 'grid';
                table.style.display = 'none';
                btns[0].classList.add('active');
                btns[1].classList.remove('active');
                grid.classList.add('fade-in');
            } else {
                grid.style.display = 'none';
                table.style.display = 'table';
                btns[0].classList.remove('active');
                btns[1].classList.add('active');
                table.classList.add('fade-in');
            }
        }

        // Initialize from storage
        document.addEventListener('DOMContentLoaded', () => {
            const savedView = localStorage.getItem('marketplaceView') || 'grid';
            toggleView(savedView);
        });
    </script>
    <style>
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        /* View Toggle */
        .view-controls {
            display: flex; justify-content: flex-end; margin-bottom: 20px;
        }
        .view-toggle {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 4px;
            display: flex; gap: 4px;
        }
        .toggle-btn {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            display: flex; align-items: center; gap: 8px;
            transition: 0.3s;
        }
        .toggle-btn.active {
            background: var(--primary);
            color: #000;
        }
        .toggle-btn:hover:not(.active) {
            color: #fff;
        }

        /* Table View */
        .marketplace-table {
            width: 100%;
            border-collapse: collapse;
            display: none; /* Hidden by default */
        }
        .marketplace-table th {
            text-align: left;
            padding: 1rem;
            color: var(--text-muted);
            font-weight: 600;
            border-bottom: 1px solid var(--border);
        }
        .marketplace-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.02);
            color: white;
            vertical-align: middle;
        }
        .marketplace-table tr:hover td {
            background: rgba(255,255,255,0.02);
        }
        .table-logo {
            width: 32px; height: 32px; object-fit: contain;
            filter: brightness(0) invert(1); opacity: 0.8;
        }
    </style>

    <div class="view-controls">
        <div class="view-toggle">
            <button class="toggle-btn active" onclick="toggleView('grid')"><i class="fas fa-th"></i> Grid</button>
            <button class="toggle-btn" onclick="toggleView('table')"><i class="fas fa-list"></i> Table</button>
        </div>
    </div>

    <div class="marketplace-grid" id="marketGrid">
        <?php foreach($marketplaces as $marketplace): 
            $isConnected = ($marketplace['connection_status'] === 'connected');
        ?>
            <div class="marketplace-card" data-name="<?= strtolower(htmlspecialchars($marketplace['name'])) ?>" data-category="<?= strtolower(htmlspecialchars($marketplace['category'])) ?>">
                <div class="status-dot" style="color: <?= $isConnected ? '#34d399' : '#9ca3af' ?>; font-size: 0.65rem;">
                    <?= $isConnected ? 'Synced' : 'Inactive' ?>
                </div>
                <?php if($marketplace['logo_url']): ?>
                    <img src="<?= htmlspecialchars($marketplace['logo_url']) ?>" alt="<?= htmlspecialchars($marketplace['name']) ?>" class="marketplace-logo">
                <?php endif; ?>
                <h3><?= htmlspecialchars($marketplace['name']) ?></h3>
                <div class="inventory-badge">
                    <i class="fas fa-layer-group" style="font-size: 0.7rem;"></i> 
                    <?= number_format($marketplace['product_count']) ?> SKUs
                </div>
                <p><?= htmlspecialchars($marketplace['description']) ?></p>
                
                <div style="margin-top: auto; display: flex; gap: 10px; width: 100%;">
                    <button class="btn-connect <?= $isConnected ? 'connected' : '' ?>" onclick="toggleConnection(<?= $marketplace['id'] ?>, this)" style="flex: 1;">
                        <?= $isConnected ? 'Disconnect' : 'Connect Channel' ?>
                    </button>
                    <a href="channel_settings.php?id=<?= $marketplace['id'] ?>" class="btn-manage" title="Settings">
                        <i class="fas fa-cog"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Table View -->
    <table class="marketplace-table" id="marketTable">
        <thead>
            <tr>
                <th>Channel</th>
                <th>Category</th>
                <th>Synced SKUs</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($marketplaces as $marketplace): 
                $isConnected = ($marketplace['connection_status'] === 'connected');
            ?>
            <tr data-name="<?= strtolower(htmlspecialchars($marketplace['name'])) ?>" data-category="<?= strtolower(htmlspecialchars($marketplace['category'])) ?>">
                <td style="display: flex; align-items: center; gap: 12px;">
                    <?php if($marketplace['logo_url']): ?>
                        <img src="<?= htmlspecialchars($marketplace['logo_url']) ?>" alt="" class="table-logo">
                    <?php endif; ?>
                    <span style="font-weight: 600;"><?= htmlspecialchars($marketplace['name']) ?></span>
                </td>
                <td><span class="cat-pill" style="padding: 4px 12px; font-size: 0.8rem; pointer-events: none;"><?= htmlspecialchars($marketplace['category']) ?></span></td>
                <td><?= number_format($marketplace['product_count']) ?></td>
                <td>
                    <span style="color: <?= $isConnected ? '#34d399' : '#9ca3af' ?>; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                        <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%; display: inline-block;"></span>
                        <?= $isConnected ? 'Synced' : 'Inactive' ?>
                    </span>
                </td>
                <td>
                    <div style="display: flex; gap: 8px;">
                        <button class="btn-connect <?= $isConnected ? 'connected' : '' ?>" onclick="toggleConnection(<?= $marketplace['id'] ?>, this)" style="padding: 6px 12px; font-size: 0.8rem;">
                            <?= $isConnected ? 'Disconnect' : 'Connect' ?>
                        </button>
                        <a href="channel_settings.php?id=<?= $marketplace['id'] ?>" class="btn-manage" style="width: 32px; height: 30px; font-size: 0.8rem;">
                            <i class="fas fa-cog"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<script>
function filterMarketplaces() {
    const input = document.getElementById('marketplaceSearch');
    const filter = input.value.toLowerCase();
    
    // Filter Grid
    const gridCards = document.querySelectorAll('.marketplace-card');
    gridCards.forEach(card => {
        const name = card.getAttribute('data-name');
        card.style.display = name.includes(filter) ? "" : "none";
    });

    // Filter Table
    const tableRows = document.querySelectorAll('#marketTable tbody tr');
    tableRows.forEach(row => {
        const name = row.getAttribute('data-name');
        row.style.display = name.includes(filter) ? "" : "none";
    });
}
    // Update category filter similarly if needed, or rely on simple reload for now logic
    
    function filterCategory(cat, el) {
        document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
        el.classList.add('active');
        
        // Filter Grid
        const cards = document.getElementsByClassName('marketplace-card');
        for (let i = 0; i < cards.length; i++) {
            const cardCat = cards[i].getAttribute('data-category');
            if (cat === 'all' || cardCat === cat) {
                cards[i].style.display = "";
                setTimeout(() => cards[i].style.opacity = "1", 10);
            } else {
                cards[i].style.display = "none";
                cards[i].style.opacity = "0";
            }
        }

        // Filter Table
        const rows = document.querySelectorAll('#marketTable tbody tr');
        rows.forEach(row => {
            const rowCat = row.getAttribute('data-category');
            if (cat === 'all' || rowCat === cat) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    function toggleConnection(id, btn) {
        const isConnecting = !btn.classList.contains('connected');
        const originalText = btn.innerText;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        fetch('api/toggle_marketplace.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ marketplace_id: id })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Find all buttons for this ID (in grid and table) and update them
                // This ensures if I click in table, grid also updates
                const allBtns = document.querySelectorAll(`button[onclick="toggleConnection(${id}, this)"]`);
                
                allBtns.forEach(b => {
                    const isCard = b.closest('.marketplace-card');
                    const container = isCard ? b.closest('.marketplace-card') : b.closest('tr');
                    
                    if (data.status === 'connected') {
                        b.classList.add('connected');
                        b.innerText = isCard ? 'Disconnect' : 'Disconnect';
                        
                        if (isCard) {
                            const dot = container.querySelector('.status-dot');
                            dot.style.color = '#34d399';
                            dot.innerText = 'Synced';
                        } else {
                            // Table row update
                            const statusSpan = container.querySelector('td:nth-child(4) span');
                            statusSpan.style.color = '#34d399';
                            statusSpan.innerHTML = '<span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%; display: inline-block;"></span> Synced';
                        }
                    } else {
                        b.classList.remove('connected');
                        b.innerText = isCard ? 'Connect Channel' : 'Connect';
                        
                        if (isCard) {
                            const dot = container.querySelector('.status-dot');
                            dot.style.color = '#9ca3af';
                            dot.innerText = 'Inactive';
                        } else {
                            // Table row update
                            const statusSpan = container.querySelector('td:nth-child(4) span');
                            statusSpan.style.color = '#9ca3af';
                            statusSpan.innerHTML = '<span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%; display: inline-block;"></span> Inactive';
                        }
                    }
                });

            } else {
                alert('Error: ' + data.message);
                btn.innerText = originalText;
            }
        })
        .catch(e => {
            console.error(e);
            btn.innerText = originalText;
        });
    }
</script>

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
                <a href="mailto:support@walkon.com" class="social-btn"><i class="fas fa-envelope"></i></a>
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
                    <li><a href="Index.php#categories">Categories</a></li>
                    <li><a href="Index.php#features">Features</a></li>
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

</body>
</html>
