<?php
session_start();

// If not logged in → Send back to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$first_name = $_SESSION['first_name'] ?? '';
$last_name = $_SESSION['last_name'] ?? '';
$display_name = trim($first_name . ' ' . $last_name) ?: $email;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WALKON Shoes</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background:#f0f2f5; }
        .header {
            background:#0B0F19;
            color:white;
            padding:20px 40px;
            text-align:center;
            box-shadow:0 4px 20px rgba(0,0,0,0.1);
        }
        .header h1 { font-size:32px; margin-bottom:8px; }
        .header p { opacity:0.9; }
        .container {
            max-width:1200px;
            margin:40px auto;
            padding:20px;
        }
        .welcome-card {
            background:white;
            padding:40px;
            border-radius:20px;
            box-shadow:0 15px 40px rgba(0,0,0,0.1);
            text-align:center;
            margin-bottom:40px;
        }
        .welcome-card h2 {
            font-size:28px;
            color:#10b981;
            margin-bottom:10px;
        }
        .grid {
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(300px,1fr));
            gap:30px;
        }
        .card {
            background:white;
            padding:30px;
            border-radius:16px;
            text-align:center;
            box-shadow:0 10px 30px rgba(0,0,0,0.08);
            transition:0.3s;
        }
        .card:hover { transform:translateY(-10px); }
        .card i { font-size:50px; color:#10b981; margin-bottom:20px; }
        .card h3 { margin-bottom:15px; color:#333; }
        .btn {
            display:inline-block;
            background:#10b981;
            color:white;
            padding:14px 30px;
            border-radius:50px;
            text-decoration:none;
            font-weight:600;
            margin-top:20px;
            transition:0.3s;
        }
        .btn:hover { background:#059669; }
        .logout {
            position:fixed;
            top:20px;
            right:40px;
            background:rgba(255,255,255,0.2);
            color:white;
            padding:10px 20px;
            border-radius:30px;
            text-decoration:none;
            font-weight:600;
        }

        /* Footer Refined */
        footer {
          background: #05070A; border-top: 1px solid rgba(255,255,255,0.05);
          padding: 80px 0 40px; color: #fff;
          margin-top: 80px;
          text-align: left;
          width: 100%;
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
        .contact-item i { color: #10b981; width: 20px; }
        
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
            background: #10b981; color: #000; transform: translateY(-3px);
        }
        
        /* Footer Grid */
        .footer-nav-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;
        }
        
        .footer-col h4 {
            color: #10b981; font-size: 0.85rem; font-weight: 700;
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Footer Refined */
        footer {
          background: #05070A; border-top: 1px solid rgba(255,255,255,0.05);
          padding: 80px 0 40px; color: #fff;
          margin-top: 80px;
          text-align: left;
          width: 100%;
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
        .contact-item i { color: #10b981; width: 20px; }
        
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
            background: #10b981; color: #000; transform: translateY(-3px);
        }
        
        /* Footer Grid */
        .footer-nav-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;
        }
        
        .footer-col h4 {
            color: #10b981; font-size: 0.85rem; font-weight: 700;
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

    <div class="header" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 30px 40px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
            <img src="assets/shoe_logo_green.png" alt="WalkOn Logo" style="height: 50px; width: auto;">
            <span style="font-family: 'Outfit', sans-serif; font-size: 32px; font-weight: 700; color: white; letter-spacing: 0;">WALK<span style="color:#10b981">ON</span></span>
        </div>
        <p>Multi-Channel E-Commerce Platform</p>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="container">
        <div class="welcome-card">
            <h2>Welcome back, <?php echo htmlspecialchars($display_name); ?>!</h2>
            <p>Manage your shoe listings across Amazon, Flipkart, Shopify, Instagram, TikTok Shop, eBay, and more – all in one place.</p>
        </div>

        <div class="grid">
            <div class="card">
                <i class="fas fa-plus-circle"></i>
                <h3>Add New Listing</h3>
                <p>Upload photos, set prices, and sync to all channels instantly.</p>
                <a href="add_listing.php" class="btn">Add Shoe</a>
            </div>

            <div class="card">
                <i class="fas fa-list-alt"></i>
                <h3>My Listings</h3>
                <p>View, edit, or remove your current products.</p>
                <a href="my_listings.php" class="btn">View All</a>
            </div>

            <div class="card">
                <i class="fas fa-sync-alt"></i>
                <h3>Sync Status</h3>
                <p>Check real-time sync across all platforms.</p>
                <a href="sync_status.php" class="btn">Check Sync</a>
            </div>

            <div class="card">
                <i class="fas fa-chart-bar"></i>
                <h3>Sales Analytics</h3>
                <p>Track performance and earnings.</p>
                <a href="analytics.php" class="btn">View Report</a>
            </div>

            <div class="card">
                <i class="fas fa-shopping-bag"></i>
                <h3>My Orders</h3>
                <p>Track customer orders and payment status.</p>
                <a href="my_orders.php" class="btn">View Orders</a>
            </div>

            <div class="card">
                <i class="fas fa-tags"></i>
                <h3>Smart Pricing</h3>
                <p>Automatically adjust prices based on competition.</p>
                <a href="smart_pricing.php" class="btn">Manage Pricing</a>
            </div>

            <div class="card">
                <i class="fas fa-plug"></i>
                <h3>Marketplaces</h3>
                <p>Manage your sales channel integrations.</p>
                <a href="marketplaces.php" class="btn">Manage Channels</a>
            </div>

            <div class="card">
                <i class="fas fa-layer-group"></i>
                <h3>Bulk Operations</h3>
                <p>Edit thousands of SKUs in seconds.</p>
                <a href="bulk_operations.php" class="btn">Bulk Edit</a>
            </div>
        </div>
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
</body>
</html>
</body>
</html>
