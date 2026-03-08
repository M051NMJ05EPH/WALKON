<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Security - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-purple: #a855f7;
            --primary-purple-dark: #9333ea;
            --bg-purple: #c084fc;
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-white: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.8);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-purple);
            color: var(--text-white);
            min-height: 100vh;
            padding: 40px 20px;
            overflow-x: hidden;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 40px;
            padding: 10px 20px;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            border: 1px solid var(--glass-border);
            transition: 0.3s;
        }

        .back-btn:hover { background: rgba(255, 255, 255, 0.25); transform: translateX(-5px); }

        .header {
            text-align: center;
            margin-bottom: 60px;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .shield-icon {
            width: 100px;
            height: 100px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            border: 1px solid var(--glass-border);
            font-size: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            letter-spacing: -1px;
        }

        .header p {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .security-card {
            background: var(--glass-bg);
            backdrop-filter: blur(30px);
            border-radius: 30px;
            padding: 45px;
            border: 1px solid var(--glass-border);
            margin-bottom: 30px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        }

        .card-title {
            font-size: 1.8rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .card-subtext {
            color: var(--text-muted);
            margin-bottom: 25px;
            font-size: 1.05rem;
        }

        .security-list {
            list-style: none;
        }

        .security-list li {
            font-size: 1.1rem;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .security-list li i {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            padding: 15px 20px;
            border-radius: 15px;
            color: white;
            outline: none;
            transition: 0.3s;
        }

        .form-input:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: white;
        }

        .action-btn {
            background: white;
            color: var(--primary-purple-dark);
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .action-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        /* 2FA Toggle */
        .tfa-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-switch input { opacity: 0; width: 0; height: 0; }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(255, 255, 255, 0.2);
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px; width: 26px;
            left: 4px; bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider { background-color: #10b981; }
        input:checked + .slider:before { transform: translateX(26px); }

        @media (max-width: 768px) {
            .header h1 { font-size: 2.5rem; }
            .security-card { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="header">
            <div class="shield-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1>Account Security</h1>
            <p>Manage your password, enable two-factor authentication, and keep your personal data safe and secure.</p>
        </div>

        <!-- Password Management Tips -->
        <div class="security-card">
            <h2 class="card-title">
                <i class="fas fa-lock"></i> Password Management
            </h2>
            <p class="card-subtext">A strong password is your first line of defense. Here's how to create and manage a secure password:</p>
            
            <ul class="security-list">
                <li><i class="fas fa-check"></i> Use at least 12 characters mixing uppercase, lowercase, numbers, and symbols</li>
                <li><i class="fas fa-check"></i> Avoid using personal information like birthdays or names</li>
                <li><i class="fas fa-check"></i> Never reuse passwords across different platforms</li>
                <li><i class="fas fa-check"></i> Change your password every 3-6 months for maximum security</li>
            </ul>
        </div>

        <!-- Password Change Form -->
        <div class="security-card">
            <h2 class="card-title">
                <i class="fas fa-key"></i> Change Password
            </h2>
            <form action="#" method="POST">
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" class="form-input" placeholder="••••••••">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-input" placeholder="Minimum 12 chars">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-input" placeholder="Repeat password">
                    </div>
                </div>
                <button type="submit" class="action-btn">Update Password</button>
            </form>
        </div>

        <!-- 2FA Section -->
        <div class="security-card">
            <h2 class="card-title">
                <i class="fas fa-mobile-alt"></i> Two-Factor Authentication
            </h2>
            <p class="card-subtext">Add an extra layer of security to your account by requiring a code from your phone setup.</p>
            
            <div class="tfa-container">
                <div>
                    <h4 style="margin-bottom: 5px;">Enable 2FA via SMS/App</h4>
                    <p style="font-size: 0.9rem; color: var(--text-muted); margin: 0;">Protection for login and sensitive actions</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <!-- Login History -->
        <div class="security-card" style="margin-bottom: 60px;">
            <h2 class="card-title">
                <i class="fas fa-history"></i> Recent Login Activity
            </h2>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                            <th style="padding: 15px 0; color: var(--text-muted);">Device / Location</th>
                            <th style="padding: 15px 0; color: var(--text-muted);">Date & Time</th>
                            <th style="padding: 15px 0; color: var(--text-muted);">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 15px 0;">Windows PC • Mumbai, IN</td>
                            <td style="padding: 15px 0;">Today, 10:45 AM</td>
                            <td style="padding: 15px 0;"><span style="color: #10b981; font-weight: 700;">Current Session</span></td>
                        </tr>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 15px 0;">iPhone 13 • Delhi, IN</td>
                            <td style="padding: 15px 0;">Yesterday, 08:20 PM</td>
                            <td style="padding: 15px 0; color: var(--text-muted);">Successful</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
