<?php
session_start();
include 'config.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
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

    // Fetch Materials
    $mat_stmt = $pdo->query("SELECT * FROM materials ORDER BY name ASC");
    $materials = $mat_stmt->fetchAll(PDO::FETCH_ASSOC);

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? '';
    $subcategory = trim($_POST['subcategory'] ?? '');
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
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file_tmp);
                if (strpos($mime, 'image') === 0) {
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
            // Check for duplicate SKU in normalized schema
            $stmt = $pdo->prepare("SELECT id FROM product_skus WHERE sku = ?");
            $stmt->execute([$sku]);
            if ($stmt->fetch()) {
                $error_msg = "SKU already exists. Please use a unique SKU.";
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
                }

                // Insert into Product Base
                // Now expecting IDs directly from the form
                $sql_base = "INSERT INTO product_base (seller_id, name, category_id, sub_category_id, status, created_at) 
                             VALUES (?, ?, ?, ?, 'published', NOW())";
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
                    $stmt_sz = $pdo->prepare("INSERT INTO product_sizes (product_id, size) VALUES (?, ?)");
                    foreach ($size_arr as $sz) {
                        $stmt_sz->execute([$new_product_id, trim($sz)]);
                    }
                }

                // Insert Colors
                if ($colors) {
                    $color_arr = explode(',', $colors);
                    $stmt_cl = $pdo->prepare("INSERT INTO product_colors (product_id, color) VALUES (?, ?)");
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
                $shoe_type = $_POST['shoe_type'] ?? '';
                $gender = $_POST['gender'] ?? '';
                $occasion = $_POST['occasion'] ?? '';

                $stmt_specs = $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, gender, heel_height, outer_material, season, shoe_type, occasion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_specs->execute([$new_product_id, $brand_id, $gender, $heel_height, $outer_material, $season, $shoe_type, $occasion]);
                
                $success_msg = "Product listed successfully!";

            }
        } catch (PDOException $e) {
            $error_msg = "Database Error: " . $e->getMessage();
        }
    }
}

