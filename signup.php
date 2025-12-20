<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WALKON - Start Selling Shoes</title>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <style>
        /* Zypheris Teal Style */
        body {background: linear-gradient(to bottom, #e0f7fa, #ffffff); font-family: 'Roboto', sans-serif; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; padding:20px;}
        .container {background:white; max-width:420px; width:100%; border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.1); overflow:hidden; text-align:center;}
        .header {background:linear-gradient(135deg,#00bcd4,#00796b); color:white; padding:70px 20px 60px; position:relative;}
        .header::after {content:''; position:absolute; bottom:0; left:0; width:100%; height:40px; background:white; border-radius:50% 50% 0 0 / 100% 100% 0 0;}
        .logo {font-size:42px; font-weight:300; font-style:italic;}
        .headline {font-size:28px; font-weight:500; margin:20px 0 10px;}
        .subline {font-size:16px; opacity:0.9;}
        .btn {width:100%; padding:16px; background:#00796b; color:white; border:none; border-radius:30px; font-size:18px; cursor:pointer; margin:30px 0;}
        .btn:hover {background:#004d40;}
        .link {color:#00bcd4; text-decoration:none; font-weight:500;}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">WALKON</div>
            <h2 class="headline">Start Selling Shoes Today</h2>
            <p class="subline">Join thousands of suppliers reaching customers worldwide</p>
        </div>
        <div style="padding:40px 30px;">
            <div id="g_id_onload" data-client_id="YOUR_CLIENT_ID.apps.googleusercontent.com" data-callback="handleGoogle"></div>
            <div class="g_id_signin" data-type="standard" data-size="large" data-theme="outline" data-text="continue_with" data-shape="pill" data-logo_alignment="left"></div>

            <button onclick="location.href='login.php'" class="btn" style="margin-top:40px;">Already have an account? Log In</button>
        </div>
    </div>

    <script>
        function handleGoogle(response) {
            fetch('google-login.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'credential=' + response.credential
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) window.location.href = data.redirect;
            });
        }
    </script>
</body>
</html>