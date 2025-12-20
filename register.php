<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account - WALKON</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(to bottom, #f8f9fa, #e9ecef); }
        .card { max-width: 450px; margin: 50px auto; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .logo { font-size: 2.5rem; font-weight: bold; color: #007bff; }
        .tagline { color: #6c757d; }
        .password-toggle { cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card p-5">
            <div class="text-center mb-4">
                <h1 class="logo">WALKON</h1>
                <p class="tagline">Join WALKON and step into comfort</p>
            </div>
            <form>
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" value="MOSIN M JOSEPH" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="mosinmjoseph2028@mca.e" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <input id="password" type="password" class="form-control" required>
                        <span class="input-group-text password-toggle">
                            <i id="toggleIcon" class="bi bi-eye"></i>
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <input id="confirmPassword" type="password" class="form-control" required>
                        <span class="input-group-text password-toggle">
                            <i id="toggleIconConfirm" class="bi bi-eye"></i>
                        </span>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Sign Up</button>
                <div class="text-center my-3">or</div>
                <button type="button" class="btn btn-outline-danger w-100">
                    <i class="bi bi-google"></i> Sign up with Google
                </button>
                <p class="text-center mt-4">Already have an account? <a href="#">Login</a></p>
            </form>
        </div>
    </div>

    <script>
        // Toggle for Password field
        document.querySelectorAll('.password-toggle').forEach(item => {
            item.addEventListener('click', function () {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                }
            });
        });
    </script>
</body>
</html>