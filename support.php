<?php
session_start();
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Help Center - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --dark-bg: #0B0F19;
            --dark-card: #151B2B;
            --dark-border: #2A3241;
            --text-main: #F1F5F9;
            --text-muted: #94A3B8;
        }
        body { font-family: 'Inter', sans-serif; background: var(--dark-bg); color: var(--text-main); margin: 0; padding: 0; }
        .navbar { background: rgba(5, 7, 10, 0.95); height: 80px; display: flex; align-items: center; border-bottom: 1px solid var(--dark-border); padding: 0 40px; }
        .logo { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: white; text-decoration: none; }
        .logo span { color: var(--primary); }
        .back-btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            margin-right: 30px;
            transition: 0.3s;
            font-size: 0.95rem;
        }
        .back-btn-nav:hover { color: var(--primary); transform: translateX(-5px); }
        .container { max-width: 1000px; margin: 60px auto; padding: 0 20px; }
        .hero { text-align: center; margin-bottom: 60px; }
        h1 { font-family: 'Playfair Display', serif; font-size: 3rem; margin-bottom: 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .card-link { text-decoration: none; color: inherit; display: block; }
        .card { background: var(--dark-card); border: 1px solid var(--dark-border); border-radius: 20px; padding: 30px; transition: 0.3s; height: 100%; display: flex; flex-direction: column; justify-content: flex-start; }
        .card:hover { transform: translateY(-5px); border-color: var(--primary); box-shadow: 0 10px 30px rgba(16, 185, 129, 0.1); }
        .card i { font-size: 2rem; color: var(--primary); margin-bottom: 20px; }
        h3 { margin-bottom: 15px; }
        p { color: var(--text-muted); font-size: 0.95rem; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="javascript:history.back()" class="back-btn-nav"><i class="fas fa-arrow-left"></i> Back</a>
        <a href="dashboard.php" class="logo">WALK<span>ON</span></a>
    </nav>
    <div class="container">
        <div class="hero">
            <h1>How can we help?</h1>
            <p>Our concierge team is available 24/7 to assist with your footwear journey.</p>
        </div>
        <div class="grid">
            <?php
            // Use PDO query instead of MySQLi
            $stmt = $pdo->query("SELECT title, slug, icon_class, summary FROM help_pages ORDER BY id ASC");
            // Check if there are rows (fetchAll to count or just loop)
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                foreach($rows as $row) {
                    ?>
                    <a href="help_article.php?slug=<?php echo htmlspecialchars($row['slug']); ?>" class="card-link">
                        <div class="card">
                            <i class="<?php echo htmlspecialchars($row['icon_class']); ?>"></i>
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p><?php echo htmlspecialchars($row['summary']); ?></p>
                        </div>
                    </a>
                    <?php
                }
            } else {
                echo "<p>No help topics found.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
