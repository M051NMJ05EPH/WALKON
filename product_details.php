<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$product_id = $_GET['id'] ?? 0;

if (!$product_id) {
    header("Location: my_listings.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Product not found.");
    }

    // Process images
    $images_raw = $product['images'];
    $images = [];
    if (!empty($images_raw)) {
        $decoded = json_decode($images_raw, true);
        $images = is_array($decoded) ? $decoded : [$images_raw];
    }
    
    // Default image if none exist
    if (empty($images)) {
        $images = ['https://via.placeholder.com/600x600?text=No+Image+Available'];
    }
    
    // Ensure all image paths are correct (handle local vs absolute)
    foreach ($images as $k => $img) {
        if (!empty($img) && !preg_match('/^http/', $img) && !file_exists($img)) {
            // If it looks like a local path but doesn't exist, use a placeholder
            // Unless it's just missing the leading slash or something similar we can fix
        }
    }

    // Mock data for premium feel (since some fields might be missing in DB)
    $brand = "SPARX"; // Placeholder brand
    $rating = 4.1;
    $reviews_count = 517;
    $mrp = $product['price'] * 1.25; // Simulated M.R.P.
    $discount = 22; // Simulated discount %
    
} catch (PDOException $e) {
    die("Error fetching product details: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #28a745;
            --secondary: #1e293b;
            --text-dark: #333;
            --text-light: #666;
            --bg-light: #f8f9fa;
            --border: #ddd;
            --accent: #e44d26; /* For discounts/offers */
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
        body { background: white; color: var(--text-dark); }

        .navbar {
            padding: 15px 40px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar .logo { font-weight: 700; font-size: 24px; color: var(--primary); text-decoration: none; }
        .back-btn { text-decoration: none; color: var(--text-dark); font-weight: 500; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
        .back-btn:hover { color: var(--primary); }

        .container {
            max-width: 1300px;
            margin: 40px auto;
            padding: 0 40px;
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 60px;
        }

        /* Left Column: Gallery */
        .gallery-container {
            display: flex;
            gap: 20px;
        }

        .thumbnails {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-height: 600px;
            overflow-y: auto;
            scrollbar-width: none; /* Hide scrollbar for clean look */
        }
        .thumbnails::-webkit-scrollbar { display: none; }

        .thumb {
            width: 70px;
            height: 90px;
            border: 1px solid var(--border);
            border-radius: 6px;
            cursor: pointer;
            object-fit: cover;
            transition: 0.2s;
            background: #fff;
        }

        .thumb:hover, .thumb.active {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.1);
        }

        .main-image-view {
            flex-grow: 1;
            position: relative;
            background: #fff;
            border-radius: 12px;
            border: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 600px;
        }

        .main-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: 0.3s ease;
        }

        .share-btn {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: white;
            border: 1px solid var(--border);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .share-btn:hover { transform: scale(1.1); color: var(--primary); }

        /* Right Column: Details */
        .details-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .brand-name { color: #007185; font-size: 14px; font-weight: 500; text-decoration: none; }
        .brand-name:hover { text-decoration: underline; }

        .product-title { font-size: 28px; font-weight: 600; line-height: 1.3; color: #111; }

        .rating-box { display: flex; align-items: center; gap: 10px; font-size: 14px; }
        .stars { color: #ffa41c; }
        .reviews-count { color: #007185; }

        .price-section { border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 20px 0; }
        .discount-row { display: flex; align-items: baseline; gap: 15px; }
        .discount-pct { color: #e44d26; font-size: 28px; font-weight: 300; }
        .current-price { font-size: 28px; font-weight: 600; display: flex; align-items: flex-start; }
        .current-price small { font-size: 16px; margin-top: 4px; margin-right: 2px; }
        .mrp-row { color: #565959; font-size: 14px; margin-top: 5px; }
        .mrp-row span { text-decoration: line-through; }

        .tax-note { font-size: 12px; color: #565959; margin-top: 5px; }

        /* Offers Section */
        .offers-section { margin-top: 10px; }
        .section-title { font-size: 16px; font-weight: 600; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .offers-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .offer-card { border: 1px solid var(--border); border-radius: 8px; padding: 15px; transition: 0.3s; cursor: pointer; }
        .offer-card:hover { border-color: var(--primary); background: rgba(40, 167, 69, 0.02); }
        .offer-card h4 { font-size: 14px; font-weight: 600; margin-bottom: 5px; }
        .offer-card p { font-size: 13px; color: var(--text-light); line-height: 1.4; }
        .offer-link { display: block; margin-top: 10px; color: #007185; font-size: 13px; font-weight: 500; text-decoration: none; }

        /* Trust Badges */
        .trust-badges { display: flex; justify-content: space-between; border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px; }
        .badge-item { text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; width: 80px; }
        .badge-icon { background: #f8f9fa; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; color: var(--text-dark); border: 1px solid #eee; }
        .badge-text { font-size: 11px; color: #007185; line-height: 1.2; font-weight: 500; }

        /* Color/Size Selection */
        .selection-section { margin-top: 20px; display: flex; flex-direction: column; gap: 20px; }
        .selector { display: flex; flex-direction: column; gap: 10px; }
        .selector-label { font-size: 14px; font-weight: 600; }
        .selector-label span { font-weight: 400; color: #111; margin-left: 5px; }

        .option-list { display: flex; gap: 10px; flex-wrap: wrap; }
        
        .color-option { 
            width: 80px; 
            border: 1px solid var(--border); 
            border-radius: 8px; 
            padding: 5px; 
            cursor: pointer; 
            text-align: center;
        }
        .color-option img { width: 100%; border-radius: 4px; margin-bottom: 4px; }
        .color-option span { font-size: 11px; }
        .color-option.active { border-color: var(--primary); box-shadow: 0 0 0 1px var(--primary); }

        .size-option {
            padding: 8px 16px; border: 1px solid var(--border); border-radius: 4px; font-size: 14px; cursor: pointer; transition: 0.2s;
        }
        .size-option:hover { background: #f0f2f2; border-color: #888c8c; }
        .size-option.active { background: #fef8f2; border-color: #e77600; box-shadow: 0 0 3px rgba(228, 121, 17, 0.5); font-weight: 600; }

        .btn-group { display: flex; gap: 15px; margin-top: 30px; }
        .btn { flex: 1; padding: 15px; border-radius: 30px; font-weight: 600; cursor: pointer; transition: 0.3s; border: none; text-align: center; text-decoration: none; }
        .btn-cart { background: #ffd814; color: #0f1111; }
        .btn-cart:hover { background: #f7ca00; }
        .btn-buy { background: #007185; color: #ffffff; }
        .btn-buy:hover { background: #005f73; }

        /* Responsive */
        @media (max-width: 1024px) {
            .container { grid-template-columns: 1fr; gap: 40px; padding: 0 20px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div style="display:flex; gap:20px; align-items:center;">
        <a href="dashboard.php" class="logo">WALKON</a>
        <a href="dashboard.php" class="back-btn" style="color:var(--primary);"><i class="fas fa-home"></i> Dashboard</a>
    </div>
    <a href="my_listings.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Products</a>
</nav>

<div class="container">
    <!-- Left Column: Image Gallery -->
    <div class="gallery-container">
        <div class="thumbnails">
            <?php foreach ($images as $index => $img_url): ?>
                <img src="<?php echo htmlspecialchars($img_url); ?>" 
                     class="thumb <?php echo $index === 0 ? 'active' : ''; ?>" 
                     onclick="switchImage(this, '<?php echo htmlspecialchars($img_url); ?>')"
                     alt="Thumbnail">
            <?php endforeach; ?>
        </div>
        
        <div class="main-image-view">
            <img src="<?php echo htmlspecialchars($images[0]); ?>" class="main-image" id="mainImage" alt="Shoe Product">
            <div class="share-btn"><i class="fas fa-share-alt"></i></div>
        </div>
    </div>

    <!-- Right Column: Product Details -->
    <div class="details-column">
        <div>
            <a href="#" class="brand-name">Brand: <?php echo htmlspecialchars($brand); ?></a>
            <h1 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h1>
            
            <div class="rating-box">
                <div class="stars">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <i class="fas fa-star<?php echo $i > floor($rating) ? ($i - $rating < 1 ? '-half-alt' : '-o') : ''; ?>"></i>
                    <?php endfor; ?>
                </div>
                <span class="reviews-count"><?php echo $reviews_count; ?> ratings</span>
            </div>
        </div>

        <div class="price-section">
            <div class="discount-row">
                <span class="discount-pct">-<?php echo $discount; ?>%</span>
                <span class="current-price"><small>₹</small><?php echo number_format($product['price']); ?></span>
            </div>
            <div class="mrp-row">
                M.R.P.: <span>₹<?php echo number_format($mrp); ?></span>
            </div>
            <p class="tax-note">Inclusive of all taxes</p>
        </div>

        <div class="offers-section">
            <h3 class="section-title"><i class="fas fa-percentage"></i> Offers</h3>
            <div class="offers-grid">
                <div class="offer-card">
                    <h4>Cashback</h4>
                    <p>Upto ₹28.00 cashback as Amazon Pay...</p>
                    <a href="#" class="offer-link">1 offer ></a>
                </div>
                <div class="offer-card">
                    <h4>Bank Offer</h4>
                    <p>Upto ₹1,500.00 discount on select...</p>
                    <a href="#" class="offer-link">30 offers ></a>
                </div>
            </div>
        </div>

        <div class="trust-badges">
            <div class="badge-item">
                <div class="badge-icon"><i class="fas fa-undo"></i></div>
                <span class="badge-text">10 days Return & Exchange</span>
            </div>
            <div class="badge-item">
                <div class="badge-icon"><i class="fas fa-hand-holding-usd"></i></div>
                <span class="badge-text">Pay on Delivery</span>
            </div>
            <div class="badge-item">
                <div class="badge-icon"><i class="fas fa-shipping-fast"></i></div>
                <span class="badge-text">Free Delivery</span>
            </div>
            <div class="badge-item">
                <div class="badge-icon"><i class="fas fa-trophy"></i></div>
                <span class="badge-text">Top Brand</span>
            </div>
        </div>

        <div class="selection-section">
            <div class="selector">
                <div class="selector-label">Colour: <span id="selectedColor">Dark Grey</span></div>
                <div class="option-list">
                    <div class="color-option active" onclick="selectOption(this, 'selectedColor', 'Dark Grey')">
                        <img src="<?php echo htmlspecialchars($images[0]); ?>" alt="Grey">
                        <span>₹<?php echo number_format($product['price']); ?></span>
                    </div>
                    <?php if (isset($images[1])): ?>
                    <div class="color-option" onclick="selectOption(this, 'selectedColor', 'Navy Blue')">
                        <img src="<?php echo htmlspecialchars($images[1]); ?>" alt="Blue">
                        <span>₹<?php echo number_format($product['price'] * 0.9); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="selector">
                <div class="selector-label">Size: <span id="selectedSize">7 UK</span></div>
                <div class="option-list">
                    <div class="size-option active" onclick="selectOption(this, 'selectedSize', '7 UK')">7 UK</div>
                    <div class="size-option" onclick="selectOption(this, 'selectedSize', '8 UK')">8 UK</div>
                    <div class="size-option" onclick="selectOption(this, 'selectedSize', '9 UK')">9 UK</div>
                    <div class="size-option" onclick="selectOption(this, 'selectedSize', '10 UK')">10 UK</div>
                </div>
            </div>
        </div>

        <div class="btn-group">
            <button class="btn btn-cart">Add to Cart</button>
            <button class="btn btn-buy">Buy Now</button>
        </div>

        <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
            <h3 style="font-size: 16px; margin-bottom: 10px;">About this item</h3>
            <p style="font-size: 14px; line-height: 1.6; color: #565959;">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </p>
        </div>
    </div>
</div>

<script>
    function switchImage(thumb, url) {
        document.getElementById('mainImage').src = url;
        document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
    }

    function selectOption(el, labelId, value) {
        document.getElementById(labelId).innerText = value;
        el.parentElement.querySelectorAll(el.tagName === 'DIV' ? '.active' : '.active').forEach(item => {
            item.classList.remove('active');
        });
        el.classList.add('active');
    }
</script>

</body>
</html>
