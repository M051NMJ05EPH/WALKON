<?php
session_start();
include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $terms = isset($_POST['terms']);

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = "Please fill in all fields!";
    } elseif (!$terms) {
        $error = "You must agree to the Terms & Conditions!";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = $_POST['role'] ?? 'customer';
            if (!in_array($role, ['entrepreneur', 'store', 'customer'])) $role = 'customer';
            
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            
            if ($stmt->execute([$first_name, $last_name, $email, $hashed_password, $role])) {
                $success = "Account created successfully! <a href='login.php'>Login here</a>";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an account - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            --font-heading: 'Outfit', sans-serif;
            --accent: #10b981;        /* Emerald Green */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
            scroll-behavior: smooth;
        }

        body {
            background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 50%, #e0f2fe 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            background-attachment: fixed;
            overflow-x: hidden;
        }

        .auth-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .visual-side {
            flex: 1.2;
            background: linear-gradient(135deg, #10b981 0%, #2563eb 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem;
            color: #fff;
            overflow: hidden;
        }

        .visual-side::before {
            content: '';
            position: absolute;
            width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=2012&auto=format&fit=crop') no-repeat center center/cover;
            opacity: 0.5;
            mix-blend-mode: overlay;
        }

        .visual-content {
            z-index: 10;
            max-width: 500px;
            text-align: center;
        }

        .visual-content h2 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 2rem;
            letter-spacing: -2px;
            text-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .benefit-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 15px 25px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: left;
        }

        .benefit-item i {
            color: #fff;
            font-size: 1.5rem;
        }

        .scroll-down-hint {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            color: #fff;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            z-index: 15;
            transition: 0.3s;
            opacity: 0.8;
        }

        .scroll-down-hint:hover {
            opacity: 1;
            transform: translateX(-50%) translateY(5px);
        }

        .scroll-down-hint i {
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-10px);}
            60% {transform: translateY(-5px);}
        }

        .form-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
        }

        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--primary);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 1000;
            border: none;
            font-size: 1.2rem;
        }

        .back-to-top.active {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            transform: translateY(-5px);
            background: var(--primary-hover);
        }

        .form-content {
            width: 100%;
            max-width: 440px;
        }

        .header {
            margin-bottom: 3rem;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            letter-spacing: -1px;
            color: var(--text-main);
        }

        .header p {
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .header a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .name-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .input-wrapper {
            position: relative;
        }

        input {
            width: 100%;
            padding: 1.2rem 1.5rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            font-size: 1rem;
            color: var(--text-main);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            transform: translateY(-2px);
        }

        .btn-submit {
            width: 100%;
            padding: 1.2rem;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.2);
        }

        .btn-submit:hover {
            background: var(--primary-hover);
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(37, 99, 235, 0.3);
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 1rem;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            text-decoration: none;
            color: var(--text-main);
            font-weight: 600;
            transition: all 0.3s;
        }

        .social-btn:hover {
            background: #f8fafc;
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .social-btn img {
            height: 22px;
        }

        .divider {
            display: flex; align-items: center; gap: 1rem;
            margin: 2rem 0; color: #cbd5e1; font-size: 0.9rem;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: #e2e8f0;
        }

        .social-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .msg {
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            text-align: center;
        }

        .error { background: rgba(239, 68, 68, 0.1); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.2); }
        .success { background: rgba(16, 185, 129, 0.1); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.2); }

        @media (max-width: 968px) {
            .auth-container {
                flex-direction: column;
            }
            .visual-side {
                min-height: 100vh;
                padding: 4rem 2rem;
            }
            .form-side {
                padding: 4rem 1.5rem;
                background: #f8fafc;
            }
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="visual-side">
        <div class="visual-content">
            <h2>Start your journey with <span style="color: var(--primary);">Walkon</span>.</h2>
            
            <div class="benefit-item">
                <i class="fas fa-percent"></i>
                <div>
                    <strong>0% Selling Fees</strong>
                    <p style="font-size: 0.85rem; color: #94a3b8; margin: 0;">Keep 100% of your profits for the first 3 months.</p>
                </div>
            </div>

            <div class="benefit-item">
                <i class="fas fa-sync"></i>
                <div>
                    <strong>Instant Marketplace Sync</strong>
                    <p style="font-size: 0.85rem; color: #94a3b8; margin: 0;">List once, sell on Amazon, Shopify & more.</p>
                </div>
            </div>

            <div class="benefit-item">
                <i class="fas fa-rocket"></i>
                <div>
                    <strong>Fast Payouts</strong>
                    <p style="font-size: 0.85rem; color: #94a3b8; margin: 0;">Get your earnings directly in your bank account.</p>
                </div>
            </div>

            <a href="#register-header" class="scroll-down-hint">
                <span>Start Application</span>
                <i class="fas fa-chevron-down"></i>
            </a>
        </div>
    </div>
    
    <div class="form-side">
        <div class="form-content" id="register-header">
            <div class="header">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 2.5rem;">
                    <img src="assets/shoe_logo_green.png" alt="WalkOn Logo" style="height: 44px; width: auto;">
                    <div style="font-family: 'Outfit', sans-serif; font-size: 30px; font-weight: 800; line-height: 1; letter-spacing: -0.5px; text-transform: uppercase;">
                        <span style="color: var(--text-main);">Walk</span><span style="color: var(--accent);">on</span>
                    </div>
                </div>
                <h1>Join the Elite</h1>
                <p>Already a seller? <a href="login.php">Welcome back!</a></p>
            </div>

            <?php if ($error): ?><div class="msg error"><?php echo $error; ?></div><?php endif; ?>
            <?php if ($success): ?><div class="msg success"><?php echo $success; ?></div><?php endif; ?>

            <form action="register.php" method="POST">
                <div class="name-row">
                    <input type="text" name="first_name" placeholder="First name" required>
                    <input type="text" name="last_name" placeholder="Last name" required>
                </div>

                <div class="form-group">
                    <input type="email" name="email" placeholder="Email address" required>
                </div>

                <div class="form-group">
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" placeholder="Create password" required>
                        <i class="fas fa-eye-slash" id="togglePassword" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer;"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 10px; color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">Join as a:</label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        <label style="display: flex; flex-direction: column; align-items: center; gap: 8px; background: #f8fafc; padding: 10px 5px; border-radius: 12px; border: 1px solid #e2e8f0; cursor: pointer; text-align: center; color: var(--text-main); transition: 0.3s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='#e2e8f0'">
                            <input type="radio" name="role" value="customer" checked style="width: auto;">
                            <span style="font-size: 0.8rem; font-weight: 600;">Customer</span>
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center; gap: 8px; background: #f8fafc; padding: 10px 5px; border-radius: 12px; border: 1px solid #e2e8f0; cursor: pointer; text-align: center; color: var(--text-main); transition: 0.3s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='#e2e8f0'">
                            <input type="radio" name="role" value="entrepreneur" style="width: auto;">
                            <span style="font-size: 0.8rem; font-weight: 600;">Entrepreneur</span>
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center; gap: 8px; background: #f8fafc; padding: 10px 5px; border-radius: 12px; border: 1px solid #e2e8f0; cursor: pointer; text-align: center; color: var(--text-main); transition: 0.3s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='#e2e8f0'">
                            <input type="radio" name="role" value="store" style="width: auto;">
                            <span style="font-size: 0.8rem; font-weight: 600;">Store</span>
                        </label>
                    </div>
                </div>

                <label class="terms" style="display: flex; align-items: center; cursor: pointer; color: var(--text-muted);">
                    <input type="checkbox" name="terms" required style="width: auto; margin-right: 10px;">
                    <span style="font-size: 0.95rem;">I agree to the <a href="#" style="color: var(--primary); font-weight: 600;">Terms & Conditions</a></span>
                </label>

                <button type="submit" class="btn-submit">Join WalkOn</button>
            </form>

            <div class="divider">Or continue with</div>

            <div class="social-grid">
                <a href="#" onclick="triggerGoogleLogin(event)" class="social-btn">
                    <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" alt="Google">
                    Google
                </a>
                <a href="#" class="social-btn">
                    <i class="fab fa-apple" style="font-size: 1.3rem;"></i>
                    Apple
                </a>
            </div>
        </div>
    </div>
</div>

<button class="back-to-top" id="backToTop" title="Go to top">
    <i class="fas fa-arrow-up"></i>
</button>

<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
    // -- GOOGLE SIGN-IN SETUP --
    let tokenClient;

    window.onload = function() {
        tokenClient = google.accounts.oauth2.initTokenClient({
            client_id: '<?php echo GOOGLE_CLIENT_ID; ?>',
            scope: 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
            callback: (response) => {
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

    function triggerGoogleLogin(e) {
        e.preventDefault();
        if (tokenClient) {
            tokenClient.requestAccessToken();
        } else {
            alert("Google Sign-In is loading... Try again in a second.");
        }
    }

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
                alert("Registration Failed: " + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("System Error during Google Registration");
        });
    }

    // -- PASSWORD TOGGLE --
    const togglePassword = document.getElementById('togglePassword');
    if(togglePassword) {
        const password = document.getElementById('password');
        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }

    // -- SCROLL BUTTONS LOGIC --
    const backToTop = document.getElementById('backToTop');
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTop.classList.add('active');
        } else {
            backToTop.classList.remove('active');
        }
    });

    backToTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
</script>

</body>
</html>