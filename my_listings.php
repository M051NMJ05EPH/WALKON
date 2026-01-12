<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];

// Get filter parameters
$search = trim($_GET['search'] ?? '');
$filter_category = $_GET['category'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_channel = $_GET['channel'] ?? '';

try {
    // Get the actual seller_id for this user
    $stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
    $stmt_seller->execute([$email]);
    $seller = $stmt_seller->fetch();
    $seller_id = $seller ? $seller['id'] : -1;

    // Fetch dynamic filter options for the UI
    $categories = $pdo->query("SELECT DISTINCT category FROM products WHERE seller_id = $seller_id AND category IS NOT NULL AND category != ''")->fetchAll(PDO::FETCH_COLUMN);
    $statuses = $pdo->query("SELECT DISTINCT status FROM products WHERE seller_id = $seller_id")->fetchAll(PDO::FETCH_COLUMN);

    // Build the query
    $query = "SELECT * FROM products WHERE seller_id = ?";
    $params = [$seller_id];

    if ($search) {
        $query .= " AND (product_name LIKE ? OR sku LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($filter_category) {
        $query .= " AND category = ?";
        $params[] = $filter_category;
    }

    if ($filter_status) {
        $query .= " AND status = ?";
        $params[] = $filter_status;
    }

    if ($filter_channel) {
        // Channels are stored as a comma-separated string, so we use LIKE
        $query .= " AND channels LIKE ?";
        $params[] = "%$filter_channel%";
    }

    $query .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
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
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --primary-light: #ecfdf5;
            --primary-dark: #059669;
            --secondary: #6366f1;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --bg-body: #f9fafb;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius-lg: 1rem;
            --radius-md: 0.75rem;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        
        body { background: var(--bg-body); color: var(--text-main); line-height: 1.5; padding: 2rem; }

        .container { max-width: 1400px; margin: 0 auto; }
        
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 2.5rem;
        }
        
        .header h1 { font-size: 2.25rem; font-weight: 700; color: var(--text-main); letter-spacing: -0.025em; }
        .header p { color: var(--text-muted); font-size: 1.1rem; }

        .header-actions { display: flex; gap: 1rem; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: var(--shadow-md); }

        .btn-secondary { background: #fee2e2; color: #991b1b; }
        .btn-secondary:hover { background: #fecaca; }

        /* Search Section */
        .search-wrapper {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 3rem;
            border: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .search-input-group {
            display: flex;
            gap: 0.5rem;
            flex: 1;
        }

        .filter-row {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #f3f4f6;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .select-input {
            padding: 0.5rem 2rem 0.5rem 1rem;
            border-radius: var(--radius-md);
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            color: var(--text-main);
            font-size: 0.875rem;
            outline: none;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
            min-width: 140px;
        }

        .select-input:focus { border-color: var(--primary); background-color: var(--white); }

        .search-form { display: flex; flex-direction: column; width: 100%; gap: 1rem; }

        .search-input {
            flex: 1;
            border: 1px solid #e5e7eb;
            padding: 0.75rem 1.25rem;
            font-size: 1rem;
            color: var(--text-main);
            outline: none;
            border-radius: var(--radius-md);
            background: #f9fafb;
        }

        .search-input:focus { border-color: var(--primary); background-color: var(--white); }

        .btn-search {
            background: var(--text-main);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
        }

        /* Grid & Cards */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }
        
        .card-link { text-decoration: none; color: inherit; display: block; height: 100%; }

        .card {
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f3f4f6;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .card:hover { transform: translateY(-8px); box-shadow: var(--shadow-lg); border-color: #e5e7eb; }
        
        .image-container {
            width: 100%;
            padding-top: 85%; /* Aspect ratio */
            position: relative;
            background: #f8fafc;
            overflow: hidden;
        }

        .card-img {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            object-fit: contain;
            padding: 1.5rem;
            transition: transform 0.5s ease;
        }

        .card:hover .card-img { transform: scale(1.08); }
        
        .card-body { 
            padding: 1.5rem; 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
        }

        .card-title { 
            font-size: 1.25rem; 
            font-weight: 600; 
            color: var(--text-main);
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 3.5rem; /* Forces consistent space for 2 lines */
        }

        .price-row { display: flex; align-items: baseline; gap: 0.5rem; margin-bottom: 0.5rem; }
        .card-price { color: var(--primary-dark); font-weight: 700; font-size: 1.5rem; }
        
        .card-sku { color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.25rem; }
        
        .channels { 
            display: flex; 
            gap: 0.75rem; 
            margin-bottom: 1.5rem;
            min-height: 1.5rem;
        }
        .channel-icon { 
            font-size: 1.25rem; 
            color: #9ca3af; 
            transition: color 0.2s;
        }
        .card:hover .channel-icon { color: var(--text-main); }
        
        .card-footer {
            margin-top: auto;
            padding-top: 1.25rem;
            border-top: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            background: var(--primary-light);
            color: var(--primary-dark);
        }

        .stock-info { font-size: 0.875rem; color: var(--text-muted); font-weight: 500; }

        /* Actions */
        .card-actions {
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 10;
            display: flex;
            gap: 0.5rem;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.2s ease;
        }
        .card:hover .card-actions { opacity: 1; transform: translateY(0); }

        .btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            color: #ef4444;
            box-shadow: var(--shadow-md);
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-icon:hover { background: #ef4444; color: white; transform: scale(1.1); }

        .empty-state {
            text-align: center;
            padding: 8rem 2rem;
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 2px dashed #e5e7eb;
        }
        .empty-state i { font-size: 4rem; color: #d1d5db; margin-bottom: 1.5rem; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h1>My Listings</h1>
            <p>You have <?php echo count($listings); ?> active products across all channels.</p>
        </div>
        <div class="header-actions">
            <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <a href="add_listing.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Product</a>
        </div>
    </div>

    <div class="search-wrapper">
        <form action="my_listings.php" method="GET" class="search-form">
            <div class="search-input-group">
                <input type="text" name="search" class="search-input" placeholder="Search by name or SKU..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-search"><i class="fas fa-search"></i> Search</button>
                <?php if ($search || $filter_category || $filter_status || $filter_channel): ?>
                    <a href="my_listings.php" class="btn" style="color:var(--text-muted)"><i class="fas fa-times"></i> Clear</a>
                <?php endif; ?>
            </div>

            <div class="filter-row">
                <div class="filter-group">
                    <span class="filter-label">Category:</span>
                    <select name="category" class="select-input" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $filter_category === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <span class="filter-label">Status:</span>
                    <select name="status" class="select-input" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <?php foreach ($statuses as $stat): ?>
                            <option value="<?php echo htmlspecialchars($stat); ?>" <?php echo $filter_status === $stat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($stat)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <span class="filter-label">Channel:</span>
                    <select name="channel" class="select-input" onchange="this.form.submit()">
                        <option value="">All Channels</option>
                        <?php 
                        $all_channels = ['Amazon', 'Shopify', 'TikTok', 'eBay', 'Flipkart', 'Instagram'];
                        foreach ($all_channels as $ch): ?>
                            <option value="<?php echo htmlspecialchars($ch); ?>" <?php echo $filter_channel === $ch ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ch); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <?php if (count($listings) > 0): ?>
        <div class="grid">
            <?php foreach ($listings as $product): ?>
                <?php 
                    $images_raw = $product['images'];
                    $first_image = 'https://via.placeholder.com/400x400?text=No+Preview';

                    if (!empty($images_raw)) {
                        $decoded = json_decode($images_raw, true);
                        $candidates = is_array($decoded) ? $decoded : [$images_raw];
                        
                        foreach ($candidates as $url) {
                            $is_local = (strpos($url, 'uploads/') === 0);
                            $is_http = (strpos($url, 'http') === 0);
                            
                            // Check for image extension
                            $path_only = parse_url($url, PHP_URL_PATH);
                            $ext = strtolower(pathinfo($path_only, PATHINFO_EXTENSION));
                            $is_image_ext = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                            
                            // Special case for providers like Unsplash
                            $is_image_provider = (strpos($url, 'unsplash.com') !== false || strpos($url, 'placeholder.com') !== false);

                            if ($is_local && file_exists($url)) {
                                $first_image = $url;
                                break;
                            } elseif ($is_http && ($is_image_ext || $is_image_provider)) {
                                $first_image = $url;
                                break;
                            }
                        }
                    }
                    $channels = $product['channels'] ? explode(',', $product['channels']) : [];
                ?>
                <div class="card-wrapper">
                    <div class="card">
                        <div class="card-actions">
                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn-icon" onclick="return confirm('Remove this listing permanently?')" title="Delete Product">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                        
                        <a href="product_details.php?id=<?php echo $product['id']; ?>" class="card-link">
                            <div class="image-container">
                                <img src="<?php echo htmlspecialchars($first_image); ?>" class="card-img" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                            </div>
                            
                            <div class="card-body">
                                <h3 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                                
                                <div class="price-row">
                                    <span class="card-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                </div>
                                
                                <div class="card-sku">SKU: <?php echo htmlspecialchars($product['sku']); ?></div>
                                
                                <div class="channels">
                                    <?php foreach($channels as $ch): 
                                        $ch_clean = strtolower(trim($ch));
                                        $icon_map = [
                                            'amazon' => 'amazon',
                                            'shopify' => 'shopify',
                                            'tiktok' => 'tiktok',
                                            'ebay' => 'ebay',
                                            'flipkart' => 'cart-shopping',
                                            'instagram' => 'instagram'
                                        ];
                                        $icon = $icon_map[$ch_clean] ?? 'globe';
                                    ?>
                                        <i class="fab fa-<?php echo $icon; ?> channel-icon" title="<?php echo htmlspecialchars($ch); ?>"></i>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="card-footer">
                                    <span class="status-badge"><?php echo htmlspecialchars($product['status']); ?></span>
                                    <span class="stock-info"><?php echo $product['quantity']; ?> in stock</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>No products found</h3>
            <p>Start your inventory by adding your first product to the system.</p>
            <a href="add_listing.php" class="btn btn-primary" style="margin-top: 1.5rem;">Add New Listing</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
