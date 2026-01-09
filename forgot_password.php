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
                // Check if error is due to missing columns (1054 Unknown column)
                if ($e->errorInfo[1] == 1054) {
                    // Start transaction
                    try {
                        $pdo->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL");
                    } catch (Exception $ex) {} // Ignore if exists
                    
                    try {
                        $pdo->exec("ALTER TABLE users ADD COLUMN reset_expires DATETIME DEFAULT NULL");
                    } catch (Exception $ex) {} // Ignore if exists
                    
                    // Retry the update
                    $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                    $stmt->execute([$token, $expires, $user['id']]);
                } else {
                    throw $e; // Re-throw other errors
                }
            }

            $reset_link = "http://localhost/MINIPROJECT2.0/reset_password.php?token=" . $token;

            $subject = "WALKON Shoes - Password Reset";
            $message = "
            <html>
                <body style='font-family:Arial,sans-serif;background:#f0f2f5;padding:20px;'>
                    <div style='max-width:600px;margin:auto;background:white;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.1);'>
                        <div style='background:#28a745;color:white;text-align:center;padding:30px;'>
                            <h1>WALKON Shoes</h1>
                            <p>Multi-Channel E-Commerce Platform</p>
                        </div>
                        <div style='padding:40px;text-align:center;'>
                            <h2>Reset Your Password</h2>
                            <p>Click the button below to set a new password:</p>
                            <a href='$reset_link' style='display:inline-block;margin:30px 0;background:#28a745;color:white;padding:16px 32px;text-decoration:none;border-radius:12px;font-weight:bold;'>Reset Password</a>
                            <p>This link expires in 1 hour.</p>
                        </div>
                    </div>
                </body>
            </html>
            ";

            $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: no-reply@walkonshoes.local";

            // For Localhost/Testing: Display link directly instead of failing mail()
            $success = "Password reset link generated successfully!<br>
                        <a href='$reset_link' style='color:#a78bfa;font-weight:bold;text-decoration:underline;'>Click here to reset password</a>";
            
            // Commenting out mail function to prevent timeout on localhost
            /* 
            if (mail($email, $subject, $message, $headers)) {
                $success = "Reset link sent! Check your email.";
            } else {
                $error = "Failed to send email. Try again.";
            }
            */
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
    <title>Forgot Password - WALKON Shoes</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body, html { height:100%; background:#0f172a; color:white; }
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
            min-height: 70vh;
            background: #1e293b;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0,0,0,0.6);
        }
        .left-image {
            flex: 1;
            position: relative;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.8)),
                        url('https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: flex-end;
            padding: 60px;
        }
        .left-image h2 {
            font-size: 36px;
            font-weight: 700;
            line-height: 1.3;
            max-width: 80%;
            z-index: 2;
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
            margin-bottom: 30px;
        }
        input[type="email"] {
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
            transition: 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(147,51,234,0.4);
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
        .success {
            background: rgba(34,197,94,0.2);
            color: #86efac;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 20px;
            border-left: 4px solid #22c55e;
        }
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
            .right-form {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-wrapper">
            <div class="left-image">
                <h2>Reset Your<br>Password</h2>
            </div>

            <div class="right-form">
                <div class="logo">
                    <h1>WALKON</h1>
                </div>

                <div class="welcome-text">
                    <h2>Forgot Password?</h2>
                    <p>Enter your email and we'll send you a link to reset your password</p>
                </div>

                <?php if ($error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required placeholder="Enter your email">
                    </div>

                    <button type="submit" class="btn-login">Send Reset Link</button>
                </form>

                <div class="links">
                    Remember password? <a href="login.php">Back to Login</a>
                </div>
            </div>
        </div>
    </div>

    <button id="scrollTopBtn" title="Go to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
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