<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WALKON - Login Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .header {
            background: linear-gradient(to right, #e0f7e0, #c8e6c9);
            padding: 60px 20px;
            border-radius: 0 0 30px 30px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
        }
        .logo {
            font-size: 4.5rem;
            font-weight: bold;
            color: #2e7d32;
        }
        .welcome {
            font-size: 2.5rem;
            color: #1b5e20;
            margin-top: 10px;
        }
        .success-box {
            background-color: #e8f5e9;
            border-radius: 25px;
            padding: 35px;
            text-align: center;
            max-width: 600px;
            margin: 50px auto;
            box-shadow: 0 6px 15px rgba(0,0,0,0.08);
            color: #2e7d32;
            font-size: 1.6rem;
            font-weight: 600;
            border: 2px solid #c8e6c9;
        }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="header">
            <div class="logo">WALKON</div>
            <div class="welcome">Welcome Back</div>
        </div>

        <div class="success-box">
            Login Successful! Redirecting to Dashboard...
        </div>
    </div>

    <!-- Auto redirect to product.php after 3 seconds -->
    <script>
        setTimeout(function() {
            window.location.href = "product.php";
        }, 3000);
    </script>
</body>
</html>