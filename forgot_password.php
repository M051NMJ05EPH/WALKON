<?php
session_start();
include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = "Please enter your email address!";
    } else {
        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(50));
            $expires = date("Y-m-d H:i:s", time() + 3600); // 1 hour

            try {
                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                $stmt->execute([$token, $expires, $user['id']]);
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1054) {
                    try { $pdo->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL"); } catch (Exception $ex) {}
                    try { $pdo->exec("ALTER TABLE users ADD COLUMN reset_expires DATETIME DEFAULT NULL"); } catch (Exception $ex) {}
                    $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                    $stmt->execute([$token, $expires, $user['id']]);
                } else { throw $e; }
            }

            $reset_link = "http://localhost/WALKON-rough/reset_password.php?token=" . $token;
            
            // Success message for localhost
            $success = "A reset link has been generated! For this demo, you can use it below:<br>
                        <a href='$reset_link' style='color:#10b981;font-weight:700;text-decoration:underline;'>RESET PASSWORD NOW</a>";
        } else {
            $success = "If that email is registered, a reset link has been sent.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --primary-hover: #059669;
            --bg: #FFFFFF;
            --text-dark: #1F2937;
            --text-light: #6B7280;
            --input-bg: #F3F4F6;
            --border: #E5E7EB;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit',sans-serif; }
        body { background-color: var(--bg); color: var(--text-dark); height: 100vh; display: flex; overflow: hidden; }

        .container { display: flex; width: 100%; height: 100%; }
        .visual-side { flex: 1; background: #F9FAFB; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .visual-side img { max-width: 80%; height: auto; filter: drop-shadow(0 25px 50px rgba(0, 0, 0, 0.15)); transform: rotate(-15deg); }

        .form-side { width: 50%; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .form-content { width: 100%; max-width: 440px; }

        .header { text-align: center; margin-bottom: 2.5rem; }
        .header .logo { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 2rem; }
        .header .logo img { height: 48px; }
        .header .logo span { font-size: 32px; font-weight: 800; text-transform: uppercase; color: #1F2937; letter-spacing: -1px; }
        .header .logo span b { color: var(--primary); }
        .header h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 0.5rem; letter-spacing: -1px; }
        .header p { color: var(--text-light); }

        .form-group { margin-bottom: 1.5rem; }
        input { width: 100%; padding: 1rem; background: var(--input-bg); border: 1px solid transparent; border-radius: 50px; font-size: 1rem; transition: all 0.3s ease; }
        input:focus { outline: none; background: #fff; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); }

        .btn-submit { width: 100%; padding: 1rem; background: var(--primary); color: #fff; border: none; border-radius: 50px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .btn-submit:hover { background: var(--primary-hover); transform: translateY(-2px); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2); }

        .msg { padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.95rem; text-align: center; line-height: 1.6; }
        .error { background: #FEE2E2; color: #EF4444; }
        .success { background: #DCFCE7; color: #166534; }

        .footer { text-align: center; margin-top: 2rem; font-size: 0.95rem; color: var(--text-light); }
        .footer a { color: var(--primary); text-decoration: none; font-weight: 600; }

        @media (max-width: 968px) { .visual-side { display: none; } .form-side { width: 100%; } }
    </style>
</head>
<body>

<div class="container">
    <div class="visual-side">
        <img src="assets/login_premium.png" alt="Premium Shoe">
    </div>
    
    <div class="form-side">
        <div class="form-content">
            <div class="header">
                <div class="logo">
                    <img src="assets/shoe_logo_green.png" alt="WalkOn">
                    <span>WALK<b>ON</b></span>
                </div>
                <h1>Reset Password</h1>
                <p>Enter your email address to receive a reset link.</p>
            </div>

            <?php if ($error): ?><div class="msg error"><?php echo $error; ?></div><?php endif; ?>
            <?php if ($success): ?><div class="msg success"><?php echo $success; ?></div><?php endif; ?>

            <form action="forgot_password.php" method="POST">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                <button type="submit" class="btn-submit">Send Reset Link</button>
            </form>

            <div class="footer">
                Back to <a href="login.php">Sign In</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>