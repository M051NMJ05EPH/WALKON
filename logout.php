<?php
session_start();

// Log logout before destroying session
if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
    require_once 'config.php';
    require_once 'includes/activity_logger.php';
    
    $logger = new ActivityLogger($pdo);
    $logger->logLogout($_SESSION['user_id'], $_SESSION['email']);
}

session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --primary-hover: #059669;
            --bg: #FFFFFF;
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
            background-color: #F9FAFB;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .logout-card {
            width: 100%;
            max-width: 480px;
            background: var(--bg);
            border-radius: 32px;
            padding: 4rem 3rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .icon-box {
            width: 80px;
            height: 80px;
            background: rgba(16, 185, 129, 0.1);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 2rem;
        }

        .logo img {
            height: 40px;
        }

        .logo span {
            font-size: 1.5rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: -1px;
        }

        h2 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
        }

        p {
            color: var(--text-light);
            margin-bottom: 2.5rem;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: var(--primary);
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
        }

        .btn-home:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(16, 185, 129, 0.3);
        }

        .timer-info {
            margin-top: 2rem;
            font-size: 0.95rem;
            color: var(--text-light);
        }

        #countdown {
            font-weight: 700;
            color: var(--primary);
        }
    </style>
</head>
<body>

<div class="logout-card">
    <div class="logo">
        <img src="assets/shoe_logo_green.png" alt="WalkOn">
        <div style="font-family: 'Outfit', sans-serif;">
            <span style="color: #1F2937;">Walk</span><span style="color: #10b981;">on</span>
        </div>
    </div>

    <div class="icon-box">
        <i class="fas fa-sign-out-alt"></i>
    </div>

    <h2>Safely Logged Out</h2>
    <p>Your session has ended successfully. Thank you for using Walkon platform today.</p>

    <a href="Index.php" class="btn-home">
        <i class="fas fa-home"></i> Go to Homepage
    </a>

    <div class="timer-info">
        Redirecting in <span id="countdown">5</span> seconds...
    </div>
</div>

<script>
    let seconds = 5;
    const countdownElement = document.getElementById('countdown');

    const timer = setInterval(() => {
        seconds--;
        countdownElement.textContent = seconds;

        if (seconds <= 0) {
            clearInterval(timer);
            window.location.href = 'Index.php';
        }
    }, 1000);
</script>

</body>
</html>