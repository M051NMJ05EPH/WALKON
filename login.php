<?php
session_start();
include 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields!";
    } else {
        $stmt = $pdo->prepare("SELECT id, email, password, first_name, last_name, is_verified FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                header("Location: dashboard.php");
                exit();
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
    <title>Login - WALKON Shoes</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body, html { height:100%; background:#0f172a; color:white; overflow-x:hidden; }
        .container {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-wrapper {
            display: flex;
            width: 90%;
            max-width: 1100px;
            min-height: 80vh;
            background: #1e293b;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0,0,0,0.6);
        }
        .left-image {
            flex: 1;
            position: relative;
            display: flex;
            align-items: flex-end;
            padding: 60px;
            overflow: hidden;
            /* background removed in favor of video */
        }
        /* Video Background */
        .left-image video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }
        /* Overlay removed to show original image */
        .left-image h2 {
            font-size: 36px;
            font-weight: 700;
            line-height: 1.3;
            max-width: 80%;
            z-index: 2;
            position: relative;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5); /* Added subtle text shadow for readability */
        }
        .right-form {
            flex: 1;
            padding: 60px 50px;
            background: #1e293b;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .logo {
            text-align: center;
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
        .welcome-text h2 {
            font-size: 32px;
            margin-bottom: 8px;
            text-align: center;
        }
        .welcome-text p {
            text-align: center;
            color: #94a3b8;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        .form-group {
            margin-bottom: 20px;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 16px 20px;
            background: #334155;
            border: none;
            border-radius: 16px;
            color: white;
            font-size: 16px;
        }
        input::placeholder {
            color: #94a3b8;
        }
        input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(40,167,69,0.3);
        }
        .password-group {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 20px;
            top: 18px;
            color: #94a3b8;
            cursor: pointer;
            font-size: 18px;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            margin: 20px 0;
            font-size: 14px;
            color: #94a3b8;
        }
        .checkbox-group input {
            margin-right: 10px;
            accent-color: #28a745;
        }
        .btn-login {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #a78bfa, #9333ea);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 30px;
            transition: 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(147,51,234,0.4);
        }
        .divider {
            text-align: center;
            margin: 30px 0;
            color: #64748b;
            position: relative;
        }
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #334155;
        }
        .divider span {
            background: #1e293b;
            padding: 0 20px;
        }
        .social-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .social-btn {
            flex: 1;
            padding: 14px 24px;
            background: #1a1a1b;
            color: white;
            border: 1px solid #333;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
        }
        .social-btn:hover {
            background: #2d2d2e;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
            border-color: #444;
        }
        .social-btn img {
            width: 22px;
            height: 22px;
        }
        
        .links {
            text-align: center;
            margin-top: 30px;
            color: #94a3b8;
            font-size: 14px;
        }
        .links a {
            color: #a78bfa;
            text-decoration: none;
            font-weight: 500;
        }
        .error {
            background: rgba(239,68,68,0.2);
            color: #fca5a5;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 20px;
            border-left: 4px solid #ef4444;
        }

        /* Scroll to Top Button */
        #scrollTopBtn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 99;
            background: #28a745;
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(40,167,69,0.4);
            display: none;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        #scrollTopBtn:hover {
            background: #218838;
            transform: translateY(-5px);
        }
        #scrollTopBtn.show {
            display: flex;
        }

        @media (max-width: 992px) {
            .login-wrapper {
                flex-direction: column;
                height: auto;
                margin: 20px;
            }
            .left-image {
                height: 300px;
                padding: 40px;
            }
            .left-image h2 {
                font-size: 28px;
            }
            .right-form {
                padding: 40px 30px;
            }
            .social-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-wrapper">
            <!-- Left Side -->
            <div class="left-image" style="background: url('assets/login_premium.png') center/cover no-repeat;">
                <h2>Capturing Steps,<br>Creating Success</h2>
            </div>

            <!-- Right Side -->
            <div class="right-form">
                <div class="logo" style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 30px;">
                    <img src="assets/shoe_logo_green.png" alt="WalkOn Logo" style="height: 50px; width: auto;">
                    <div style="font-family: 'Outfit', sans-serif; font-size: 32px; font-weight: 800; line-height: 1; text-transform: uppercase; letter-spacing: -1px;">
                        <span style="color: #fff;">WALK</span><span style="color: #10b981;">ON</span>
                    </div>
                </div>

                <div class="welcome-text">
                    <h2>Welcome to WALKON Shoes!</h2>
                    <p>Log in to manage your shoe listings across our multi-channel e-commerce platform</p>
                </div>

                <?php if ($error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required placeholder="Email">
                    </div>

                    <div class="form-group password-group">
                        <input type="password" name="password" id="password" required placeholder="Enter your password">
                        <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="remember">
                        <label for="remember">Remember me</label>
                    </div>
                       <div class="links" style="text-align:center; margin-top:20px;">
                    <a href="forgot_password.php" style="color:#a78bfa; font-size:14px;">Forgot Password?</a>
                </div>

                    <button type="submit" class="btn-login">Log In</button>
                    
                       
  
                </form>

                <div class="divider"><span>Or sign in with</span></div>

                <div class="social-buttons">
                   <a href="google-login.php" class="social-btn google">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 48 48">
                            <path fill="#ea4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                            <path fill="#4285f4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                            <path fill="#fbbc05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24s.92 7.54 2.56 10.78l7.97-6.19z"/>
                            <path fill="#34a853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                            <path fill="none" d="M0 0h48v48H0z"/>
                        </svg>
                        Sign in with Google
                    </a>
                    <a href="#" class="social-btn" style="background-color: #000000; color: white; border: 1px solid #333;">
                        <i class="fab fa-apple" style="font-size:22px;"></i>
                        Apple
                    </a>
                </div>

                <div class="links">
                    Don't have an account? <a href="register.php">Sign up</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll to Top Button -->
    <button id="scrollTopBtn" title="Go to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        function togglePassword() {
            const field = document.getElementById('password');
            const icon = field.nextElementSibling;
            if (field.type === "password") {
                field.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                field.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }

        // Scroll to Top Button
        const scrollBtn = document.getElementById("scrollTopBtn");
        window.onscroll = function() {
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                scrollBtn.classList.add("show");
            } else {
                scrollBtn.classList.remove("show");
            }
        };
        scrollBtn.onclick = function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };
    </script>
</body>
</html>