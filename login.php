<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WALKON Supplier</title>
    <style>/* Same style as above */</style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">WALKON</div>
            <h2 class="headline">Welcome Back</h2>
            <p class="subline">Log in to manage your shoe listings</p>
        </div>
        <div style="padding:40px 30px;">
            <form id="loginform">
                <div style="margin-bottom:20px;">
                    <input type="email" name="email" placeholder="Email" style="width:100%; padding:14px; border-radius:30px; border:1px solid #ddd;" required>
                </div>
                <div style="margin-bottom:20px;">
                    <input type="password" name="password" placeholder="Password" style="width:100%; padding:14px; border-radius:30px; border:1px solid #ddd;" required>
                </div>
                <button type="submit" class="btn">Log In</button>
            </form>

            <p style="margin-top:30px;"><a href="signup.php" class="link">New supplier? Sign up here</a></p>
        </div>
    </div>

    <script>
        document.getElementById('loginform').onsubmit = function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fetch('login-process.php', {method:'POST', body:fd})
            .then(r => r.json())
            .then(data => {
                if (data.success) window.location.href = 'products.php';
                else alert('Invalid login');
            });
        };
    </script>
</body>
</html>