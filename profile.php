<?php
session_start();
$user_id = $_SESSION['user_id'] ?? null;
$is_guest = !$user_id;
include 'config.php';

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Handle Photo Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['profile_photo']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowed)) {
        $upload_dir = 'uploads/profiles/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_path)) {
            // Add profile_photo column if it doesn't exist
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) DEFAULT NULL");
            } catch (Exception $e) { /* Column already exists */ }
            
            $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
            $stmt->execute([$upload_path, $user_id]);
            $success_msg = "Profile photo updated successfully!";
        }
    } else {
        $error_msg = "Invalid file type. Only JPG, PNG, and GIF allowed.";
    }
}

// Handle Profile Update
// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'] ?? null;
    
    // Validate phone number (only digits, 10 characters)
    if (!empty($phone)) {
        $phone = preg_replace('/[^0-9]/', '', $phone); // Remove non-digits
        if (strlen($phone) !== 10) {
            $error_msg = "Phone number must be exactly 10 digits.";
        }
    }

    if (empty($error_msg)) {
        try {
            // Add phone column if it doesn't exist
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL");
            } catch (Exception $e) { /* Column already exists */ }
            // Add profile_photo column if it doesn't exist
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) DEFAULT NULL");
            } catch (Exception $e) { /* Column already exists */ }
            
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?");
            $stmt->execute([$first_name, $last_name, $email, $phone, $user_id]);
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['email'] = $email;
            $success_msg = "Profile updated successfully!";
        } catch (Exception $e) {
            $error_msg = "Error updating profile: " . $e->getMessage();
        }
    }
}

