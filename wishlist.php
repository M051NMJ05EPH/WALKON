<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch Wishlist Items
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
            --bg: #030712;
            --card-bg: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #f9fafb;
            --text-muted: #9ca3af;
            --glass: rgba(17, 24, 39, 0.8);
        }

        body { 
            font-family: 'Outfit', sans-serif; 
            background: var(--bg); 
            color: var(--text-main);
            margin: 0; padding: 0;
        }

        .container { max-width: 1400px; margin: 0 auto; padding: 0 2rem; }

        .navbar {
            background: var(--glass);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 1rem 0;
            position: sticky; top: 0; z-index: 100;
        }
        .nav-inner { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.5rem; font-weight: 800; color: white; text-decoration: none; display: flex; align-items: center; gap: 8px; }

        .page-header { margin: 3rem 0; }
        .page-header h1 { font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; }

        .product-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem;
            padding-bottom: 4rem;
        }

        .product-card {
            background: var(--card-bg); border: 1px solid var(--border);
            border-radius: 24px; padding: 1.25rem; text-decoration: none; color: white;
            transition: 0.4s; position: relative;
            display: block;
        }
        .product-card:hover { 
            border-color: var(--primary); transform: translateY(-8px);
        }

        .img-wrap {
            height: 240px; background: #0f172a; border-radius: 16px;
            margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        .img-wrap img { width: 100%; height: 100%; object-fit: contain; }

        .remove-btn {
            position: absolute; top: 20px; right: 20px;
            width: 36px; height: 36px; border-radius: 50%;
            background: rgba(239, 68, 68, 0.1); color: #ef4444;
            border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: 0.3s; z-index: 10;
        }
        .remove-btn:hover { background: #ef4444; color: white; transform: rotate(90deg); }

        .empty-state {
            text-align: center; padding: 4rem 0; grid-column: 1 / -1;
        }
        .empty-state i { font-size: 4rem; color: var(--border); margin-bottom: 1rem; }
        .btn-home {
            display: inline-block; margin-top: 1.5rem; padding: 12px 24px;
            background: var(--primary); color: white; text-decoration: none;
            border-radius: 50px; font-weight: 600;
        }

        /* Footer Refined */
        footer {
          background: #05070A; border-top: 1px solid var(--border);
          padding: 80px 0 40px; color: #fff;
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
</head>
<body>

<nav class="navbar">
    <div class="container nav-inner">
        <a href="index.php" class="logo">
            <img src="assets/shoe_logo_green.png" alt="WalkOn Logo" style="height: 38px; width: auto;">
            <span style="font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 700; color: white; letter-spacing: 0;">WALK<span style="color:var(--primary)">ON</span></span>
        </a>
        <div style="display:flex; gap: 2rem;">
            <a href="index.php" style="color:var(--text-muted); text-decoration:none; font-weight:500;">Home</a>
            <a href="shop.php" style="color:var(--text-muted); text-decoration:none; font-weight:500;">Shop</a>
            <a href="dashboard.php" style="color:var(--text-muted); text-decoration:none; font-weight:500;">Dashboard</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="page-header">
        <h1>My Wishlist</h1>
        <p style="color:var(--text-muted)">Your saved items</p>
    </div>

    <div class="product-grid" id="wishlistGrid">
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="far fa-heart"></i>
                <h3>Your wishlist is empty</h3>
                <p style="color:var(--text-muted);">Browse our collection and save your favorites.</p>
                <a href="shop.php" class="btn-home">Start Shopping</a>
            </div>
        <?php else: ?>
            <?php foreach($products as $p): ?>
                <div class="product-card" id="card-<?= $p['id'] ?>">
                    <button class="remove-btn" onclick="removeFromWishlist(<?= $p['id'] ?>)" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                    <a href="product_details.php?id=<?= $p['id'] ?>" style="text-decoration:none; color:inherit;">
                        <div class="img-wrap">
                            <img src="<?= htmlspecialchars($p['primary_image'] ?: 'https://via.placeholder.com/400') ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 0.5rem;">
                            <?= htmlspecialchars($p['category_name']) ?>
                        </div>
                        <h3 style="font-size: 1.2rem; margin-bottom: 0.5rem;"><?= htmlspecialchars($p['name']) ?></h3>
                        <div style="font-size: 1.25rem; font-weight: 800; color: var(--primary);">
                            ₹<?= number_format($p['price']) ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    </div>
</div>

<footer>
  <div class="footer-container">
    <!-- Left Card -->
    <div class="footer-card">
      <a href="index.php" class="footer-logo">
         <img src="assets/shoe_logo_green.png" alt="WalkOn" style="height: 32px;">
         <div class="brand-text"><span style="color:#fff">WALK</span><span style="color:#10b981">ON</span></div>
      </a>
      <p class="footer-desc">Elevating the global footwear industry with intelligent multi-channel technology. One inventory, infinite possibilities.</p>
      
      <div class="contact-info">
        <div class="contact-item"><i class="fas fa-envelope"></i> support@walkon.com</div>
        <div class="contact-item"><i class="fas fa-phone"></i> +91 90745 85775</div>
        <div class="contact-item"><i class="fas fa-map-marker-alt"></i> Kottayam, Kerala, India</div>
      </div>

      <div class="social-links">
        <a href="#" class="social-btn"><i class="fas fa-phone-alt"></i></a>
        <a href="#" class="social-btn"><i class="fab fa-instagram"></i></a>
        <a href="#" class="social-btn"><i class="fab fa-whatsapp"></i></a>
        <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="social-btn"><i class="fab fa-linkedin-in"></i></a>
        <a href="#" class="social-btn"><i class="fab fa-youtube"></i></a>
      </div>
    </div>

    <!-- Right Columns -->
    <div class="footer-nav-grid">
      <div class="footer-col">
        <h4>NAVIGATION</h4>
        <ul class="footer-links">
          <li><a href="index.php">Home</a></li>
          <li><a href="index.php#categories">Categories</a></li>
          <li><a href="index.php#features">Features</a></li>
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

<script>
function removeFromWishlist(productId) {
    if(!confirm('Remove this item from wishlist?')) return;

    fetch('toggle_wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Server response:', text);
                throw new Error('Invalid JSON response from server');
            }
        });
    })
    .then(data => {
        if (data.success) {
            const card = document.getElementById('card-' + productId);
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            setTimeout(() => {
                card.remove();
                if(document.querySelectorAll('.product-card').length === 0) {
                    location.reload(); 
                }
            }, 300);
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again or check the console.');
    });
}
</script>

</body>
</html>
