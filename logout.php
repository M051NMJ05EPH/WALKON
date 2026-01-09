<?php
session_start();
session_destroy(); // Clears all session data
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - Walkon Shoes</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        .container {
            background: white;
            width: 90%;
            max-width: 500px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            text-align: center;
        }

        .header {
            background: #28a745;
            color: white;
            padding: 40px 20px;
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .content {
            padding: 50px 30px;
        }

        .icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .message h2 {
            font-size: 26px;
            margin-bottom: 15px;
            color: #333;
        }

        .message p {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 14px 32px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: 0.3s;
        }

        .btn:hover {
            background: #218838;
        }

        .redirect-info {
            margin-top: 30px;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>WALKON</h1>
            <p>Seller Portal</p>
        </div>

        <div class="content">
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>

            <div class="message">
                <h2>Logged Out Successfully</h2>
                <p>You have been securely logged out of your Walkon Shoes seller account.<br>Thank you for managing your listings with us!</p>
                
                <a href="Index.php" class="btn">
                    <i class="fas fa-home"></i> Go to Home Page
                </a>
            </div>

            <div class="redirect-info">
                Redirecting you to home page in <span id="countdown">5</span> seconds...
            </div>
        </div>
    </div>

    <script>
        // Auto redirect after 5 seconds
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