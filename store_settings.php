<?php
session_start();
require_once 'config.php';
require_once 'includes/auth_check.php';
require_once 'includes/activity_logger.php';

// Require admin, entrepreneur, store, or store_owner role
requireRole(['admin', 'entrepreneur', 'store', 'store_owner']);

$logger = new ActivityLogger($pdo);

// Get seller_id for the logged in user
$seller_id = null;
$is_entrepreneur = ($_SESSION['role'] === 'entrepreneur');
$label_prefix = $is_entrepreneur ? 'Brand' : 'Store';

if (!isAdmin()) {
    $stmt_seller = $pdo->prepare("SELECT id, name FROM sellers WHERE email = ?");
    $stmt_seller->execute([$_SESSION['email']]);
    $seller = $stmt_seller->fetch();
    
    if ($seller) {
        $seller_id = $seller['id'];
        $seller_name = $seller['name'];
    } else {
        // Auto-create seller account if not found
        $full_name = ($_SESSION['first_name'] ?? 'Store') . ' ' . ($_SESSION['last_name'] ?? 'Owner');
        $stmt_new = $pdo->prepare("INSERT INTO sellers (name, email, password, business_name, created_at) VALUES (?, ?, ?, ?, NOW())");
        // Use a dummy password hash or copy from users table if possible, here using a default hash for safety
        $dummy_hash = '$2y$10$abcdefghijklmnopqrstuv'; 
        $stmt_new->execute([$full_name, $_SESSION['email'], $dummy_hash, 'My Store']);
        $seller_id = $pdo->lastInsertId();
        $seller_name = $full_name;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['settings'])) {
    try {
        $pdo->beginTransaction();
        foreach ($_POST['settings'] as $key => $value) {
            $cat = $_POST['categories'][$key] ?? 'other';
            $stmt = $pdo->prepare("
                INSERT INTO store_settings (setting_key, setting_value, category, seller_id, updated_by) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE setting_value = ?, category = ?, updated_by = ?, updated_at = NOW()
            ");
            $stmt->execute([$key, $value, $cat, $seller_id, $_SESSION['user_id'], $value, $cat, $_SESSION['user_id']]);
        }
        $pdo->commit();
        $logger->log($_SESSION['user_id'], 'settings_updated', "Updated store settings");
        $success_message = "Settings saved successfully!";
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error_message = "Failed to save settings: " . $e->getMessage();
    }
}

// Fetch current settings (filtered by seller_id or global if admin is viewing and chooses)
try {
    $sql_fetch = "SELECT setting_key, setting_value, category FROM store_settings";
    $params_fetch = [];
    if (!isAdmin()) {
        $sql_fetch .= " WHERE seller_id = ? OR seller_id IS NULL";
        $params_fetch[] = $seller_id;
    }
    
    $stmt = $pdo->prepare($sql_fetch);
    $stmt->execute($params_fetch);
    $settings_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $settings = [];
    foreach ($settings_raw as $setting) {
        $category = $setting['category'] ?: 'other';
        $settings[$category][$setting['setting_key']] = $setting['setting_value'];
    }
} catch (PDOException $e) {
    $settings = [];
    $error_message = "Failed to load settings";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Settings - WALKON Platform</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #10b981;
            --primary-light: #34d399;
            --primary-dark: #059669;
            --dark-bg: #0B0F19;
            --dark-card: #151B2B;
            --dark-border: #2A3241;
            --text-main: #F1F5F9;
            --text-muted: #94A3B8;
            --font-heading: 'Playfair Display', serif;
            --font-body: 'Inter', sans-serif;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        
        body { 
            font-family: var(--font-body); 
            background: var(--dark-bg); 
            color: var(--text-main); 
        }

        /* Navbar */
        .navbar {
            background: rgba(5, 7, 10, 0.95);
            backdrop-filter: blur(20px);
            position: fixed; width: 100%; top: 0; z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            height: 80px;
        }
        .nav-container {
            max-width: 1600px; margin: 0 auto; padding: 0 2rem; height: 100%;
            display: flex; justify-content: space-between; align-items: center;
        }
        
        .logo-box {
            display: flex; align-items: center; gap: 12px; text-decoration: none;
        }
        .logo-box img { height: 35px; width: auto; }
        .logo-box .brand-name {
            font-size: 1.5rem; font-weight: 700; color: white; letter-spacing: -0.5px;
        }
        .logo-box .brand-name span { color: var(--primary); }
        .back-btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            margin-right: 30px;
            transition: 0.3s;
            font-size: 0.95rem;
        }
        .back-btn-nav:hover { color: var(--primary); transform: translateX(-5px); }

        .nav-links { display: flex; align-items: center; gap: 2rem; }
        .nav-links a { 
            text-decoration: none; font-weight: 500; font-size: 0.9rem;
            color: #e2e8f0; transition: 0.3s;
        }
        .nav-links a:hover { color: var(--primary); }

        .role-badge {
            background: var(--primary);
            color: #000;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .container {
            max-width: 1200px;
            margin: 120px auto 60px;
            padding: 0 2rem;
        }

        .page-header {
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #fff 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 1.05rem;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--primary);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--dark-border);
            overflow-x: auto;
        }

        .tab {
            padding: 14px 24px;
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-weight: 600;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: 0.3s;
            white-space: nowrap;
        }

        .tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .settings-section {
            background: var(--dark-card);
            border-radius: 16px;
            border: 1px solid var(--dark-border);
            padding: 30px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            color: var(--primary);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--dark-border);
            border-radius: 10px;
            color: white;
            font-size: 0.95rem;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .color-input-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .color-input-group input[type="color"] {
            width: 60px;
            height: 45px;
            padding: 4px;
            cursor: pointer;
        }

        .color-input-group input[type="text"] {
            flex: 1;
        }

        .actions-bar {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--dark-border);
        }

        .btn {
            padding: 12px 32px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: #000;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid var(--dark-border);
            color: var(--text-main);
        }

        .btn-secondary:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .nav-links { display: none; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="javascript:history.back()" class="back-btn-nav"><i class="fas fa-arrow-left"></i> Back</a>
            <a href="index.php" class="logo-box">
                <img src="assets/shoe_logo_green.png" alt="WalkOn">
                <div class="brand-name">Walk<span>on</span></div>
            </a>
            
            <div class="nav-links">
                <span class="role-badge">
                    <?= isAdmin() ? '👑 Admin' : ($is_entrepreneur ? '💎 Entrepreneur' : '🏪 Store Partner') ?>
                </span>
                <a href="<?= isAdmin() ? 'dashboard.php' : ($is_entrepreneur ? 'entrepreneur_dashboard.php' : 'store_dashboard.php') ?>">Dashboard</a>
                <a href="my_listings.php">Inventory</a>
                <a href="store_settings.php" style="color: var(--primary);">Settings</a>
                <a href="logout.php">Sign Out</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1><?= $label_prefix ?> Identity</h1>
            <p>Configure your <?= strtolower($label_prefix) ?>'s assets, business information, and preferences</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $success_message ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab active" onclick="switchTab('business')">
                <i class="fas fa-store"></i> Business Info
            </button>
            <button class="tab" onclick="switchTab('financial')">
                <i class="fas fa-coins"></i> Financial
            </button>
            <button class="tab" onclick="switchTab('policy')">
                <i class="fas fa-file-contract"></i> Policies
            </button>
            <button class="tab" onclick="switchTab('branding')">
                <i class="fas fa-palette"></i> Branding & Story
            </button>
            <button class="tab" onclick="switchTab('social')">
                <i class="fas fa-share-alt"></i> Social Links
            </button>
        </div>

        <form method="POST" action="">
            <!-- Business Info Tab -->
            <div class="tab-content active" id="business">
                <div class="settings-section">
                    <div class="section-title">
                        <i class="fas fa-building"></i>
                        Business Information
                    </div>
                    <div class="form-grid">
                        <input type="hidden" name="categories[store_name]" value="business">
                        <input type="hidden" name="categories[store_email]" value="business">
                        <input type="hidden" name="categories[store_phone]" value="business">
                        <input type="hidden" name="categories[store_address]" value="business">
                        <input type="hidden" name="categories[store_hours]" value="business">
                        <input type="hidden" name="categories[store_info]" value="business">

                        <div class="form-group">
                            <label><?= $label_prefix ?> Name</label>
                            <input type="text" name="settings[store_name]" 
                                   value="<?= htmlspecialchars($settings['business']['store_name'] ?? $seller_name ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Contact Email</label>
                            <input type="email" name="settings[store_email]" 
                                   value="<?= htmlspecialchars($settings['business']['store_email'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Contact Phone</label>
                            <input type="text" name="settings[store_phone]" 
                                   value="<?= htmlspecialchars($settings['business']['store_phone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Store Address</label>
                            <input type="text" name="settings[store_address]" 
                                   value="<?= htmlspecialchars($settings['business']['store_address'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Business Hours</label>
                            <input type="text" name="settings[store_hours]" placeholder="e.g. 9:00 AM - 6:00 PM"
                                   value="<?= htmlspecialchars($settings['business']['store_hours'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>About <?= $label_prefix ?> / Mission</label>
                            <textarea name="settings[store_info]" placeholder="Tell your story and mission..."><?= htmlspecialchars($settings['business']['store_info'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Tab -->
            <div class="tab-content" id="financial">
                <div class="settings-section">
                    <div class="section-title">
                        <i class="fas fa-money-bill-wave"></i>
                        Financial Settings
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Currency</label>
                            <select name="settings[currency]">
                                <option value="INR" <?= ($settings['financial']['currency'] ?? '') === 'INR' ? 'selected' : '' ?>>INR - Indian Rupee</option>
                                <option value="USD" <?= ($settings['financial']['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                                <option value="EUR" <?= ($settings['financial']['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                                <option value="GBP" <?= ($settings['financial']['currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Currency Symbol</label>
                            <input type="text" name="settings[currency_symbol]" 
                                   value="<?= htmlspecialchars($settings['financial']['currency_symbol'] ?? '₹') ?>">
                        </div>
                        <div class="form-group">
                            <label>Tax Rate (%)</label>
                            <input type="number" step="0.01" name="settings[tax_rate]" 
                                   value="<?= htmlspecialchars($settings['financial']['tax_rate'] ?? '18') ?>">
                        </div>
                        <div class="form-group">
                            <label>Default Shipping Fee</label>
                            <input type="number" step="0.01" name="settings[default_shipping_fee]" 
                                   value="<?= htmlspecialchars($settings['financial']['default_shipping_fee'] ?? '0') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Policy Tab -->
            <div class="tab-content" id="policy">
                <div class="settings-section">
                    <div class="section-title">
                        <i class="fas fa-shield-alt"></i>
                        Store Policies
                    </div>
                    <div class="form-group">
                        <label>Return Window (Days)</label>
                        <input type="number" name="settings[return_window_days]" 
                               value="<?= htmlspecialchars($settings['policy']['return_window_days'] ?? '30') ?>">
                    </div>
                    <div class="form-group">
                        <label>Return Policy Description</label>
                        <textarea name="settings[return_policy_text]"><?= htmlspecialchars($settings['policy']['return_policy_text'] ?? 'Items can be returned within 30 days of purchase in original condition.') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Shipping Policy</label>
                        <textarea name="settings[shipping_policy_text]"><?= htmlspecialchars($settings['policy']['shipping_policy_text'] ?? 'We ship orders within 2-3 business days.') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Branding Tab -->
            <div class="tab-content" id="branding">
                <div class="settings-section">
                    <div class="section-title">
                        <i class="fas fa-images"></i>
                        Visual Identity
                    </div>
                    <div class="form-grid">
                        <input type="hidden" name="categories[brand_logo]" value="branding">
                        <input type="hidden" name="categories[brand_banner]" value="branding">
                        <input type="hidden" name="categories[brand_color_primary]" value="branding">
                        <input type="hidden" name="categories[brand_color_secondary]" value="branding">

                        <div class="form-group">
                            <label>Logo URL</label>
                            <input type="text" name="settings[brand_logo]" placeholder="https://..."
                                   value="<?= htmlspecialchars($settings['branding']['brand_logo'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Cover/Banner URL</label>
                            <input type="text" name="settings[brand_banner]" placeholder="https://..."
                                   value="<?= htmlspecialchars($settings['branding']['brand_banner'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Primary Brand Color</label>
                            <div class="color-input-group">
                                <input type="color" id="primaryColorPicker" 
                                       value="<?= htmlspecialchars($settings['branding']['brand_color_primary'] ?? '#10b981') ?>">
                                <input type="text" name="settings[brand_color_primary]" id="primaryColorText"
                                       value="<?= htmlspecialchars($settings['branding']['brand_color_primary'] ?? '#10b981') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Secondary Brand Color</label>
                            <div class="color-input-group">
                                <input type="color" id="secondaryColorPicker"
                                       value="<?= htmlspecialchars($settings['branding']['brand_color_secondary'] ?? '#059669') ?>">
                                <input type="text" name="settings[brand_color_secondary]" id="secondaryColorText"
                                       value="<?= htmlspecialchars($settings['branding']['brand_color_secondary'] ?? '#059669') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Links Tab -->
            <div class="tab-content" id="social">
                <div class="settings-section">
                    <div class="section-title">
                        <i class="fas fa-hashtag"></i>
                        Connect with Customers
                    </div>
                    <div class="form-grid">
                        <input type="hidden" name="categories[social_instagram]" value="social">
                        <input type="hidden" name="categories[social_facebook]" value="social">
                        <input type="hidden" name="categories[social_twitter]" value="social">
                        <input type="hidden" name="categories[social_linkedin]" value="social">

                        <div class="form-group">
                            <label><i class="fab fa-instagram"></i> Instagram URL</label>
                            <input type="text" name="settings[social_instagram]" placeholder="https://instagram.com/yourbrand"
                                   value="<?= htmlspecialchars($settings['social']['social_instagram'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="fab fa-facebook"></i> Facebook URL</label>
                            <input type="text" name="settings[social_facebook]" placeholder="https://facebook.com/yourbrand"
                                   value="<?= htmlspecialchars($settings['social']['social_facebook'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="fab fa-twitter"></i> Twitter / X URL</label>
                            <input type="text" name="settings[social_twitter]" placeholder="https://twitter.com/yourbrand"
                                   value="<?= htmlspecialchars($settings['social']['social_twitter'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="fab fa-linkedin"></i> LinkedIn URL</label>
                            <input type="text" name="settings[social_linkedin]" placeholder="https://linkedin.com/company/yourbrand"
                                   value="<?= htmlspecialchars($settings['social']['social_linkedin'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="actions-bar">
                <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
                    <i class="fas fa-undo"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </form>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Mark tab as active
            event.target.closest('.tab').classList.add('active');
        }

        // Color picker sync
        document.getElementById('primaryColorPicker').addEventListener('input', function(e) {
            document.getElementById('primaryColorText').value = e.target.value;
        });

        document.getElementById('secondaryColorPicker').addEventListener('input', function(e) {
            document.getElementById('secondaryColorText').value = e.target.value;
        });

        document.getElementById('primaryColorText').addEventListener('input', function(e) {
            if (/^#[0-9A-F]{6}$/i.test(e.target.value)) {
                document.getElementById('primaryColorPicker').value = e.target.value;
            }
        });

        document.getElementById('secondaryColorText').addEventListener('input', function(e) {
            if (/^#[0-9A-F]{6}$/i.test(e.target.value)) {
                document.getElementById('secondaryColorPicker').value = e.target.value;
            }
        });
    </script>

</body>
</html>
