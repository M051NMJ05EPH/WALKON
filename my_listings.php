<?php
session_start();
include 'config.php';

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
    
    $search = trim($_GET['search'] ?? '');
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE seller_id = ? AND (product_name LIKE ? OR sku LIKE ?) ORDER BY created_at DESC");
        $stmt->execute([$seller_id, "%$search%", "%$search%"]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC");
        $stmt->execute([$seller_id]);
    }
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching listings: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Listings - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #28a745;
            --text-dark: #333;
            --bg-light: #f8f9fa;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
        body { background: var(--bg-light); color: var(--text-dark); padding: 40px; }

        .container { max-width: 1200px; margin: 0 auto; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h1 { font-size: 28px; }
        .btn-add {
            background: var(--primary); color: white; padding: 10px 20px; border-radius: 30px; text-decoration: none; font-weight: 600;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .card {
            background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: 0.3s;
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        
        .card-img-top {
            width: 100%; height: 200px; object-fit: cover;
            background: #eee;
        }
        
        .card-body { padding: 20px; }
        .card-title { font-size: 18px; font-weight: 600; margin-bottom: 10px; }
        .card-price { color: var(--primary); font-weight: 700; font-size: 18px; margin-bottom: 5px; }
        .card-sku { color: #888; font-size: 13px; margin-bottom: 15px; }
        
        .channels { margin-bottom: 15px; }
        .channel-icon { margin-right: 5px; color: #555; }
        
        .status-badge {
            display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;
            background: #d4edda; color: #155724;
        }

        .empty-state { text-align: center; padding: 60px; color: #888; }

        .search-container {
            margin-bottom: 30px;
            display: flex;
            gap: 10px;
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .search-input {
            flex-grow: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
        }
        .btn-search {
            background: var(--text-dark);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }
        .btn-cancel {
            background: #f8d7da;
            color: #721c24;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
        }
        .btn-cancel:hover { background: #f5c6cb; }

        .card-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 8px;
            opacity: 0;
            transition: 0.3s;
        }
        .card:hover .card-actions { opacity: 1; }
        .btn-remove {
            background: rgba(220, 53, 69, 0.9);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-remove:hover { background: #c82333; transform: scale(1.1); }
        .card { position: relative; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h1>My Listings</h1>
            <p>Manage your active products</p>
        </div>
        <div>
            <a href="dashboard.php" class="btn-cancel" style="margin-right:10px;"><i class="fas fa-times"></i> Cancel</a>
            <a href="add_listing.php" class="btn-add"><i class="fas fa-plus"></i> Add New Listing</a>
        </div>
    </div>

    <div class="search-container">
        <form action="my_listings.php" method="GET" style="display:flex; width:100%; gap:10px;">
            <input type="text" name="search" class="search-input" placeholder="Search by product name or SKU..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn-search"><i class="fas fa-search"></i> Search</button>
            <?php if ($search): ?>
                <a href="my_listings.php" class="btn-search" style="background:#6c757d; text-decoration:none;">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (count($listings) > 0): ?>
        <div class="grid">
            <?php foreach ($listings as $product): ?>
                <?php 
                    // Robust image selection
                    $images_raw = $product['images'];
                    $first_image = 'https://via.placeholder.com/280x200?text=No+Image';

                    if (!empty($images_raw)) {
                        $decoded = json_decode($images_raw, true);
                        $candidates = is_array($decoded) ? $decoded : [$images_raw];
                        
                        // Try to find a valid image candidate
                        foreach ($candidates as $url) {
                            $is_local = (strpos($url, 'uploads/') === 0);
                            $is_http = (strpos($url, 'http') === 0);
                            
                            // Check if it's likely a direct image (ends in common extension or is local)
                            $path = parse_url($url, PHP_URL_PATH);
                            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                            $is_image_ext = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                            
                            if ($is_local && file_exists($url)) {
                                $first_image = $url;
                                break;
                            } elseif ($is_http && $is_image_ext) {
                                $first_image = $url;
                                // Don't break yet, if there's a local one later we might prefer it, 
                                // but for now let's just take the first valid image found.
                                break;
                            }
                        }
                    }
                    
                    // Channels
                    $channels = $product['channels'] ? explode(',', $product['channels']) : [];
                ?>
                <a href="product_details.php?id=<?php echo $product['id']; ?>" class="card-link" style="text-decoration:none; color:inherit;">
                <div class="card">
                    <img src="<?php echo htmlspecialchars($first_image); ?>" class="card-img-top" alt="Product">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        <div class="card-price">₹<?php echo number_format($product['price'], 2); ?></div>
                        <div class="card-sku">SKU: <?php echo htmlspecialchars($product['sku']); ?></div>
                        
                        <div class="card-actions">
                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn-remove" onclick="return confirm('Are you sure you want to remove this product?')" title="Remove Product">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                        
                        <div class="channels">
                            <?php foreach($channels as $ch): ?>
                                <i class="fab fa-<?php echo strtolower(trim($ch)); ?> channel-icon" title="<?php echo htmlspecialchars($ch); ?>"></i>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span class="status-badge"><?php echo htmlspecialchars($product['status']); ?></span>
                            <span style="font-size:13px; color:#888;"><?php echo $product['quantity']; ?> in stock</span>
                        </div>
                    </div>
                </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box-open" style="font-size: 50px; margin-bottom: 20px;"></i>
            <h3>No listings found</h3>
            <p>Get started by adding your first product.</p>
            <a href="add_listing.php" class="btn-add" style="margin-top:20px; display:inline-block;">Add Listing</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
