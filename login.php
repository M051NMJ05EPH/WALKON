<?php
session_start();
include 'config.php';
require_once 'includes/activity_logger.php';

$error = '';
$intended_role = $_GET['intended_role'] ?? '';

// Check if user is already logged in - redirect directly to their dashboard
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? '';
    switch ($role) {
        case 'admin':
            header("Location: admin_dashboard.php");
            break;
        case 'entrepreneur':
            header("Location: entrepreneur_dashboard.php");
            break;
        case 'store':
        case 'store_owner':
            header("Location: store_dashboard.php");
            break;
        case 'customer':
        default:
            header("Location: customer_dashboard.php");
            break;
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields!";
    } else {
        $stmt = $pdo->prepare("SELECT id, email, password, first_name, last_name, role, is_active, seller_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Check if account is active
            if (!$user['is_active']) {
                $error = "Your account has been deactivated. Please contact support.";
            } elseif (password_verify($password, $user['password'])) {
                
                // Seller-Specific Portal Enforcement
                if ($intended_role === 'seller') {
                    $seller_roles = ['store', 'store_owner', 'entrepreneur'];
                    if (!in_array($user['role'], $seller_roles)) {
                        $error = "Access Denied: This portal is for Verified Sellers only. Please use the standard login or register for a seller account.";
                    }
                }

                if (!$error) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['seller_id'] = $user['seller_id'];
                    
                    // Log login activity
                    $logger = new ActivityLogger($pdo);
                    $logger->logLogin($user['id'], $user['email']);
                    
                    // Role-based redirect
                    switch ($user['role']) {
                        case 'admin':
                            header("Location: admin_dashboard.php");
                            break;
                        case 'entrepreneur':
                            header("Location: entrepreneur_dashboard.php");
                            break;
                        case 'store':
                        case 'store_owner':
                            header("Location: store_dashboard.php");
                            break;
                        default:
                            header("Location: customer_dashboard.php");
                    }
                    exit();
                }
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "No account found with that email!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - WALKON Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;       /* Royal Blue */
            --primary-hover: #1d4ed8;
            --bg: #ffffff;
            --light-bg: #f0f9ff;
            --surface: rgba(255, 255, 255, 0.8);
            --card-bg: #ffffff;
            --border: #bae6fd;
            --text-main: #1e3a8a;     /* Deep Blue */
            --text-muted: #64748b;
            --font-heading: 'Playfair Display', serif;
            --font-body: 'Inter', sans-serif;
            --accent: #10b981;        /* Emerald Green */
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family: var(--font-body); }

        body {
            background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 50%, #e0f2fe 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-attachment: fixed;
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            max-width: 1200px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 40px 100px -20px rgba(37, 99, 235, 0.15);
            margin: 2rem;
            min-height: 700px;
        }

        /* Visual Side */
        .visual-side {
            flex: 1.2;
            position: relative;
            background: linear-gradient(135deg, #10b981 0%, #2563eb 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            border-right: 1px solid var(--border);
        }

        .visual-side::before {
            content: '';
            position: absolute;
            width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=1000&auto=format&fit=crop') no-repeat center center/cover;
            opacity: 0.5;
            mix-blend-mode: overlay;
        }
        
        .visual-content {
            position: relative; z-index: 2;
            text-align: center; padding: 3rem;
        }
        
        .visual-content h2 {
            font-family: var(--font-heading);
            font-size: 3.5rem; color: #fff;
            margin-bottom: 1rem;
            text-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .visual-content p {
            font-size: 1.1rem; color: rgba(255, 255, 255, 0.9);
            max-width: 400px; margin: 0 auto;
            line-height: 1.6;
        }

        /* Form Side */
        .form-side {
            flex: 1;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
        }

        .brand-logo {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 3rem;
            text-decoration: none;
        }
        .brand-logo img { height: 50px; width: auto; }
        .brand-logo span {
            font-family: 'Outfit', sans-serif; font-size: 28px; font-weight: 800; 
            line-height: 1; text-transform: uppercase;
        }

        .auth-header { margin-bottom: 2.5rem; }
        .auth-header h1 {
            font-family: var(--font-heading);
            font-size: 2.5rem; margin-bottom: 0.5rem;
            color: var(--text-main);
        }
        .auth-header p { color: var(--text-muted); }

        .form-group { margin-bottom: 1.5rem; position: relative; }
        .form-group label {
            display: block; margin-bottom: 0.6rem;
            color: var(--text-muted); font-size: 0.9rem; font-weight: 500;
        }

        input {
            width: 100%;
            padding: 1.1rem 1.2rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            color: var(--text-main);
            font-size: 1rem;
            transition: 0.3s;
        }
        input:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .toggle-password {
            position: absolute; right: 15px; top: 48px;
            color: var(--text-muted); cursor: pointer;
        }

        .actions {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 2.5rem; font-size: 0.9rem;
        }
        .actions a { color: var(--primary); text-decoration: none; font-weight: 500; }
        .actions label { color: var(--text-muted); cursor: pointer; display: flex; align-items: center; gap: 8px; }

        .btn-submit {
            width: 100%; padding: 1.1rem;
            background: var(--primary); color: #fff;
            border: none; border-radius: 12px;
            font-size: 1.05rem; font-weight: 700;
            cursor: pointer; transition: 0.3s;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
        }
        .btn-submit:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(37, 99, 235, 0.3);
        }

        .divider {
            display: flex; align-items: center; gap: 1rem;
            margin: 2rem 0; color: #cbd5e1; font-size: 0.9rem;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: #e2e8f0;
        }

        .social-btn {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; padding: 1rem;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px; color: var(--text-main);
            text-decoration: none; font-weight: 500;
            transition: 0.3s;
        }
        .social-btn:hover {
            background: #f8fafc; border-color: var(--primary);
        }

        .footer-text {
            text-align: center; margin-top: 2rem;
            color: var(--text-muted); font-size: 0.95rem;
        }
        .footer-text a { color: var(--primary); text-decoration: none; font-weight: 600; }

        @media (max-width: 900px) {
            .visual-side { display: none; }
            .login-wrapper { max-width: 500px; min-height: auto; }
            .form-side { padding: 3rem; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- Visual Side -->
    <div class="visual-side">
        <div class="visual-content">
            <h2>Walk with Confidence</h2>
            <p>Join the community of trendsetters. Experience premium footwear designed for the modern era.</p>
        </div>
    </div>

    <!-- Form Side -->
    <div class="form-side">
        <a href="index.php" class="brand-logo">
            <img src="assets/shoe_logo_green.png" alt="WalkOn">
            <div>
                <span style="color: var(--text-main);">WALK</span><span style="color: var(--accent);">ON</span>
            </div>
        </a>

        <div class="auth-header">
            <h1><?= $intended_role === 'seller' ? 'Seller Hub' : 'Welcome Back' ?></h1>
            <p><?= $intended_role === 'seller' ? 'Access your store command center.' : 'Please enter your details to sign in.' ?></p>
        </div>

        <?php if ($error): ?>
            <div style="background: rgba(239,68,68,0.15); color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid rgba(239,68,68,0.3);">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="login.php<?= $intended_role ? '?intended_role='.$intended_role : '' ?>" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
                <i class="fas fa-eye-slash toggle-password" id="togglePswd"></i>
            </div>

            <div class="actions">
                <label><input type="checkbox"> Remember me</label>
                <a href="forgot_password.php">Forgot password?</a>
            </div>

            <button type="submit" class="btn-submit">Sign In</button>
        </form>

        <div class="divider">OR CONTINUE WITH</div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <a href="#" onclick="triggerGoogleLogin(event)" class="social-btn">
                <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" style="height: 20px;"> Google
            </a>
            <a href="#" class="social-btn">
                <i class="fab fa-apple" style="font-size: 1.2rem;"></i> Apple
            </a>
        </div>

        <div class="footer-text">
            Don't have an account? <a href="register.php">Create Account</a>
        </div>
    </div>
</div>

<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
    // -- GOOGLE SIGN-IN SETUP --
    let tokenClient;

    window.onload = function() {
        // 1. Initialize the Token Client (Popup flow)
        tokenClient = google.accounts.oauth2.initTokenClient({
            client_id: '<?php echo GOOGLE_CLIENT_ID; ?>',
            scope: 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
            callback: (response) => {
                // 2. Handle the response from the Popup
                if (response.error) {
                    alert("Google Sign-In Error: " + response.error);
                    return;
                }
                
                if (response.access_token) {
                    processGoogleLogin(response.access_token);
                }
            },
        });
    };

    // 3. Triggered when user clicks "Google" button
    function triggerGoogleLogin(e) {
        e.preventDefault();
        if (tokenClient) {
            tokenClient.requestAccessToken();
        } else {
            alert("Google Sign-In is loading... Try again in a second.");
        }
    }

    // 4. Send token to backend
    function processGoogleLogin(accessToken) {
        fetch('google-process-token.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ access_token: accessToken })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'dashboard.php';
            } else {
                alert("Login Failed: " + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("System Error during Google Login");
        });
    }

    // -- PASSWORD TOGGLE --
    const togglePswd = document.getElementById('togglePswd');
    if(togglePswd) {
        togglePswd.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }
</script>

</body>
</html>