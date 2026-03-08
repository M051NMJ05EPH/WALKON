<?php
session_start();
include 'config.php';

$error = '';
$success = '';

if (!isset($_GET['token']) && !isset($_POST['token'])) {
    header("Location: login.php");
    exit();
}

$token = $_GET['token'] ?? $_POST['token'];

// Verify token
$stmt = $pdo->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Invalid or expired token!");
}

// Check expiration
if (strtotime($user['reset_expires']) < time()) {
    die("Token expired! Please request a new one.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (empty($password) || empty($confirm)) {
        $error = "Please fill in all fields!";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        // Update password
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        
        if ($stmt->execute([$hashed, $user['id']])) {
            $success = "Password updated successfully! <a href='login.php' style='color:#10b981; font-weight:700;'>Login now</a>";
        } else {
            $error = "Failed to update password. Try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --primary-hover: #059669;
            --bg: #111827;
            --card-bg: #1F2937;
            --text-dark: #F3F4F6;
            --text-light: #9CA3AF;
            --input-bg: #374151;
            --border: #4B5563;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit',sans-serif; }
        body { background-color: var(--bg); color: var(--text-dark); height: 100vh; display: flex; overflow: hidden; }

        .container { display: flex; width: 100%; height: 100%; }
        .visual-side { flex: 1; background: #0F172A; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .visual-side img { max-width: 80%; height: auto; filter: drop-shadow(0 25px 50px rgba(0, 0, 0, 0.5)); transform: rotate(-10deg); }

        .form-side { width: 50%; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .form-content { width: 100%; max-width: 440px; }

        .header { margin-bottom: 2rem; }
        .header .logo { display: flex; align-items: center; gap: 12px; margin-bottom: 2rem; }
        .header .logo img { height: 40px; }
        .header .logo span { font-size: 28px; font-weight: 800; text-transform: uppercase; color: #fff; letter-spacing: -1px; }
        .header .logo span b { color: var(--primary); }
        .header h1 { font-size: 2.2rem; font-weight: 700; margin-bottom: 0.5rem; letter-spacing: -1px; }
        .header p { color: var(--text-light); }

        .form-group { margin-bottom: 1rem; }
        input { width: 100%; padding: 1rem; background: var(--input-bg); border: 1px solid var(--border); border-radius: 8px; font-size: 1rem; color: #fff; transition: all 0.3s ease; }
        input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2); }

        .btn-submit { width: 100%; padding: 1rem; background: var(--primary); color: #fff; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; margin-top: 1rem; }
        .btn-submit:hover { background: var(--primary-hover); transform: translateY(-2px); }

        .msg { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.95rem; text-align: center; }
        .error { background: rgba(239, 68, 68, 0.2); color: #EF4444; }
        .success { background: rgba(16, 185, 129, 0.2); color: #10B981; }

        @media (max-width: 968px) { .visual-side { display: none; } .form-side { width: 100%; } }
    </style>
</head>
<body>

<div class="container">
    <div class="visual-side">
        <img src="assets/register_premium.png" alt="Premium Shoe">
    </div>
    
    <div class="form-side">
        <div class="form-content">
            <div class="header">
                <div class="logo">
                    <img src="assets/shoe_logo_green.png" alt="WalkOn">
                    <span>WALK<b>ON</b></span>
                </div>
                <h1>New Password</h1>
                <p>Please enter your new password below.</p>
            </div>

            <?php if ($error): ?><div class="msg error"><?php echo $error; ?></div><?php endif; ?>
            <?php if ($success): ?><div class="msg success"><?php echo $success; ?></div><?php endif; ?>

            <?php if (!$success): ?>
            <form action="reset_password.php" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="form-group">
                    <input type="password" name="password" placeholder="New Password" required minlength="6">
                </div>
                <div class="form-group">
                    <input type="password" name="confirm" placeholder="Confirm Password" required>
                </div>
                <button type="submit" class="btn-submit">Update Password</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