// Fetch Brands
$brands_stmt = $pdo->query("SELECT * FROM brands ORDER BY name ASC");
$brands = $brands_stmt->fetchAll(PDO::FETCH_ASSOC);
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
        /* Footer Refined */
        footer {
          background: #05070A; border-top: 1px solid var(--border);
          padding: 80px 0 40px; color: #fff;
          margin-top: 50px;
          text-align: left;
           width: 100%;
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
        .contact-item i { color: #10b981; width: 20px; }
        
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
            background: #10b981; color: #000; transform: translateY(-3px);
        }
        
        /* Footer Grid */
        .footer-nav-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;
        }
        
        .footer-col h4 {
            color: #10b981; font-size: 0.85rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.5rem;
        }
        
        .footer-links { list-style: none; padding: 0; margin: 0; }
        .footer-links li { margin-bottom: 1rem; }
        .footer-links a {
            color: #e2e8f0; text-decoration: none; font-size: 0.95rem;
            transition: 0.3s;
        }
        .footer-links a:hover { color: #10b981; padding-left: 5px; }

        @media (max-width: 1024px) {
            .footer-container { grid-template-columns: 1fr; }
            .footer-card { max-width: 500px; }
        }
        @media (max-width: 768px) {
            .footer-nav-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

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
                <input type="text" class="form-control" name="product_name" placeholder="e.g. Nike Air Zoom Pegasus 39" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" placeholder="Describe your product..."></textarea>
            </div>

            <div class="row">
                <div class="form-group col-md-6">
                    <label class="form-label">Category</label>
                    <select class="form-control" name="category" id="categorySelect" required onchange="filterSubcategories()">
                        <option value="">Select Category</option>
                        <?php foreach ($db_categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="sub_category">Sub Category</label>
                    <select class="form-control" name="subcategory" id="subcategorySelect" required>
                        <option value="">Select Subcategory</option>
                        <?php foreach ($db_subcategories as $sub): ?>
                            <option value="<?php echo $sub['id']; ?>">
                                <?php echo htmlspecialchars($sub['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- New Product Specs Section -->
            <h4 style="margin:20px 0 15px; color:#475569;">Specifications</h4>
            <div class="row">
                <div class="form-group col-md-4">
                    <label class="form-label">Brand</label>
                    <select class="form-control" name="brand_id">
                        <option value="">Select Brand</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label class="form-label">Shoe Type</label>
                    <select class="form-control" name="shoe_type">
                        <option value="">Select Type</option>
                        <option value="Sneakers">Sneakers</option>
                        <option value="Boots">Boots</option>
                        <option value="Loafers">Loafers</option>
                        <option value="Sandals">Sandals</option>
                        <option value="Heels">Heels</option>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label class="form-label">Gender</label>
                    <select class="form-control" name="gender" required>
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
            
            <div class="row">
                <div class="form-group col-md-4">
                    <label class="form-label">Outer Material</label>
                    <select class="form-control" name="outer_material">
                        <option value="">Select Material</option>
                        <?php foreach ($materials as $m): ?>
                            <option value="<?php echo htmlspecialchars($m['name']); ?>"><?php echo htmlspecialchars($m['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label">Heel Height</label>
                    <input type="text" class="form-control" name="heel_height" placeholder="e.g. Flat, 2 inches">
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label">Season</label>
                    <select class="form-control" name="season">
                        <option value="">Select Season</option>
                        <option value="All Season">All Season</option>
                        <option value="Summer">Summer</option>
                        <option value="Winter">Winter</option>
                    </select>
                </div>
            </div>

            <script>
            function filterSubcategories() {
                const catId = document.getElementById('categorySelect').value;
                const subSelect = document.getElementById('subcategorySelect');
                const options = subSelect.querySelectorAll('option[data-category]');
                
                // Reset selection
                subSelect.value = "";
                
                options.forEach(opt => {
                    if (!catId || opt.getAttribute('data-category') == catId) {
                        opt.style.display = 'block';
                    } else {
                        opt.style.display = 'none';
                    }
                });
            }
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
                    <input type="number" step="0.01" class="form-control" name="price" placeholder="0.00" required>
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
                <label class="channel-card" onclick="toggleChannel(this)">
                    <input type="checkbox" name="channels[]" value="Amazon" class="channel-checkbox">
                    <i class="fab fa-amazon"></i>
                    <span>Amazon</span>
                </label>
                <label class="channel-card" onclick="toggleChannel(this)">
                    <input type="checkbox" name="channels[]" value="Flipkart" class="channel-checkbox">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Flipkart</span>
                </label>
                <label class="channel-card" onclick="toggleChannel(this)">
                    <input type="checkbox" name="channels[]" value="Shopify" class="channel-checkbox">
                    <i class="fab fa-shopify"></i>
                    <span>Shopify</span>
                </label>
                <label class="channel-card" onclick="toggleChannel(this)">
                    <input type="checkbox" name="channels[]" value="Instagram" class="channel-checkbox">
                    <i class="fab fa-instagram"></i>
                    <span>Instagram</span>
                </label>
                <label class="channel-card" onclick="toggleChannel(this)">
                    <input type="checkbox" name="channels[]" value="TikTok Shop" class="channel-checkbox">
                    <i class="fab fa-tiktok"></i>
                    <span>TikTok Shop</span>
                </label>
                <label class="channel-card" onclick="toggleChannel(this)">
                    <input type="checkbox" name="channels[]" value="eBay" class="channel-checkbox">
                    <i class="fab fa-ebay"></i>
                    <span>eBay</span>
                </label>
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
        // Simple validation
        if (step === 1) {
            const name = document.querySelector('input[name="product_name"]').value;
            if (!name) { alert("Please enter a product name"); return; }
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
        // The click on label toggles checkbox automatically, we just update style
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
</body>
</html>
