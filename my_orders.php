<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'] ?? null;
$email = $_SESSION['email'] ?? null;

// Only sellers/admins MUST be logged in for their respective views, but customers can see a login prompt
$is_guest = !$user_id;

    try {
        // Only fetch data if logged in
        $order_status_list = [];
        $payment_status_list = ['unpaid', 'paid', 'failed', 'refunded'];
        $orders = [];
    $available_channels = [];
    $top_products = [];

    if ($user_id) {
        $role = $_SESSION['role'] ?? 'customer';
        $seller_id = $_SESSION['seller_id'] ?? null;
        
        if (in_array($role, ['entrepreneur', 'store'])) {
            if (!$seller_id) {
                $stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
                $stmt_seller->execute([$email]);
                $seller = $stmt_seller->fetch();
                
                if (!$seller) {
                    die("<div style='padding:40px; text-align:center; font-family:sans-serif; background:#0B0F19; color:white; min-height:100vh;'>
                            <h2 style='color:#10b981'>Account Sync Required</h2>
                            <p>Your seller account is not fully setup. Please contact support.</p>
                            <a href='index.php' style='display:inline-block; padding:12px 24px; background:#10b981; color:white; text-decoration:none; border-radius:50px; font-weight:600; margin-top:20px;'>Return Home</a>
                         </div>");
                }
                $seller_id = $seller['id'];
                $_SESSION['seller_id'] = $seller_id;
            }
        }

        $search = trim($_GET['search'] ?? '');
        $order_status = trim($_GET['order_status'] ?? '');
        $payment_status = trim($_GET['payment_status'] ?? '');
        $channel_filter = trim($_GET['channel'] ?? '');

        // Get unique statuses for filters
        $status_query = "SELECT DISTINCT status FROM orders WHERE 1=1";
        $status_params = [];
        if ($role === 'customer') { $status_query .= " AND user_id = ?"; $status_params[] = $user_id; }
        elseif ($role !== 'admin') { $status_query .= " AND seller_id = ?"; $status_params[] = $seller_id; }

        $order_statuses = $pdo->prepare($status_query . " AND status IS NOT NULL");
        $order_statuses->execute($status_params);
        $order_status_list = $order_statuses->fetchAll(PDO::FETCH_COLUMN);

        try {
            $payment_statuses = $pdo->prepare($status_query . " AND payment_status IS NOT NULL");
            $payment_statuses->execute($status_params);
            $payment_status_list = $payment_statuses->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) { $payment_status_list = ['unpaid', 'paid', 'failed', 'refunded']; }

        // Fetch orders
        $sort_by = $_GET['sort_by'] ?? 'date_desc';

        $sql = "SELECT 
                    o.*, 
                    pb.name as product_name, 
                    ps.sku,
                    b.name as brand_name,
                    c.name as category_name,
                    (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as primary_image,
                    (SELECT url FROM product_media pm WHERE pm.product_id = pb.id LIMIT 1) as fallback_image
                FROM orders o 
                LEFT JOIN product_base pb ON o.product_id = pb.id
                LEFT JOIN product_skus ps ON pb.id = ps.product_id
                LEFT JOIN product_specs spec ON pb.id = spec.product_id
                LEFT JOIN brands b ON spec.brand_id = b.id
                LEFT JOIN categories c ON pb.category_id = c.id
                WHERE 1=1";
        
        $params = [];
        if ($role === 'customer') {
            $sql .= " AND o.user_id = ?";
            $params[] = $user_id;
        } elseif ($role !== 'admin') {
            $sql .= " AND o.seller_id = ?";
            $params[] = $seller_id;
        }

        if ($search) {
            $sql .= " AND (o.id LIKE ? OR pb.name LIKE ? OR o.customer_name LIKE ? OR ps.sku LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($order_status) {
            $sql .= " AND o.status = ?";
            $params[] = $order_status;
        }
        if ($channel_filter) {
            $sql .= " AND o.channel = ?";
            $params[] = $channel_filter;
        }
        if ($payment_status) {
            $sql .= " AND o.payment_status = ?";
            $params[] = $payment_status;
        }

        // Tab Filtering Logic
        $tab = $_GET['tab'] ?? 'orders';
        if ($role === 'customer') {
            if ($tab === 'not-shipped') {
                $sql .= " AND o.status IN ('pending', 'processing', 'shipped')";
            } elseif ($tab === 'buy-again') {
                $sql .= " AND o.status = 'delivered'";
            }
        }

        // Sorting Logic
        switch ($sort_by) {
            case 'status_asc': $sql .= " ORDER BY o.status ASC"; break;
            case 'status_desc': $sql .= " ORDER BY o.status DESC"; break;
            case 'price_high': $sql .= " ORDER BY o.total_price DESC"; break;
            case 'price_low': $sql .= " ORDER BY o.total_price ASC"; break;
            case 'date_asc': $sql .= " ORDER BY o.order_date ASC"; break;
            default: $sql .= " ORDER BY o.order_date DESC"; break;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt_channels = $pdo->query("SELECT name FROM marketplaces ORDER BY name ASC");
        $available_channels = $stmt_channels->fetchAll(PDO::FETCH_COLUMN);

        // Show product performance insights for all seller products
        if ($role !== 'customer') {
            $sql_top = "SELECT pb.id, pb.name, 
                        (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as img, 
                        COUNT(o.id) as total_orders, COALESCE(SUM(o.total_price), 0) as total_revenue
                        FROM product_base pb
                        LEFT JOIN orders o ON pb.id = o.product_id
                        WHERE pb.seller_id = ?
                        GROUP BY pb.id
                        ORDER BY total_orders DESC, pb.name ASC";
            $stmt_top = $pdo->prepare($sql_top);
            $stmt_top->execute([$seller_id]);
            $top_products = $stmt_top->fetchAll(PDO::FETCH_ASSOC);
        }
    }

} catch (PDOException $e) {
    die("Error fetching orders: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - WALKON Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... (styles remain mostly the same, ensuring compatibility) ... */
        :root {
            --primary: #10b981;
            --primary-hover: #059669;
            --bg: #030712;
            --card-bg: #111827;
            --text-dark: #F3F4F6;
            --text-light: #9CA3AF;
            --border: rgba(255, 255, 255, 0.08);
        }
        
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { background: var(--bg); color: var(--text-dark); min-height: 100vh; }

        /* ... Skipping unchanged CSS for brevity, trust existing styles ... */
        /* Keeping critical layout styles */
        .container { max-width: 1400px; margin: 40px auto; padding: 0 40px; }
        
        /* ... (Keep existing styles) ... */
        
        /* New Styles for Enhanced table elements */
        .brand-pill { font-size: 0.65rem; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px; color: #ddd; letter-spacing: 0.5px; text-transform: uppercase; margin-right: 6px; }

        /* ... (Rest of styles from original file should be preserved if not replaced) ... */
        /* Note: Since we are replacing a large chunk effectively, we rely on the user's existing styles unless we need to add specifically. */
        /* Re-including critical generic styles to be safe since this is a partial replace contextually */
        .search-section { background: rgba(17, 24, 39, 0.7); backdrop-filter: blur(20px); padding: 30px; border-radius: 28px; border: 1px solid var(--border); margin-bottom: 30px; }
        .search-bar { display: flex; gap: 15px; margin-bottom: 20px; }
        .input-group { position: relative; flex: 1; }
        .search-input { width: 100%; padding: 14px 14px 14px 50px; background: #0B0F19; border: 1px solid var(--border); border-radius: 14px; color: white; }
        .btn-action { padding: 14px 28px; background: var(--primary); color: white; border: none; border-radius: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .filter-row { display: flex; gap: 15px; flex-wrap: wrap; }
        .filter-select { padding: 12px 20px; background: #0B0F19; border: 1px solid var(--border); border-radius: 12px; color: var(--text-light); cursor: pointer; font-weight: 500; appearance: none; }
        .table-container { background: rgba(17, 24, 39, 0.4); border-radius: 32px; border: 1px solid var(--border); overflow: hidden; }
        .order-table { width: 100%; border-collapse: collapse; text-align: left; }
        .order-table th { background: rgba(255,255,255,0.03); padding: 25px; font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: var(--text-light); border-bottom: 1px solid var(--border); }
        .order-table td { padding: 25px; border-bottom: 1px solid var(--border); color: white; vertical-align: middle; }
        .prod-info { display: flex; align-items: center; gap: 15px; }
        .prod-img { width: 56px; height: 56px; border-radius: 12px; object-fit: cover; background: #0B0F19; border: 1px solid var(--border); }
        .status-pill { padding: 8px 16px; border-radius: 12px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
        .status-pending { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .status-completed, .status-paid { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        /* Missing styles will utilize defaults or existing css if block allows, but for replace this is heavy. 
           Strategy: We will trust the "StartLine" to avoid overwriting the <style> block if possible, 
           but the StartLine 46 includes logic. 
           Actually, the user's file had CSS embedded. I will try to keep the upper CSS untouched if possible, 
           but I need to update the PHP logic at the top. 
           
           Wait, I can't easily perform a split replace for PHP at top AND HTML in body in one go without replacing everything in between.
           I will replace the PHP logic block and the Filters HTML block separately to be safer/cleaner? 
           No, `my_orders.php` lines 46-106 is the fetch logic.
           I will replace lines 47-101 (fetch logic) first.
        */

} catch (PDOException $e) {
    die("Error fetching orders: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - WALKON Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;       /* Royal Blue */
            --primary-hover: #1d4ed8;
            --bg: #ffffff;
            --card-bg: #ffffff;
            --text-dark: #1e293b;     /* Deep Navy */
            --text-light: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --info: #3b82f6;
            --danger: #ef4444;
            --sky-light: #f0f9ff;
            --sky-mid: #e0f2fe;
        }
        
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { 
            background: radial-gradient(circle at 10% 20%, var(--sky-mid) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, var(--sky-light) 0%, transparent 40%),
                        var(--bg);
            color: var(--text-dark); min-height: 100vh; 
        }

        /* Header */
        .header {
            background: #fff;
            padding: 40px 20px;
            text-align: center;
            border-bottom: 1px solid var(--border);
            position: relative;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }
        .logo-box { display: flex; flex-direction: column; align-items: center; gap: 12px; margin-bottom: 5px; }
        .logo-box img { height: 45px; width: auto; }
        .brand-name { font-size: 28px; font-weight: 800; color: var(--text-dark); text-transform: uppercase; letter-spacing: -0.5px; }
        .brand-name span { color: var(--primary); }
        .header p { color: var(--text-light); font-size: 0.9rem; }

        .back-nav {
            position: absolute;
            top: 45px;
            left: 40px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-light);
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }
        .back-nav:hover { color: var(--primary); transform: translateX(-5px); }

        .container { max-width: 1400px; margin: 40px auto; padding: 0 40px; }

        /* Stats Cards */
        .stats-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .summary-card { 
            background: #fff; 
            padding: 30px; 
            border-radius: 24px; 
            border: 1px solid var(--border); 
            display: flex; 
            flex-direction: column; 
            gap: 8px; 
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        }
        .summary-card:hover { transform: translateY(-5px); border-color: var(--primary); box-shadow: 0 20px 40px rgba(37, 99, 235, 0.1); }
        .summary-card::after {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }
        .summary-label { font-size: 0.85rem; color: var(--text-light); text-transform: uppercase; font-weight: 700; letter-spacing: 1px; }
        .summary-value { font-size: 2.5rem; font-weight: 800; color: var(--text-dark); }

        /* Product Insight Grid */
        .insights-wrapper { 
            overflow-x: auto; 
            padding-bottom: 15px; 
            margin-bottom: 30px; 
            scrollbar-width: thin;
            scrollbar-color: var(--primary) transparent;
        }
        .insights-wrapper::-webkit-scrollbar { height: 4px; }
        .insights-wrapper::-webkit-scrollbar-track { background: transparent; }
        .insights-wrapper::-webkit-scrollbar-thumb { background: rgba(16, 185, 129, 0.3); border-radius: 10px; }
        
        .insights-grid { display: flex; gap: 20px; padding-bottom: 5px; }
        .insight-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: 0.3s;
            width: 320px;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }
        .insight-card:hover { background: var(--sky-light); transform: translateY(-3px); border-color: var(--primary); }
        .insight-img { width: 60px; height: 60px; border-radius: 12px; object-fit: cover; background: #fff; border: 1px solid var(--border); }
        .insight-info { overflow: hidden; }
        .insight-info h4 { font-size: 0.85rem; color: var(--text-dark); margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 700; }
        .insight-stats { display: flex; gap: 12px; font-size: 0.75rem; color: var(--text-light); }
        .insight-stats b { color: var(--primary); }

        /* Filters */
        .search-section {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            padding: 30px;
            border-radius: 28px;
            border: 1px solid var(--border);
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        }
        .search-bar { display: flex; gap: 15px; margin-bottom: 20px; }
        .input-group { position: relative; flex: 1; }
        .input-group i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--text-light); }
        .search-input { 
            width: 100%; 
            padding: 14px 14px 14px 50px; 
            background: #fff; 
            border: 1px solid var(--border); 
            border-radius: 14px; 
            color: var(--text-dark); 
            font-size: 0.95rem; 
            transition: 0.3s; 
        }
        .search-input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
        
        .btn-action { 
            padding: 14px 28px; 
            background: var(--primary); 
            color: white; 
            border: none; 
            border-radius: 14px; 
            font-weight: 700; 
            cursor: pointer; 
            transition: 0.3s; 
            display: flex; 
            align-items: center; 
            gap: 10px;
            text-decoration: none;
        }
        .btn-action:hover { background: var(--primary-hover); transform: translateY(-2px); }
        .btn-secondary { background: #374151; }
        .btn-secondary:hover { background: #4B5563; }

        .filter-row { display: flex; gap: 15px; flex-wrap: wrap; }
        .filter-select { 
            padding: 12px 20px; 
            background: #fff; 
            border: 1px solid var(--border); 
            border-radius: 12px; 
            color: var(--text-light); 
            cursor: pointer; 
            font-weight: 500;
            transition: 0.3s;
        }
        .filter-select:hover { border-color: var(--primary); color: var(--text-dark); }
        .filter-select:focus { outline: none; border-color: var(--primary); }

        /* Custom Dropdown Styling */
        .filter-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239CA3AF'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
            padding-right: 45px;
        }

        .btn-clear {
            background: rgba(255, 255, 255, 0.05);
            color: #F3F4F6;
            border: 1px solid var(--border);
        }
        .btn-clear:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: #9CA3AF;
        }

        /* Table */
        .table-container { 
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(20px);
            border-radius: 32px; 
            border: 1px solid var(--border); 
            overflow: hidden; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        }
        .order-table { width: 100%; border-collapse: collapse; text-align: left; }
        .order-table th { 
            background: rgba(0,0,0,0.02); 
            padding: 25px; 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            font-weight: 800; 
            color: var(--text-light); 
            letter-spacing: 1.5px;
            border-bottom: 1px solid var(--border);
        }
        .order-table td { padding: 25px; border-bottom: 1px solid var(--border); font-size: 0.95rem; vertical-align: middle; color: var(--text-dark); }
        .order-table tr:last-child td { border-bottom: none; }
        .order-table tr { transition: 0.3s; }
        .order-table tr:hover { background: rgba(16, 185, 129, 0.03); }

        .prod-info { display: flex; align-items: center; gap: 15px; }
        .prod-img { width: 56px; height: 56px; border-radius: 12px; object-fit: cover; background: #fff; border: 1px solid var(--border); }
        .prod-name { font-weight: 700; color: var(--text-dark); display: block; margin-bottom: 4px; }
        .prod-sku { font-size: 0.75rem; color: var(--text-light); display: block; }

        .cust-name { font-weight: 600; color: var(--text-dark); display: block; }
        .channel-tag { font-size: 0.75rem; color: var(--primary); font-weight: 700; text-transform: uppercase; }

        .status-pill { 
            display: inline-block; 
            padding: 8px 16px; 
            border-radius: 12px; 
            font-size: 0.7rem; 
            font-weight: 800; 
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .status-pending, .status-processing { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .status-shipped { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
        .status-delivered, .status-completed, .status-paid { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .status-cancelled { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
        .status-unpaid { background: rgba(156, 163, 175, 0.15); color: #cbd5e1; border: 1px solid rgba(156, 163, 175, 0.3); }

        .pay-link { 
            color: var(--primary); 
            text-decoration: none; 
            font-weight: 700; 
            font-size: 0.75rem; 
            display: inline-flex; 
            align-items: center; 
            gap: 5px; 
            padding: 4px 10px; 
            background: rgba(16, 185, 129, 0.1); 
            border-radius: 6px;
            transition: 0.2s;
        }
        .pay-link:hover { background: var(--primary); color: white; }

        /* QR Modal */
        .qr-modal {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(3, 7, 18, 0.95);
            display: none;
            align-items: center; justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(10px);
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .qr-content {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 32px;
            border: 1px solid var(--border);
            text-align: center;
            width: 90%;
            max-width: 400px;
            transform: scale(0.9);
            transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .qr-modal.active { display: flex; }
        .qr-modal.active .qr-content { transform: scale(1); }

        .qr-code-img {
            width: 220px;
            height: 220px;
            background: white;
            padding: 15px;
            border-radius: 20px;
            margin: 20px auto;
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.2);
        }
        
        .qr-close {
            position: absolute;
            top: 20px; right: 20px;
            color: var(--text-light);
            font-size: 1.5rem;
            cursor: pointer;
            transition: 0.2s;
        }
        .qr-close:hover { color: white; }
        
        .upi-id { font-size: 0.8rem; color: var(--text-light); background: #0B0F19; padding: 10px; border-radius: 10px; margin-top: 20px; display: inline-block; }

        .empty-state { text-align: center; padding: 100px 40px; }
        .empty-state i { font-size: 4rem; color: var(--border); margin-bottom: 20px; }
        .empty-state h3 { font-size: 1.5rem; margin-bottom: 10px; }
        .empty-state p { color: var(--text-light); }

        /* Footer */
        footer {
            background: #05070A;
            border-top: 1px solid var(--border);
            padding: 80px 0 40px;
            margin-top: 100px;
        }
        .footer-container { max-width: 1400px; margin: 0 auto; padding: 0 2rem; display: grid; grid-template-columns: 1.2fr 2fr; gap: 4rem; }
        .footer-card { background: #0f131f; border: 1px solid var(--border); border-radius: 24px; padding: 3rem; }
        .footer-logo { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; text-decoration: none; }
        .footer-logo .brand-text { font-size: 1.5rem; font-weight: 800; color: white; }
        .footer-desc { color: var(--text-light); font-size: 0.95rem; line-height: 1.6; margin-bottom: 30px; }
        .contact-info { display: flex; flex-direction: column; gap: 15px; }
        .contact-item { display: flex; align-items: center; gap: 12px; color: white; font-size: 0.9rem; }
        .contact-item i { color: var(--primary); }
        .footer-nav-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; }
        .footer-col h4 { color: var(--primary); font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 25px; }
        .footer-links { list-style: none; }
        .footer-links li { margin-bottom: 12px; }
        .footer-links a { color: #E2E8F0; text-decoration: none; font-size: 0.95rem; transition: 0.3s; }
        .footer-links a:hover { color: var(--primary); padding-left: 8px; }

        @media (max-width: 1024px) {
            .footer-container { grid-template-columns: 1fr; }
            .back-nav { position: relative; top: 0; left: 0; margin-bottom: 20px; justify-content: center; }
            .container { padding: 0 20px; }
            .search-bar { flex-direction: column; }
        }
        /* Amazon Style Card Layout */
        .order-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 25px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.03);
            transition: 0.3s;
        }
        .order-card:hover { border-color: var(--primary); transform: translateY(-2px); }
        .card-header {
            background: rgba(0,0,0,0.02);
            padding: 15px 25px;
            border-bottom: 1px solid var(--border);
            display: grid;
            grid-template-columns: repeat(4, 1fr) auto;
            gap: 20px;
            font-size: 0.8rem;
            color: var(--text-light);
        }
        .header-item span { display: block; text-transform: uppercase; font-weight: 700; margin-bottom: 4px; letter-spacing: 0.5px; }
        .header-item b { color: var(--text-dark); font-size: 0.95rem; }
        .header-actions { text-align: right; }
        .header-actions a { color: var(--primary); text-decoration: none; font-weight: 600; margin-left: 15px; }
        .header-actions a:hover { text-decoration: underline; }

        .card-body { padding: 25px; }
        .order-status-banner { font-size: 1.2rem; font-weight: 800; color: var(--text-dark); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .order_content { display: grid; grid-template-columns: 120px 1fr 220px; gap: 30px; align-items: start; }
        .item_thumb { width: 120px; height: 120px; border-radius: 12px; object-fit: contain; background: #fff; border: 1px solid var(--border); padding: 10px; }
        .item_details h4 { color: var(--text-dark); font-size: 1.1rem; margin-bottom: 8px; font-weight: 700; }
        .item_details p { color: var(--text-light); font-size: 0.85rem; line-height: 1.5; }
        
        .side-actions { display: flex; flex-direction: column; gap: 10px; }
        .btn-amazon { 
            padding: 10px 20px; 
            border-radius: 8px; 
            font-size: 0.85rem; 
            font-weight: 700; 
            text-align: center; 
            text-decoration: none; 
            transition: 0.2s; 
            border: 1px solid var(--border);
            cursor: pointer;
            width: 100%;
        }
        .btn-yellow { background: #FFD814; color: #0F1111; border-color: #FCD200; box-shadow: 0 2px 5px rgba(213, 217, 217, 0.5); }
        .btn-yellow:hover { background: #F7CA00; }
        .btn-white { background: rgba(255,255,255,0.05); color: white; border-color: var(--border); }
        .btn-white:hover { background: rgba(255,255,255,0.1); border-color: #9CA3AF; }

        /* Tabs */
        .order-tabs { display: flex; gap: 30px; margin-bottom: 30px; border-bottom: 1px solid var(--border); padding-bottom: 1px; }
        .tab-item { 
            color: var(--text-light); 
            text-decoration: none; 
            padding-bottom: 12px; 
            font-weight: 700; 
            font-size: 1rem; 
            position: relative; 
            transition: 0.3s;
        }
        .tab-item.active { color: var(--primary); }
        .tab-item.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 3px; background: var(--primary); border-radius: 3px 3px 0 0; }
        .tab-item:hover { color: white; }

        @media (max-width: 992px) {
            .card-header { grid-template-columns: 1fr 1fr; gap: 15px; }
            .order_content { grid-template-columns: 100px 1fr; }
            .side-actions { grid-column: 1 / -1; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        }
        @media (max-width: 576px) {
            .side-actions { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <header class="header">
        <a href="javascript:history.back()" class="back-nav"><i class="fas fa-arrow-left"></i> Back</a>
        <div class="logo-box">
            <img src="assets/shoe_logo_green.png" alt="WalkOn">
            <div class="brand-name">Walk<span>on</span></div>
        </div>
        <p>Orders & Lifecycle Management</p>
    </header>

    <div class="container">
        <!-- Stats Row -->
        <div class="stats-summary">
            <div class="summary-card">
                <span class="summary-label">Total Volume</span>
                <span class="summary-value"><?php echo count($orders); ?></span>
            </div>
            <div class="summary-card" style="border-right: 3px solid var(--primary);">
                <span class="summary-label">Revenue</span>
                <span class="summary-value">₹<?php 
                    $total_rev = array_sum(array_column($orders, 'total_price'));
                    echo number_format($total_rev, 0); 
                ?></span>
            </div>
            <div class="summary-card">
                <span class="summary-label">Pending</span>
                <span class="summary-value"><?php 
                    echo count(array_filter($orders, function($o) { return strtolower($o['status']) == 'pending'; }));
                ?></span>
            </div>
            <div class="summary-card">
                <span class="summary-label">Total Products</span>
                <span class="summary-value"><?php 
                    $stmt_prod_count = $pdo->prepare("SELECT COUNT(*) FROM product_base WHERE seller_id = ?");
                    $stmt_prod_count->execute([$seller_id]);
                    echo $stmt_prod_count->fetchColumn();
                ?></span>
            </div>
        </div>
        <!-- Product Performance Insights -->
        <div style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between;">
            <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-dark); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-chart-line" style="color: var(--primary);"></i> Product Analytics
            </h3>
            <span style="font-size: 0.8rem; color: var(--text-light);">Real-time performance of your live listings</span>
        </div>
        
        <div class="insights-wrapper">
            <div class="insights-grid">
            <?php if (!empty($top_products)): ?>
                <?php foreach($top_products as $top): ?>
                    <div class="insight-card">
                        <img src="<?php echo htmlspecialchars($top['img'] ?? 'https://via.placeholder.com/100'); ?>" class="insight-img" onerror="this.src='https://via.placeholder.com/100'">
                        <div class="insight-info">
                            <h4><?php echo htmlspecialchars($top['name']); ?></h4>
                            <div class="insight-stats">
                                <span>Orders: <b><?php echo $top['total_orders']; ?></b></span>
                                <span>Revenue: <b>₹<?php echo number_format($top['total_revenue'], 0); ?></b></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; padding: 20px; text-align: center; color: var(--text-light); font-style: italic;">
                    No product data available yet...
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Tabs -->
    <div class="order-tabs" style="margin-top: 30px;">
        <a href="?tab=orders" class="tab-item <?= ($_GET['tab'] ?? 'orders') === 'orders' ? 'active' : '' ?>">Orders</a>
        <a href="?tab=buy-again" class="tab-item <?= ($_GET['tab'] ?? '') === 'buy-again' ? 'active' : '' ?>">Buy Again</a>
        <a href="?tab=not-shipped" class="tab-item <?= ($_GET['tab'] ?? '') === 'not-shipped' ? 'active' : '' ?>">Not Yet Shipped</a>
    </div>
        <div class="search-section">
            <form action="my_orders.php" method="GET">
                <div class="search-bar" style="margin-bottom: 25px;">
                    <div class="input-group">
                        <i class="fas fa-search" style="font-size: 1.1rem; color: var(--text-light);"></i>
                        <input type="text" name="search" class="search-input" 
                               style="padding: 18px 18px 18px 55px; font-size: 1.05rem; background: rgba(255,255,255,0.8); border-radius: 16px;" 
                               placeholder="Search by Order ID, Product, or Customer..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <button type="submit" class="btn-action" style="padding: 0 35px; border-radius: 16px; font-size: 1rem;">
                        Search Orders
                    </button>
                    <?php if ($search || $order_status || $payment_status || $channel_filter): ?>
                        <a href="my_orders.php" class="btn-action btn-clear" style="padding: 0 25px; border-radius: 16px; font-size: 1rem; display: flex; align-items: center;">
                            Clear Filters
                        </a>
                    <?php endif; ?>
                </div>

                <div class="filter-row" style="gap: 12px;">
                    <select name="order_status" class="filter-select" onchange="this.form.submit()" style="flex: 1; min-width: 160px; background: rgba(255,255,255,0.8); border-radius: 12px;">
                        <option value="">Status: All</option>
                        <?php foreach($order_status_list as $s): ?>
                            <option value="<?php echo htmlspecialchars($s); ?>" <?php echo $order_status == $s ? 'selected' : ''; ?>>
                                <?php echo ucfirst(htmlspecialchars($s)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="sort_by" class="filter-select" onchange="this.form.submit()" style="flex: 1; min-width: 160px; background: rgba(255,255,255,0.8); border-radius: 12px;">
                        <option value="date_desc" <?php echo ($sort_by ?? '') == 'date_desc' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="date_asc" <?php echo ($sort_by ?? '') == 'date_asc' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="status_asc" <?php echo ($sort_by ?? '') == 'status_asc' ? 'selected' : ''; ?>>Status (A-Z)</option>
                        <option value="status_desc" <?php echo ($sort_by ?? '') == 'status_desc' ? 'selected' : ''; ?>>Status (Z-A)</option>
                        <option value="price_high" <?php echo ($sort_by ?? '') == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="price_low" <?php echo ($sort_by ?? '') == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    </select>

                    <select name="channel" class="filter-select" onchange="this.form.submit()" style="flex: 1.5; min-width: 180px; background: rgba(255,255,255,0.8); border-radius: 12px;">
                        <option value="">Channel: All</option>
                        <?php foreach($available_channels as $ch): ?>
                            <option value="<?php echo htmlspecialchars($ch); ?>" <?php echo $channel_filter == $ch ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ch); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <a href="export_orders.php?<?php echo http_build_query($_GET); ?>" class="btn-action" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); padding: 0 25px; border-radius: 12px; font-size: 0.85rem;">
                        <i class="fas fa-file-export"></i> &nbsp;Export Report
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="table-container" style="<?= $role === 'customer' || $is_guest ? 'background:transparent; border:none; box-shadow:none;' : '' ?>">
            <?php if ($is_guest): ?>
                <div class="empty-state">
                    <i class="fas fa-lock" style="color: var(--primary);"></i>
                    <h3>Track Your Journey</h3>
                    <p style="margin-bottom: 2rem;">Sign in to view your order history, track deliveries, and manage returns.</p>
                    <a href="login.php?redirect=my_orders.php" class="btn-action" style="display: inline-flex; width: auto; margin: 0 auto;">Login to My Account</a>
                </div>
            <?php elseif (count($orders) > 0): ?>
                <?php if ($role === 'customer'): ?>
                    <!-- Amazon Style Card View for Customers -->
                    <?php foreach ($orders as $order): 
                        $img_url = $order['primary_image'] ?? $order['fallback_image'] ?? 'https://via.placeholder.com/100?text=No+Data';
                        $status_class = strtolower($order['status']);
                        $arrival_date = date('d F', strtotime($order['order_date'] . ' + 7 days'));
                    ?>
                        <div class="order-card">
                            <div class="card-header">
                                <div class="header-item">
                                    <span>Order Placed</span>
                                    <b><?php echo date('d F Y', strtotime($order['order_date'])); ?></b>
                                </div>
                                <div class="header-item">
                                    <span>Total</span>
                                    <b>₹<?php echo number_format($order['total_price'], 2); ?></b>
                                </div>
                                <div class="header-item">
                                    <span>Ship To</span>
                                    <b><?php echo htmlspecialchars($order['customer_name'] ?? 'Customer'); ?> <i class="fas fa-chevron-down" style="font-size: 0.7rem; margin-left: 5px;"></i></b>
                                </div>
                                <div class="header-item">
                                    <span>Order #</span>
                                    <b>402-<?= str_pad($order['id'], 7, '0', STR_PAD_LEFT) ?>-<?= rand(1000000, 9999999) ?></b>
                                </div>
                                <div class="header-actions">
                                    <a href="order_details.php?id=<?= $order['id'] ?>">View order details</a>
                                    <a href="invoice.php?id=<?= $order['id'] ?>" target="_blank">Invoice <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i></a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="order-status-banner">
                                    <?php if ($status_class === 'delivered'): ?>
                                        <i class="fas fa-check-circle" style="color: var(--primary);"></i> Delivered <?= date('d F Y', strtotime($order['order_date'] . ' + 3 days')) ?>
                                    <?php elseif ($status_class === 'cancelled'): ?>
                                        <i class="fas fa-times-circle" style="color: #ef4444;"></i> Cancelled
                                    <?php else: ?>
                                        <i class="fas fa-truck" style="color: var(--primary);"></i> Arriving <?= $arrival_date ?>
                                    <?php endif; ?>
                                </div>
                                <div class="order_content">
                                    <img src="<?= htmlspecialchars($img_url) ?>" class="item_thumb" onerror="this.src='https://via.placeholder.com/100'">
                                    <div class="item_details">
                                        <h4><?= htmlspecialchars($order['product_name']) ?></h4>
                                        <p>Return window closed on <?= date('d M Y', strtotime($order['order_date'] . ' + 30 days')) ?></p>
                                        <div style="margin-top: 15px; display: flex; gap: 10px;">
                                            <a href="product_detail.php?id=<?= $order['product_id'] ?>" class="btn-amazon btn-yellow" style="width: auto;">Buy it again</a>
                                            <a href="#" class="btn-amazon btn-white" style="width: auto;">View your item</a>
                                        </div>
                                    </div>
                                    <div class="side-actions">
                                        <?php if ($status_class !== 'cancelled'): ?>
                                            <a href="<?= !empty($order['tracking_number']) ? 'help_tracking.php?tracking_id=' . $order['tracking_number'] : '#' ?>" class="btn-amazon btn-yellow"><i class="fas fa-search-location"></i> Track package</a>
                                        <?php endif; ?>
                                        <a href="#" class="btn-amazon btn-white"><i class="fas fa-undo"></i> Return or replace items</a>
                                        <a href="#" class="btn-amazon btn-white"><i class="fas fa-gift"></i> Share gift receipt</a>
                                        <a href="write_review.php?product_id=<?= $order['product_id'] ?>&order_id=<?= $order['id'] ?>" class="btn-amazon btn-white"><i class="fas fa-star"></i> Write a product review</a>
                                        <?php if (($order['payment_status'] ?? 'unpaid') !== 'paid'): ?>
                                            <button onclick="showQR('<?= $order['total_price'] ?>', 'ORD-<?= $order['id'] ?>')" class="btn-amazon btn-yellow">
                                                <i class="fas fa-qrcode"></i> Pay with QR
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Existing Table View for Admin/Sellers -->
                    <table class="order-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>All Products</th>
                                <th>Order By</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <?php 
                                    $img_url = $order['primary_image'] ?? $order['fallback_image'] ?? 'https://via.placeholder.com/100?text=No+Data';
                                ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; flex-direction: column;">
                                            <span style="font-weight: 800; color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
                                                <i class="fas fa-hashtag" style="font-size: 0.7rem; color: var(--primary);"></i>
                                                ORD-<?php echo $order['id']; ?>
                                            </span>
                                            <span style="font-size: 0.7rem; color: var(--text-light); margin-top: 4px;">Verified Order</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="prod-info" style="display: flex; align-items: center; gap: 15px;">
                                            <img src="<?php echo htmlspecialchars($img_url); ?>" class="prod-img" style="width: 50px; height: 50px; border-radius: 8px; border: 1px solid var(--border); background: #fff;" onerror="this.src='https://via.placeholder.com/100'">
                                            <div>
                                                <span style="font-weight: 700; color: var(--text-dark); display: block;"><?php echo htmlspecialchars($order['product_name']); ?></span>
                                                <span style="font-size: 0.7rem; color: var(--text-light);">SKU: <?php echo htmlspecialchars($order['sku']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-weight: 600; color: var(--text-dark); display: block;"><?php echo htmlspecialchars($order['customer_name'] ?? 'Customer'); ?></span>
                                        <span style="font-size: 0.75rem; color: var(--primary); font-weight: 700; text-transform: uppercase;">
                                            <i class="fas fa-store"></i> <?php echo htmlspecialchars($order['channel'] ?? 'Direct'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-weight: 800; color: var(--text-dark);">₹<?php echo number_format($order['total_price'], 2); ?></span>
                                    </td>
                                    <td>
                                        <div style="position: relative;">
                                            <select class="status-pill status-<?php echo strtolower($order['status']); ?>" 
                                                    onchange="handleStatusChange(<?php echo $order['id']; ?>, this)" 
                                                    style="appearance: none; -webkit-appearance: none; cursor: pointer; padding-right: 25px; outline: none; border: 1px solid var(--border); background: #fff; border-radius: 8px; padding: 6px 12px; color: var(--text-dark); font-size: 0.75rem;">
                                                <?php 
                                                $all_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
                                                foreach($all_statuses as $st): ?>
                                                    <option value="<?php echo $st; ?>" <?php echo strtolower($order['status']) == $st ? 'selected' : ''; ?>>
                                                        <?php echo ucfirst($st); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <i class="fas fa-chevron-down" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.6rem; pointer-events: none; opacity: 0.5;"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-pill status-<?php echo strtolower($order['payment_status'] ?? 'unpaid'); ?>" style="font-size: 0.7rem; font-weight: 800;">
                                            <?php echo ucfirst(htmlspecialchars($order['payment_status'] ?? 'Unpaid')); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="color: var(--text-light); font-size: 0.85rem;"><i class="far fa-calendar-alt"></i> <?php echo date('d M, Y', strtotime($order['order_date'])); ?></span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                            <a href="invoice.php?id=<?= $order['id'] ?>" target="_blank" class="btn-action" style="padding: 6px 10px; font-size: 0.7rem; border-radius: 8px; color: var(--primary); background: rgba(37, 99, 235, 0.1); border: 1px solid rgba(37, 99, 235, 0.2);">
                                                <i class="fas fa-file-invoice"></i> Slip
                                            </a>
                                            <a href="https://wa.me/?text=Hi%20<?= urlencode($order['customer_name'] ?? 'Customer') ?>,%20regarding%20your%20WalkOn%20order%20ORD-<?= $order['id'] ?>..." target="_blank" class="btn-action" style="padding: 6px 10px; font-size: 0.7rem; border-radius: 8px; color: #16a34a; background: rgba(22, 163, 74, 0.1); border: 1px solid rgba(22, 163, 74, 0.2);">
                                                <i class="fab fa-whatsapp"></i> Chat
                                            </a>
                                            <button onclick="updateTracking(<?= $order['id'] ?>, '<?= $order['tracking_number'] ?? '' ?>')" class="btn-action" style="padding: 6px 10px; font-size: 0.7rem; border-radius: 8px; color: #475569; background: #f1f5f9; border: 1px solid #cbd5e1;">
                                                <i class="fas fa-truck"></i> ID
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-bag" style="font-size: 3rem; color: var(--border); margin-bottom: 20px; display: block;"></i>
                    <h3 style="color: white; margin-bottom: 10px;">No orders discovered</h3>
                    <p style="color: var(--text-light);">Your orders will appear here once you make a purchase.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-card">
                <a href="index.php" class="footer-logo">
                    <img src="assets/shoe_logo_green.png" alt="WalkOn" style="height: 32px;">
                    <div class="brand-text">WALK<span style="color:#10b981">ON</span></div>
                </a>
                <p class="footer-desc">The ultimate multi-channel e-commerce solution for premium footwear. Scaling your shoe business globally.</p>
                <div class="contact-info">
                    <div class="contact-item"><i class="fas fa-envelope"></i> support@walkon.com</div>
                    <div class="contact-item"><i class="fas fa-phone"></i> +91 90745 85775</div>
                </div>
            </div>
            <div class="footer-nav-grid">
                <div class="footer-col">
                    <h4>Internal</h4>
                    <ul class="footer-links">
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="my_listings.php">My Listings</a></li>
                        <li><a href="add_listing.php">Add Shoe</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Management</h4>
                    <ul class="footer-links">
                        <li><a href="my_orders.php">View Orders</a></li>
                        <li><a href="marketplaces.php">Channels</a></li>
                        <li><a href="sync_status.php">Sync Engine</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Support</h4>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Terms of Use</a></li>
                        <li><a href="#">Privacy</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- QR Modal Container -->
    <div id="qrModal" class="qr-modal">
        <div class="qr-content">
            <i class="fas fa-times qr-close" onclick="hideQR()"></i>
            <h2 style="margin-bottom: 5px; color: white;">Scan to Pay</h2>
            <p style="color: var(--text-light); font-size: 0.9rem;" id="qrDesc">Complete payment for Order</p>
            
            <div class="qr-code-img">
                <img id="qrImg" src="" style="width: 100%; height: 100%; border-radius: 10px;" alt="Payment QR">
            </div>
            
            <h3 id="qrAmount" style="color: var(--primary); font-size: 1.8rem; font-weight: 800; margin: 10px 0;">₹ 0</h3>
            <div class="upi-id">Merchant VPA: <strong style="color: white;">walkon@okaxis</strong></div>
            
            <p style="margin-top: 30px; font-size: 0.8rem; color: var(--text-light);">Scan with any UPI App (GPay, PhonePe, Paytm)</p>
        </div>
    </div>

    <script>
        function showQR(amount, orderId) {
            const upiId = 'walkon@okaxis';
            const name = 'WalkOn Store';
            const cleanAmount = parseFloat(amount).toFixed(2);
            
            // Generate UPI payment string
            const upiString = `upi://pay?pa=${upiId}&pn=${encodeURIComponent(name)}&am=${cleanAmount}&cu=INR&tn=${encodeURIComponent('Payment for ' + orderId)}`;
            
            // Using Google Charts API for QR Generation
            const qrUrl = `https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=${encodeURIComponent(upiString)}&choe=UTF-8`;
            
            document.getElementById('qrImg').src = qrUrl;
            document.getElementById('qrAmount').innerText = `₹ ${parseFloat(amount).toLocaleString('en-IN')}`;
            document.getElementById('qrDesc').innerText = `Complete payment for ${orderId}`;
            document.getElementById('qrModal').classList.add('active');
        }

        function hideQR() {
            document.getElementById('qrModal').classList.remove('active');
        }

        function handleStatusChange(orderId, selectElement) {
            const newStatus = selectElement.value;
            let trackingNumber = null;
            
            if (newStatus === 'shipped') {
                trackingNumber = prompt("Please enter the tracking number (optional):");
            }
            
            updateStatus(orderId, newStatus, trackingNumber, selectElement);
        }

        function updateTracking(orderId, currentTracking) {
            const newTracking = prompt("Enter or update Tracking ID for Order #ORD-" + orderId + ":", currentTracking || '');
            if (newTracking !== null && newTracking !== currentTracking) {
                const payload = { 
                    order_id: orderId, 
                    tracking_number: newTracking 
                };
                
                fetch('update_order_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Tracking updated successfully!');
                        location.reload();
                    } else {
                        alert(data.message || 'Error updating tracking.');
                    }
                }).catch(err => {
                    alert('Connection error.');
                });
            }
        }

        function updateStatus(orderId, newStatus, trackingNumber, selectElement) {
            // Optimistic UI update
            selectElement.className = 'status-pill status-' + newStatus.toLowerCase();
            
            const payload = { 
                order_id: orderId, 
                status: newStatus 
            };
            
            if (trackingNumber) {
                payload.tracking_number = trackingNumber;
            }
            
            fetch('update_order_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Flash success effect
                    selectElement.style.boxShadow = '0 0 15px rgba(37, 99, 235, 0.4)';
                    setTimeout(() => {
                        selectElement.style.boxShadow = '';
                        if (trackingNumber) location.reload(); // Reload to show tracking number
                    }, 1000);
                } else {
                    alert(data.message);
                    location.reload(); 
                }
            })
            .catch(err => {
                console.error('Status update error:', err);
                alert('Connection error. Could not update status.');
                location.reload();
            });
        }

        // Download receipt function
        function downloadReceipt(orderId) {
            // In a real implementation, this would fetch the receipt from the server
            alert(`Downloading receipt for Order #ORD-${orderId}...\n\nThis would generate a PDF receipt with:\n- Order details\n- Payment information\n- Transaction ID\n- Customer details`);
            
            // Real implementation would be:
            // window.location.href = `download_receipt.php?order_id=${orderId}`;
        }

        // Close on background click
        window.onclick = function(event) {
            const modal = document.getElementById('qrModal');
            if (event.target == modal) hideQR();
        }
    </script>

</body>
</html>
