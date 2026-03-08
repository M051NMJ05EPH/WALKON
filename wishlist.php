<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'] ?? null;

// Fetch Wishlist Items
// Fetch Wishlist Items
try {
    $products = [];
    if ($user_id) {
        $stmt = $pdo->prepare("
            SELECT w.id as wishlist_id, pb.id, pb.name, pp.price, c.name as category_name,
                   (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as primary_image
            FROM wishlist w
            JOIN product_base pb ON w.product_id = pb.id
            LEFT JOIN product_prices pp ON pb.id = pp.product_id
            LEFT JOIN categories c ON pb.category_id = c.id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // If error occurs (like table missing), handle gracefully
    $products = [];
    $error_msg = "Wishlist unavailable at the moment.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --primary-light: #34d399;
            --primary-dark: #059669;
            --bg-dark: #030712;
            --card-bg: #111827;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.08);
            --dark-border: #2A3241;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --font-heading: 'Playfair Display', serif;
        }

        body { 
            font-family: 'Outfit', sans-serif; 
            background: var(--bg-dark); 
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

        .container { max-width: 1400px; margin: 0 auto; padding: 0 2rem; }

        /* Sleek Navbar matching Shop.php */
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
        .nav-links a:hover { color: var(--primary); }

        .btn-start {
          background: var(--primary); color: #000; padding: 0.8rem 1.8rem; border-radius: 50px; font-weight: 600; font-size: 0.9rem; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: 0.3s;
        }
        .btn-start:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2); }

        .btn-outline-nav {
          padding: 0.6rem 1.5rem; border-radius: 6px; border: 1px solid var(--primary);
          color: var(--primary); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;
          text-decoration: none; transition: 0.3s;
        }
        .btn-outline-nav:hover { background: var(--primary); color: #000; }

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

        .page-header { margin: 120px 0 3rem; }
        .page-header h1 { 
            font-family: 'Playfair Display', serif; font-size: 3.5rem; font-weight: 800; 
            letter-spacing: -2px; margin-bottom: 0.5rem;
        }

        .product-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2.5rem;
            padding-bottom: 5rem;
        }

        /* Premium Card Design */
        .product-card {
            background: var(--card-bg); border: 1px solid var(--border);
            border-radius: 32px; overflow: hidden; position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex; flex-direction: column; height: 100%;
        }
        .product-card:hover { 
            border-color: var(--primary); transform: translateY(-12px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
        }

        .img-wrap {
            height: 280px; background: #0f172a; position: relative;
            display: flex; align-items: center; justify-content: center;
            padding: 2rem; overflow: hidden;
        }
        .img-wrap img { 
            max-width: 90%; max-height: 90%; object-fit: contain; 
            transition: 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            filter: drop-shadow(0 15px 30px rgba(0,0,0,0.3));
        }
        .product-card:hover .img-wrap img { transform: scale(1.1) rotate(-5deg); filter: drop-shadow(0 25px 50px rgba(0,0,0,0.5)); }

        /* Floating Price Badge (Glassmorphism) */
        .floating-price {
            position: absolute; top: 20px; left: 20px;
            background: rgba(16, 185, 129, 0.2);
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(16, 185, 129, 0.3);
            padding: 8px 16px; border-radius: 12px;
            color: var(--primary); font-weight: 800; font-size: 1.1rem;
            z-index: 5;
        }

        .remove-btn {
            position: absolute; top: 20px; right: 20px;
            width: 44px; height: 44px; border-radius: 14px;
            background: rgba(239, 68, 68, 0.1); color: #ef4444;
            backdrop-filter: blur(8px); border: 1px solid rgba(239, 68, 68, 0.2);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all 0.3s; z-index: 10; font-size: 1.1rem;
        }
        .remove-btn:hover { background: #ef4444; color: white; transform: rotate(90deg) scale(1.1); }

        .card-content { padding: 1.5rem 2rem 2rem; flex-grow: 1; }
        .category { font-size: 0.75rem; color: var(--primary); font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 0.5rem; }
        .product-name { 
            font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 800; 
            margin-bottom: 1.5rem; color: #fff; line-height: 1.2;
        }

        .btn-view {
            width: 100%; padding: 1rem; border-radius: 14px;
            background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border);
            color: #fff; text-decoration: none; font-weight: 600;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            transition: 0.3s;
        }
        .btn-view:hover { background: var(--primary); color: #000; border-color: var(--primary); }

        .empty-state {
            text-align: center; padding: 8rem 0; grid-column: 1 / -1;
        }
        .empty-state i { font-size: 5rem; color: rgba(255,255,255,0.05); margin-bottom: 2rem; }
        .btn-shop {
            display: inline-flex; align-items: center; gap: 12px; margin-top: 2rem; padding: 16px 32px;
            background: var(--primary); color: #000; text-decoration: none;
            border-radius: 50px; font-weight: 700; font-size: 1.1rem; transition: 0.3s;
        }
        .btn-shop:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(16, 185, 129, 0.3); }

        /* Smooth Hide Animation */
        .removing {
            transform: scale(0.9) translateY(20px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
            <img src="assets/shoe_logo_green.png" alt="WalkOn Logo" style="height: 50px; width: auto; filter: drop-shadow(0 0 10px rgba(16, 185, 129, 0.2));">
            <div style="font-family: 'Outfit', sans-serif; font-size: 28px; font-weight: 800; line-height: 1; letter-spacing: -0.5px; text-transform: uppercase;">
                <span style="color: #fff;">Walk</span><span style="color: #10b981;">on</span>
            </div>
        </a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="shop.php">Shop</a>
            <a href="wishlist.php" style="color: var(--primary);">Wishlist</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
            <?php endif; ?>
            
            <div style="display: flex; align-items: center; gap: 1.5rem; margin-left: 1rem;">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" style="text-decoration: none; color: #fff; font-weight: 600; font-size: 0.9rem;">Logout</a>
                <?php else: ?>
                    <a href="login.php" style="text-decoration: none; color: #fff; font-weight: 600; font-size: 0.9rem;">Login</a>
                <?php endif; ?>
                <a href="start_selling.php" class="btn-start">
                    Start Selling <i class="fas fa-arrow-right"></i>
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
    <div class="page-header">
        <h1>My Wishlist</h1>
        <p style="color:var(--text-muted); font-size: 1.1rem;">Curated collection of your favorite performance gear.</p>
    </div>

    <div class="product-grid" id="wishlistGrid">
        <?php if (!$user_id): ?>
            <div class="empty-state">
                <i class="fas fa-lock" style="color: var(--primary); margin-bottom: 1.5rem;"></i>
                <h2 style="font-family:'Playfair Display', serif; font-size: 2.5rem;">One Step Closer</h2>
                <p style="color:var(--text-muted); font-size: 1.1rem; max-width: 500px; margin: 0 auto;">Sign in to save your favorite performance gear and access them from any device.</p>
                <a href="login.php?redirect=wishlist.php" class="btn-shop">Login to My Account <i class="fas fa-sign-in-alt"></i></a>
            </div>
        <?php elseif (empty($products)): ?>
            <div class="empty-state">
                <i class="far fa-heart"></i>
                <h2 style="font-family:'Playfair Display', serif; font-size: 2.5rem;">Your wishlist is empty</h2>
                <p style="color:var(--text-muted); font-size: 1.1rem;">Explore the marketplace and save the best for later.</p>
                <a href="shop.php" class="btn-shop">Start Shopping <i class="fas fa-shopping-bag"></i></a>
            </div>
        <?php else: ?>
            <?php foreach($products as $p): ?>
                <div class="product-card" id="card-<?= $p['id'] ?>">
                    <div class="floating-price">₹<?= number_format($p['price']) ?></div>
                    <button class="remove-btn" onclick="removeFromWishlist(<?= $p['id'] ?>)" title="Remove Item">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    
                    <div class="img-wrap">
                        <img src="<?= htmlspecialchars($p['primary_image'] ?: 'https://via.placeholder.com/600') ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                    </div>
                    
                    <div class="card-content">
                        <div class="category"><?= htmlspecialchars($p['category_name'] ?: 'Performance') ?></div>
                        <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
                        
                        <a href="product_detail.php?id=<?= $p['id'] ?>" class="btn-view">
                            View Details <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function removeFromWishlist(productId) {
    const card = document.getElementById('card-' + productId);
    
    fetch('toggle_wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            card.classList.add('removing');
            setTimeout(() => {
                card.remove();
                if(document.querySelectorAll('.product-card').length === 0) {
                    location.reload(); 
                }
            }, 400);
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
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

</body>
</html>
