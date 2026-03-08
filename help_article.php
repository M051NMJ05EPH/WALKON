<?php
include 'config.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$article = null;

if ($slug) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM help_pages WHERE slug = ?");
        $stmt->execute([$slug]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle error or just ignore
    }
}

if (!$article) {
    header("Location: support.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - Help Center</title>
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
            color: #333;
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .container { max-width: 1000px; margin: 0 auto; }
        
        /* Navigation */
        .back-button {
            display: inline-flex; align-items: center; gap: 10px; color: white;
            text-decoration: none; font-weight: 600; margin-bottom: 30px;
            padding: 12px 24px; background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px); border-radius: 50px;
            border: 2px solid rgba(255, 255, 255, 0.3); transition: all 0.3s ease;
        }
        .back-button:hover { background: rgba(255, 255, 255, 0.3); transform: translateX(-5px); }
        
        /* Header */
        .header { text-align: center; margin-bottom: 60px; animation: fadeInDown 0.8s ease; }
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
        .header p { font-size: 1.3rem; color: rgba(255, 255, 255, 0.95); font-weight: 400; }

        /* Content Card Generic */
        .content-card {
            background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(30px);
            border-radius: 30px; padding: 50px; border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1); margin-bottom: 30px;
            animation: fadeInUp 0.8s ease;
        }
        .content-card h2 {
            font-size: 2rem; font-weight: 700; color: white; margin-bottom: 20px;
            display: flex; align-items: center; gap: 15px;
        }
        .content-card p, .content-card li {
            font-size: 1.1rem; color: rgba(255, 255, 255, 0.95); line-height: 1.8; margin-bottom: 20px;
        }
        .content-card ul { list-style: none; padding: 0; }
        .content-card ul li { padding-left: 35px; position: relative; }
        .content-card ul li::before {
            content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
            position: absolute; left: 0; color: white; background: rgba(255, 255, 255, 0.3);
            width: 24px; height: 24px; border-radius: 50%; display: flex;
            align-items: center; justify-content: center; font-size: 0.8rem;
        }

        /* Tracking Tool */
        .tracking-tool {
            background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(20px);
            border-radius: 20px; padding: 40px; border: 2px solid rgba(255, 255, 255, 0.3); margin-top: 30px;
        }
        .tracking-input-group { display: flex; gap: 15px; margin-bottom: 20px; }
        .tracking-input-group input {
            flex: 1; padding: 18px 25px; border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px);
            border-radius: 50px; font-size: 1.1rem; color: white; outline: none;
        }
        .tracking-input-group input::placeholder { color: rgba(255, 255, 255, 0.7); }
        .tracking-input-group button {
            padding: 18px 40px; border: none; background: white; color: #667eea;
            font-size: 1.1rem; font-weight: 700; border-radius: 50px; cursor: pointer;
        }

        /* Payment Grid */
        .payment-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 30px; }
        .payment-box {
            background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(20px);
            padding: 30px; border-radius: 20px; border: 2px solid rgba(255, 255, 255, 0.3);
            text-align: center; transition: all 0.3s ease;
        }
        .payment-box:hover { transform: translateY(-5px); background: rgba(255, 255, 255, 0.3); }
        .payment-box i { font-size: 3rem; color: white; margin-bottom: 15px; }
        .payment-box h4 { font-size: 1.2rem; font-weight: 700; color: white; }

        /* Steps Grid (Returns) */
        .steps-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 30px; }
        .step-card {
            background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(20px);
            padding: 30px; border-radius: 20px; border: 2px solid rgba(255, 255, 255, 0.3);
            text-align: center;
        }
        .step-number {
            width: 60px; height: 60px; background: white; color: #f5576c;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; font-weight: 800; margin: 0 auto 20px;
        }
        .step-card h4 { font-size: 1.2rem; font-weight: 700; color: white; margin-bottom: 10px; }

        /* Contact Box */
        .contact-box {
            background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(20px);
            padding: 30px; border-radius: 20px; border: 2px solid rgba(255, 255, 255, 0.3);
            margin: 20px 0; transition: all 0.3s ease; text-align: center;
        }
        .contact-box i { font-size: 3rem; color: white; margin-bottom: 15px; }
        .contact-box h3 { font-size: 1.5rem; font-weight: 700; color: white; margin-bottom: 10px; }
        .contact-box a { color: white; text-decoration: none; font-size: 1.2rem; font-weight: 600; }

        /* Table (Size Guide) */
        table {
            width: 100%; border-collapse: collapse; margin-top: 20px;
            background: rgba(255, 255, 255, 0.15); border-radius: 15px; overflow: hidden;
        }
        th, td { padding: 15px; text-align: center; color: white; border-bottom: 1px solid rgba(255, 255, 255, 0.2); }
        th { background: rgba(255, 255, 255, 0.2); font-weight: 700; }

        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="header">
            <div class="icon-hero">
                <i class="<?php echo htmlspecialchars($article['icon_class']); ?>"></i>
            </div>
            <h1><?php echo htmlspecialchars($article['title']); ?></h1>
            <p><?php echo htmlspecialchars($article['summary']); ?></p>
        </div>

        <!-- Dynamic Content Rendered Here -->
        <?php echo $article['content']; ?>
        
    </div>
</body>
</html>
