<?php
session_start();
include 'config.php';

// Auth & Role Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'store', 'entrepreneur', 'store_owner'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$product_id) {
    header("Location: my_listings.php");
    exit();
}

// Fetch seller information for the current user
$stmt_seller = $pdo->prepare("SELECT s.id FROM sellers s JOIN users u ON s.email = u.email WHERE u.id = ?");
$stmt_seller->execute([$_SESSION['user_id']]);
$seller = $stmt_seller->fetch();
$seller_id = $seller ? $seller['id'] : -1;

// Verify Ownership & Fetch Product Data
$stmt = $pdo->prepare("
    SELECT pb.*, 
           ps.sku, 
           pp.price, 
           pst.quantity, 
           pd.content as description,
           GROUP_CONCAT(DISTINCT pc.color_name) as colors,
           GROUP_CONCAT(DISTINCT sz.size_value) as sizes,
           GROUP_CONCAT(DISTINCT pch.channel_name) as channels,
           spec.brand_id, spec.gender, spec.outer_material, spec.heel_height, spec.season, spec.occasion
    FROM product_base pb
    LEFT JOIN product_skus ps ON pb.id = ps.product_id
    LEFT JOIN product_prices pp ON pb.id = pp.product_id
    LEFT JOIN product_stock pst ON pb.id = pst.product_id
    LEFT JOIN product_descriptions pd ON pb.id = pd.product_id
    LEFT JOIN product_colors pc ON pb.id = pc.product_id
    LEFT JOIN product_sizes sz ON pb.id = sz.product_id
    LEFT JOIN product_channels pch ON pb.id = pch.product_id
    LEFT JOIN product_specs spec ON pb.id = spec.product_id
    WHERE pb.id = ? AND pb.seller_id = ?
    GROUP BY pb.id
");
$stmt->execute([$product_id, $seller_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found or access denied.");
}

// Fetch Images
$stmt_img = $pdo->prepare("SELECT url, is_primary FROM product_media WHERE product_id = ? ORDER BY is_primary DESC");
$stmt_img->execute([$product_id]);
$images = $stmt_img->fetchAll(PDO::FETCH_ASSOC);

// Fetch Categories for Dropdown
$cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$db_categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Subcategories
$sub_stmt = $pdo->query("SELECT * FROM sub_categories ORDER BY name ASC");
$db_subcategories = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Materials
$mat_stmt = $pdo->query("SELECT * FROM materials ORDER BY name ASC");
$materials = $mat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Brands
$brands_stmt = $pdo->query("SELECT * FROM brands ORDER BY name ASC");
$brands = $brands_stmt->fetchAll(PDO::FETCH_ASSOC);

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
    
    // Images
    $new_image_urls = [];
    if (!empty($_POST['image_urls_data'])) {
        $decoded = json_decode($_POST['image_urls_data'], true);
        if (is_array($decoded)) $new_image_urls = $decoded;
    }

    // File Uploads
    if (isset($_FILES['product_images'])) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        foreach ($_FILES['product_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['product_images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['product_images']['name'][$key];
                $file_tmp = $_FILES['product_images']['tmp_name'][$key];
                $mime = null;
                if (class_exists('finfo')) {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($file_tmp);
                } elseif (function_exists('mime_content_type')) {
                    $mime = mime_content_type($file_tmp);
                }
                
                if ($mime && strpos($mime, 'image') === 0) {
                    $new_name = uniqid('img_', true) . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
                    $dest = $upload_dir . $new_name;
                    if (move_uploaded_file($file_tmp, $dest)) {
                        $new_image_urls[] = $dest;
                    }
                }
            }
        }
    }

    if (empty($product_name) || empty($sku) || $price <= 0) {
        $error_msg = "Please fill in all required fields (Name, SKU, Price).";
    } else {
        try {
            $pdo->beginTransaction();

            // Update Product Base
            $pdo->prepare("UPDATE product_base SET name=?, category_id=?, sub_category_id=?, updated_at=NOW() WHERE id=?")
                ->execute([$product_name, $category, $subcategory, $product_id]);

            // Update SKU (Check duplicate first if changed)
            if ($sku !== $product['sku']) {
                $chk = $pdo->prepare("SELECT id FROM product_skus WHERE sku = ? AND product_id != ?");
                $chk->execute([$sku, $product_id]);
                if ($chk->fetch()) {
                    throw new Exception("SKU already exists.");
                }
                $pdo->prepare("UPDATE product_skus SET sku=? WHERE product_id=?")->execute([$sku, $product_id]);
            }

            // Update Price
            $pdo->prepare("UPDATE product_prices SET price=? WHERE product_id=?")->execute([$price, $product_id]);

            // Update Stock
            $pdo->prepare("UPDATE product_stock SET quantity=? WHERE product_id=?")->execute([$quantity, $product_id]);

            // Update Description
            $chk_desc = $pdo->prepare("SELECT id FROM product_descriptions WHERE product_id=?");
            $chk_desc->execute([$product_id]);
            if ($chk_desc->fetch()) {
                $pdo->prepare("UPDATE product_descriptions SET content=? WHERE product_id=?")->execute([$description, $product_id]);
            } else {
                $pdo->prepare("INSERT INTO product_descriptions (product_id, content) VALUES (?, ?)")->execute([$product_id, $description]);
            }

            // Update Specs
            $brand_id = $_POST['brand_id'] ?: null;
            $heel_height = $_POST['heel_height'] ?? '';
            $outer_material = $_POST['outer_material'] ?? '';
            $season = $_POST['season'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $occasion = $_POST['occasion'] ?? '';

            // Check if specs exist
            $chk_specs = $pdo->prepare("SELECT product_id FROM product_specs WHERE product_id=?");
            $chk_specs->execute([$product_id]);
            if ($chk_specs->fetch()) {
                $pdo->prepare("UPDATE product_specs SET brand_id=?, gender=?, heel_height=?, outer_material=?, season=?, occasion=? WHERE product_id=?")
                    ->execute([$brand_id, $gender, $heel_height, $outer_material, $season, $occasion, $product_id]);
            } else {
                $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, gender, heel_height, outer_material, season, occasion) VALUES (?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$product_id, $brand_id, $gender, $heel_height, $outer_material, $season, $occasion]);
            }

            // Replace Sizes (Delete all, insert new)
            $pdo->prepare("DELETE FROM product_sizes WHERE product_id=?")->execute([$product_id]);
            if ($sizes) {
                $size_arr = explode(',', $sizes);
                $stmt_sz = $pdo->prepare("INSERT INTO product_sizes (product_id, size_value) VALUES (?, ?)");
                foreach ($size_arr as $sz) $stmt_sz->execute([$product_id, trim($sz)]);
            }

            // Replace Colors
            $pdo->prepare("DELETE FROM product_colors WHERE product_id=?")->execute([$product_id]);
            if ($colors) {
                $color_arr = explode(',', $colors);
                $stmt_cl = $pdo->prepare("INSERT INTO product_colors (product_id, color_name) VALUES (?, ?)");
                foreach ($color_arr as $cl) $stmt_cl->execute([$product_id, trim($cl)]);
            }

            // Replace Channels
            $pdo->prepare("DELETE FROM product_channels WHERE product_id=?")->execute([$product_id]);
            if (!empty($channels_arr)) {
                $stmt_chan = $pdo->prepare("INSERT INTO product_channels (product_id, channel_name) VALUES (?, ?)");
                foreach ($channels_arr as $chan) $stmt_chan->execute([$product_id, $chan]);
            }

            // Add NEW Images (Don't delete old ones unless explicitly requested - for now we just append new ones)
            if (!empty($new_image_urls)) {
                $stmt_media = $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary) VALUES (?, ?, ?)");
                foreach ($new_image_urls as $url) {
                    $stmt_media->execute([$product_id, $url, 0]); // Add as secondary
                }
            }

            $pdo->commit();
            $success_msg = "Product updated successfully!";
            
            // Refresh data
            header("Refresh:0"); 
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #10b981; --primary-dark: #059669; --text-dark: #0f172a; --bg-light: #f8fafc; --white: #ffffff; --border: #e2e8f0; }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
        body { background: var(--bg-light); color: var(--text-dark); padding: 0; display: flex; flex-direction: column; min-height: 100vh; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; flex: 1; width: 100%; }
        
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { font-size: 32px; font-weight: 700; color: var(--text-dark); margin-bottom: 10px; }
        
        .form-card { background: var(--white); border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 25px; }
        .form-label { display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark); }
        .form-control { width: 100%; padding: 14px 18px; border: 2px solid var(--border); border-radius: 12px; font-size: 15px; transition: 0.3s; }
        .form-control:focus { border-color: var(--primary); outline: none; }
        textarea.form-control { resize: vertical; min-height: 120px; }
        
        .btn { padding: 14px 30px; border-radius: 50px; border: none; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-primary { background: var(--primary); color: var(--white); }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); }
        .btn-secondary { background: #e9ecef; color: var(--text-dark); text-decoration: none; display: inline-block; }

        .image-preview { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
        .img-thumb { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
        
        .channel-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; }
        .channel-card { background: var(--white); border: 2px solid var(--border); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; }
        .channel-card input:checked + i + span { color: var(--primary); }
        .channel-card.selected { border-color: var(--primary); background: #f0fdf4; }
        
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }

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
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            opacity: 0.7;
            transition: 0.3s;
        }
        .back-btn:hover {
            color: var(--primary);
            opacity: 1;
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
        <h1>Edit Product</h1>
        <p>Update listing details for SKUs: <?php echo htmlspecialchars($product['sku']); ?></p>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?> <a href="my_listings.php">Back to Listings</a></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-error"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form-card">
        <input type="hidden" name="image_urls_data" id="image_urls_data">

        <!-- Basic Info -->
        <div class="form-group">
            <label class="form-label">Product Name</label>
            <input type="text" class="form-control" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>

        <div class="row" style="display:flex; gap:20px;">
            <div class="form-group" style="flex:1;">
                <label class="form-label">Category</label>
                <select class="form-control" name="category" id="categorySelect" onchange="filterSubcategories()" required>
                    <option value="">Select Category</option>
                    <?php foreach ($db_categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex:1;">
                <label class="form-label">Sub Category</label>
                <select class="form-control" name="subcategory" id="subcategorySelect" required>
                    <option value="">-- Select Category First --</option>
                </select>
            </div>
            <div class="form-group" style="flex:1;">
                <label class="form-label">Brand</label>
                <select class="form-control" name="brand_id">
                    <option value="">Select Brand</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?php echo $b['id']; ?>" <?php echo $product['brand_id'] == $b['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($b['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Price & Stock -->
        <div class="row" style="display:flex; gap:20px;">
            <div class="form-group" style="flex:1;">
                <label class="form-label">Price (₹)</label>
                <input type="number" step="0.01" class="form-control" name="price" value="<?php echo $product['price']; ?>" required>
            </div>
            <div class="form-group" style="flex:1;">
                <label class="form-label">Quantity</label>
                <input type="number" class="form-control" name="quantity" value="<?php echo $product['quantity']; ?>">
            </div>
            <div class="form-group" style="flex:1;">
                <label class="form-label">SKU</label>
                <input type="text" class="form-control" name="sku" value="<?php echo htmlspecialchars($product['sku']); ?>">
            </div>
        </div>

        <!-- Variants -->
        <div class="row" style="display:flex; gap:20px;">
            <div class="form-group" style="flex:1;">
                <label class="form-label">Sizes (comma separated)</label>
                <input type="text" class="form-control" name="sizes" value="<?php echo htmlspecialchars($product['sizes']); ?>">
            </div>
            <div class="form-group" style="flex:1;">
                <label class="form-label">Colors (comma separated)</label>
                <input type="text" class="form-control" name="colors" value="<?php echo htmlspecialchars($product['colors']); ?>">
            </div>
        </div>

        <!-- Specs -->
        <!-- Specs -->
        <h4 style="margin:20px 0 15px; color:#475569;">Specifications</h4>
        <div class="row" style="display:flex; gap:20px; flex-wrap:wrap;">
            <div class="form-group" style="flex:1; min-width:200px;">
                <label class="form-label">Gender</label>
                <select class="form-control" name="gender">
                    <option value="">Select</option>
                    <?php foreach(['Men','Women','Unisex','Kids', 'Boys', 'Girls', 'Babies'] as $g): ?>
                        <option value="<?php echo $g; ?>" <?php echo $product['gender'] == $g ? 'selected' : ''; ?>><?php echo $g; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex:1; min-width:200px;">
                <label class="form-label">Season</label>
                <select class="form-control" name="season">
                    <option value="">Select Season</option>
                    <?php foreach(['All Season','Summer','Winter','Spring','Autumn'] as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo ($product['season'] ?? '') == $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
             <div class="form-group" style="flex:1; min-width:200px;">
                <label class="form-label">Occasion</label>
                <select class="form-control" name="occasion">
                    <option value="">Select Occasion</option>
                    <?php foreach(['Casual','Sports','Formal','Party','Ethnic','Outdoor'] as $o): ?>
                        <option value="<?php echo $o; ?>" <?php echo ($product['occasion'] ?? '') == $o ? 'selected' : ''; ?>><?php echo $o; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="row" style="display:flex; gap:20px; flex-wrap:wrap;">
            <div class="form-group" style="flex:1; min-width:200px;">
                <label class="form-label">Outer Material</label>
                <select class="form-control" name="outer_material">
                    <option value="">Select Material</option>
                    <?php foreach ($materials as $m): ?>
                        <option value="<?php echo htmlspecialchars($m['name']); ?>" <?php echo ($product['outer_material'] ?? '') == $m['name'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex:1; min-width:200px;">
                <label class="form-label">Heel Height</label>
                <input type="text" class="form-control" name="heel_height" value="<?php echo htmlspecialchars($product['heel_height'] ?? ''); ?>">
            </div>
        </div>

        <!-- Images -->
        <div class="form-group">
            <label class="form-label">Current Images</label>
            <div class="image-preview">
                <?php foreach($images as $img): ?>
                    <img src="<?php echo htmlspecialchars($img['url']); ?>" class="img-thumb">
                <?php endforeach; ?>
            </div>
            <label class="form-label">Add New Images</label>
            <input type="file" name="product_images[]" multiple class="form-control">
        </div>

        <!-- Channels -->
        <div class="form-group">
            <label class="form-label">Active Channels</label>
            <div class="channel-grid">
                <?php 
                $prod_channels = !empty($product['channels']) ? explode(',', $product['channels']) : [];
                $available = ['Amazon', 'Flipkart', 'Shopify', 'Instagram', 'eBay'];
                foreach($available as $ch): 
                    $checked = in_array($ch, $prod_channels) ? 'checked' : '';
                    $cls = $checked ? 'selected' : '';
                ?>
                <label class="channel-card <?php echo $cls; ?>" onclick="this.classList.toggle('selected')">
                    <input type="checkbox" name="channels[]" value="<?php echo $ch; ?>" <?php echo $checked; ?> style="display:none;">
                    <i class="fas fa-check-circle" style="font-size:24px; margin-bottom:5px; color:#ddd;"></i>
                    <span style="display:block; font-weight:600;"><?php echo $ch; ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="margin-top:40px; text-align:right;">
            <a href="my_listings.php" class="btn btn-secondary" style="margin-right:15px;">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
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
        const initialSelectedId = "<?php echo $product['sub_category_id']; ?>";
        
        console.log("Filtering subcategories for Category ID:", catId, "Initial ID:", initialSelectedId);

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
                if (sub.id == initialSelectedId) opt.selected = true;
                subSelect.appendChild(opt);
                foundCount++;
            }
        });

        console.log("Found subcategories:", foundCount);

        if (foundCount === 0) {
            subSelect.innerHTML = '<option value="">No subcategories available</option>';
            subSelect.disabled = true;
        }
    }

    window.addEventListener('DOMContentLoaded', filterSubcategories);
</script>
</body>
</html>
