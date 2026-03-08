<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Customer Support - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            padding: 40px 20px;
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .container { max-width: 1000px; margin: 0 auto; }
        .back-button {
            display: inline-flex; align-items: center; gap: 10px; color: white;
            text-decoration: none; font-weight: 600; margin-bottom: 30px;
            padding: 12px 24px; background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px); border-radius: 50px;
            border: 2px solid rgba(255, 255, 255, 0.3); transition: all 0.3s ease;
        }
        .back-button:hover { background: rgba(255, 255, 255, 0.3); transform: translateX(-5px); }
        .header { text-align: center; margin-bottom: 60px; }
        .icon-hero {
            width: 120px; height: 120px; margin: 0 auto 30px;
            background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(20px);
            border-radius: 30px; display: flex; align-items: center;
            justify-content: center; border: 2px solid rgba(255, 255, 255, 0.4);
        }
        .icon-hero i { font-size: 4rem; color: white; }
        .header h1 {
            font-size: 3.5rem; font-weight: 800; color: white;
            margin-bottom: 20px; text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .header p { font-size: 1.3rem; color: rgba(255, 255, 255, 0.95); }
        .content-card {
            background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(30px);
            border-radius: 30px; padding: 40px; border: 2px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 30px; text-align: center;
        }
        .content-card h2 {
            font-size: 2rem; font-weight: 700; color: white; margin-bottom: 20px;
        }
        .content-card p {
            font-size: 1.1rem; color: rgba(255, 255, 255, 0.95);
            line-height: 1.8; margin-bottom: 15px;
        }
        .contact-box {
            background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(20px);
            padding: 30px; border-radius: 20px; border: 2px solid rgba(255, 255, 255, 0.3);
            margin: 20px 0; transition: all 0.3s ease;
        }
        .contact-box:hover { transform: scale(1.02); background: rgba(255, 255, 255, 0.35); }
        .contact-box i { font-size: 3rem; color: white; margin-bottom: 15px; }
        .contact-box h3 { font-size: 1.5rem; font-weight: 700; color: white; margin-bottom: 10px; }
        .contact-box a {
            color: white; text-decoration: none; font-size: 1.2rem; font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="help_support.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Help Center
        </a>
        <div class="header">
            <div class="icon-hero"><i class="fas fa-headset"></i></div>
            <h1>Live Customer Support</h1>
            <p>We're here to help you 24/7</p>
        </div>
        <div class="content-card">
            <h2>Get in Touch</h2>
            <p>Our team of footwear experts is ready to assist you with any questions or concerns.</p>
            <div class="contact-box">
                <i class="fas fa-phone"></i>
                <h3>Call Us</h3>
                <a href="tel:+911234567890">+91 123 456 7890</a>
                <p style="margin-top: 10px; font-size: 0.95rem;">Available 24/7</p>
            </div>
            <div class="contact-box">
                <i class="fas fa-envelope"></i>
                <h3>Email Support</h3>
                <a href="mailto:support@walkon.com">support@walkon.com</a>
                <p style="margin-top: 10px; font-size: 0.95rem;">Response within 24 hours</p>
            </div>
            <div class="contact-box">
                <i class="fas fa-comments"></i>
                <h3>Live Chat</h3>
                <p>Click the chat icon in the bottom right corner to start a conversation with our team instantly!</p>
            </div>
        </div>
    </div>
</body>
</html>
