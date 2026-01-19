<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - WALKON</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #16a34a, #22c55e);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
      padding: 20px;
    }
    .card {
      background: white;
      padding: 3.5rem 3rem;
      border-radius: 28px;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.22);
      max-width: 460px;
      width: 100%;
      text-align: center;
      animation: fadeIn 0.8s ease-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .logo-svg {
      height: 70px;
      margin-bottom: 1.8rem;
      transition: transform 0.3s ease;
    }
    .logo-svg:hover {
      transform: scale(1.05);
    }
    h2 {
      font-size: 2rem;
      font-weight: 700;
      color: #1e293b;
      margin: 0 0 0.8rem;
    }
    p {
      color: #64748b;
      font-size: 1.05rem;
      margin-bottom: 2.5rem;
      line-height: 1.6;
    }
    form {
      text-align: left;
    }
    input {
      width: 100%;
      padding: 1.3rem 1.5rem;
      margin-bottom: 1.5rem;
      border: 2px solid #e2e8f0;
      border-radius: 18px;
      font-size: 1.1rem;
      transition: all 0.3s ease;
    }
    input:focus {
      outline: none;
      border-color: #16a34a;
      box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.15);
    }
    .btn {
      width: 100%;
      padding: 1.3rem;
      background: linear-gradient(to right, #16a34a, #22c55e);
      color: white;
      border: none;
      border-radius: 18px;
      font-size: 1.1rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 8px 20px rgba(22, 163, 74, 0.3);
    }
    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 28px rgba(22, 163, 74, 0.4);
    }
    .link {
      margin-top: 2rem;
    }
    .link a {
      color: #16a34a;
      font-weight: 600;
      text-decoration: none;
      font-size: 1.05rem;
      transition: color 0.3s ease;
    }
    .link a:hover {
      color: #15803d;
      text-decoration: underline;
    }
    @media (max-width: 480px) {
      .card { padding: 2.5rem 2rem; border-radius: 24px; }
      .logo-svg { height: 60px; }
    }
  </style>
</head>
<body>

  <div class="card">
    <!-- Premium WALKON SVG Logo -->
    <a href="index.php" aria-label="WALKON Home" style="display: flex; align-items: center; justify-content: center; gap: 15px; text-decoration: none; margin-bottom: 1.8rem;">
      <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M2 17C2 17 2 19 4 19H20C22 19 22 17 22 17V15L18 10H14L10 12L5 14L2 17Z" fill="#10b981"/>
          <path d="M14 10L12 7H9L7 9" stroke="#10b981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M11 11.5L12 13.5M14 10.5L15 12.5M17 9.5L18 11.5" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
      </svg>
      <span style="font-size: 42px; font-weight: 900; color: #1e293b; letter-spacing: 1.5px;">WALKON</span>
    </a>

    <h2>Reset Your Password</h2>
    <p>Enter your email address and we'll send you a link to regain access to your account.</p>

    <form action="send-reset-link.php" method="POST">
      <input type="email" name="email" placeholder="business@email.com" required aria-label="Email address">
      <button type="submit" class="btn">Send Reset Link</button>
    </form>

    <div class="link">
      <a href="login.php">← Back to Login</a>
    </div>
  </div>

</body>
</html>