<?php
/**
 * Seed Products - Add sample shoes for all brands
 */
include 'config.php';

// Get seller ID (use first seller or create demo seller)
try {
    $stmt = $pdo->query("SELECT id FROM sellers LIMIT 1");
    $seller = $stmt->fetch();
    
    if (!$seller) {
        // Create demo seller
        $pdo->exec("INSERT INTO sellers (name, email, password, business_name) VALUES ('Demo Seller', 'demo@walkon.com', '" . password_hash('demo123', PASSWORD_DEFAULT) . "', 'WALKON Store')");
        $seller_id = $pdo->lastInsertId();
    } else {
        $seller_id = $seller['id'];
    }

    // Get all brands
    $brands = $pdo->query("SELECT id, name FROM brands")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all categories
    $categories = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($brands)) {
        die("No brands found. Please run update_brands.php first.");
    }
    
    if (empty($categories)) {
        die("No categories found. Please add categories first.");
    }

    // Product templates per brand
    $product_templates = [
        'Nike' => [
            ['Air Max 270', 8999, 'Running Shoes', 'The Nike Air Max 270 features Nike\'s biggest heel Air unit yet for a super-soft ride.'],
            ['Air Force 1 Low', 7999, 'Sneakers', 'The radiance lives on in the iconic AF1 Low, a basketball original.'],
            ['Revolution 6', 4999, 'Running Shoes', 'Lightweight and breathable mesh upper for everyday running comfort.'],
        ],
        'adidas' => [
            ['Ultraboost 22', 12999, 'Running Shoes', 'Experience incredible energy return with BOOST midsole technology.'],
            ['Stan Smith', 6999, 'Sneakers', 'The legendary tennis shoe with a clean, minimalist design.'],
            ['Alphabounce+', 5999, 'Running Shoes', 'Responsive cushioning for a smooth, stable stride.'],
        ],
        'PUMA' => [
            ['RS-X', 7999, 'Sneakers', 'Bold design with chunky silhouette and vibrant colors.'],
            ['Velocity Nitro 2', 8499, 'Running Shoes', 'Ultra-responsive NITRO foam for explosive speed.'],
            ['Suede Classic', 5499, 'Sneakers', 'Iconic suede sneaker that defined street style.'],
        ],
        'Reebok' => [
            ['Classic Leather', 5999, 'Sneakers', 'Timeless design with premium leather upper.'],
            ['Nano X3', 9999, 'Sports', 'Versatile training shoe for high-intensity workouts.'],
            ['Floatride Energy 4', 7999, 'Running Shoes', 'Lightweight and responsive for long-distance runs.'],
        ],
        'New Balance' => [
            ['Fresh Foam 1080v12', 11999, 'Running Shoes', 'Plush cushioning for premium comfort mile after mile.'],
            ['574 Core', 6499, 'Sneakers', 'The iconic 574 with classic ENCAP midsole technology.'],
            ['FuelCell Rebel v3', 9999, 'Running Shoes', 'Fast and fun with FuelCell propulsion.'],
        ],
        'Skechers' => [
            ['Go Walk 6', 4999, 'Sneakers', 'Ultra-light and incredibly comfortable for all-day wear.'],
            ['D\'Lites Fresh Start', 5499, 'Sneakers', 'Chunky heritage design with Air-Cooled Memory Foam.'],
            ['Max Cushioning Elite', 7999, 'Running Shoes', 'Maximum cushioned comfort for high-impact activities.'],
        ],
        'Under Armour' => [
            ['HOVR Phantom 3', 10999, 'Running Shoes', 'Zero gravity feel with UA HOVR technology.'],
            ['Charged Assert 10', 5499, 'Running Shoes', 'Lightweight and durable for everyday running.'],
            ['Project Rock 5', 11999, 'Sports', 'Built tough for intense training sessions.'],
        ],
        'Vans' => [
            ['Old Skool', 4999, 'Sneakers', 'The iconic side stripe makes this a timeless classic.'],
            ['Sk8-Hi', 5499, 'Sneakers', 'High-top heritage style for skaters and style seekers.'],
            ['Authentic', 3999, 'Sneakers', 'The original Vans shoe with simple canvas upper.'],
        ],
        'Converse' => [
            ['Chuck Taylor All Star', 4499, 'Sneakers', 'The original basketball shoe, now a cultural icon.'],
            ['Chuck 70', 6999, 'Sneakers', 'Premium materials with vintage styling details.'],
            ['One Star Pro', 5999, 'Sneakers', 'Suede upper with CONS cushioning for skate performance.'],
        ],
        'Timberland' => [
            ['Premium 6-Inch Boot', 14999, 'Boots', 'The original yellow boot - waterproof and iconic.'],
            ['Euro Sprint Hiker', 9999, 'Boots', 'Lightweight hiking boot for urban adventures.'],
            ['Bradstreet Chukka', 7999, 'Boots', 'Modern chukka with SensorFlex comfort technology.'],
        ],
        'Clarks' => [
            ['Desert Boot', 8999, 'Boots', 'The original desert boot with crepe sole.'],
            ['Wallabee', 10999, 'Boots', 'Distinctive moccasin construction with nature-inspired design.'],
            ['Un Costa Lace', 6999, 'Formal Shoes', 'Lightweight casual with Unstructured comfort.'],
        ],
        'Crocs' => [
            ['Classic Clog', 3499, 'Sandals & Slides', 'The original comfort clog with Croslite foam.'],
            ['LiteRide 360 Clog', 4499, 'Sandals & Slides', 'Next-generation LiteRide foam for soft cushioning.'],
            ['Bayaband Clog', 3999, 'Sandals & Slides', 'Sporty style with bold racing stripes.'],
        ],
        'Dr. Martens' => [
            ['1460 Boot', 15999, 'Boots', 'The original Dr. Martens boot with AirWair sole.'],
            ['1461 Oxford', 12999, 'Formal Shoes', 'Classic 3-eye shoe with signature yellow stitching.'],
            ['Jadon Platform', 17999, 'Boots', 'Platform version of the iconic 1460.'],
        ],
        'ASICS' => [
            ['Gel-Kayano 29', 12999, 'Running Shoes', 'Maximum support for overpronators with GEL technology.'],
            ['Gel-Nimbus 25', 11999, 'Running Shoes', 'Plush cushioning for neutral runners.'],
            ['GT-2000 11', 9999, 'Running Shoes', 'Stability and support for everyday training.'],
        ],
        'Fila' => [
            ['Disruptor 2', 5999, 'Sneakers', 'Chunky platform sneaker that started the dad shoe trend.'],
            ['Ray Tracer', 4999, 'Sneakers', 'Retro-inspired runner with bold colorblocking.'],
            ['Memory Workshift', 3999, 'Sneakers', 'Slip-resistant work shoe with memory foam.'],
        ],
        'Jordan' => [
            ['Air Jordan 1 Mid', 10999, 'Sneakers', 'The legendary silhouette in mid-top form.'],
            ['Air Jordan 4 Retro', 16999, 'Sneakers', 'Iconic design with visible Air cushioning.'],
            ['Jordan Max Aura 4', 7999, 'Sports', 'Basketball-inspired style for everyday wear.'],
        ],
        'Bata' => [
            ['Comfit Leather Formal', 2999, 'Formal Shoes', 'Classic leather formal with cushioned insole.'],
            ['Power Running Shoe', 1999, 'Running Shoes', 'Affordable performance for daily runners.'],
            ['Northstar Canvas', 1499, 'Sneakers', 'Casual canvas shoe for everyday wear.'],
        ],
        'Woodland' => [
            ['Leather Outdoor Boot', 5999, 'Boots', 'Rugged leather boot for outdoor adventures.'],
            ['Casual Sneaker', 3999, 'Sneakers', 'Comfortable casual with premium materials.'],
            ['Trekking Shoe', 4999, 'Sports', 'Durable trekking shoe with excellent grip.'],
        ],
        'Sparx' => [
            ['Running Pro SM-672', 1299, 'Running Shoes', 'Lightweight mesh upper for breathability.'],
            ['Sports Sandal SS-101', 799, 'Sandals & Slides', 'Durable sports sandal for active lifestyle.'],
            ['Canvas Sneaker SC-300', 999, 'Sneakers', 'Casual canvas shoe at an affordable price.'],
        ],
        'Campus' => [
            ['Dragon Running', 1799, 'Running Shoes', 'Performance running shoe with EVA midsole.'],
            ['Battle Sneaker', 1499, 'Sneakers', 'Street-ready style at budget-friendly price.'],
            ['First Sports', 1299, 'Sports', 'Multi-sport shoe for school and play.'],
        ],
        'Red Tape' => [
            ['Leather Oxford', 3499, 'Formal Shoes', 'Premium leather formal for professionals.'],
            ['Athleisure Sneaker', 2999, 'Sneakers', 'Modern sneaker with athletic styling.'],
            ['Chelsea Boot', 4499, 'Boots', 'Classic Chelsea boot with elastic side panels.'],
        ],
        'Liberty' => [
            ['Formal Lace-Up', 2499, 'Formal Shoes', 'Traditional formal shoe with comfort insole.'],
            ['Force 10 Running', 1999, 'Running Shoes', 'Performance running at affordable price.'],
            ['Casual Loafer', 2299, 'Formal Shoes', 'Slip-on comfort for casual occasions.'],
        ],
        'Lotto' => [
            ['Stadium Running', 2499, 'Running Shoes', 'Italian design with performance features.'],
            ['Tennis Classic', 2999, 'Sports', 'Court-ready tennis shoe with stability.'],
            ['Lifestyle Sneaker', 2299, 'Sneakers', 'Casual style for everyday wear.'],
        ],
        'Relaxo' => [
            ['Flite Slipper', 399, 'Sandals & Slides', 'Lightweight and comfortable daily slipper.'],
            ['Sparx Floater', 799, 'Sandals & Slides', 'Sporty floater for outdoor activities.'],
            ['Hawaii Chappal', 299, 'Sandals & Slides', 'Classic rubber slipper for home use.'],
        ],
        'Paragon' => [
            ['Vertex Slipper', 499, 'Sandals & Slides', 'Comfortable daily wear slipper.'],
            ['Stimulus Sandal', 699, 'Sandals & Slides', 'Durable sandal for regular use.'],
            ['Ladies Chappal', 399, 'Sandals & Slides', 'Everyday comfort for women.'],
        ],
        'Action' => [
            ['Running Shoes', 1499, 'Running Shoes', 'Performance running at budget price.'],
            ['Sports Sandal', 699, 'Sandals & Slides', 'Active sandal for sports activities.'],
            ['School Shoes', 899, 'Formal Shoes', 'Durable school shoes for kids.'],
        ],
        'Lee Cooper' => [
            ['Leather Sneaker', 3999, 'Sneakers', 'Premium leather casual sneaker.'],
            ['Workwear Boot', 4499, 'Boots', 'Durable boot for work environments.'],
            ['Canvas Casual', 2499, 'Sneakers', 'Lightweight canvas for casual wear.'],
        ],
    ];

    $genders = ['Men', 'Women', 'Unisex'];
    $added = 0;
    $skipped = 0;

    foreach ($brands as $brand) {
        $brand_name = $brand['name'];
        $brand_id = $brand['id'];
        
        // Get products for this brand
        $products = $product_templates[$brand_name] ?? [
            [$brand_name . ' Running Shoe', 2999, 'Running Shoes', 'Quality running shoe from ' . $brand_name],
            [$brand_name . ' Casual Sneaker', 2499, 'Sneakers', 'Comfortable casual sneaker from ' . $brand_name],
            [$brand_name . ' Sports Shoe', 2799, 'Sports', 'Performance sports shoe from ' . $brand_name],
        ];
        
        foreach ($products as $product) {
            $name = $product[0];
            $price = $product[1];
            $category_name = $product[2];
            $description = $product[3];
            
            // Find category
            $category_id = null;
            foreach ($categories as $cat) {
                if (stripos($cat['name'], explode(' ', $category_name)[0]) !== false) {
                    $category_id = $cat['id'];
                    break;
                }
            }
            if (!$category_id) $category_id = $categories[0]['id'];
            
            // Check if product already exists
            $check = $pdo->prepare("SELECT id FROM product_base WHERE name = ? AND seller_id = ?");
            $check->execute([$name, $seller_id]);
            if ($check->fetch()) {
                $skipped++;
                continue;
            }
            
            // Random gender
            $gender = $genders[array_rand($genders)];
            
            // Insert product_base
            $stmt = $pdo->prepare("INSERT INTO product_base (seller_id, category_id, name, status) VALUES (?, ?, ?, 'published')");
            $stmt->execute([$seller_id, $category_id, $name]);
            $product_id = $pdo->lastInsertId();
            
            // Insert description
            // $pdo->prepare("INSERT INTO product_descriptions (product_id, content) VALUES (?, ?)")->execute([$product_id, $description]);
            
            // Insert price (with max_price for original price display)
            $max_price = $price + rand(500, 2000);
            $pdo->prepare("INSERT INTO product_prices (product_id, price, min_price, max_price) VALUES (?, ?, ?, ?)")->execute([$product_id, $price, $price - 500, $max_price]);
            
            // Insert stock
            $pdo->prepare("INSERT INTO product_stock (product_id, quantity) VALUES (?, ?)")->execute([$product_id, rand(10, 100)]);
            
            // Insert SKU
            $sku = strtoupper(substr($brand_name, 0, 3)) . '-' . str_pad($product_id, 5, '0', STR_PAD_LEFT);
            $pdo->prepare("INSERT INTO product_skus (product_id, sku) VALUES (?, ?)")->execute([$product_id, $sku]);
            
            // Insert specs
            $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, gender, outer_material, occasion) VALUES (?, ?, ?, ?, ?)")
                ->execute([$product_id, $brand_id, $gender, 'Synthetic', 'Casual']);
            
            // Insert placeholder image
            $image_url = 'https://via.placeholder.com/400x400?text=' . urlencode($name);
            $pdo->prepare("INSERT INTO product_media (product_id, url, type, is_primary) VALUES (?, ?, 'image', 1)")->execute([$product_id, $image_url]);
            
            $added++;
        }
    }

    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Products Seeded - WALKON</title>
        <link href='https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap' rel='stylesheet'>
        <style>
            * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit',sans-serif; }
            body { background:#030712; color:#fff; min-height:100vh; display:flex; align-items:center; justify-content:center; }
            .card { background:#111827; padding:60px; border-radius:32px; text-align:center; max-width:600px; border:1px solid rgba(255,255,255,0.08); }
            .icon { width:100px; height:100px; background:rgba(16,185,129,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 30px; font-size:3rem; color:#10b981; }
            h1 { font-size:2rem; margin-bottom:15px; }
            .stats { display:flex; gap:30px; justify-content:center; margin:30px 0; flex-wrap:wrap; }
            .stat { text-align:center; }
            .stat-value { font-size:2.5rem; font-weight:800; color:#10b981; }
            .stat-label { font-size:0.85rem; color:#9ca3af; text-transform:uppercase; }
            .btn { display:inline-block; padding:15px 35px; background:#10b981; color:white; text-decoration:none; border-radius:50px; font-weight:600; margin:10px; transition:0.3s; }
            .btn:hover { background:#059669; transform:translateY(-3px); }
            .btn-secondary { background:#374151; }
        </style>
    </head>
    <body>
        <div class='card'>
            <div class='icon'>👟</div>
            <h1>Products Seeded Successfully!</h1>
            <p style='color:#9ca3af; margin-bottom:20px;'>Sample products have been added for all brands in your database.</p>
            <div class='stats'>
                <div class='stat'>
                    <div class='stat-value'>$added</div>
                    <div class='stat-label'>Products Added</div>
                </div>
                <div class='stat'>
                    <div class='stat-value'>$skipped</div>
                    <div class='stat-label'>Already Existed</div>
                </div>
                <div class='stat'>
                    <div class='stat-value'>" . count($brands) . "</div>
                    <div class='stat-label'>Brands</div>
                </div>
            </div>
            <a href='shop.php' class='btn'>Browse Shop</a>
            <a href='index.php' class='btn btn-secondary'>Go Home</a>
        </div>
    </body>
    </html>";

} catch (PDOException $e) {
    echo "<div style='padding:40px; text-align:center; font-family:sans-serif; background:#030712; color:white; min-height:100vh;'>
            <h2 style='color:#ef4444;'>Error</h2>
            <p style='color:#9ca3af;'>" . htmlspecialchars($e->getMessage()) . "</p>
            <a href='dashboard.php' style='display:inline-block; margin-top:20px; padding:12px 24px; background:#374151; color:white; text-decoration:none; border-radius:50px;'>Back to Dashboard</a>
          </div>";
}
?>
