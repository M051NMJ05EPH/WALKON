<?php
session_start();
include 'config.php';

// Auth & Role Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'store', 'entrepreneur', 'store_owner'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];

// Get the actual seller_id for this user - Support staff mapping
try {
    $seller_id = $_SESSION['seller_id'] ?? null;
    // Fix: Retry DB lookup if session has -1 (invalid/stale) state
    if (!$seller_id || $seller_id == -1) {
        $stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
        $stmt_seller->execute([$email]);
        $seller = $stmt_seller->fetch();
        $seller_id = $seller ? $seller['id'] : -1;
        if ($seller_id != -1) $_SESSION['seller_id'] = $seller_id;
    }
    
    // If no seller ID found for store/entrepreneur, don't die - just show empty list
    if ($seller_id == -1 && $_SESSION['role'] !== 'admin') {
        $listings = [];
        $health = ['total_value' => 0, 'low_stock_count' => 0, 'total_skus' => 0];
        $category_list = [];
        $subcategory_list = [];
        $status_list = [];
        $brand_list = [];
        
        // Fix: Initialize filters to prevent warnings
        $search = '';
        $status_filter = '';
        $category_filter = '';
        $subcategory_filter = '';
        $channel_filter = '';
        $stock_filter = '';
        $brand_filter = '';
        
        // Skip the query execution
        goto render_page;
    }
    
    $search = trim($_GET['search'] ?? '');
    $status_filter = trim($_GET['status'] ?? '');
    $category_filter = trim($_GET['category'] ?? '');
    $subcategory_filter = trim($_GET['subcategory'] ?? '');
    $channel_filter = trim($_GET['channel'] ?? '');
    $stock_filter = trim($_GET['stock_filter'] ?? '');
    $brand_filter = trim($_GET['brand_id'] ?? '');

    // Get unique categories and statuses for filters
    $categories = $pdo->prepare("SELECT DISTINCT c.name FROM categories c 
                                JOIN product_base pb ON pb.category_id = c.id 
                                WHERE pb.seller_id = ?");
    $categories->execute([$seller_id]);
    $category_list = $categories->fetchAll(PDO::FETCH_COLUMN);

    // Dynamic Subcategory Fetching based on Category Filter
    $subcategory_list = [];
    if ($category_filter) {
        $sub_sql = "SELECT DISTINCT sc.name FROM sub_categories sc
                    JOIN categories c ON sc.category_id = c.id 
                    WHERE c.name = ?";
        $sub_categories = $pdo->prepare($sub_sql);
        $sub_categories->execute([$category_filter]);
        $subcategory_list = $sub_categories->fetchAll(PDO::FETCH_COLUMN);
    }

    $statuses = $pdo->prepare("SELECT DISTINCT status FROM product_base WHERE seller_id = ?");
    $statuses->execute([$seller_id]);
    $status_list = $statuses->fetchAll(PDO::FETCH_COLUMN);

    // Get brands associated with this seller's products
    $brands_stmt = $pdo->prepare("SELECT DISTINCT b.id, b.name FROM brands b 
                                 JOIN product_specs spec ON spec.brand_id = b.id 
                                 JOIN product_base pb ON pb.id = spec.product_id 
                                 WHERE pb.seller_id = ?");
    $brands_stmt->execute([$seller_id]);
    $brand_list = $brands_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reset subcategory filter if it's no longer valid for the selected category
    if ($subcategory_filter && !in_array($subcategory_filter, $subcategory_list)) {
        $subcategory_filter = '';
    }

    // Build Dynamic Query
    $query = "SELECT 
                pb.id, 
                pb.name as product_name, 
                pb.status, 
                pb.created_at,
                pp.price, 
                ps.sku, 
                pst.quantity,
                b.id as brand_id,
                b.name as brand_name,
                b.logo_url as brand_logo,
                (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT url FROM product_media pm WHERE pm.product_id = pb.id LIMIT 1) as fallback_image,
                GROUP_CONCAT(pc.channel_name) as channels_str
              FROM product_base pb
              LEFT JOIN product_prices pp ON pb.id = pp.product_id
              LEFT JOIN product_skus ps ON pb.id = ps.product_id
              LEFT JOIN product_stock pst ON pb.id = pst.product_id
              LEFT JOIN product_specs spec ON pb.id = spec.product_id
              LEFT JOIN brands b ON spec.brand_id = b.id
              LEFT JOIN categories c ON pb.category_id = c.id
              LEFT JOIN product_channels pc ON pb.id = pc.product_id
              WHERE pb.seller_id = ?";
    
    $params = [$seller_id];

    if ($search) {
        $query .= " AND (pb.name LIKE ? OR ps.sku LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($status_filter) {
        $query .= " AND pb.status = ?";
        $params[] = $status_filter;
    }
    if ($category_filter) {
        $query .= " AND c.name = ?";
        $params[] = $category_filter;
    }
    if ($subcategory_filter) {
        $query .= " AND EXISTS (SELECT 1 FROM sub_categories sc WHERE sc.id = pb.sub_category_id AND sc.name = ?)";
        $params[] = $subcategory_filter;
    }
    if ($channel_filter) {
        $query .= " AND EXISTS (SELECT 1 FROM product_channels pch WHERE pch.product_id = pb.id AND pch.channel_name = ?)";
        $params[] = $channel_filter;
    }
    if ($stock_filter === 'low') {
        $query .= " AND pst.quantity < 10";
    }
    if ($brand_filter) {
        $query .= " AND b.id = ?";
        $params[] = $brand_filter;
    }

    $query .= " GROUP BY pb.id, pp.price, ps.sku, pst.quantity"; 
    $query .= " ORDER BY pb.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stock Health Stats
    $stmt_health = $pdo->prepare("
        SELECT 
            SUM(ps.quantity * pp.price) as total_value,
            COUNT(CASE WHEN ps.quantity < 10 THEN 1 END) as low_stock_count,
            COUNT(*) as total_skus
        FROM product_base pb
        JOIN product_stock ps ON pb.id = ps.product_id
        JOIN product_prices pp ON pb.id = pp.product_id
        WHERE pb.seller_id = ?
    ");
    $stmt_health->execute([$seller_id]);
    $health = $stmt_health->fetch(PDO::FETCH_ASSOC);

    render_page:;

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
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;       /* Royal Blue */
            --primary-glow: rgba(37, 99, 235, 0.2);
            --primary-dark: #1d4ed8;
            --bg: #ffffff;
            --surface: rgba(255, 255, 255, 0.82);
            --card-bg: #ffffff;
            --text-main: #1e293b;     /* Deep Navy */
            --text-muted: #64748b;
            --border: #e2e8f0;
            --sky-light: #f0f9ff;
            --sky-mid: #e0f2fe;
            --glass: rgba(255, 255, 255, 0.5);
            --glass-active: rgba(37, 99, 235, 0.1);
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { 
            background: radial-gradient(circle at 10% 20%, var(--sky-mid) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, var(--sky-light) 0%, transparent 40%),
                        var(--bg);
            color: var(--text-main); padding: 0; display: flex; flex-direction: column; min-height: 100vh; overflow-x: hidden; 
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--sky-mid); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary); }

        .container { max-width: 1400px; margin: 0 auto; padding: 40px 2rem; flex: 1; width: 100%; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 50px; }
        .header h1 { font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 800; color: var(--text-main); margin-bottom: 8px; letter-spacing: -0.5px; }
        .header p { color: var(--text-muted); font-size: 1.1rem; font-weight: 500; }
        
        .btn-add {
            background: var(--primary); color: #fff; padding: 14px 28px; border-radius: 14px; text-decoration: none; font-weight: 600;
            display: inline-flex; align-items: center; gap: 10px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.2);
        }
        .btn-add:hover { background: var(--primary-dark); transform: translateY(-3px); box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3); }

        .btn-cancel {
            background: #fff; color: var(--text-main); padding: 14px 24px; border-radius: 14px; text-decoration: none;
            font-weight: 500; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .btn-cancel:hover { background: var(--sky-light); transform: translateY(-2px); border-color: var(--primary); }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }
        
        .card {
            background: var(--card-bg); border-radius: 24px; overflow: hidden; 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; height: 100%;
            border: 1px solid var(--border); position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        }
        .card:hover { transform: translateY(-10px); border-color: var(--primary); box-shadow: 0 30px 60px rgba(37, 99, 235, 0.08); }
        
        .card-img-wrapper { position: relative; width: 100%; height: 260px; overflow: hidden; background: #000; }
        .card-img-top { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1); }
        .card:hover .card-img-top { transform: scale(1.1); }

        .brand-badge {
            position: absolute; top: 15px; right: 15px; background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px); color: #fff; padding: 6px 14px; border-radius: 50px;
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            border: 1px solid rgba(255,255,255,0.1); z-index: 5;
        }

        .floating-price {
            position: absolute; top: 15px; left: 15px; background: var(--primary);
            color: #fff; padding: 8px 16px; border-radius: 12px;
            font-weight: 800; font-size: 16px; z-index: 5; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
        }
        
        .card-body { padding: 25px; display: flex; flex-direction: column; flex-grow: 1; position: relative; }
        .card-category { color: var(--primary); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 10px; }
        .card-title { 
            font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; line-height: 1.3; height: 2.6em;
            overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; color: var(--text-main);
        }
        .card-sku { color: var(--text-muted); font-size: 11px; margin-bottom: 20px; font-family: monospace; opacity: 0.6; }
        
        .card-footer {
            margin-top: auto; display: flex; justify-content: space-between; align-items: center;
            padding-top: 20px; border-top: 1px solid var(--border);
        }

        .status-badge {
            display: inline-block; padding: 6px 14px; border-radius: 30px; font-size: 10px; font-weight: 800;
            text-transform: uppercase; letter-spacing: 0.5px; background: rgba(16, 185, 129, 0.1); color: var(--primary);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        .status-badge.low-stock { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }

        .stock-count { font-size: 12px; color: var(--text-muted); font-weight: 600; }
        .stock-count b { color: var(--text-main); }

        .search-container { margin-bottom: 40px; }
        .search-form { display: flex; width: 100%; gap: 15px; position: relative; }
        .search-input {
            flex-grow: 1; padding: 16px 25px; border: 1px solid var(--border); border-radius: 16px;
            font-family: inherit; font-size: 15px; transition: all 0.3s; background: var(--card-bg); color: #fff;
        }
        .search-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); }
        
        .btn-search {
            background: #fff; color: #000; border: none; padding: 0 30px; border-radius: 16px;
            cursor: pointer; font-weight: 600; transition: all 0.3s; display: flex; align-items: center; gap: 8px;
        }
        .btn-search:hover { background: var(--primary); transform: translateY(-2px); }
        
        .card-actions {
            position: absolute; bottom: 15px; right: 15px; display: flex; gap: 8px;
            opacity: 0; transform: translateY(10px); transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); z-index: 10;
        }
        .card:hover .card-actions { opacity: 1; transform: translateY(0); }
        .btn-action {
            background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(12px); color: #fff; width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; text-decoration: none;
            font-size: 16px; border: 1px solid var(--border); transition: all 0.3s;
        }
        .btn-action.edit:hover { background: var(--primary); color: #000; border-color: var(--primary); transform: scale(1.1); }
        .btn-action.delete:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: scale(1.1); }

        .filters-wrapper {
            background: var(--card-bg); padding: 30px; border-radius: 24px; border: 1px solid var(--border); margin-bottom: 50px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .filter-group { display: flex; gap: 15px; margin-top: 20px; flex-wrap: wrap; }
        .filter-select {
            padding: 12px 20px; border-radius: 14px; border: 1px solid var(--border); background: #fff;
            font-size: 14px; font-family: inherit; color: var(--text-main); cursor: pointer; transition: all 0.3s; min-width: 160px;
        }
        .filter-select:focus { border-color: var(--primary); outline: none; background: var(--sky-light); }

        /* Suggestions */
        .suggestions-dropdown {
            position: absolute; top: 100%; left: 0; right: 0; background: var(--card-bg); border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5); z-index: 1000; margin-top: 10px; border: 1px solid var(--border);
            display: none; overflow: hidden;
        }

        /* BULK ACTIONS STICKY BAR */
        .bulk-bar {
            position: fixed; bottom: -100px; left: 50%; transform: translateX(-50%);
            width: 90%; max-width: 800px; background: rgba(16, 185, 129, 0.95);
            backdrop-filter: blur(15px); border-radius: 24px; padding: 20px 40px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 0 20px rgba(16, 185, 129, 0.3);
            z-index: 2000; transition: 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            color: #000;
        }
        .bulk-bar.active { bottom: 40px; }
        .bulk-info { font-weight: 800; font-size: 1.1rem; }
        .bulk-actions { display: flex; gap: 12px; }
        .btn-bulk {
            padding: 10px 20px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.1);
            background: rgba(255,255,255,0.2); font-weight: 700; font-size: 0.85rem;
            cursor: pointer; transition: 0.3s; color: #000;
        }
        .btn-bulk:hover { background: #000; color: #fff; }

        .select-overlay {
            position: absolute; top: 15px; left: 15px; z-index: 10;
            width: 24px; height: 24px; border-radius: 6px; border: 2px solid var(--border);
            background: rgba(0,0,0,0.4); cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: 0.3s;
        }
        .select-overlay.checked { background: var(--primary); border-color: var(--primary); }
        .select-overlay i { font-size: 0.8rem; color: #000; display: none; }
        .select-overlay.checked i { display: block; }
        .suggestion-item {
            padding: 15px 25px; cursor: pointer; display: flex; align-items: center; gap: 15px;
            transition: all 0.2s; border-bottom: 1px solid var(--border); color: var(--text-muted);
        }
        .suggestion-item:hover { background: rgba(255,255,255,0.05); color: var(--primary); }

        /* Unified Layout Overhaul */
        .inventory-wrapper { display: grid; grid-template-columns: 280px 1fr; gap: 40px; margin-top: 30px; }
        
        .sidebar { background: var(--card-bg); border-radius: 24px; padding: 30px; border: 1px solid var(--border); height: fit-content; position: sticky; top: 100px; }
        .sidebar h3 { font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--border); }
        
        .health-bar {
            background: linear-gradient(90deg, rgba(17, 24, 39, 0.8) 0%, rgba(11, 15, 25, 0.8) 100%);
            backdrop-filter: blur(20px);
            border-radius: 20px; padding: 30px 45px; margin-bottom: 40px; border: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.05); background: #fff;
        }
        .health-item { display: flex; align-items: center; gap: 20px; }
        .health-icon { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; border: 1px solid var(--border); }
        .health-text h4 { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin: 0; letter-spacing: 1px; font-weight: 700; }
        .health-text p { font-size: 1.4rem; font-weight: 800; color: var(--text-main); margin: 0; line-height: 1.2; }

        .view-toggle { display: flex; background: var(--glass); border-radius: 12px; padding: 5px; gap: 5px; border: 1px solid var(--border); }
        .toggle-btn { 
            padding: 8px 15px; border-radius: 8px; border: none; background: transparent; color: var(--text-muted); 
            cursor: pointer; transition: 0.3s; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;
        }
        .toggle-btn.active { background: var(--primary); color: #000; font-weight: 600; }

        /* Quick-Edit Styles */
        .editable-cell { cursor: pointer; position: relative; padding: 4px 8px; border-radius: 6px; transition: 0.2s; }
        .editable-cell:hover { background: rgba(255,255,255,0.05); }
        .editable-cell:hover::after { content: '\f303'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; right: -15px; font-size: 0.7rem; color: var(--primary); }
        
        .edit-input { 
            background: #fff !important; color: #000 !important; border: 2px solid var(--primary) !important;
            padding: 4px 8px !important; border-radius: 6px !important; width: 80px !important; font-size: 13px !important;
        }

        /* Power Table */
        .power-table-wrapper { background: var(--card-bg); backdrop-filter: blur(15px); border-radius: 24px; border: 1px solid var(--border); overflow: hidden; display: none; box-shadow: 0 20px 50px rgba(0,0,0,0.3); }
        .power-table { width: 100%; border-collapse: collapse; }
        .power-table th { background: rgba(0,0,0,0.3); padding: 20px 25px; text-align: left; font-size: 0.75rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--border); }
        .power-table td { padding: 18px 25px; color: var(--text-main); border-bottom: 1px solid var(--border); font-size: 0.9rem; font-weight: 500; }
        .power-table tr:hover { background: var(--sky-light); }
        .prod-mini-img { width: 48px; height: 48px; border-radius: 12px; object-fit: cover; border: 1px solid var(--border); }

        /* Bulk Selection */
        .bulk-select-wrapper { position: absolute; top: 15px; left: 15px; z-index: 15; }
        .bulk-check { width: 20px; height: 20px; accent-color: var(--primary); transform: scale(1.2); cursor: pointer; }
        
        .bulk-action-bar {
            position: fixed; bottom: -100px; left: 50%; transform: translateX(-50%);
            background: #fff; color: #000; padding: 18px 45px; border-radius: 100px;
            display: flex; align-items: center; gap: 40px; box-shadow: 0 40px 80px rgba(0,0,0,0.6);
            transition: 0.6s cubic-bezier(0.19, 1, 0.22, 1); z-index: 2000; border: 4px solid var(--primary);
        }
        .bulk-action-bar.active { bottom: 40px; }
        .bulk-count { font-weight: 700; font-size: 0.9rem; }
        .bulk-btn { 
            background: transparent; border: none; padding: 8px 15px; border-radius: 8px;
            font-weight: 600; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 8px;
        }
        .bulk-btn.delete { color: #ef4444; }
        .bulk-btn.delete:hover { background: rgba(239, 68, 68, 0.1); }
        .bulk-btn.status { color: var(--primary-dark); }
        .bulk-btn.status:hover { background: rgba(16, 185, 129, 0.1); }
    </style>

    <style>
        .back-nav {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 25px;
            transition: 0.3s;
            font-size: 0.9rem;
        }
        .back-nav:hover { color: var(--primary); transform: translateX(-5px); }
    </style>
</head>
<body>

<div class="container">
    <a href="javascript:history.back()" class="back-nav"><i class="fas fa-arrow-left"></i> Back</a>
    <div class="header">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 5px;">
                <img src="assets/shoe_logo_green.png" alt="WalkOn" style="height: 42px; width: auto;">
                <span style="font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; color: var(--text-main); letter-spacing: 0;">WALK<span style="color:var(--primary)">ON</span></span>
            </div>
            <h1>My Listings</h1>
            <p>Manage and track your product inventory</p>
        </div>
        <div style="display: flex; gap: 15px; align-items: center;">
            <div class="view-toggle">
                <button class="toggle-btn active" onclick="switchView('grid', this)"><i class="fas fa-th-large"></i> Grid</button>
                <button class="toggle-btn" onclick="switchView('table', this)"><i class="fas fa-list"></i> Table</button>
            </div>
            <a href="export_listings.php?<?php echo http_build_query($_GET); ?>" class="btn-cancel" style="border-color: rgba(23, 162, 184, 0.3); color: #17a2b8;">
                <i class="fas fa-file-export"></i> Export
            </a>
            <a href="add_listing.php" class="btn-add"><i class="fas fa-plus"></i> Add Product</a>
        </div>
    </div>

    <!-- Stock Health Summary -->
    <div class="health-bar">
        <div class="health-item" onclick="window.location.href='my_listings.php'" style="cursor: pointer;">
            <div class="health-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--primary);">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="health-text">
                <h4>Total Inventory Value</h4>
                <p>₹<?= number_format($health['total_value'] ?? 0, 0) ?></p>
            </div>
        </div>
        <div class="health-item" onclick="window.location.href='my_listings.php?stock_filter=low'" style="cursor: pointer;">
            <div class="health-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="health-text">
                <h4>Low Stock Alerts</h4>
                <p><?= $health['low_stock_count'] ?> SKUs</p>
            </div>
        </div>
        <div class="health-item">
            <div class="health-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="health-text">
                <h4>Active SKUs</h4>
                <p><?= $health['total_skus'] ?></p>
            </div>
        </div>
    </div>

    <div class="inventory-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <h3>Inventory Filter</h3>
            <form action="my_listings.php" method="GET">
                <div class="form-group">
                    <label>Search SKUs</label>
                    <input type="text" name="search" id="searchInput" class="search-input" placeholder="Name or SKU..." value="<?php echo htmlspecialchars($search); ?>" style="width: 100%; font-size: 0.8rem; padding: 10px;">
                    <div id="suggestions" class="suggestions-dropdown"></div>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category" class="filter-select" style="width: 100%;" onchange="this.form.subcategory.value=''; this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach($category_list as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $category_filter == $cat ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Sub Category</label>
                    <select name="subcategory" class="filter-select" style="width: 100%;" onchange="this.form.submit()" <?= !$category_filter ? 'disabled opacity:0.5' : '' ?>>
                        <option value="">All Sub</option>
                        <?php foreach($subcategory_list as $sub): ?>
                            <option value="<?= htmlspecialchars($sub) ?>" <?= $subcategory_filter == $sub ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sub) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Channel</label>
                    <select name="channel" class="filter-select" style="width: 100%;" onchange="this.form.submit()">
                        <option value="">All Channels</option>
                        <?php foreach(['Amazon', 'Flipkart', 'Shopify', 'TikTok Shop'] as $ch): ?>
                            <option value="<?= $ch ?>" <?= $channel_filter == $ch ? 'selected' : '' ?>><?= $ch ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="filter-select" style="width: 100%;" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <?php foreach($status_list as $s): ?>
                            <option value="<?= $s ?>" <?= $status_filter == $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Brand</label>
                    <select name="brand_id" class="filter-select" style="width: 100%;" onchange="this.form.submit()">
                        <option value="">All Brands</option>
                        <?php foreach($brand_list as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $brand_filter == $b['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-search" style="width: 100%; justify-content: center; margin-top: 10px; background: var(--primary); color: #000;">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                <?php if($search || $status_filter || $category_filter || $channel_filter || $brand_filter): ?>
                    <a href="my_listings.php" style="display: block; text-align: center; color: var(--primary); font-size: 0.8rem; margin-top: 15px; font-weight: 600; text-decoration: none;">
                        <i class="fas fa-times-circle"></i> Clear All Filters
                    </a>
                <?php endif; ?>
            </form>
        </aside>

        <!-- Main Content Area -->
        <main class="main-inventory">
            <?php if (count($listings) > 0): ?>
                <!-- Grid View -->
                <div id="gridView" class="grid">
                    <?php foreach ($listings as $product): ?>
                        <?php 
                            $first_image = $product['primary_image'] ?? $product['fallback_image'] ?? 'https://via.placeholder.com/400x300?text=No+Listing+Image';
                            $channels = !empty($product['channels_str']) ? explode(',', $product['channels_str']) : [];
                        ?>
                        <div class="card">
                            <div class="bulk-select-wrapper">
                                <input type="checkbox" class="bulk-check" value="<?= $product['id'] ?>" onchange="toggleBulkBar()">
                            </div>
                            <div class="card-img-wrapper">
                                <div class="floating-price">₹<?php echo number_format($product['price'], 0); ?></div>
                                <?php if($product['brand_name']): ?>
                                    <div class="brand-badge"><?= htmlspecialchars($product['brand_name']) ?></div>
                                <?php endif; ?>
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                                    <img src="<?php echo htmlspecialchars($first_image); ?>" class="card-img-top" alt="Product">
                                </a>
                                
                                <div class="card-actions">
                                    <a href="edit_listing.php?id=<?php echo $product['id']; ?>" class="btn-action edit" title="Edit Listing">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="generateQR(<?= $product['id'] ?>)" class="btn-action" style="background: var(--primary); color: #000;" title="Generate Authenticity QR">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                    <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn-action delete" onclick="return confirm('Remove listing?')" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="card-body">
                                <h3 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                                <div class="card-sku"><?php echo htmlspecialchars($product['sku']); ?></div>
                                
                                <div class="card-footer">
                                    <span class="status-badge <?php echo $product['quantity'] < 10 ? 'low-stock' : ''; ?>">
                                        <?php echo htmlspecialchars($product['status']); ?>
                                    </span>
                                    <span class="stock-count">In Stock: <b><?= $product['quantity']; ?></b></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Table View (Power Mode) -->
                <div id="tableView" class="power-table-wrapper">
                    <table class="power-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Brand</th>
                                <th>Inventory</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listings as $product): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="bulk-check" value="<?= $product['id'] ?>" onchange="toggleBulkBar()">
                                </td>
                                <td style="display: flex; align-items: center; gap: 15px;">
                                    <img src="<?= htmlspecialchars($product['primary_image'] ?? $product['fallback_image']) ?>" class="prod-mini-img">
                                    <span><?= htmlspecialchars($product['product_name']) ?></span>
                                </td>
                                <td style="font-family: monospace; color: var(--text-muted);"><?= $product['sku'] ?></td>
                                <td>
                                    <div class="editable-cell" onclick="makeEditable(this, <?= $product['id'] ?>, 'price')">
                                        ₹<?= number_format($product['price'], 0) ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight:700; color:var(--text-muted); font-size:0.75rem;">
                                        <?= htmlspecialchars($product['brand_name'] ?: 'No Brand') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="editable-cell" onclick="makeEditable(this, <?= $product['id'] ?>, 'stock')">
                                        <?= $product['quantity'] ?> units
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge"><?= $product['status'] ?></span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <button onclick="generateQR(<?= $product['id'] ?>)" class="btn-action" style="width: 32px; height: 32px; font-size: 14px; background: var(--primary); color: #000;" title="Generate QR">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                        <a href="edit_listing.php?id=<?= $product['id'] ?>" class="btn-action edit" style="width: 32px; height: 32px; font-size: 14px;"><i class="fas fa-edit"></i></a>
                                        <a href="delete_product.php?id=<?= $product['id'] ?>" class="btn-action delete" style="width: 32px; height: 32px; font-size: 14px;"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open" style="font-size: 50px; margin-bottom: 20px; color: var(--text-muted);"></i>
                    <h3>No items found</h3>
                    <p>Try adjusting your search or filters.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Bulk Action Bar -->
    <div id="bulkBar" class="bulk-action-bar">
        <div class="bulk-count"><span id="selectedCount">0</span> items selected</div>
        <div style="display: flex; gap: 10px;">
            <button class="bulk-btn status" onclick="bulkUpdate('published')"><i class="fas fa-check-circle"></i> Mark Active</button>
            <button class="bulk-btn status" style="color: var(--text-muted);" onclick="bulkUpdate('draft')"><i class="fas fa-eye-slash"></i> Hide</button>
            <button class="bulk-btn" style="background: rgba(16, 185, 129, 0.1); color: var(--primary);" onclick="bulkAdjustPrice()"><i class="fas fa-tags"></i> Adjust Price</button>
            <button class="bulk-btn delete" onclick="bulkDelete()"><i class="fas fa-trash-alt"></i> Delete</button>
        </div>
        <button class="toggle-btn" onclick="clearSelection()" style="padding: 5px 10px; font-size: 0.7rem;">Cancel</button>
    </div>
    <!-- QR Result Modal -->
    <div id="qrModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 3000; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
        <div style="background: var(--card-bg); padding: 40px; border-radius: 32px; border: 1px solid var(--primary); max-width: 400px; width: 90%; text-align: center; position: relative; box-shadow: 0 0 50px rgba(16, 185, 129, 0.2);">
            <button onclick="closeQRModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: #fff; font-size: 1.5rem; cursor: pointer;">&times;</button>
            <h3 style="margin-bottom: 20px; font-size: 1.5rem;">Product Authenticity QR</h3>
            <div id="qrImageContainer" style="background: #fff; padding: 15px; border-radius: 20px; display: inline-block; margin-bottom: 20px;">
                <img id="qrCodeImg" src="" alt="QR Code" style="width: 250px; height: 250px;">
            </div>
            <div style="margin-bottom: 15px;">
                <p style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Serial Number</p>
                <div id="qrSerial" style="font-family: monospace; font-size: 1.1rem; color: var(--primary); font-weight: 700;"></div>
            </div>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 25px;">This QR code can be printed and attached to the product for customer verification.</p>
            <button onclick="window.print()" class="btn-add" style="width: 100%; justify-content: center;"><i class="fas fa-print"></i> Print QR Code</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('suggestions');
    let currentFocus = -1;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        if (query.length < 1) {
            suggestionsBox.style.display = 'none';
            return;
        }

        fetch(`get_suggestions.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    suggestionsBox.innerHTML = '';
                    data.forEach((item, index) => {
                        const div = document.createElement('div');
                        div.className = 'suggestion-item';
                        div.innerHTML = `<i class="fas fa-search"></i> ${item}`;
                        div.addEventListener('click', function() {
                            searchInput.value = item;
                            suggestionsBox.style.display = 'none';
                            searchInput.form.submit();
                        });
                        suggestionsBox.appendChild(div);
                    });
                    suggestionsBox.style.display = 'block';
                } else {
                    suggestionsBox.style.display = 'none';
                }
            });
    });

    searchInput.addEventListener('keydown', function(e) {
        const items = suggestionsBox.getElementsByClassName('suggestion-item');
        if (e.keyCode == 40) {
            currentFocus++;
            addActive(items);
        } else if (e.keyCode == 38) {
            currentFocus--;
            addActive(items);
        } else if (e.keyCode == 13) {
            if (currentFocus > -1) {
                if (items[currentFocus]) items[currentFocus].click();
                e.preventDefault();
            }
        }
    });

    function addActive(items) {
        if (!items) return false;
        removeActive(items);
        if (currentFocus >= items.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (items.length - 1);
        items[currentFocus].classList.add('active');
        items[currentFocus].scrollIntoView({ block: 'nearest' });
    }

    function removeActive(items) {
        for (let i = 0; i < items.length; i++) {
            items[i].classList.remove('active');
        }
    }

    });
});

function switchView(view, btn) {
    document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    if(view === 'grid') {
        document.getElementById('gridView').style.display = 'grid';
        document.getElementById('tableView').style.display = 'none';
    } else {
        document.getElementById('gridView').style.display = 'none';
        document.getElementById('tableView').style.display = 'block';
    }
}

function makeEditable(el, id, field) {
    if (el.querySelector('input')) return;
    
    const originalValue = el.innerText.replace('₹', '').replace(' units', '').trim();
    const input = document.createElement('input');
    input.type = field === 'status' ? 'text' : 'number';
    input.value = originalValue;
    input.className = 'edit-input';
    
    el.innerHTML = '';
    el.appendChild(input);
    input.focus();
    
    input.onblur = () => saveEdit(input, id, field, el, originalValue);
    input.onkeydown = (e) => {
        if(e.key === 'Enter') input.blur();
        if(e.key === 'Escape') {
            el.innerText = field === 'price' ? `₹${originalValue}` : `${originalValue} units`;
        }
    };
}

function saveEdit(input, id, field, container, original) {
    const newValue = input.value;
    if (newValue === original) {
        container.innerText = field === 'price' ? `₹${original}` : `${original} units`;
        return;
    }

    container.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('api/update_inventory.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: id, field: field, value: newValue })
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            container.innerText = field === 'price' ? `₹${newValue}` : `${newValue} units`;
            // Optional: trigger a success animation/toast
        } else {
            alert('Error: ' + data.message);
            container.innerText = field === 'price' ? `₹${original}` : `${original} units`;
        }
    });
}

function toggleBulkBar() {
    const checks = document.querySelectorAll('.bulk-check:checked');
    const bar = document.getElementById('bulkBar');
    const count = document.getElementById('selectedCount');
    
    if(checks.length > 0) {
        bar.classList.add('active');
        count.innerText = checks.length;
    } else {
        bar.classList.remove('active');
    }
}

function toggleSelectAll(master) {
    document.querySelectorAll('.bulk-check').forEach(c => {
        c.checked = master.checked;
    });
    toggleBulkBar();
}

function clearSelection() {
    document.querySelectorAll('.bulk-check').forEach(c => c.checked = false);
    const selectAll = document.getElementById('selectAll');
    if(selectAll) selectAll.checked = false;
    toggleBulkBar();
}

function generateQR(id) {
    // Show loading state
    const btn = event.currentTarget;
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;

    fetch('api/generate_qr.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: id })
    })
    .then(r => r.json())
    .then(data => {
        btn.innerHTML = originalContent;
        btn.disabled = false;
        
        if(data.success) {
            document.getElementById('qrCodeImg').src = data.qr_code_url;
            document.getElementById('qrSerial').innerText = data.serial_number;
            document.getElementById('qrModal').style.display = 'flex';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        btn.innerHTML = originalContent;
        btn.disabled = false;
        alert('Request failed');
    });
}

function closeQRModal() {
    document.getElementById('qrModal').style.display = 'none';
}

function bulkUpdate(status) {
    const ids = Array.from(document.querySelectorAll('.bulk-check:checked')).map(c => c.value);
    if(ids.length === 0) return;
    
    if(!confirm(`Apply "${status}" to ${ids.length} products?`)) return;

    fetch('api/bulk_update_products.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ids: ids, action: 'status', value: status })
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function bulkAdjustPrice() {
    const ids = Array.from(document.querySelectorAll('.bulk-check:checked')).map(c => c.value);
    const adjustment = prompt("Price Adjustment? (e.g. +10%, -500, or 2500 for fixed):");
    if(!adjustment) return;

    fetch('api/bulk_update_products.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ids: ids, action: 'price', value: adjustment })
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function bulkDelete() {
    const ids = Array.from(document.querySelectorAll('.bulk-check:checked')).map(c => c.value);
    if(ids.length === 0) return;
    
    if(confirm(`DANGER: Permanently delete ${ids.length} listings?`)) {
        fetch('api/bulk_update_products.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: ids, action: 'delete' })
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) location.reload();
            else alert(data.message);
        });
    }
}
</script>
</body>
</html>
