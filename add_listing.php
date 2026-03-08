<?php
session_start();
include 'config.php';

// Auth & Role Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'store', 'entrepreneur', 'store_owner'])) {
    header("Location: login.php");
    exit();
}

    // Fetch seller information for the current user
    $stmt_seller = $pdo->prepare("SELECT s.id FROM sellers s JOIN users u ON s.email = u.email WHERE u.id = ?");
    $stmt_seller->execute([$_SESSION['user_id']]);
    $seller = $stmt_seller->fetch();
    
    $seller_id = $seller ? $seller['id'] : -1;
    $seller_ref_id = $seller_id; // For consistency in insert

    // Fetch Categories for Dropdown
    $cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $db_categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Subcategories for Dropdown (grouped by category_id for JS)
    $sub_stmt = $pdo->query("SELECT * FROM sub_categories ORDER BY name ASC");
    $db_subcategories = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Materials (with Self-Healing Logic)
    try {
        $mat_stmt = $pdo->query("SELECT * FROM materials ORDER BY name ASC");
        $materials = $mat_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        if ($e->getCode() == '42S02') { // Table or view not found
            // Auto-create table
            $pdo->exec("CREATE TABLE IF NOT EXISTS materials (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE
            )");
            // Seed defaults
            $defaults = ['Leather', 'Canvas', 'Mesh', 'Suede', 'Synthetic', 'Rubber', 'Foam', 'Knit', 'Nylon', 'Velvet'];
            $stmt = $pdo->prepare("INSERT IGNORE INTO materials (name) VALUES (?)");
            foreach ($defaults as $m) $stmt->execute([$m]);
            
            // Retry fetch
            $mat_stmt = $pdo->query("SELECT * FROM materials ORDER BY name ASC");
            $materials = $mat_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // Fetch Marketplaces (New)
    try {
        $market_stmt = $pdo->query("SELECT * FROM marketplaces WHERE is_active = 1 ORDER BY display_order ASC");
        $marketplaces = $market_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $marketplaces = []; 
    }

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    // Sanitize inputs to allow NULLs for database compatibility
    $category = !empty($_POST['category']) ? $_POST['category'] : null;
    $subcategory = !empty($_POST['subcategory']) ? $_POST['subcategory'] : null;
    $sizes = trim($_POST['sizes'] ?? '');
    $colors = trim($_POST['colors'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 0);
    
    // Channels
    $channels_arr = isset($_POST['channels']) ? $_POST['channels'] : [];
    $channels_str = implode(',', $channels_arr);
    
    // 1. Handle URL Images (existing)
    $image_urls = [];
    if (!empty($_POST['image_urls_data'])) {
        $decoded = json_decode($_POST['image_urls_data'], true);
        if (is_array($decoded)) $image_urls = $decoded;
    }

    // 2. Handle File Uploads (NEW)
    if (isset($_FILES['product_images'])) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        foreach ($_FILES['product_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['product_images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['product_images']['name'][$key];
                $file_tmp = $_FILES['product_images']['tmp_name'][$key];
                
                // Simple validation check (images only)
                $mime = null;
                if (class_exists('finfo')) {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($file_tmp);
                } elseif (function_exists('mime_content_type')) {
                    $mime = mime_content_type($file_tmp);
                }

                if ($mime && strpos($mime, 'image') === 0) {
                    // Generate unique name
                    $new_name = uniqid('img_', true) . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
                    $dest = $upload_dir . $new_name;
                    
                    if (move_uploaded_file($file_tmp, $dest)) {
                        $image_urls[] = $dest;
                    }
                }
            }
        }
    }

    $images_json = json_encode($image_urls);

    // Validation
    if (empty($product_name) || empty($sku) || $price <= 0) {
        $error_msg = "Please fill in all required fields (Name, SKU, Price).";
    } else {
        try {
            // Check for duplicate SKU in normalized schema with ownership check
            $stmt = $pdo->prepare("
                SELECT ps.id, pb.seller_id, pb.id as product_id 
                FROM product_skus ps
                JOIN product_base pb ON ps.product_id = pb.id
                WHERE ps.sku = ?
            ");
            $stmt->execute([$sku]);
            $existing_sku = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_sku) {
                // If I own it, offer to edit. If someone else owns it, tell to change.
                if ($seller_id != -1 && $existing_sku['seller_id'] == $seller_id) {
                    $error_msg = "You already have a product with this SKU. <a href='edit_listing.php?id=" . $existing_sku['product_id'] . "' class='text-blue-500 hover:underline'>Edit it here</a>.";
                } else {
                    $error_msg = "This SKU is already taken by another seller. Please use a unique SKU (e.g. " . $sku . "-" . rand(100,999) . ").";
                }
            } else {
                // Ensure seller record exists (Bridge between users and sellers)
                $stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt_user->execute([$user_id]);
                $curr_user = $stmt_user->fetch();

                $seller_ref_id = $user_id;

                if ($curr_user) {
                    // Check if this user exists in sellers table
                    $stmt_chk = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
                    $stmt_chk->execute([$curr_user['email']]);
                    $seller_exists = $stmt_chk->fetch();

                    if ($seller_exists) {
                        $seller_ref_id = $seller_exists['id'];
                    } else {
                        // Create seller record from user record
                        $full_name = $curr_user['first_name'] . ' ' . $curr_user['last_name'];
                        $stmt_new_seller = $pdo->prepare("INSERT INTO sellers (name, email, password, business_name, created_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmt_new_seller->execute([
                            $full_name, 
                            $curr_user['email'], 
                            $curr_user['password'],
                            'My Store' // Default business name
                        ]);
                        $seller_ref_id = $pdo->lastInsertId();
                    }

                    // CRITICAL FIX: Update session immediately so My Listings can find this seller
                    $_SESSION['seller_id'] = $seller_ref_id;
                    $seller_id = $seller_ref_id; // Ensure current page queries use the new ID
                }

                // Handle "Other" Category
                if ($category === 'other' && !empty($_POST['new_category'])) {
                    $new_cat_name = trim($_POST['new_category']);
                    // Check if category already exists to avoid duplicates
                    $stmt_check_cat = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
                    $stmt_check_cat->execute([$new_cat_name]);
                    $existing_cat = $stmt_check_cat->fetch();
                    
                    if ($existing_cat) {
                        $category = $existing_cat['id'];
                    } else {
                        $stmt_new_cat = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
                        $stmt_new_cat->execute([$new_cat_name]);
                        $category = $pdo->lastInsertId();
                    }
                }

                // Handle "Other" Subcategory
                if ($subcategory === 'other' && !empty($_POST['new_subcategory']) && $category) {
                    $new_sub_name = trim($_POST['new_subcategory']);
                    // Check if subcategory already exists for this category
                    $stmt_check_sub = $pdo->prepare("SELECT id FROM sub_categories WHERE name = ? AND category_id = ?");
                    $stmt_check_sub->execute([$new_sub_name, $category]);
                    $existing_sub = $stmt_check_sub->fetch();

                    if ($existing_sub) {
                        $subcategory = $existing_sub['id'];
                    } else {
                        $stmt_new_sub = $pdo->prepare("INSERT INTO sub_categories (category_id, name) VALUES (?, ?)");
                        $stmt_new_sub->execute([$category, $new_sub_name]);
                        $subcategory = $pdo->lastInsertId();
                    }
                }

                // Insert into Product Base
                // All new products start as 'pending' for brand verification
                $sql_base = "INSERT INTO product_base (seller_id, name, category_id, sub_category_id, status, approval_status, created_at) 
                             VALUES (?, ?, ?, ?, 'published', 'pending', NOW())";
                $stmt_base = $pdo->prepare($sql_base);
                $stmt_base->execute([$seller_ref_id, $product_name, $category, $subcategory]);
                $new_product_id = $pdo->lastInsertId();

                // Insert SKU
                $pdo->prepare("INSERT INTO product_skus (product_id, sku) VALUES (?, ?)")
                    ->execute([$new_product_id, $sku]);

                // Insert Price
                $pdo->prepare("INSERT INTO product_prices (product_id, price) VALUES (?, ?)")
                    ->execute([$new_product_id, $price]);

                // Insert Stock
                $pdo->prepare("INSERT INTO product_stock (product_id, quantity) VALUES (?, ?)")
                    ->execute([$new_product_id, $quantity]);

                // Insert Description
                if ($description) {
                    $pdo->prepare("INSERT INTO product_descriptions (product_id, content) VALUES (?, ?)")
                        ->execute([$new_product_id, $description]);
                }

                // Insert Sizes
                if ($sizes) {
                    $size_arr = explode(',', $sizes);
                    $stmt_sz = $pdo->prepare("INSERT INTO product_sizes (product_id, size_value) VALUES (?, ?)");
                    foreach ($size_arr as $sz) {
                        $stmt_sz->execute([$new_product_id, trim($sz)]);
                    }
                }

                // Insert Colors
                if ($colors) {
                    $color_arr = explode(',', $colors);
                    $stmt_cl = $pdo->prepare("INSERT INTO product_colors (product_id, color_name) VALUES (?, ?)");
                    foreach ($color_arr as $cl) {
                        $stmt_cl->execute([$new_product_id, trim($cl)]);
                    }
                }

                // Insert Media
                if (!empty($image_urls)) {
                    $stmt_media = $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary) VALUES (?, ?, ?)");
                    foreach ($image_urls as $idx => $url) {
                        $is_primary = ($idx === 0) ? 1 : 0;
                        $stmt_media->execute([$new_product_id, $url, $is_primary]);
                    }
                }
                
                // Insert Channels
                if (!empty($channels_arr)) {
                    $stmt_chan = $pdo->prepare("INSERT INTO product_channels (product_id, channel_name) VALUES (?, ?)");
                    foreach ($channels_arr as $chan) {
                        $stmt_chan->execute([$new_product_id, $chan]);
                    }
                }

                // Insert Product Specs (New)
                $brand_id = !empty($_POST['brand_id']) ? $_POST['brand_id'] : null;
                $heel_height = $_POST['heel_height'] ?? '';
                $outer_material = $_POST['outer_material'] ?? '';
                $season = $_POST['season'] ?? '';
                $gender = $_POST['gender'] ?? '';
                $occasion = $_POST['occasion'] ?? '';

                $stmt_specs = $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, gender, heel_height, outer_material, season, occasion) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt_specs->execute([$new_product_id, $brand_id, $gender, $heel_height, $outer_material, $season, $occasion]);
                
                $success_msg = "Product listed successfully!";

            }
        } catch (PDOException $e) {
            $error_msg = "Database Error: " . $e->getMessage();
        }
    }
}

