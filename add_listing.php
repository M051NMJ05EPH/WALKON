<?php
session_start();
include 'config.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
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
            // Check for duplicate SKU
            $stmt = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
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

                // Insert Product
                $sql = "INSERT INTO products (seller_id, product_name, sku, price, description, category, subcategory, sizes, colors, quantity, channels, images, status, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', NOW())";
                $insert = $pdo->prepare($sql);
                $insert->execute([$seller_ref_id, $product_name, $sku, $price, $description, $category, $subcategory, $sizes, $colors, $quantity, $channels_str, $images_json]);
                $success_msg = "Listing created successfully and synced to selected channels!";
            }
        } catch (Exception $e) {
            $error_msg = "Error creating listing: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Listing - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #28a745;
            --primary-dark: #218838;
            --text-dark: #333;
            --text-light: #6c757d;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --border: #e9ecef;
        }
        
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
        body { background: var(--bg-light); color: var(--text-dark); padding-bottom: 50px; }

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
    </style>
</head>
<body>

<div class="container">
    <div class="header">
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
                    <select class="form-control" name="category">
                        <option value="">Select Category</option>
                        <option value="Sneakers">Sneakers</option>
                        <option value="Boots">Boots</option>
                        <option value="Sandals">Sandals</option>
                        <option value="Formal">Formal</option>
                        <option value="Sports">Sports</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">Subcategory</label>
                    <select class="form-control" name="subcategory">
                        <option value="">(none)</option>
                        <option value="Men">Men</option>
                        <option value="Women">Women</option>
                        <option value="Kids">Kids</option>
                        <option value="Unisex">Unisex</option>
                    </select>
                </div>
            </div>

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
                    <i class="fas fa-cart-shopping"></i>
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
                    <input type="checkbox" name="channels[]" value="TikTok" class="channel-checkbox">
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

</body>
</html>
