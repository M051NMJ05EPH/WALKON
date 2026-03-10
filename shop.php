<?php
session_start();
include 'config.php';
include 'includes/auth_check.php';

// Detect if user is a reseller
$current_seller_id = $_SESSION['seller_id'] ?? null;
if (!$current_seller_id && isset($_SESSION['user_id']) && isSeller()) {
    $stmt_sel = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
    $stmt_sel->execute([$_SESSION['email']]);
    $current_seller_id = $stmt_sel->fetchColumn() ?: -1;
    if ($current_seller_id != -1) $_SESSION['seller_id'] = $current_seller_id;
}

// Fetch names of products already stocked by this seller
$stocked_names = [];
if ($current_seller_id > 0) {
    $stmt_s = $pdo->prepare("SELECT name FROM product_base WHERE seller_id = ?");
    $stmt_s->execute([$current_seller_id]);
    $stocked_names = $stmt_s->fetchAll(PDO::FETCH_COLUMN);
}

// 1. Get Filters
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$sub_category_id = isset($_GET['subcategory']) ? intval($_GET['subcategory']) : 0;
$brand_id = isset($_GET['brand']) ? intval($_GET['brand']) : 0;
$outer_material = isset($_GET['material']) ? $_GET['material'] : '';
$gender = isset($_GET['gender']) ? $_GET['gender'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// 2. Fetch Filter Data
try {
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch subcategories
    if ($category_id > 0) {
        $sub_stmt = $pdo->prepare("SELECT id, name FROM sub_categories WHERE category_id = ? ORDER BY name");
        $sub_stmt->execute([$category_id]);
        $sub_categories = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $sub_categories = [];
    }

    $brands = $pdo->query("SELECT id, name FROM brands ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $materials = $pdo->query("SELECT DISTINCT outer_material FROM product_specs WHERE outer_material IS NOT NULL AND outer_material != ''")->fetchAll(PDO::FETCH_COLUMN);
    
    // Fetch unique sizes for filter
    $all_sizes = $pdo->query("SELECT DISTINCT size_value FROM product_sizes ORDER BY CAST(size_value AS UNSIGNED), size_value")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = $sub_categories = $brands = $materials = $all_sizes = [];
}

// 3. Build Product Query
$verified_only = isset($_GET['verified_only']) && $_GET['verified_only'] == '1';
$selected_size = $_GET['size'] ?? '';

$sql = "SELECT DISTINCT pb.id, pb.name, pp.price, pp.max_price, c.name as category_name, 
        b.name as brand_name, b.is_verified as brand_verified,
        COALESCE(s.business_name, s.name) as store_name, s.is_verified as seller_verified,
        (SELECT AVG(rating) FROM product_reviews pr WHERE pr.product_id = pb.id) as avg_rating,
        (SELECT COUNT(*) FROM product_reviews pr WHERE pr.product_id = pb.id) as review_count,
        (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as primary_image,
        (SELECT url FROM product_media pm WHERE pm.product_id = pb.id LIMIT 1) as fallback_image
        FROM product_base pb
        LEFT JOIN product_prices pp ON pb.id = pp.product_id
        LEFT JOIN categories c ON pb.category_id = c.id
        LEFT JOIN product_specs spec ON pb.id = spec.product_id
        LEFT JOIN brands b ON spec.brand_id = b.id
        LEFT JOIN sellers s ON pb.seller_id = s.id";

if (!empty($selected_size)) {
    $sql .= " LEFT JOIN product_sizes ps ON pb.id = ps.product_id";
}

$sql .= " WHERE pb.status = 'published'";

$params = [];

if ($category_id > 0) {
    $sql .= " AND pb.category_id = ?";
    $params[] = $category_id;
}
if ($sub_category_id > 0) {
    $sql .= " AND pb.sub_category_id = ?";
    $params[] = $sub_category_id;
}
if ($brand_id > 0) {
    $sql .= " AND spec.brand_id = ?";
    $params[] = $brand_id;
}
if (!empty($outer_material)) {
    $sql .= " AND spec.outer_material = ?";
    $params[] = $outer_material;
}
if (!empty($gender)) {
    $sql .= " AND spec.gender = ?";
    $params[] = $gender;
}
if (!empty($selected_size)) {
    $sql .= " AND ps.size_value = ?";
    $params[] = $selected_size;
}
if ($verified_only) {
    $sql .= " AND (b.is_verified = 1 OR s.is_verified = 1)";
}
if (!empty($search_query)) {
    $sql .= " AND (pb.name LIKE ?)";
    $params[] = "%$search_query%";
}
// Add Seller Filter
$seller_id = isset($_GET['seller']) ? intval($_GET['seller']) : 0;
if ($seller_id > 0) {
    $sql .= " AND pb.seller_id = ?";
    $params[] = $seller_id;
}

// Sorting
switch ($sort) {
    case 'price_low': $sql .= " ORDER BY pp.price ASC"; break;
    case 'price_high': $sql .= " ORDER BY pp.price DESC"; break;
    default: $sql .= " ORDER BY pb.created_at DESC"; break;
}

// Pagination Logic
$limit = 12;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Count Total Products for Pagination
$count_sql = "SELECT COUNT(DISTINCT pb.id) FROM product_base pb " . strstr($sql, "LEFT JOIN"); 
// Extract the FROM/JOIN/WHERE part for counting
$count_sql = preg_replace('/ORDER BY.*/', '', $count_sql);

try {
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_products = $count_stmt->fetchColumn();
    $total_pages = ceil($total_products / $limit);

    $sql .= " LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
    $total_pages = 0;
}

// Get wishlist IDs for the user
$wishlist_ids = [];
if (isset($_SESSION['user_id'])) {
    $stmt_wish = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $stmt_wish->execute([$_SESSION['user_id']]);
    $wishlist_ids = $stmt_wish->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - WALKON Premium Marketplace</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;       /* Green Accent */
            --secondary: #2563eb;     /* Royal Blue Accent */
            --light-bg: #f0f9ff;
            --light-card: #ffffff;
            --light-border: #bae6fd;
            --text-main: #0f172a;     /* Deep Navy for better readability */
            --text-green: #10b981;    /* Green Text */
            --text-muted: #64748b;
            --border: rgba(37, 99, 235, 0.1);
            --glass: rgba(255, 255, 255, 0.7);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Outfit', sans-serif; 
            background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 50%, #e0f2fe 100%);
            background-attachment: fixed;
            color: var(--text-main);
            line-height: 1.6;
        }

        .container { max-width: 1400px; margin: 0 auto; padding: 0 2rem; }

        /* Navbar (Sleek) matching Index.php */
        .navbar {
            background: linear-gradient(135deg, #10b981 0%, #2563eb 100%);
            backdrop-filter: blur(20px);
            position: fixed; width: 100%; top: 0; z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            height: 80px;
        }
        .nav-container {
          max-width: 1400px; margin: 0 auto; padding: 0 2rem; height: 100%;
          display: flex; justify-content: space-between; align-items: center;
        }
        
        .nav-links { display: flex; align-items: center; gap: 2.5rem; }
        .nav-links a { 
          text-decoration: none; font-weight: 500; font-size: 0.9rem;
          color: #ffffff; letter-spacing: 0.3px;
          transition: all 0.3s ease;
          position: relative;
        }
        .nav-links a:not(.btn)::after {
          content: ''; position: absolute; width: 0; height: 1px;
          bottom: -4px; left: 0; background: var(--primary);
          transition: width 0.3s ease;
        }
        .nav-links a:not(.btn):hover::after { width: 100%; }
        .nav-links a:hover { color: rgba(255,255,255,0.8); transform: translateY(-1px); }

        /* Buttons matching Index.php */
        .btn {
          padding: 1rem 2.5rem; border-radius: 50px; font-weight: 600;
          text-decoration: none; transition: all 0.3s; font-size: 0.95rem;
          letter-spacing: 0.5px; display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-primary { 
          background: var(--primary); color: #000; border: none;
          box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
        }
        .btn-primary:hover { 
          background: #34d399; transform: translateY(-3px);
          box-shadow: 0 10px 30px rgba(16, 185, 129, 0.5);
        }

        .shop-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 4rem;
            margin-top: 140px; /* Increased to clear the 80px fixed navbar + extra space */
            align-items: start;
        }

        /* Sidebar Filters Refined */
        .sidebar {
            position: sticky; top: 100px;
            background: #000000;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 2.5rem 1.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .sidebar-section { margin-bottom: 2.5rem; }
        .sidebar-section h3 { 
            font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px;
            color: rgba(255,255,255,0.4); margin-bottom: 1.5rem; font-weight: 800;
            padding-left: 1rem;
        }
        .filter-list { list-style: none; padding: 0; }
        .filter-item { margin-bottom: 4px; }
        .filter-link { 
            text-decoration: none; color: #94a3b8; font-size: 0.95rem;
            display: flex; align-items: center; gap: 12px;
            padding: 0.8rem 1rem; border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .filter-link::before {
            content: ''; width: 6px; height: 6px; background: var(--secondary);
            border-radius: 50%; opacity: 0; transform: scale(0);
            transition: 0.3s;
        }
        .filter-link:hover { 
            background: rgba(255,255,255,0.05); color: #ffffff;
            padding-left: 1.25rem;
        }
        .filter-link:hover::before {
            opacity: 1; transform: scale(1);
        }
        .filter-link.active { 
            background: rgba(37, 99, 235, 0.1); color: var(--secondary);
            font-weight: 700; border: 1px solid rgba(37, 99, 235, 0.2);
        }
        .filter-link.active::before {
            opacity: 1; transform: scale(1.5); background: var(--secondary);
            box-shadow: 0 0 15px var(--secondary);
        }

        /* Scrollable Filter Lists */
        .filter-scroll-container {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 10px;
        }
        .filter-scroll-container::-webkit-scrollbar {
            width: 4px;
        }
        .filter-scroll-container::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
        }
        .filter-scroll-container::-webkit-scrollbar-thumb {
            background: rgba(37, 99, 235, 0.3);
            border-radius: 10px;
        }
        .filter-scroll-container::-webkit-scrollbar-thumb:hover {
            background: var(--secondary);
        }

        /* Search Bar */
        .search-box {
            position: relative; margin-bottom: 2rem;
        }
        .search-box input {
            width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px; color: #ffffff; outline: none; transition: 0.3s;
        }
        .search-box input:focus { border-color: var(--primary); box-shadow: 0 0 15px rgba(16, 185, 129, 0.1); }
        .search-box i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; }

        /* Clear Filters */
        .clear-btn {
            display: block; width: 100%; padding: 0.75rem; text-align: center;
            background: #fee2e2; color: #ef4444;
            border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 0.9rem;
            margin-top: 1rem; transition: 0.3s;
        }
        .clear-btn:hover { background: #fecaca; }

        /* Main Content */
        .results-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 2rem;
        }
        .results-header h1 { font-size: 2rem; font-weight: 800; color: var(--text-main); }
        
        .sort-select {
            background: #ffffff; border: 1px solid var(--light-border);
            color: var(--text-main); padding: 0.5rem 1rem; border-radius: 10px; outline: none;
        }

        .product-grid {
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); 
            gap: 3rem;
        }
        .product-card {
            background: #ffffff; border: 1px solid var(--light-border);
            border-radius: 24px; padding: 1.25rem; text-decoration: none; color: var(--text-main);
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.03);
        }
        .product-card:hover { 
            border-color: var(--secondary); transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.1);
        }
        .img-wrap {
            height: 320px; background: radial-gradient(circle at 30% 30%, #eff6ff, #f8fafc); border-radius: 16px;
            margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        .img-wrap img { width: 100%; height: 100%; object-fit: contain; }
        
        .brand { font-size: 0.75rem; color: var(--text-green); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.25rem; }
        .name { font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-main); height: 1.4em; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
        .price-box { display: flex; align-items: center; gap: 10px; }
        .price { font-size: 1.25rem; font-weight: 800; color: var(--text-green); }
        .old-price { text-decoration: line-through; color: var(--text-muted); font-size: 0.9rem; }

        .no-results {
            text-align: center; padding: 120px 20px; grid-column: 1 / -1;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 30px;
            border: 2px dashed var(--border);
        }
        .no-results i { font-size: 5rem; color: var(--secondary); margin-bottom: 2rem; opacity: 0.2; }
        .no-results h3 { font-size: 1.8rem; color: #1e293b; margin-bottom: 1rem; }

        /* Stocking Action */
        .stock-action-btn {
            background: rgba(16, 185, 129, 0.1); color: var(--primary);
            border: 1px solid var(--primary); padding: 6px 14px; border-radius: 50px;
            font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: 0.3s;
            display: inline-flex; align-items: center; gap: 6px; position: absolute; bottom: 1.25rem; right: 1.25rem;
        }
        .stock-action-btn:hover { background: var(--primary); color: #000; }
        .stocked-badge {
            background: rgba(255, 255, 255, 0.05); color: #94a3b8;
            border: 1px solid rgba(255, 255, 255, 0.1); padding: 6px 14px; border-radius: 50px;
            font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;
            position: absolute; bottom: 1.25rem; right: 1.25rem;
        }


        /* Discount Ribbon */
        .discount-badge {
            position: absolute; top: 20px; left: 20px;
            background: #ef4444; color: white; padding: 4px 12px;
            border-radius: 50px; font-size: 0.75rem; font-weight: 800; z-index: 10;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
        }

        /* Verified Badge */
        .verified-badge {
            display: inline-flex; align-items: center; gap: 4px;
            background: rgba(16, 185, 129, 0.1); color: var(--primary);
            padding: 2px 8px; border-radius: 4px; font-size: 0.65rem; font-weight: 800;
            margin-left: 6px; border: 1px solid var(--primary);
            text-transform: uppercase;
        }

        /* Compare Checkbox */
        .compare-checkbox {
            position: absolute; bottom: 1.25rem; left: 1.25rem;
            display: flex; align-items: center; gap: 8px; font-size: 0.8rem;
            color: var(--text-muted); cursor: pointer; z-index: 10;
        }
        .compare-checkbox input { accent-color: var(--primary); cursor: pointer; }

        /* Quick View Button */
        .quick-view-btn {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) scale(0.9);
            background: var(--secondary); color: #fff; padding: 12px 24px; border-radius: 50px;
            font-weight: 700; opacity: 0; transition: 0.3s; z-index: 20; border: none; cursor: pointer;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.3);
        }
        .product-card:hover .quick-view-btn { opacity: 1; transform: translate(-50%, -50%) scale(1); }

        /* Seller Info */
        .seller-preview {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--light-border);
            font-size: 0.8rem;
        }
        .rating-stars { color: #f97316; font-size: 0.75rem; }

        /* Sticky Compare Bar */
        #compareBar {
            position: fixed; bottom: -100px; left: 50%; transform: translateX(-50%);
            width: 90%; max-width: 800px; background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px); border: 1px solid var(--light-border);
            border-bottom: none; border-radius: 20px 20px 0 0;
            padding: 1.5rem 2rem; z-index: 2000; transition: 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 -20px 50px rgba(37, 99, 235, 0.1);
        }
        #compareBar.active { bottom: 0; }
        .compare-items { display: flex; gap: 15px; }
        .compare-thumb { width: 50px; height: 50px; border-radius: 10px; background: #ffffff; border: 1px solid var(--light-border); overflow: hidden; }
        .compare-thumb img { width: 100%; height: 100%; object-fit: cover; }

        /* Verified Toggle */
        .switch { position: relative; display: inline-block; width: 44px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #334155; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--primary); }
        input:checked + .slider:before { transform: translateX(20px); }

        .size-filter-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-top: 1rem; }
        .size-btn { 
            padding: 8px; background: rgba(255,255,255,0.05); border: 1px solid var(--border);
            border-radius: 8px; font-size: 0.8rem; color: var(--text-muted); text-decoration: none;
            text-align: center; transition: 0.3s;
        }
        .size-btn:hover, .size-btn.active { background: var(--primary); color: #000; border-color: var(--primary); font-weight: 700; }
        .btn-alert:hover { background: var(--gray-900); color: white; border-style: solid; }

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

        @media (max-width: 1024px) {
            .shop-layout { grid-template-columns: 1fr; }
            .sidebar { position: static; margin-bottom: 2rem; }
        }

        /* PREMIUM FOOTER STYLES */
        :root {
            --footer-green: #10b981;
            --footer-bg: #05070a;
            --footer-border: rgba(255,255,255,0.05);
        }
        /* Footer Refined */
        footer {
          background: #05070a !important; border-top: 1px solid var(--footer-border) !important;
          padding: 80px 0 40px !important; color: #ffffff !important;
          margin-top: 100px;
        }
        .footer-container {
            max-width: 1400px; margin: 0 auto; padding: 0 2rem;
            display: grid; grid-template-columns: 1.2fr 2fr; gap: 4rem;
        }
        
        /* Footer Card */
        .footer-card {
            background: #0a0e17; /* Deep black card background */
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 24px; padding: 3rem;
            display: flex; flex-direction: column; gap: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .footer-logo {
            display: flex; align-items: center; gap: 10px; text-decoration: none;
        }
        .brand-text {
            font-family: 'Outfit', sans-serif; font-size: 24px; font-weight: 700; line-height: 1;
        }
        .footer-desc {
            color: rgba(255,255,255,0.8); font-size: 0.95rem; line-height: 1.6; margin-bottom: 0.5rem;
        }
        
        .contact-info { display: flex; flex-direction: column; gap: 0.8rem; }
        .contact-item {
            display: flex; align-items: center; gap: 10px;
            color: #ffffff; font-size: 0.9rem;
        }
        .contact-item i { color: var(--text-green); width: 20px; }
        
        .social-links {
            display: flex; gap: 1rem; margin-top: 1rem;
        }
        .social-btn {
            width: 40px; height: 40px; border-radius: 10px;
            background: rgba(255,255,255,0.05);
            display: flex; align-items: center; justify-content: center;
            color: #ffffff; text-decoration: none; transition: 0.3s;
        }
        .social-btn:hover {
            background: var(--footer-green); color: #000; transform: translateY(-3px);
        }
        
        /* Footer Grid */
        .footer-nav-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;
        }
        
        .footer-col h4 {
            color: var(--footer-green); font-size: 0.85rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.5rem;
        }
        
        .footer-links { list-style: none; padding: 0; margin: 0; }
        .footer-links li { margin-bottom: 1rem; }
        .footer-links a {
            color: #ffffff; text-decoration: none; font-size: 0.95rem;
            transition: 0.3s;
        }
        .footer-links a:hover { color: var(--footer-green); padding-left: 5px; }

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

        @media (max-width: 1024px) {
            .footer-container { grid-template-columns: 1fr; }
            .footer-card { max-width: 500px; }
        }
        @media (max-width: 768px) {
            .footer-nav-grid { grid-template-columns: 1fr 1fr; }
        }
        
        @media (max-width: 900px) {
          .footer-grid { grid-template-columns: 1fr 1fr; gap: 3rem; }
        }
        @media (max-width: 600px) {
          .footer-grid { grid-template-columns: 1fr; }
          .footer-bottom { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
            <img src="assets/shoe_logo_green.png" alt="WalkOn Logo" style="height: 60px; width: auto; filter: drop-shadow(0 0 10px rgba(16, 185, 129, 0.2));">
            <div style="font-family: 'Outfit', sans-serif; font-size: 36px; font-weight: 800; line-height: 1; letter-spacing: -0.5px; text-transform: uppercase;">
                <span style="color: #fff;">Walk</span><span style="color: #10b981;">on</span>
            </div>
        </a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="shop.php" style="color: var(--primary);">Shop</a>
            <a href="wishlist.php">Wishlist</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
            <?php endif; ?>
            <div style="display: flex; align-items: center; gap: 1.5rem; margin-left: 1rem;">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" style="text-decoration: none; color: #fff; font-weight: 600; font-size: 0.9rem;">Logout</a>
                <?php else: ?>
                    <a href="login.php" style="text-decoration: none; color: #fff; font-weight: 600; font-size: 0.9rem;">Login</a>
                <?php endif; ?>
                <a href="start_selling.php" class="btn btn-primary" style="padding: 0.8rem 1.8rem; border-radius: 50px; font-size: 0.9rem; gap: 8px;">
                    Start Selling <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i>
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
    <div class="shop-layout">
        <aside class="sidebar">
            <div class="search-box">
                <form action="shop.php" method="GET">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search_query) ?>">
                </form>
            </div>

            <div class="sidebar-section">
                <h3>Categories</h3>
                <ul class="filter-list">
                    <li class="filter-item">
                        <a href="shop.php" class="filter-link <?= $category_id == 0 ? 'active' : '' ?>">All Collections</a>
                    </li>
                    <?php foreach($categories as $cat): ?>
                        <li class="filter-item">
                            <a href="shop.php?category=<?= $cat['id'] ?>&brand=<?= $brand_id ?>&gender=<?= urlencode($gender) ?>&material=<?= urlencode($outer_material) ?>" class="filter-link <?= $category_id == $cat['id'] ? 'active' : '' ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php if (!empty($sub_categories)): ?>
            <div class="sidebar-section">
                <h3>Subcategories</h3>
                <ul class="filter-list">
                    <li class="filter-item">
                        <a href="shop.php?category=<?= $category_id ?>&brand=<?= $brand_id ?>&gender=<?= urlencode($gender) ?>&material=<?= urlencode($outer_material) ?>" class="filter-link <?= $sub_category_id == 0 ? 'active' : '' ?>">All Subcategories</a>
                    </li>
                    <?php foreach($sub_categories as $sub): ?>
                        <li class="filter-item">
                            <a href="shop.php?category=<?= $category_id ?>&subcategory=<?= $sub['id'] ?>&brand=<?= $brand_id ?>&gender=<?= urlencode($gender) ?>&material=<?= urlencode($outer_material) ?>" class="filter-link <?= $sub_category_id == $sub['id'] ? 'active' : '' ?>">
                                <?= htmlspecialchars($sub['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="sidebar-section">
                <h3>Brands</h3>
                <div class="filter-scroll-container">
                    <ul class="filter-list">
                        <li class="filter-item">
                            <a href="shop.php?category=<?= $category_id ?>&subcategory=<?= $sub_category_id ?>&gender=<?= urlencode($gender) ?>&material=<?= urlencode($outer_material) ?>" class="filter-link <?= $brand_id == 0 ? 'active' : '' ?>">
                                All Brands
                            </a>
                        </li>
                        <?php foreach($brands as $b): ?>
                            <li class="filter-item">
                                <a href="shop.php?brand=<?= $b['id'] ?>&category=<?= $category_id ?>&subcategory=<?= $sub_category_id ?>&gender=<?= urlencode($gender) ?>&material=<?= urlencode($outer_material) ?>" class="filter-link <?= $brand_id == $b['id'] ? 'active' : '' ?>">
                                    <?= htmlspecialchars($b['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="sidebar-section">
                <h3>Gender</h3>
                <ul class="filter-list">
                    <li class="filter-item">
                        <a href="shop.php?category=<?= $category_id ?>&subcategory=<?= $sub_category_id ?>&brand=<?= $brand_id ?>&material=<?= urlencode($outer_material) ?>" class="filter-link <?= empty($gender) ? 'active' : '' ?>">All Genders</a>
                    </li>
                    <?php 
                    $gender_options = ['Men', 'Women', 'Boys', 'Girls', 'Kids', 'Babies', 'Unisex'];
                    foreach($gender_options as $g): 
                    ?>
                        <li class="filter-item">
                            <a href="shop.php?gender=<?= urlencode($g) ?>&category=<?= $category_id ?>&subcategory=<?= $sub_category_id ?>&brand=<?= $brand_id ?>&material=<?= urlencode($outer_material) ?>" class="filter-link <?= $gender == $g ? 'active' : '' ?>">
                                <?= $g ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sidebar-section">
                <h3>Material</h3>
                <div class="filter-scroll-container">
                    <ul class="filter-list">
                        <li class="filter-item">
                            <a href="shop.php?category=<?= $category_id ?>&subcategory=<?= $sub_category_id ?>&brand=<?= $brand_id ?>&gender=<?= urlencode($gender) ?>" class="filter-link <?= empty($outer_material) ? 'active' : '' ?>">Any Material</a>
                        </li>
                        <?php foreach($materials as $m): ?>
                            <li class="filter-item">
                                <a href="shop.php?material=<?= urlencode($m) ?>&category=<?= $category_id ?>&subcategory=<?= $sub_category_id ?>&brand=<?= $brand_id ?>&gender=<?= urlencode($gender) ?>" class="filter-link <?= $outer_material == $m ? 'active' : '' ?>">
                                    <?= htmlspecialchars($m) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="sidebar-section">
                <h3>Filter by Size</h3>
                <div class="size-filter-grid">
                    <?php foreach($all_sizes as $s): ?>
                        <a href="shop.php?size=<?= urlencode($s) ?>&category=<?= $category_id ?>&subcategory=<?= $sub_category_id ?>&brand=<?= $brand_id ?>&gender=<?= urlencode($gender) ?>&material=<?= urlencode($outer_material) ?>" 
                           class="size-btn <?= $selected_size == $s ? 'active' : '' ?>">
                            <?= htmlspecialchars($s) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if($category_id || $sub_category_id || $brand_id || $outer_material || $gender || $search_query || $selected_size): ?>
                <a href="shop.php" class="clear-btn">Clear All Filters</a>
            <?php endif; ?>
        </aside>

        <main>
            <div class="results-header">
                <div>
                    <h1 style="color:var(--text-muted); font-size: 0.8rem; text-transform:uppercase; letter-spacing:1px; margin-bottom: 0.5rem;">Marketplace</h1>
                    <h2 style="font-size: 2.8rem; letter-spacing: -2px; margin: 0; color: #0f172a; font-weight: 800;">
                        <?php 
                        if(!empty($search_query)) echo 'Search: "' . htmlspecialchars($search_query) . '"';
                        elseif($brand_id > 0) {
                            $b_name = "Premium Brand";
                            foreach($brands as $br) if($br['id'] == $brand_id) $b_name = $br['name'];
                            echo htmlspecialchars($b_name);
                        }
                        elseif($category_id > 0) {
                            $c_name = "Collection";
                            foreach($categories as $ca) if($ca['id'] == $category_id) $c_name = $ca['name'];
                            echo htmlspecialchars($c_name);
                        }
                        else echo 'Global Marketplace';
                        ?>
                    </h2>
                    <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px;">
                        <span style="background: var(--secondary); color: #fff; padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 700;">
                            <?= count($products) ?> Products
                        </span>
                        <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">
                            Active filters currently applied
                        </span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 2rem;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Verified Only</span>
                        <label class="switch">
                            <input type="checkbox" onchange="toggleVerified(this)" <?= $verified_only ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <form action="shop.php" method="GET" id="sortForm">
                        <input type="hidden" name="category" value="<?= $category_id ?>">
                        <input type="hidden" name="subcategory" value="<?= $sub_category_id ?>">
                        <input type="hidden" name="brand" value="<?= $brand_id ?>">
                        <input type="hidden" name="gender" value="<?= htmlspecialchars($gender) ?>">
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search_query) ?>">
                        <input type="hidden" name="size" value="<?= htmlspecialchars($selected_size) ?>">
                        <input type="hidden" name="verified_only" id="verifiedInput" value="<?= $verified_only ? '1' : '' ?>">
                        <select name="sort" class="sort-select" onchange="document.getElementById('sortForm').submit()">
                            <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest First</option>
                            <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="product-grid">
                <?php if (empty($products)): ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>No matches found</h3>
                        <p style="color:var(--text-muted);">Try adjusting your filters or search query.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($products as $idx => $p): 
                        $img = $p['primary_image'] ?? $p['fallback_image'] ?? 'https://via.placeholder.com/400?text=No+Image';
                        $is_active = (isset($_GET['search']) && $_GET['search'] == $p['name']) || ($idx === 0 && empty($search_query));
                    ?>
                        <div class="product-card" id="card-<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>" data-img="<?= htmlspecialchars($img) ?>" style="<?= $is_active ? 'border-color: var(--primary); box-shadow: 0 0 20px rgba(16, 185, 129, 0.15);' : '' ?>; position: relative; padding-bottom: 5.5rem;">
                            <!-- Discount Badge -->
                            <?php if ($p['max_price'] > $p['price']): 
                                $disc = round((($p['max_price'] - $p['price']) / $p['max_price']) * 100);
                            ?>
                                <div class="discount-badge"><?= $disc ?>% OFF</div>
                            <?php endif; ?>

                            <!-- Compare Checkbox -->
                            <label class="compare-checkbox">
                                <input type="checkbox" onchange="handleCompare(this, <?= $p['id'] ?>)"> Compare
                            </label>

                            <!-- Quick View -->
                            <button class="quick-view-btn" onclick="openQuickView(<?= $p['id'] ?>)">QUICK VIEW</button>
                            
                            <!-- Wishlist Button -->
                            <?php $in_wish = in_array($p['id'], $wishlist_ids); ?>
                            <div class="wishlist-btn" onclick="toggleWishlist(event, <?= $p['id'] ?>)" style="position: absolute; top: 12px; right: 12px; z-index: 10; width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: <?= $in_wish ? '#ef4444' : '#fff' ?>; cursor: pointer; transition: 0.3s;">
                                <i class="<?= $in_wish ? 'fas' : 'far' ?> fa-heart"></i>
                            </div>
                            
                            <a href="product_detail.php?id=<?= $p['id'] ?>" style="text-decoration:none; color:inherit; display:block;">
                                <div class="img-wrap" style="background: #0a0f1d;">
                                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                                </div>
                                <!-- Brand Name + Verified Badge -->
                                <div style="display: flex; align-items: center; margin-bottom: 0.25rem;">
                                    <span style="font-size: 0.7rem; color: var(--primary); text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">
                                        <?= htmlspecialchars($p['brand_name'] ?? ($p['category_name'] ?? 'FOOTWEAR')) ?>
                                    </span>
                                    <?php if ($p['brand_verified']): ?>
                                        <span class="verified-badge"><i class="fas fa-check-circle"></i> Brand</span>
                                    <?php endif; ?>
                                </div>
                                <!-- Product Name -->
                                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--text-main);">
                                    <?= htmlspecialchars($p['name']) ?>
                                </h3>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="price-box">
                                        <span style="font-size: 1.2rem; font-weight: 800; color: var(--primary);">₹<?= number_format($p['price']) ?></span>
                                        <?php if ($p['max_price'] > $p['price']): ?>
                                            <span class="old-price">₹<?= number_format($p['max_price']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Seller Info & Rating -->
                                <div class="seller-preview">
                                    <div style="color: var(--text-muted); font-size: 0.75rem;">
                                        Sold by <span style="color: #fff; font-weight: 600;"><?= htmlspecialchars($p['store_name'] ?? 'WALKON') ?></span>
                                        <?php if ($p['seller_verified']): ?>
                                            <i class="fas fa-check-circle" style="color: var(--primary); font-size: 0.7rem; margin-left: 2px;"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="rating-stars">
                                        <?php 
                                        $rating = round($p['avg_rating'] ?? 0);
                                        for($i=1; $i<=5; $i++) echo '<i class="' . ($i <= $rating ? 'fas' : 'far') . ' fa-star"></i>';
                                        ?>
                                        <span style="color: var(--text-muted); font-size: 0.7rem; margin-left: 4px;">(<?= $p['review_count'] ?>)</span>
                                    </div>
                                </div>
                            </a>

                            <!-- Stock Action for Resellers -->
                            <?php if ($current_seller_id > 0): ?>
                                <?php if (in_array($p['name'], $stocked_names)): ?>
                                    <div class="stocked-badge">
                                        <i class="fas fa-check-circle"></i> Stocked
                                    </div>
                                <?php else: ?>
                                    <button class="stock-action-btn" onclick="stockProduct(<?= $p['id'] ?>)">
                                        <i class="fas fa-plus"></i> Stock This
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>


                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination & AI Navigation -->
            <?php if ($total_pages > 1): ?>
                <div style="display: flex; flex-direction: column; align-items: center; gap: 30px; margin-top: 50px;">
                    <!-- Traditional Pagination -->
                    <div style="display: flex; gap: 10px;">
                        <?php if ($page > 1): ?>
                            <a href="shop.php?page=<?= $page - 1 ?>&category=<?= $category_id ?>&subcategory=<?= $sub_category_id ?>&brand=<?= $brand_id ?>&gender=<?= urlencode($gender) ?>&search=<?= urlencode($search_query) ?>&verified_only=<?= $verified_only ? '1' : '' ?>&sort=<?= $sort ?>" 
                               style="padding: 10px 20px; border: 1px solid var(--light-border); border-radius: 12px; text-decoration: none; color: var(--text-main); font-weight: 700;">
                                <i class="fas fa-arrow-left"></i> Previous
                            </a>
                        <?php endif; ?>

                        <div style="display: flex; gap: 5px;">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="shop.php?page=<?= $i ?>&category=<?= $category_id ?>&subcategory=<?= $sub_category_id ?>&brand=<?= $brand_id ?>&gender=<?= urlencode($gender) ?>&search=<?= urlencode($search_query) ?>&verified_only=<?= $verified_only ? '1' : '' ?>&sort=<?= $sort ?>" 
                                   style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; border: 1px solid <?= $page == $i ? 'var(--primary)' : 'var(--light-border)' ?>; background: <?= $page == $i ? 'var(--primary)' : '#fff' ?>; color: <?= $page == $i ? '#000' : 'var(--text-main)' ?>; border-radius: 10px; text-decoration: none; font-weight: 700;">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>

                        <?php if ($page < $total_pages): ?>
                            <a href="shop.php?page=<?= $page + 1 ?>&category=<?= $category_id ?>&subcategory=<?= $sub_category_id ?>&brand=<?= $brand_id ?>&gender=<?= urlencode($gender) ?>&search=<?= urlencode($search_query) ?>&verified_only=<?= $verified_only ? '1' : '' ?>&sort=<?= $sort ?>" 
                               style="padding: 10px 20px; background: var(--primary); border: none; border-radius: 12px; text-decoration: none; color: #000; font-weight: 700;">
                                Next Page <i class="fas fa-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>



                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Sticky Compare Bar -->
<div id="compareBar">
    <div style="display: flex; align-items: center; gap: 20px;">
        <div style="font-weight: 800; color: var(--primary);">COMPARISON</div>
        <div class="compare-items" id="compareItems">
            <!-- Thumbnails inserted here -->
        </div>
    </div>
    <div style="display: flex; gap: 15px; align-items: center;">
        <div id="compareCount" style="font-size: 0.9rem; font-weight: 600;">0 items selected</div>
        <button onclick="startComparison()" class="btn btn-primary" style="padding: 0.6rem 1.5rem; font-size: 0.8rem;">Compare Now</button>
        <button onclick="clearCompare()" style="background: none; border: none; color: #ef4444; font-weight: 700; cursor: pointer; font-size: 0.8rem;">Clear</button>
    </div>
</div>

<!-- Quick View Modal (Simplified) -->
<div id="quickViewOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 5000; display: none; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
    <div style="background: var(--bg); border: 1px solid var(--border); width: 90%; max-width: 900px; border-radius: 30px; display: grid; grid-template-columns: 1fr 1fr; overflow: hidden; position: relative;">
        <button onclick="closeQuickView()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; z-index: 10;"><i class="fas fa-times"></i></button>
        <div id="qvImage" style="background: #0f172a; display: flex; align-items: center; justify-content: center; padding: 40px;">
            <img id="modalImg" src="" style="width: 100%; height: auto; object-fit: contain;">
        </div>
        <div style="padding: 40px; border-left: 1px solid var(--border);">
            <div id="qvBrand" style="color: var(--primary); font-weight: 800; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px;">BRAND</div>
            <h2 id="qvName" style="font-size: 2rem; margin-bottom: 20px;">Product Name</h2>
            <div id="qvPrice" style="font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-bottom: 30px;">₹0,000</div>
            <p id="qvDesc" style="color: var(--text-muted); margin-bottom: 40px;">Brief description loading...</p>
            <a id="qvLink" href="#" class="btn btn-primary" style="width: 100%;">View Details</a>
        </div>
    </div>
</div>

<!-- 360° Immersive Viewer Modal -->
<div id="immersive360Overlay" style="position: fixed; inset: 0; background: rgba(15,23,42,0.95); z-index: 7000; display: none; align-items: center; justify-content: center; backdrop-filter: blur(20px);">
    <div style="background: #f8fafc; width: 95%; max-width: 1100px; height: 85vh; border-radius: 40px; overflow: hidden; display: grid; grid-template-columns: 320px 1fr; border: 1px solid rgba(0,0,0,0.1); box-shadow: 0 40px 100px rgba(0,0,0,0.3);">
        <!-- Sidebar Controls -->
        <div style="background: #f0f4f8; padding: 40px; display: flex; flex-direction: column; gap: 30px; border-right: 1px solid rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; gap: 10px; background: #d1fae5; color: #059669; padding: 6px 14px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; width: fit-content;">
                <i class="fas fa-sync fa-spin"></i> 360° VIEW
            </div>
            <div>
                <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 10px;">Immersive<br>Product View</h2>
                <p style="font-size: 0.85rem; color: #64748b; line-height: 1.5;">Interact with the model by dragging or using the slider below.</p>
            </div>

            <div>
                <h4 style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; margin-bottom: 15px;">Quick Actions</h4>
                <div style="display: flex; gap: 10px;">
                    <button onclick="reset360()" style="flex: 1; padding: 15px; background: white; border: 1px solid rgba(0,0,0,0.05); border-radius: 15px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 5px;">
                        <i class="fas fa-undo" style="color: #64748b;"></i><span style="font-size: 0.7rem; font-weight: 700;">Reset</span>
                    </button>
                    <button id="autoSpinBtn" onclick="toggle360Auto()" style="flex: 1; padding: 15px; background: white; border: 1px solid rgba(0,0,0,0.05); border-radius: 15px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 5px;">
                        <i class="fas fa-play" style="color: #10b981;"></i><span style="font-size: 0.7rem; font-weight: 700;">Auto</span>
                    </button>
                    <button style="flex: 1; padding: 15px; background: white; border: 1px solid rgba(0,0,0,0.05); border-radius: 15px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 5px;">
                        <i class="fas fa-search-plus" style="color: #64748b;"></i><span style="font-size: 0.7rem; font-weight: 700;">Zoom</span>
                    </button>
                </div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h4 style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; font-weight: 800; letter-spacing: 1px;">Manual Rotation</h4>
                    <span id="angleValue" style="color: #10b981; font-weight: 800; font-size: 1.1rem;">0°</span>
                </div>
                <input type="range" min="-180" max="180" value="0" id="rotateSlider" style="width: 100%; height: 6px; accent-color: #10b981; cursor: pointer;">
                <div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 0.65rem; color: #cbd5e1; font-weight: 700;">
                    <span>ANGLE</span>
                </div>
            </div>

            <div style="background: #e0f2fe; border-radius: 20px; padding: 25px; border: 1px solid rgba(37,99,235,0.1);">
                <h4 style="font-size: 0.7rem; color: #0369a1; text-transform: uppercase; font-weight: 800; margin-bottom: 15px;">Tips</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px; font-size: 0.75rem; color: #0c4a6e; font-weight: 600;">
                    <li style="display: flex; gap: 10px;"><i class="fas fa-mouse"></i> Drag left/right to rotate freely</li>
                    <li style="display: flex; gap: 10px;"><i class="fas fa-sliders-h"></i> Use slider for precise control</li>
                    <li style="display: flex; gap: 10px;"><i class="fas fa-play-circle"></i> Hit Auto Spin for continuous view</li>
                </ul>
            </div>
        </div>

        <!-- Main Viewport -->
        <div style="background: url('https://www.toptal.com/designers/subtlepatterns/uploads/grid_noise.png'); position: relative; display: flex; align-items: center; justify-content: center; overflow: hidden;">
            <button onclick="document.getElementById('immersive360Overlay').style.display='none'" style="position: absolute; top: 30px; right: 30px; width: 45px; height: 45px; border-radius: 50%; background: white; border: 1px solid rgba(0,0,0,0.05); color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 20px rgba(0,0,0,0.05);"><i class="fas fa-times"></i></button>
            
            <div id="immersive360Viewport" style="width: 80%; height: 70%; display: flex; align-items: center; justify-content: center; cursor: grab;">
                <img id="v360Shoe" src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=80" style="width: 100%; max-width: 600px; filter: drop-shadow(0 40px 60px rgba(0,0,0,0.2)); transition: 0.1s linear;">
            </div>

            <div style="position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%); display: flex; align-items: center; gap: 10px; background: white; padding: 10px 25px; border-radius: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.03);">
                <div style="width: 6px; height: 20px; background: #10b981; border-radius: 3px;"></div>
                <span style="font-size: 0.75rem; font-weight: 800; color: #1e293b; letter-spacing: 1px; text-transform: uppercase;">Drag to Rotate</span>
            </div>
        </div>
    </div>
</div>

<script>
let compareList = [];

// 360 Viewer Logic
let is360Spinning = false;
let spin360Interval;
let current360Angle = 0;
let isDragging360 = false;
let startX360 = 0;

function open360Viewer() {
    document.getElementById('immersive360Overlay').style.display = 'flex';
}

function reset360() {
    current360Angle = 0;
    update360Transform();
    if(is360Spinning) toggle360Auto();
}

function update360Transform() {
    const shoe = document.getElementById('v360Shoe');
    const slider = document.getElementById('rotateSlider');
    const angleText = document.getElementById('angleValue');
    
    shoe.style.transform = `rotate(${current360Angle}deg)`;
    slider.value = current360Angle;
    angleText.innerText = `${current360Angle}°`;
}

function toggle360Auto() {
    const btn = document.getElementById('autoSpinBtn');
    const icon = btn.querySelector('i');
    is360Spinning = !is360Spinning;
    
    if(is360Spinning) {
        icon.className = 'fas fa-pause';
        icon.style.color = '#ef4444';
        spin360Interval = setInterval(() => {
            current360Angle = (current360Angle + 1) % 180;
            if(current360Angle > 180) current360Angle = -180;
            update360Transform();
        }, 30);
    } else {
        icon.className = 'fas fa-play';
        icon.style.color = '#10b981';
        clearInterval(spin360Interval);
    }
}

// Drag logic
const viewport360 = document.getElementById('immersive360Viewport');
viewport360.addEventListener('mousedown', (e) => {
    isDragging360 = true;
    startX360 = e.clientX;
    viewport360.style.cursor = 'grabbing';
});

window.addEventListener('mouseup', () => {
    isDragging360 = false;
    viewport360.style.cursor = 'grab';
});

window.addEventListener('mousemove', (e) => {
    if(!isDragging360) return;
    const delta = e.clientX - startX360;
    current360Angle += Math.round(delta * 0.5);
    if(current360Angle > 180) current360Angle = -180;
    if(current360Angle < -180) current360Angle = 180;
    update360Transform();
    startX360 = e.clientX;
});

// Slider logic
document.getElementById('rotateSlider').addEventListener('input', (e) => {
    current360Angle = parseInt(e.target.value);
    update360Transform();
});

function openAIAdvisor() {
    const overlay = document.getElementById('aiAdvisorOverlay');
    const text = document.getElementById('aiResultText');
    overlay.style.display = 'flex';
    
    text.innerHTML = '';
    const messages = [
        "> Syncing with your recent searches...",
        "> Analyzing color palette preferences (Black, Royal Blue, Neon)...",
        "> Identifying silhouette trends: 'High-Top', 'Athletic'...",
        "> RECOMMENDED COLLECTION: 'Future Hypebound 2026' - Optimized for urban mobility and comfort.",
        "> ACTION: View curated collection on next visit for exclusive early access."
    ];
    
    let i = 0;
    function type() {
        if (i < messages.length) {
            text.innerHTML += messages[i] + "<br>";
            i++;
            setTimeout(type, 800);
        }
    }
    type();
}

function handleCompare(checkbox, id) {
    const card = document.getElementById('card-' + id);
    const name = card.dataset.name;
    const img = card.dataset.img;

    if (checkbox.checked) {
        if (compareList.length >= 4) {
            alert("Maximum 4 items can be compared.");
            checkbox.checked = false;
            return;
        }
        compareList.push({ id, name, img });
    } else {
        compareList = compareList.filter(item => item.id !== id);
    }
    updateCompareBar();
}

function updateCompareBar() {
    const bar = document.getElementById('compareBar');
    const itemsCont = document.getElementById('compareItems');
    const countText = document.getElementById('compareCount');
    
    if (!itemsCont) return;

    itemsCont.innerHTML = '';
    compareList.forEach(item => {
        const thumb = document.createElement('div');
        thumb.className = 'compare-thumb';
        thumb.innerHTML = `<img src="${item.img}" alt="${item.name}">`;
        itemsCont.appendChild(thumb);
    });

    countText.innerText = `${compareList.length} items selected`;
    
    if (compareList.length > 0) bar.classList.add('active');
    else bar.classList.remove('active');
}

function clearCompare() {
    compareList = [];
    document.querySelectorAll('.compare-checkbox input').forEach(cb => cb.checked = false);
    updateCompareBar();
}

function startComparison() {
    if (compareList.length < 2) {
        alert("Please select at least 2 items to compare.");
        return;
    }
    const ids = compareList.map(item => item.id).join(',');
    window.location.href = `compare.php?ids=${ids}`;
}

function toggleVerified(checkbox) {
    const input = document.getElementById('verifiedInput');
    input.value = checkbox.checked ? '1' : '';
    document.getElementById('sortForm').submit();
}

function openQuickView(id) {
    const overlay = document.getElementById('quickViewOverlay');
    overlay.style.display = 'flex';
    
    // Fetch details via AJAX
    fetch(`api/get_product_details.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalImg').src = data.image;
            document.getElementById('qvBrand').innerText = data.brand;
            document.getElementById('qvName').innerText = data.name;
            document.getElementById('qvPrice').innerText = '₹' + new Intl.NumberFormat().format(data.price);
            document.getElementById('qvDesc').innerText = data.description || "No description available.";
            document.getElementById('qvLink').href = `product_detail.php?id=${id}`;
        });
}

function closeQuickView() {
    document.getElementById('quickViewOverlay').style.display = 'none';
}

function toggleWishlist(event, productId) {
    event.preventDefault();
    event.stopPropagation();
    
    const btn = event.currentTarget;
    const icon = btn.querySelector('i');
    
    fetch('toggle_wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.action === 'added') {
                icon.classList.remove('far');
                icon.classList.add('fas');
                btn.style.color = '#ef4444';
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                btn.style.color = '#fff';
            }
        } else if (data.message === 'User not logged in') {
            window.location.href = 'login.php';
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        console.error('Wishlist error:', err);
        alert('Could not update wishlist. Try again later.');
    });
}

function stockProduct(productId) {
    if (!confirm('Add this product to your store inventory for reselling?')) return;
    
    const btn = event.currentTarget || document.activeElement;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Stocking...';

    fetch('stock_product.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            btn.className = 'stocked-badge';
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Stocked';
            btn.disabled = true;
            btn.removeAttribute('onclick');
            alert(data.message);
        } else {
            alert(data.message);
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(err => {
        console.error('Stocking error:', err);
        alert('Error stocking product. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalText;
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

<?php include 'includes/chatbot.php'; ?>
</body>
</html>
