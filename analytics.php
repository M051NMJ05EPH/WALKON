<?php
session_start();
include 'config.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];

// Get the actual seller_id for this user
try {
    $stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
    $stmt_seller->execute([$email]);
    $seller = $stmt_seller->fetch();
    
    $seller_id = $seller ? $seller['id'] : -1;

    $stmt = $pdo->prepare("SELECT * FROM products WHERE seller_id = ?");
    $stmt->execute([$seller_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

// Calculate Stats
$total_listings = count($products);
$total_value = 0;
$total_stock = 0;
$categories = [];
$channel_counts = [
    'Amazon' => 0, 'Shopify' => 0, 'Flipkart' => 0, 'Instagram' => 0, 'TikTok' => 0, 'eBay' => 0
];

foreach ($products as $p) {
    $qty = intval($p['quantity']);
    $price = floatval($p['price']);
    $total_value += ($qty * $price);
    $total_stock += $qty;
    
    // Category Stats
    $cat = $p['category'] ?: 'Uncategorized';
    if (!isset($categories[$cat])) $categories[$cat] = 0;
    $categories[$cat]++;

    // Channel Stats
    $chs = explode(',', $p['channels']);
    foreach ($chs as $ch) {
        $ch_trim = trim($ch);
        foreach ($channel_counts as $key => $val) {
            if (strcasecmp($ch_trim, $key) === 0) {
                $channel_counts[$key]++;
            }
        }
    }
}

// Sort categories
arsort($categories);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Analytics - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #28a745;
            --text-dark: #333;
            --text-light: #6c757d;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --border: #e9ecef;
        }
        
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
        body { background: var(--bg-light); color: var(--text-dark); padding: 40px; }

        .container { max-width: 1200px; margin: 0 auto; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { font-size: 28px; }
        .nav-link { color: var(--text-light); text-decoration: none; margin-right: 20px; transition: 0.3s; }
        .nav-link:hover { color: var(--primary); }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
        }
        .stat-icon {
            width: 60px; height: 60px;
            border-radius: 12px;
            background: #d4edda;
            color: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            margin-right: 20px;
        }
        .stat-info h3 { font-size: 28px; font-weight: 700; color: var(--text-dark); margin-bottom: 5px; }
        .stat-info p { color: var(--text-light); font-size: 14px; }

        /* Sections Grid */
        .sections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
        }
        
        .content-card {
            background: var(--white);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .card-header { margin-bottom: 25px; border-bottom: 1px solid var(--border); padding-bottom: 15px; }
        .card-header h3 { font-size: 18px; color: var(--text-dark); }

        /* Lists */
        .data-list { list-style: none; }
        .data-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f1f1;
        }
        .data-item:last-child { border-bottom: none; }
        .data-label { color: var(--text-dark); font-weight: 500; }
        .data-value { font-weight: 600; color: var(--primary); }
        
        .progress-bg {
            background: #eee; height: 8px; width: 100px; border-radius: 4px; overflow: hidden; margin-left: auto; margin-right: 15px;
        }
        .progress-fill { background: var(--primary); height: 100%; border-radius: 4px; }
        
        /* Channel List specific */
        .channel-item i { width: 25px; text-align: center; margin-right: 10px; color: #555; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h1>Analytics Report</h1>
            <p style="color:var(--text-light)">Overview of your store performance</p>
        </div>
        <div>
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <button class="btn" style="background:var(--primary); color:white; border:none; padding:10px 20px; border-radius:20px; cursor:pointer;" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-box"></i></div>
            <div class="stat-info">
                <h3><?php echo number_format($total_listings); ?></h3>
                <p>Total Listings</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
            <div class="stat-info">
                <h3>₹<?php echo number_format($total_value, 2); ?></h3>
                <p>Total Inventory Value</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
            <div class="stat-info">
                <h3><?php echo number_format($total_stock); ?></h3>
                <p>Total Items in Stock</p>
            </div>
        </div>
    </div>

    <div class="sections-grid">
        <!-- Category Breakdown -->
        <div class="content-card">
            <div class="card-header">
                <h3>Inventory by Category</h3>
            </div>
            <ul class="data-list">
                <?php if (count($categories) > 0): ?>
                    <?php foreach ($categories as $cat => $count): ?>
                        <?php 
                            $percent = ($total_listings > 0) ? ($count / $total_listings) * 100 : 0;
                        ?>
                        <li class="data-item">
                            <span class="data-label"><?php echo htmlspecialchars($cat); ?></span>
                            <div class="progress-bg">
                                <div class="progress-fill" style="width: <?php echo $percent; ?>%"></div>
                            </div>
                            <span class="data-value"><?php echo $count; ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="data-item">No categories data available</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Channel Distribution -->
        <div class="content-card">
            <div class="card-header">
                <h3>Channel Distribution</h3>
            </div>
            <ul class="data-list">
                <li class="data-item channel-item">
                    <span><i class="fab fa-amazon"></i> Amazon</span>
                    <span class="data-value"><?php echo $channel_counts['Amazon']; ?></span>
                </li>
                <li class="data-item channel-item">
                    <span><i class="fab fa-shopify"></i> Shopify</span>
                    <span class="data-value"><?php echo $channel_counts['Shopify']; ?></span>
                </li>
                <li class="data-item channel-item">
                    <span><i class="fas fa-cart-shopping"></i> Flipkart</span>
                    <span class="data-value"><?php echo $channel_counts['Flipkart']; ?></span>
                </li>
                <li class="data-item channel-item">
                    <span><i class="fab fa-instagram"></i> Instagram</span>
                    <span class="data-value"><?php echo $channel_counts['Instagram']; ?></span>
                </li>
                <li class="data-item channel-item">
                    <span><i class="fab fa-tiktok"></i> TikTok Shop</span>
                    <span class="data-value"><?php echo $channel_counts['TikTok']; ?></span>
                </li>
                <li class="data-item channel-item">
                    <span><i class="fab fa-ebay"></i> eBay</span>
                    <span class="data-value"><?php echo $channel_counts['eBay']; ?></span>
                </li>
            </ul>
        </div>
    </div>
</div>

</body>
</html>
