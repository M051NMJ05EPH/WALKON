<?php
include 'config.php';

// Fetch all active marketplaces
try {
    $stmt = $pdo->query("SELECT * FROM marketplaces WHERE is_active = 1 ORDER BY display_order");
    $marketplaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $marketplaces = [];
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
        }

        .marketplace-card:hover .marketplace-logo {
            filter: none;
            opacity: 1;
        }

        .marketplace-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
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
        <div style="display: flex; gap: 2rem;">
            <a href="index.php" style="color: var(--text-muted); text-decoration: none; font-weight: 500; transition: 0.3s;">Home</a>
            <a href="shop.php" style="color: var(--text-muted); text-decoration: none; font-weight: 500; transition: 0.3s;">Shop</a>
            <a href="dashboard.php" style="color: var(--text-muted); text-decoration: none; font-weight: 500; transition: 0.3s;">Dashboard</a>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="container">
        <h1><span style="color: var(--primary);">Marketplaces</span></h1>
        <p>Connect your inventory across 15+ global marketplaces. Sync products, manage orders, and scale your business from a single powerful dashboard.</p>
    </div>
</section>

<section class="container">
    <div class="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <h2><?= count($marketplaces) ?>+</h2>
                <p>Marketplaces</p>
            </div>
            <div class="stat-item">
                <h2>2,500+</h2>
                <p>Active Sellers</p>
            </div>
            <div class="stat-item">
                <h2>99.9%</h2>
                <p>Uptime</p>
            </div>
            <div class="stat-item">
                <h2>24/7</h2>
                <p>Support</p>
            </div>
        </div>
    </div>

    <div class="marketplace-grid">
        <?php foreach($marketplaces as $marketplace): ?>
            <a href="<?= htmlspecialchars($marketplace['website_url']) ?>" target="_blank" class="marketplace-card">
                <?php if($marketplace['logo_url']): ?>
                    <img src="<?= htmlspecialchars($marketplace['logo_url']) ?>" alt="<?= htmlspecialchars($marketplace['name']) ?>" class="marketplace-logo">
                <?php endif; ?>
                <h3><?= htmlspecialchars($marketplace['name']) ?></h3>
                <p><?= htmlspecialchars($marketplace['description']) ?></p>
                <span class="marketplace-link">
                    Visit Platform <i class="fas fa-arrow-right"></i>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

</section>

</body>
</html>

</body>
</html>
