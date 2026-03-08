<?php
session_start();

// If no pending Google verification, redirect to login
if (!isset($_SESSION['google_pending']) || $_SESSION['google_pending'] !== true) {
    header("Location: login.php");
    exit();
}

// Get Google user data from session
$email = $_SESSION['google_email'] ?? '';
$name = $_SESSION['google_name'] ?? '';
$first_name = $_SESSION['google_first_name'] ?? '';
$last_name = $_SESSION['google_last_name'] ?? '';
$picture = $_SESSION['google_picture'] ?? '';
$is_verified = $_SESSION['google_verified'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Account - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --primary-hover: #059669;
            --bg: #FFFFFF;
            --card-bg: #F9FAFB;
            --text-dark: #1F2937;
            --text-light: #6B7280;
            --border: #E5E7EB;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .verification-card {
            width: 100%;
            max-width: 480px;
            background: var(--bg);
            border-radius: 32px;
            padding: 3rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
            text-align: center;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 2.5rem;
        }

        .logo img {
            height: 48px;
        }

        .logo span {
            font-size: 1.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: -1px;
        }

        .header h2 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .header p {
            color: var(--text-light);
            margin-bottom: 2.5rem;
            font-size: 1rem;
        }

        .profile-box {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 2.5rem;
            border: 1px solid var(--border);
            position: relative;
        }

        .profile-img-wrap {
            position: relative;
            display: inline-block;
            margin-bottom: 1.2rem;
        }

        .profile-img {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .verified-check {
            position: absolute;
            bottom: 4px;
            right: 4px;
            background: var(--primary);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            border: 3px solid var(--card-bg);
        }

        .user-name {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .user-email {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            flex: 1;
            padding: 1rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-continue {
            background: var(--primary);
            color: white;
            border: none;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
        }

        .btn-continue:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(16, 185, 129, 0.3);
        }

        .btn-cancel {
            background: #fff;
            color: var(--text-dark);
            border: 1px solid var(--border);
        }

        .btn-cancel:hover {
            background: #F9FAFB;
            border-color: #D1D5DB;
        }

        .security-footer {
            margin-top: 2rem;
            font-size: 0.85rem;
            color: var(--text-light);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .security-footer i {
            color: var(--primary);
        }
    </style>
</head>
<body>

<div class="verification-card">
    <div class="logo">
        <img src="assets/shoe_logo_green.png" alt="WalkOn">
        <div style="font-family: 'Outfit', sans-serif;">
            <span style="color: #1F2937;">Walk</span><span style="color: #10b981;">on</span>
        </div>
    </div>

    <div class="header">
        <h2>Account Verification</h2>
        <p>Confirm your details to finalize the setup.</p>
    </div>

    <div class="profile-box">
        <div class="profile-img-wrap">
            <?php if (!empty($picture)): ?>
                <img src="<?php echo htmlspecialchars($picture); ?>" alt="Profile" class="profile-img">
            <?php else: ?>
                <div class="profile-img" style="background: var(--primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 32px; font-weight: 800;">
                    <?php echo strtoupper(substr($first_name, 0, 1)); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($is_verified): ?>
                <div class="verified-check">
                    <i class="fas fa-check"></i>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($name); ?></div>
            <div class="user-email"><?php echo htmlspecialchars($email); ?></div>
        </div>
    </div>

    <form method="POST" action="google-process.php">
        <div class="actions">
            <button type="submit" name="action" value="cancel" class="btn btn-cancel">
                Cancel
            </button>
            <button type="submit" name="action" value="continue" class="btn btn-continue">
                Continue <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </form>

    <div class="security-footer">
        <i class="fas fa-shield-check"></i>
        <span>Secure authentication powered by Google</span>
    </div>
</div>

</body>
</html>