// Fetch Authorized Brands for this seller (with Self-Healing)
try {
    $brands_stmt = $pdo->prepare("
        SELECT b.id, b.name 
        FROM brands b
        JOIN brand_approvals ba ON b.id = ba.brand_id
        WHERE ba.seller_id = ? AND ba.status = 'approved'
        ORDER BY b.name ASC
    ");
    $brands_stmt->execute([$seller_id]);
    $brands = $brands_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fallback: If no authorized brands, show all brands for now (to avoid empty dropdowns)
    if (empty($brands)) {
        $brands = $pdo->query("SELECT id, name FROM brands ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    if ($e->getCode() == '42S02') { // Table not found
        // Create the missing table
        $pdo->exec("CREATE TABLE IF NOT EXISTS brand_approvals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            brand_id INT NOT NULL,
            seller_id INT NOT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE,
            FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE
        )");
        // Fallback to all brands
        $brands = $pdo->query("SELECT id, name FROM brands ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $brands = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Listing - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --text-dark: #0f172a;
            --text-light: #64748b;
            --bg-light: #f8fafc;
            --white: #ffffff;
            --border: #e2e8f0;
        }
        
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
        body { background: var(--bg-light); color: var(--text-dark); padding: 0; display: flex; flex-direction: column; min-height: 100vh; }
        .container { flex: 1; width: 100%; max-width: 1200px; margin: 0 auto; padding: 40px 20px; }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h1 { font-size: 32px; font-weight: 700; color: var(--text-dark); margin-bottom: 10px; }
        .header p { color: var(--text-light); }

        /* Progress Bar */
        .progress-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }
        .progress-container::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 4px;
            background: #e0e0e0;
            z-index: 0;
            transform: translateY(-50%);
        }
        .progress-step {
            position: relative;
            z-index: 1;
            background: var(--bg-light);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 3px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--text-light);
            transition: 0.3s;
        }
        .progress-step.active {
            border-color: var(--primary);
            background: var(--primary);
            color: var(--white);
        }
        .progress-step.completed {
            border-color: var(--primary);
            background: var(--primary);
            color: var(--white);
        }

        /* Form Card */
        .form-card {
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .form-step { display: none; }
        .form-step.active { display: block; animation: fadeIn 0.5s ease; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group { margin-bottom: 25px; }
        .form-label { display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark); }
        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 15px;
            transition: 0.3s;
        }
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(40,167,69,0.1);
        }
        textarea.form-control { resize: vertical; min-height: 120px; }

        /* Media Upload */
        .upload-area {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            background: #fafafa;
        }
        .upload-area:hover { border-color: var(--primary); background: #f0fdf4; }
        .upload-area i { font-size: 40px; color: var(--text-light); margin-bottom: 15px; }
        .upload-area p { color: var(--text-light); font-size: 14px; }
        
        .url-input-group {
            display: flex; gap: 10px; margin-top: 20px;
        }
        .btn-add {
            background: var(--text-dark); color: white; border: none; padding: 0 20px; border-radius: 10px; cursor: pointer;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .preview-item {
            position: relative;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .preview-item img { width: 100%; height: 100%; object-fit: cover; }
        .remove-img {
            position: absolute; top: 5px; right: 5px;
            background: rgba(0,0,0,0.5); color: white;
            width: 20px; height: 20px; border-radius: 50%;
            text-align: center; line-height: 20px; font-size: 12px;
            cursor: pointer;
        }

        /* Channels */
        .channel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
        }
        .channel-card {
            background: var(--white);
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
        }
        .channel-card.selected {
            border-color: var(--primary);
            background: #f0fdf4;
        }
        .channel-card i { font-size: 24px; margin-bottom: 10px; color: var(--text-dark); }
        .channel-card span { display: block; font-size: 14px; font-weight: 500; }
        
        .channel-checkbox { display: none; }

        /* Buttons */
        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .btn {
            padding: 14px 30px;
            border-radius: 50px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-secondary { background: #e9ecef; color: var(--text-dark); }
        .btn-secondary:hover { background: #dee2e6; }
        
        .btn-primary { background: var(--primary); color: var(--white); }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(40,167,69,0.3); }

        .alert {
            padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        
        .nav-back { display: inline-block; margin-top: 20px; color: var(--text-light); text-decoration: none; }

        .hidden-input { display: none; margin-top: 10px; }
        .visible-input { display: block; animation: slideDown 0.3s ease-out; }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Back Button */
        .back-btn-container {
            max-width: 900px;
            margin: 20px auto -20px;
            padding: 0 20px;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .back-btn:hover {
            color: var(--primary);
            transform: translateX(-5px);
        }
    </style>
</head>
<body>

<div class="back-btn-container">
    <a href="javascript:history.back()" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="container">
    <div class="header">
        <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 15px;">
            <img src="assets/shoe_logo_green.png" alt="WalkOn" style="height: 48px; width: auto;">
            <span style="font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; color: var(--text-dark); letter-spacing: 0;">WALK<span style="color:var(--primary)">ON</span></span>
        </div>
        <h1>New Listing</h1>
        <p>Create a listing once, sell everywhere.</p>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?> <a href="dashboard.php">Go to Dashboard</a></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-error"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="progress-container">
        <div class="progress-step active" id="step1-indicator">1</div>
        <div class="progress-step" id="step2-indicator">2</div>
        <div class="progress-step" id="step3-indicator">3</div>
    </div>

    <form method="POST" id="listingForm" enctype="multipart/form-data">
        <input type="hidden" name="image_urls_data" id="image_urls_data">

        <!-- STEP 1: Details -->
        <div class="form-card form-step active" id="step1">
            <h3 style="margin-bottom:20px;">Product Details</h3>
            
            <div class="form-group">
                <label class="form-label">Product Name</label>
                <input type="text" class="form-control" name="product_name" placeholder="e.g. Nike Air Zoom Pegasus 39">
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" placeholder="Describe your product..."></textarea>
            </div>

            <div class="row">
                <div class="form-group col-md-6">
                    <label class="form-label">Category</label>
                    <select class="form-control" name="category" id="categorySelect" onchange="filterSubcategories(); toggleOtherInput('categorySelect', 'newCategoryInput')">
                        <option value="">Select Category</option>
                        <?php foreach ($db_categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                        <option value="other" style="font-weight: bold; color: var(--primary);">+ Other (Add New)</option>
                    </select>
                    <div id="newCategoryInput" class="hidden-input">
                        <input type="text" class="form-control" name="new_category" placeholder="Enter New Category Name">
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label" for="sub_category">Sub Category</label>
                    <select class="form-control" name="subcategory" id="subcategorySelect" onchange="toggleOtherInput('subcategorySelect', 'newSubcategoryInput')" required>
                        <option value="">-- Select Category First --</option>
                    </select>
                    <div id="newSubcategoryInput" class="hidden-input">
                        <input type="text" class="form-control" name="new_subcategory" placeholder="Enter New Sub-Category Name">
                    </div>
                </div>
            </div>

            <!-- New Product Specs Section -->
            <h4 style="margin:20px 0 15px; color:#475569;">Specifications</h4>
            <div class="row">
                <div class="form-group col-md-6">
                    <label class="form-label">Brand</label>
                    <select class="form-control" name="brand_id">
                        <option value="">Select Brand</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label class="form-label">Gender</label>
                    <select class="form-control" name="gender">
                        <option value="">Select Gender</option>
                        <option value="Men">Men</option>
                        <option value="Women">Women</option>
                        <option value="Boys">Boys</option>
                        <option value="Girls">Girls</option>
                        <option value="Kids">Kids</option>
                        <option value="Babies">Babies</option>
                        <option value="Unisex">Unisex</option>
                    </select>
                </div>
            </div>
            
                <div class="form-group col-md-3">
                    <label class="form-label">Outer Material</label>
                    <select class="form-control" name="outer_material">
                        <option value="">Select Material</option>
                        <?php foreach ($materials as $m): ?>
                            <option value="<?php echo htmlspecialchars($m['name']); ?>"><?php echo htmlspecialchars($m['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label class="form-label">Heel Height</label>
                    <input type="text" class="form-control" name="heel_height" placeholder="e.g. Flat, 2 inches">
                </div>
                <div class="form-group col-md-3">
                    <label class="form-label">Season</label>
                    <select class="form-control" name="season">
                        <option value="">Select Season</option>
                        <option value="All Season">All Season</option>
                        <option value="Summer">Summer</option>
                        <option value="Winter">Winter</option>
                        <option value="Spring">Spring</option>
                        <option value="Autumn">Autumn</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label class="form-label">Occasion</label>
                    <select class="form-control" name="occasion">
                        <option value="">Select Occasion</option>
                        <option value="Casual">Casual</option>
                        <option value="Sports">Sports</option>
                        <option value="Formal">Formal</option>
                        <option value="Party">Party</option>
                        <option value="Ethnic">Ethnic</option>
                        <option value="Outdoor">Outdoor</option>
                    </select>
                </div>
            </div>

            <script>
            // Store all subcategories in a JS object
            const allSubcategories = [
                <?php foreach ($db_subcategories as $sub): ?>
                { id: "<?php echo $sub['id']; ?>", name: "<?php echo addslashes($sub['name']); ?>", category_id: "<?php echo $sub['category_id']; ?>" },
                <?php endforeach; ?>
            ];

            function filterSubcategories() {
                const catId = document.getElementById('categorySelect').value;
                const subSelect = document.getElementById('subcategorySelect');
                
                console.log("Filtering subcategories for Category ID:", catId);
                
                // Clear and add default
                subSelect.innerHTML = '<option value="">Select Subcategory</option>';

                if (!catId) {
                    subSelect.innerHTML = '<option value="">-- Select Category First --</option>';
                    subSelect.disabled = true;
                    subSelect.style.opacity = '0.6';
                    subSelect.style.cursor = 'not-allowed';
                    return;
                }
                
                subSelect.disabled = false;
                subSelect.style.opacity = '1';
                subSelect.style.cursor = 'default';
                
                let foundCount = 0;
                allSubcategories.forEach(sub => {
                    if (sub.category_id == catId) {
                        const opt = document.createElement('option');
                        opt.value = sub.id;
                        opt.textContent = sub.name;
                        subSelect.appendChild(opt);
                        foundCount++;
                    }
                });

                console.log("Found subcategories:", foundCount);

                if (foundCount === 0 && catId !== 'other') {
                    subSelect.innerHTML = '<option value="">No subcategories available</option>';
                    subSelect.disabled = true;
                }

                // Always add "Other" option if a category is selected (including 'other' category)
                const otherOpt = document.createElement('option');
                otherOpt.value = 'other';
                otherOpt.textContent = '+ Other (Add New)';
                otherOpt.style.fontWeight = 'bold';
                otherOpt.style.color = 'var(--primary)';
                subSelect.appendChild(otherOpt);
                
                if (catId === 'other') {
                    subSelect.disabled = false;
                    subSelect.style.opacity = '1';
                    subSelect.style.cursor = 'default';
                }
            }

            function toggleOtherInput(selectId, inputId) {
                const select = document.getElementById(selectId);
                const inputDiv = document.getElementById(inputId);
                
                if (select.value === 'other') {
                    inputDiv.classList.add('visible-input');
                    inputDiv.querySelector('input').required = true;
                } else {
                    inputDiv.classList.remove('visible-input');
                    inputDiv.querySelector('input').required = false;
                }
            }

            window.addEventListener('DOMContentLoaded', filterSubcategories);
            </script>

            <div class="btn-group" style="justify-content: flex-end;">
                <a href="my_listings.php" class="btn btn-secondary" style="margin-right:10px; text-decoration:none;">Cancel</a>
                <button type="button" class="btn btn-primary" onclick="nextStep(1)">Next Step</button>
            </div>
        </div>

        <!-- STEP 2: Media & Pricing -->
        <div class="form-card form-step" id="step2">
            <h3 style="margin-bottom:20px;">Media & Pricing</h3>

            <div class="form-group">
                <label class="form-label">Photos</label>
                <div class="upload-area">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Drag & Drop files here or Paste URL</p>
                    
                    <!-- File Upload Input -->
                    <input type="file" name="product_images[]" multiple accept="image/*" class="form-control" style="margin-bottom:10px; cursor:pointer;">
                    
                    <div class="url-input-group">
                        <input type="text" id="urlInput" class="form-control" placeholder="Optional: Add image via URL">
                        <button type="button" class="btn-add" onclick="addImage()">Add</button>
                    </div>
                </div>
                <div class="preview-grid" id="previewGrid"></div>
            </div>

            <div class="row">
                <div class="form-group col-md-6">
                    <label class="form-label">Price (₹)</label>
                    <input type="number" step="0.01" class="form-control" name="price" placeholder="0.00">
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control" name="quantity" placeholder="10">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6">
                    <label class="form-label">Sizes</label>
                    <input type="text" class="form-control" name="sizes" placeholder="e.g. 7, 8, 9, 10">
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">Colors</label>
                    <input type="text" class="form-control" name="colors" placeholder="e.g. Black, White, Red">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">SKU</label>
                <input type="text" class="form-control" name="sku" placeholder="Unique Product Code">
            </div>

            <div class="btn-group">
                <div>
                    <button type="button" class="btn btn-secondary" onclick="prevStep(2)">Back</button>
                    <a href="my_listings.php" class="btn btn-secondary" style="margin-left:10px; text-decoration:none; background:#f8d7da; color:#721c24;">Cancel</a>
                </div>
                <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next Step</button>
            </div>
        </div>

        <!-- STEP 3: Channels -->
        <div class="form-card form-step" id="step3">
            <h3 style="margin-bottom:20px;">Sync to Channels</h3>
            <p style="margin-bottom:20px; color:#666;">Select the platforms you want to instantly publish this listing to.</p>

            <div class="channel-grid">
                <?php foreach ($marketplaces as $m): ?>
                    <label class="channel-card" onclick="toggleChannel(this)">
                        <input type="checkbox" name="channels[]" value="<?php echo htmlspecialchars($m['name']); ?>" class="channel-checkbox">
                        <?php if (!empty($m['logo_url'])): ?>
                            <img src="<?php echo htmlspecialchars($m['logo_url']); ?>" alt="<?php echo htmlspecialchars($m['name']); ?>" style="height: 30px; margin-bottom: 10px;">
                        <?php else: ?>
                            <i class="fas fa-store"></i>
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($m['name']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="btn-group">
                <div>
                    <button type="button" class="btn btn-secondary" onclick="prevStep(3)">Back</button>
                    <a href="my_listings.php" class="btn btn-secondary" style="margin-left:10px; text-decoration:none; background:#f8d7da; color:#721c24;">Cancel</a>
                </div>
                <button type="submit" class="btn btn-primary">Publish & Sync</button>
            </div>
        </div>
    </form>
    
    <div style="text-align:center; margin-top: 30px; display:flex; justify-content:center; gap:20px;">
        <a href="my_listings.php" class="nav-back">Cancel and return to My Listings</a>
        <a href="dashboard.php" class="nav-back" style="color:var(--primary); font-weight:600;"><i class="fas fa-home"></i> Back to Dashboard</a>
    </div>
</div>

<script>
    let currentStep = 1;
    let images = [];

    function nextStep(step) {
        // Step-by-step validation
        if (step === 1) {
            const name = document.querySelector('input[name="product_name"]').value;
            const category = document.querySelector('select[name="category"]').value;
            const subcategory = document.querySelector('select[name="subcategory"]').value;
            const gender = document.querySelector('select[name="gender"]').value;
            
            if (!name) { alert("Please enter a product name"); return; }
            if (!category) { alert("Please select a category"); return; }
            if (category === 'other' && !document.querySelector('input[name="new_category"]').value.trim()) {
                alert("Please enter the new category name"); return;
            }
            if (!subcategory) { alert("Please select a subcategory"); return; }
            if (subcategory === 'other' && !document.querySelector('input[name="new_subcategory"]').value.trim()) {
                alert("Please enter the new sub-category name"); return;
            }
            if (!gender) { alert("Please select a gender"); return; }
        }
        
        if (step === 2) {
            const price = document.querySelector('input[name="price"]').value;
            if (!price || price <= 0) { alert("Please enter a valid price"); return; }
        }
        
        document.getElementById(`step${step}`).classList.remove('active');
        document.getElementById(`step${step}-indicator`).classList.add('completed');
        document.getElementById(`step${step}-indicator`).classList.remove('active');
        
        currentStep = step + 1;
        document.getElementById(`step${currentStep}`).classList.add('active');
        document.getElementById(`step${currentStep}-indicator`).classList.add('active');
    }

    function prevStep(step) {
        document.getElementById(`step${step}`).classList.remove('active');
        document.getElementById(`step${step}-indicator`).classList.remove('active');
        
        currentStep = step - 1;
        document.getElementById(`step${currentStep}`).classList.add('active');
        document.getElementById(`step${currentStep}-indicator`).classList.remove('completed');
        document.getElementById(`step${currentStep}-indicator`).classList.add('active');
    }

    // Image Handling
    function addImage() {
        const urlInput = document.getElementById('urlInput');
        const url = urlInput.value.trim();
        if (url) {
            images.push(url);
            renderImages();
            urlInput.value = '';
        }
    }

    function removeImage(index) {
        images.splice(index, 1);
        renderImages();
    }

    function renderImages() {
        const grid = document.getElementById('previewGrid');
        const hiddenInput = document.getElementById('image_urls_data');
        grid.innerHTML = '';
        
        images.forEach((url, index) => {
            const div = document.createElement('div');
            div.className = 'preview-item';
            div.innerHTML = `
                <img src="${url}" onerror="this.src='https://via.placeholder.com/100'">
                <div class="remove-img" onclick="removeImage(${index})">&times;</div>
            `;
            grid.appendChild(div);
        });
        
        hiddenInput.value = JSON.stringify(images);
    }

    // Channel Selection UI
    function toggleChannel(card) {
        const checkbox = card.querySelector('input');
        if (!checkbox) return;
        
        // Wait for the change to propagate
        setTimeout(() => {
            if (checkbox.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        }, 10);
    }
</script>

</div>

</body>
</html>
