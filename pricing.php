<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pricing - WALKON</title>
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
    .nav-links a { margin-left: 2rem; text-decoration: none; font-weight: 600; color: var(--gray-900); }
    .nav-links a:hover { color: var(--green); }
    .btn {
      padding: 0.9rem 2rem; border-radius: 12px; font-weight: 700;
      text-decoration: none; transition: all 0.3s; font-size: 1rem; display: inline-block;
    }
    .btn-primary { background: var(--green); color: white; }
    .btn-primary:hover { background: var(--green-dark); transform: translateY(-3px); }
    .btn-outline { border: 2px solid var(--green); color: var(--green); background: transparent; }
    .btn-outline:hover { background: var(--green); color: white; }

    /* Pricing Section */
    .pricing-header {
        text-align: center; padding: 160px 2rem 60px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white;
    }
    .pricing-header h1 { font-size: 3.5rem; font-weight: 900; margin-bottom: 1rem; }
    .pricing-header p { font-size: 1.2rem; opacity: 0.8; max-width: 600px; margin: 0 auto; }

    .pricing-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem; max-width: 1200px; margin: -50px auto 100px; padding: 0 2rem;
    }
    .pricing-card {
        background: white; border-radius: 24px; padding: 3rem 2rem;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1); transition: 0.4s; position: relative;
        border: 1px solid #e2e8f0;
    }
    .pricing-card:hover { transform: translateY(-10px); box-shadow: 0 30px 60px rgba(0,0,0,0.15); }
    
    .pricing-card.popular { border: 3px solid var(--green); transform: scale(1.05); z-index: 10; }
    .popular-tag {
        position: absolute; top: 0; left: 50%; transform: translate(-50%, -50%);
        background: var(--green); color: white; padding: 8px 16px; 
        border-radius: 20px; font-weight: 700; font-size: 0.9rem;
    }

    .plan-name { font-size: 1.5rem; font-weight: 700; color: var(--gray-900); margin-bottom: 1rem; }
    .plan-price { font-size: 3rem; font-weight: 900; color: var(--gray-900); margin-bottom: 2rem; }
    .plan-price span { font-size: 1rem; color: #64748b; font-weight: 500; }
    
    .features { list-style: none; margin-bottom: 2.5rem; text-align: left; }
    .features li { margin-bottom: 1rem; color: #475569; display: flex; align-items: center; gap: 10px; }
    .features li i { color: var(--green); }
    
    footer { background: var(--gray-900); color: white; text-align: center; padding: 4rem 2rem; font-size: 1.1rem; }

  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="nav-container">
    <a href="index.php" aria-label="WALKON - Home">
      <svg width="180" height="60" viewBox="0 0 210 70" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#16a34a"/>
            <stop offset="100%" stop-color="#22c55e"/>
          </linearGradient>
        </defs>
        <g transform="translate(10,15)">
          <path d="M18 35 Q5 22, 18 12 Q38 18, 32 38 Q26 50, 18 35 Z" fill="url(#g)"/>
          <path d="M32 38 Q45 25, 32 12" fill="none" stroke="#15803d" stroke-width="9" stroke-linecap="round"/>
          <path d="M18 35 H44" stroke="#15803d" stroke-width="9" stroke-linecap="round"/>
        </g>
        <text x="75" y="45" font-family="Inter, sans-serif" font-size="38" font-weight="900" fill="#0f172a" letter-spacing="-1">WALK</text>
        <text x="150" y="45" font-family="Inter, sans-serif" font-size="38" font-weight="900" fill="url(#g)" letter-spacing="-1">ON</text>
      </svg>
    </a>
    <div class="nav-links">
      <a href="index.php">Home</a>
      <a href="index.php#features">Features</a>
      <a href="login.php" class="btn btn-outline">Login</a>
      <a href="register.php" class="btn btn-primary">Start Free Trial</a>
    </div>
  </div>
</nav>

<!-- Header -->
<div class="pricing-header">
    <h1>Simple, Transparent Pricing</h1>
    <p>Choose the plan that fits your growth. No hidden fees. Cancel anytime.</p>
</div>

<!-- Pricing Grid -->
<div class="pricing-grid">
    
    <!-- Starter -->
    <div class="pricing-card">
        <div class="plan-name">Starter</div>
        <div class="plan-price">$29<span>/mo</span></div>
        <ul class="features">
            <li><i class="fas fa-check-circle"></i> 100 Product Listings</li>
            <li><i class="fas fa-check-circle"></i> 2 Channels (Amazon + 1)</li>
            <li><i class="fas fa-check-circle"></i> Daily Sync</li>
            <li><i class="fas fa-check-circle"></i> Basic Analytics</li>
        </ul>
        <a href="register.php?plan=starter" class="btn btn-outline" style="width:100%; text-align:center;">Start Free Trial</a>
    </div>

    <!-- Pro (Popular) -->
    <div class="pricing-card popular">
        <div class="popular-tag">MOST POPULAR</div>
        <div class="plan-name">Professional</div>
        <div class="plan-price">$79<span>/mo</span></div>
        <ul class="features">
            <li><i class="fas fa-check-circle"></i> Unlimited Listings</li>
            <li><i class="fas fa-check-circle"></i> All Channels Included</li>
            <li><i class="fas fa-check-circle"></i> Real-Time Sync</li>
            <li><i class="fas fa-check-circle"></i> Smart Pricing Engine</li>
            <li><i class="fas fa-check-circle"></i> Bulk Operations</li>
        </ul>
        <a href="register.php?plan=pro" class="btn btn-primary" style="width:100%; text-align:center;">Start Free Trial</a>
    </div>

    <!-- Enterprise -->
    <div class="pricing-card">
        <div class="plan-name">Enterprise</div>
        <div class="plan-price">$299<span>/mo</span></div>
        <ul class="features">
            <li><i class="fas fa-check-circle"></i> Everything in Pro</li>
            <li><i class="fas fa-check-circle"></i> Dedicated Account Manager</li>
            <li><i class="fas fa-check-circle"></i> Custom API Access</li>
            <li><i class="fas fa-check-circle"></i> Priority 24/7 Support</li>
        </ul>
        <a href="register.php?plan=enterprise" class="btn btn-outline" style="width:100%; text-align:center;">Contact Sales</a>
    </div>
</div>

<!-- Footer -->
<footer style="background-color:#0f172a; color:#e2e8f0; padding:3rem 1rem;">
  <div style="max-width:1200px; margin:0 auto;">
    <p>© 2025 WALKON Technologies Pvt. Ltd. • All rights reserved.</p>
  </div>
</footer>

</body>
</html>
