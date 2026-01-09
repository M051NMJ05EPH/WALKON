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
    <title>Verify Google Account - WALKON Shoes</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body, html { 
            height:100%; 
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color:white; 
            overflow-x:hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .verification-container {
            width: 100%;
            max-width: 500px;
            background: #1e293b;
            border-radius: 32px;
            padding: 50px 40px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.6);
            text-align: center;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            margin-bottom: 30px;
        }
        
        .logo h1 {
            font-size: 40px;
            font-weight: 700;
            background: linear-gradient(135deg, #28a745, #22c55e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .verification-header {
            margin-bottom: 40px;
        }
        
        .verification-header h2 {
            font-size: 28px;
            margin-bottom: 10px;
            color: white;
        }
        
        .verification-header p {
            color: #94a3b8;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .profile-section {
            background: #334155;
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 30px;
            position: relative;
        }
        
        .profile-picture-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid #28a745;
            object-fit: cover;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
        }
        
        .verified-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #28a745;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #334155;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }
        
        .verified-badge i {
            color: white;
            font-size: 14px;
        }
        
        .user-info h3 {
            font-size: 24px;
            color: white;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .user-info .email {
            color: #94a3b8;
            font-size: 15px;
            margin-bottom: 15px;
            word-break: break-all;
        }
        
        .verification-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(40, 167, 69, 0.2);
            color: #22c55e;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .info-box {
            background: rgba(59, 130, 246, 0.1);
            border-left: 4px solid #3b82f6;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: left;
        }
        
        .info-box p {
            color: #93c5fd;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }
        
        .info-box i {
            color: #3b82f6;
            margin-right: 8px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            flex: 1;
            padding: 18px;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .btn-continue {
            background: linear-gradient(135deg, #28a745, #22c55e);
            color: white;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
        }
        
        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(40, 167, 69, 0.4);
        }
        
        .btn-cancel {
            background: #475569;
            color: white;
        }
        
        .btn-cancel:hover {
            background: #64748b;
            transform: translateY(-2px);
        }
        
        .security-note {
            margin-top: 25px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.5;
        }
        
        .security-note i {
            color: #94a3b8;
            margin-right: 6px;
        }
        
        @media (max-width: 576px) {
            .verification-container {
                padding: 40px 30px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .verification-header h2 {
                font-size: 24px;
            }
            
            .user-info h3 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="logo">
            <h1>WALKON</h1>
        </div>
        
        <div class="verification-header">
            <h2>Verify Your Account</h2>
            <p>Please review your Google account information before continuing</p>
        </div>
        
        <div class="profile-section">
            <div class="profile-picture-wrapper">
                <?php if (!empty($picture)): ?>
                    <img src="<?php echo htmlspecialchars($picture); ?>" alt="Profile" class="profile-picture">
                <?php else: ?>
                    <div class="profile-picture" style="background: linear-gradient(135deg, #28a745, #22c55e); display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: 700;">
                        <?php echo strtoupper(substr($first_name, 0, 1)); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($is_verified): ?>
                    <div class="verified-badge">
                        <i class="fas fa-check"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="user-info">
                <h3><?php echo htmlspecialchars($name); ?></h3>
                <p class="email"><?php echo htmlspecialchars($email); ?></p>
                
                <?php if ($is_verified): ?>
                    <div class="verification-status">
                        <i class="fas fa-shield-alt"></i>
                        <span>Verified by Google</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="info-box">
            <p>
                <i class="fas fa-info-circle"></i>
                By continuing, you'll be able to access the WALKON dashboard and manage your shoe listings across multiple platforms.
            </p>
        </div>
        
        <form method="POST" action="google-process.php">
            <div class="action-buttons">
                <button type="submit" name="action" value="cancel" class="btn btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
                <button type="submit" name="action" value="continue" class="btn btn-continue">
                    <i class="fas fa-arrow-right"></i>
                    Continue
                </button>
            </div>
        </form>
        
        <div class="security-note">
            <i class="fas fa-lock"></i>
            Your information is secure and will only be used for account authentication.
        </div>
    </div>
</body>
</html>