// Handle Address Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_address'])) {
    $street = $_POST['street_address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip_code'];
    $country = $_POST['country'];
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM user_addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
        $stmt->execute([$user_id]);
        $existing_address = $stmt->fetch();
        
        if ($existing_address) {
            $stmt = $pdo->prepare("UPDATE user_addresses SET street_address = ?, city = ?, state = ?, zip_code = ?, country = ? WHERE id = ?");
            $stmt->execute([$street, $city, $state, $zip, $country, $existing_address['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, street_address, city, state, zip_code, country, is_default, address_type) VALUES (?, ?, ?, ?, ?, ?, 1, 'shipping')");
            $stmt->execute([$user_id, $street, $city, $state, $zip, $country]);
        }
        $success_msg = "Address updated successfully!";
    } catch (Exception $e) {
        $error_msg = "Error updating address: " . $e->getMessage();
    }
}

// Only fetch data if logged in
$user = null;
$address = null;

if ($user_id) {
    // Fetch User Data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Fetch Address Data
    $stmt_addr = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
    $stmt_addr->execute([$user_id]);
    $address = $stmt_addr->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-glow: rgba(37, 99, 235, 0.2);
            --dark-bg: #ffffff;
            --dark-card: rgba(255, 255, 255, 0.8);
            --dark-border: rgba(37, 99, 235, 0.15);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --glass: rgba(240, 249, 255, 0.6);
            --sky-light: #f0f9ff;
            --sky-mid: #e0f2fe;
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body { 
            font-family: 'Inter', sans-serif; 
            background: radial-gradient(circle at 10% 20%, var(--sky-mid) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, var(--sky-light) 0%, transparent 40%),
                        var(--dark-bg);
            background-attachment: fixed;
            color: var(--text-main); 
            min-height: 100vh;
            line-height: 1.6;
        }

        .navbar { 
            background: rgba(255, 255, 255, 0.8); 
            backdrop-filter: blur(20px);
            height: 80px; 
            display: flex; 
            align-items: center; 
            border-bottom: 1px solid var(--dark-border); 
            padding: 0 60px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .logo { 
            font-family: 'Playfair Display', serif; 
            font-size: 1.6rem; 
            font-weight: 700;
            color: var(--text-main); 
            text-decoration: none; 
            letter-spacing: -0.5px;
        }
        .logo span { color: var(--primary); }

        .back-btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-right: 30px;
            transition: 0.3s;
            font-size: 0.95rem;
        }
        .back-btn-nav:hover { color: var(--primary); transform: translateX(-5px); }

        .container { 
            max-width: 650px; 
            margin: 140px auto 60px; 
            padding: 0 20px; 
        }

        .card { 
            background: var(--dark-card); 
            backdrop-filter: blur(30px);
            border: 1px solid var(--dark-border); 
            border-radius: 24px; 
            padding: 45px; 
            box-shadow: 0 10px 40px rgba(37, 99, 235, 0.08);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .avatar-circle {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--primary) 0%, #3b82f6 100%);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: #000;
            box-shadow: 0 0 30px var(--primary-glow);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .avatar-circle:hover { transform: scale(1.05); }
        
        .avatar-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .avatar-upload-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: 0.3s;
            color: #fff;
        }
        
        .avatar-circle:hover .avatar-upload-overlay { opacity: 1; }
        
        #photoInput { display: none; }

        h1 { 
            font-family: 'Playfair Display', serif; 
            font-size: 2.2rem;
            margin-bottom: 10px; 
            color: var(--text-main);
        }

        .header-desc {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .form-group { margin-bottom: 24px; }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 640px) {
            .form-grid { grid-template-columns: 1fr; }
        }
        
        label { 
            display: block; 
            color: var(--text-muted); 
            font-size: 0.85rem; 
            font-weight: 600;
            margin-bottom: 10px; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        input { 
            width: 100%; 
            background: var(--glass); 
            border: 1px solid var(--dark-border); 
            border-radius: 12px; 
            padding: 14px 18px; 
            color: var(--text-main); 
            font-size: 1rem; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .btn { 
            width: 100%;
            background: var(--primary); 
            color: #ffffff; 
            border: none; 
            padding: 16px; 
            border-radius: 12px; 
            font-weight: 700; 
            font-size: 1rem;
            cursor: pointer; 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 10px;
            box-shadow: 0 4px 15px var(--primary-glow);
        }

        .btn:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 8px 25px var(--primary-glow);
            filter: brightness(1.1);
        }

        .btn:active { transform: translateY(0); }

        .alert { 
            padding: 18px; 
            border-radius: 14px; 
            margin-bottom: 30px; 
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .success { 
            background: rgba(16, 185, 129, 0.1); 
            color: #34d399; 
            border: 1px solid rgba(16, 185, 129, 0.2); 
        }

        /* Completeness Meter */
        .completeness-container {
            margin: 20px 0 30px;
            background: rgba(37, 99, 235, 0.05);
            border-radius: 20px;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .progress-track {
            flex: 1;
            height: 8px;
            background: rgba(37, 99, 235, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), #3b82f6);
            box-shadow: 0 0 10px var(--primary-glow);
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .completeness-text {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-muted);
            min-width: 40px;
        }

        @media (max-width: 640px) {
            .navbar { padding: 0 30px; }
            .card { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="javascript:history.back()" class="back-btn-nav"><i class="fas fa-arrow-left"></i> Back</a>
        <a href="dashboard.php" class="logo">WALK<span>ON</span></a>
    </nav>
    <div class="container">
        <?php if ($is_guest): ?>
            <div class="card" style="text-align: center; padding: 60px 40px;">
                <i class="fas fa-id-card-alt" style="font-size: 3.5rem; color: var(--primary); margin-bottom: 2rem;"></i>
                <h1>Account Settings</h1>
                <p class="header-desc" style="margin-bottom: 2.5rem; max-width: 400px; margin-left: auto; margin-right: auto;">
                    Please sign in to manage your profile, delivery addresses, and personal preferences.
                </p>
                <a href="login.php?redirect=profile.php" class="btn" style="display: inline-block; width: auto; padding: 16px 40px;">Sign In to Continue</a>
                
                <div style="margin-top: 2rem; color: var(--text-muted); font-size: 0.9rem;">
                    Don't have an account? <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Create one here</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
            <div class="profile-header">
                <form method="POST" enctype="multipart/form-data" id="photoForm">
                    <div class="avatar-circle" onclick="document.getElementById('photoInput').click()">
                        <?php if(!empty($user['profile_photo']) && file_exists($user['profile_photo'])): ?>
                            <img src="<?= htmlspecialchars($user['profile_photo']) ?>" alt="Profile">
                        <?php else: ?>
                            <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                        <?php endif; ?>
                        <div class="avatar-upload-overlay">
                            <i class="fas fa-camera" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <input type="file" id="photoInput" name="profile_photo" accept="image/*" onchange="this.form.submit()">
                </form>
                <h1>Profile Settings</h1>
                <p class="header-desc">Manage your account information and preferences</p>

                <?php
                $completeness = 0;
                if(!empty($user['first_name'])) $completeness += 10;
                if(!empty($user['last_name'])) $completeness += 10;
                if(!empty($user['email'])) $completeness += 10;
                if(!empty($user['phone'])) $completeness += 20;
                if(!empty($user['profile_photo'])) $completeness += 20;
                if(!empty($address['street_address'])) $completeness += 30;
                ?>
                <div class="completeness-container">
                    <span class="completeness-text">Profile Security:</span>
                    <div class="progress-track">
                        <div class="progress-bar" style="width: <?= $completeness ?>%"></div>
                    </div>
                    <span class="completeness-text"><?= $completeness ?>%</span>
                </div>
            </div>

            <?php if($success_msg): ?> 
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <?= $success_msg ?>
                </div> 
            <?php endif; ?>
            
            <?php if($error_msg): ?> 
                <div class="alert" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error_msg ?>
                </div> 
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" placeholder="Enter first name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" placeholder="Enter last name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="email@example.com" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" id="phoneInput" name="phone" placeholder="10 digit mobile number" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" pattern="[0-9]{10}" maxlength="10" title="Please enter exactly 10 digits">
                    <small style="color: var(--text-muted); font-size: 0.8rem; margin-top: 5px; display: block;">Enter 10 digit number (0-9 only)</small>
                </div>

                <button type="submit" name="update_profile" class="btn">Update Profile</button>
            </form>
        </div>

        <div class="card" style="margin-top: 30px;">
            <div class="profile-header" style="margin-bottom: 30px;">
                <h2 style="font-family:'Playfair Display', serif; font-size:1.8rem;">Shipping Address</h2>
                <p class="header-desc">Your default delivery destination</p>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label>Street Address</label>
                    <input type="text" name="street_address" placeholder="123 Luxury Ave, Penthouse 4" value="<?= htmlspecialchars($address['street_address'] ?? '') ?>" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" placeholder="Mumbai" value="<?= htmlspecialchars($address['city'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>State</label>
                        <input type="text" name="state" placeholder="Maharashtra" value="<?= htmlspecialchars($address['state'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Zip Code</label>
                        <input type="text" name="zip_code" placeholder="400001" value="<?= htmlspecialchars($address['zip_code'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" name="country" placeholder="India" value="<?= htmlspecialchars($address['country'] ?? '') ?>" required>
                    </div>
                </div>
                <button type="submit" name="update_address" class="btn">Update Address</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    // Phone number validation - only allow digits 0-9
    document.getElementById('phoneInput').addEventListener('input', function(e) {
        // Remove any non-digit characters
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Limit to 10 digits
        if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
        }
    });
    
    // Form validation on submit
    document.querySelector('form[method="POST"]').addEventListener('submit', function(e) {
        const phone = document.getElementById('phoneInput').value;
        if (phone && phone.length !== 10) {
            e.preventDefault();
            alert('Phone number must be exactly 10 digits');
            return false;
        }
    });
    </script>
</body>
</html>
