<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - WALKON Supplier</title>
    <style>
        body {
            background: linear-gradient(to bottom, #e0f7fa, #ffffff);
            font-family: 'Roboto', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            max-width: 450px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            text-align: center;
        }
        .header {
            background: linear-gradient(135deg, #00bcd4, #00796b);
            color: white;
            padding: 70px 20px 60px;
            position: relative;
        }
        .header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 40px;
            background: white;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }
        .logo {
            font-size: 42px;
            font-weight: 300;
            font-style: italic;
            margin-bottom: 10px;
        }
        .headline {
            font-size: 28px;
            font-weight: 500;
            margin: 15px 0 8px;
        }
        .subline {
            font-size: 16px;
            opacity: 0.9;
        }
        .form-section {
            padding: 30px 40px 50px;
        }
        .row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .input-group {
            flex: 1;
            text-align: left;
        }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
            font-weight: 500;
        }
        .input-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #ddd;
            border-radius: 30px;
            font-size: 16px;
            background: #f9f9f9;
        }
        .input-group input:focus {
            outline: none;
            border-color: #00bcd4;
            background: white;
        }
        .password-wrapper {
            position: relative;
        }
        .eye-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 20px;
        }
        .create-btn {
            width: 100%;
            padding: 16px;
            background: #00796b;
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 18px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 20px;
        }
        .create-btn:hover {
            background: #004d40;
        }
        .login-link {
            margin-top: 30px;
            font-size: 14px;
            color: #555;
        }
        .login-link a {
            color: #00bcd4;
            text-decoration: none;
            font-weight: 500;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">WALKON</div>
            <h2 class="headline">Create Your Account</h2>
            <p class="subline">Complete your supplier profile to start selling shoes</p>
        </div>

        <div class="form-section">
            <form id="createAccountForm">
                <div class="row">
                    <div class="input-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" placeholder="Mosin" required>
                    </div>
                    <div class="input-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" placeholder="M Joseph" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Business Name *</label>
                    <input type="text" name="business_name" placeholder="e.g. Mosin Footwear" required>
                </div>

                <div class="input-group">
                    <label>Phone Number *</label>
                    <input type="tel" name="phone" placeholder="+91 9876543210" required>
                </div>

                <div class="input-group">
                    <label>Password *</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required minlength="8">
                        <span class="eye-toggle" onclick="togglePassword('password')">👁️</span>
                    </div>
                </div>

                <div class="input-group">
                    <label>Confirm Password *</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <span class="eye-toggle" onclick="togglePassword('confirm_password')">👁️</span>
                    </div>
                </div>

                <button type="submit" class="create-btn">Create Account & Continue</button>
            </form>

            <p class="login-link">
                Already have an account? <a href="login.php">Log In</a>
            </p>
        </div>
    </div>

    <script>
        function togglePassword(id) {
            const field = document.getElementById(id);
            field.type = field.type === 'password' ? 'text' : 'password';
        }

        document.getElementById('createAccountForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;

            if (password !== confirm) {
                alert('Passwords do not match!');
                return;
            }

            const formData = new FormData(this);

            fetch('create-account-process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Account created successfully! Welcome to WALKON.');
                    window.location.href = 'products.php';
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(() => alert('Network error. Please try again.'));
        });
    </script>
</body>
</html>