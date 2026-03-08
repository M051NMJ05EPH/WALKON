<?php
session_start();
include 'config.php';

// Check if user is logged in (Optional for viewing, required for submitting)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    die("Invalid Product ID.");
}

// Fetch Product Details
$stmt = $pdo->prepare("
    SELECT pb.name, pb.id, pm.url as image_url, b.name as brand_name
    FROM product_base pb
    LEFT JOIN product_media pm ON pb.id = pm.product_id AND pm.is_primary = 1
    LEFT JOIN product_specs spec ON pb.id = spec.product_id
    LEFT JOIN brands b ON spec.brand_id = b.id
    WHERE pb.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found.");
}

// Check if user already has a review (only if logged in)
$existing_review = null;
if ($user_id) {
    $stmt_review = $pdo->prepare("SELECT rating, comment FROM product_reviews WHERE user_id = ? AND product_id = ?");
    $stmt_review->execute([$user_id, $product_id]);
    $existing_review = $stmt_review->fetch(PDO::FETCH_ASSOC);
}

$is_update = $existing_review ? true : false;
$rating = $existing_review ? intval($existing_review['rating']) : 0;
$comment = $existing_review ? $existing_review['comment'] : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_update ? 'Update' : 'Write' ?> Review - <?= htmlspecialchars($product['name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <style>
        :root { 
            --primary: #2563eb; 
            --primary-hover: #1d4ed8;
            --bg: #f0f6ff;
            --card-bg: rgba(255, 255, 255, 0.85);
            --text-main: #1e293b;
            --text-light: #64748b;
            --border: #c7dcff;
        }
        
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit',sans-serif; }
        body { 
            background:
                radial-gradient(ellipse at 0% 0%, rgba(37, 99, 235, 0.12) 0%, transparent 50%),
                radial-gradient(ellipse at 100% 0%, rgba(96, 165, 250, 0.18) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 100%, rgba(37, 99, 235, 0.08) 0%, transparent 60%),
                linear-gradient(160deg, #e0eeff 0%, #f0f6ff 40%, #ffffff 70%, #e8f3ff 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .review-card {
            background: linear-gradient(160deg, #ffffff 0%, #eef5ff 100%);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 10px 40px rgba(37, 99, 235, 0.1);
            animation: slideUp 0.6s ease-out;
            position: relative;
            overflow: hidden;
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .review-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 6px;
            background: linear-gradient(90deg, #60a5fa, #2563eb, #1d4ed8);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-light);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 2rem;
            transition: color 0.3s;
        }
        .back-link:hover { color: var(--primary); }

        .product-preview {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 2.5rem;
            padding: 1.5rem;
            background: rgba(255,255,255,0.7);
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.04);
        }
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background: linear-gradient(135deg, #e0eeff, #f0f6ff);
            border-radius: 12px;
            padding: 5px;
            border: 1px solid var(--border);
        }
        .product-info h3 { font-size: 1.2rem; font-weight: 700; margin-bottom: 4px; }
        .product-info p { color: var(--primary); font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }

        h2 { font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; letter-spacing: -0.5px; }
        .subtitle { color: var(--text-light); margin-bottom: 2.5rem; font-size: 1rem; }

        .form-group { margin-bottom: 2rem; }
        .label { display: block; margin-bottom: 12px; font-weight: 700; font-size: 0.95rem; color: var(--text-light); text-transform: uppercase; letter-spacing: 1px; }

        .rating-stars {
            display: flex;
            gap: 15px;
            font-size: 2.5rem;
            color: #dbeafe; /* light blue for empty stars */
        }
        .rating-stars i { cursor: pointer; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .rating-stars i.active { color: #fbbf24; filter: drop-shadow(0 0 10px rgba(251, 191, 36, 0.4)); transform: scale(1.1); }
        .rating-stars i:hover { transform: scale(1.2); }

        .textarea-wrap { position: relative; }
        textarea {
            width: 100%;
            background: rgba(240, 246, 255, 0.6);
            border: 1.5px solid var(--border);
            border-radius: 16px;
            padding: 1.2rem;
            color: var(--text-main);
            font-size: 1rem;
            line-height: 1.6;
            resize: none;
            transition: all 0.3s;
            outline: none;
        }
        textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.1); background: #ffffff; }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            border: none;
            padding: 1.2rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }
        .submit-btn:hover { 
            background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(37, 99, 235, 0.4);
        }
        .submit-btn:active { transform: translateY(-1px); }

        .loader {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0,0,0,0.1);
            border-top-color: #000;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        #message {
            margin-top: 1.5rem;
            text-align: center;
            padding: 1rem;
            border-radius: 12px;
            font-weight: 600;
            display: none;
        }
        #message.success { background: rgba(16, 185, 129, 0.1); color: var(--primary); border: 1px solid rgba(16, 185, 129, 0.2); }
        #message.error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    </style>
</head>
<body>

<div class="review-card">
    <a href="product_detail.php?id=<?= $product_id ?>" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Product
    </a>

    <h2><?= $is_update ? 'Update Your Review' : 'Write a Review' ?></h2>
    <p class="subtitle">Share your experience with the community.</p>

    <div class="product-preview">
        <img src="<?= htmlspecialchars($product['image_url'] ?: 'https://via.placeholder.com/300') ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img">
        <div class="product-info">
            <p><?= htmlspecialchars($product['brand_name'] ?: 'WALKON') ?></p>
            <h3><?= htmlspecialchars($product['name']) ?></h3>
        </div>
    </div>

    <form id="reviewForm">
        <input type="hidden" name="product_id" value="<?= $product_id ?>">
        <input type="hidden" name="rating" id="ratingInput" value="<?= $rating ?>">

        <div class="form-group">
            <span class="label">Overall Rating</span>
            <div class="rating-stars" id="stars">
                <?php for($i=1; $i<=5; $i++): ?>
                    <i class="fas fa-star <?= $i <= $rating ? 'active' : '' ?>" data-value="<?= $i ?>"></i>
                <?php endfor; ?>
            </div>
        </div>

        <div class="form-group">
            <span class="label">Your Thoughts</span>
            <div class="textarea-wrap">
                <textarea name="comment" id="comment" rows="5" placeholder="What did you like or dislike? How's the fit?"><?= htmlspecialchars($comment) ?></textarea>
            </div>
        </div>

        <?php if ($user_id): ?>
            <button type="submit" class="submit-btn" id="submitBtn">
                <span id="btnText"><?= $is_update ? 'Update Review' : 'Submit Review' ?></span>
                <div class="loader" id="loader"></div>
                <i class="fas fa-paper-plane" id="btnIcon"></i>
            </button>
        <?php else: ?>
            <button type="button" class="submit-btn" onclick="window.location.href='login.php?redirect=' + encodeURIComponent(window.location.href)">
                <span>Login to Post Review</span>
                <i class="fas fa-sign-in-alt"></i>
            </button>
        <?php endif; ?>

        <div id="message"></div>
    </form>
</div>

<script>
    const stars = document.querySelectorAll('.rating-stars i');
    const ratingInput = document.getElementById('ratingInput');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            const val = this.getAttribute('data-value');
            ratingInput.value = val;
            updateStars(val);
        });

        star.addEventListener('mouseover', function() {
            const val = this.getAttribute('data-value');
            highlightStars(val);
        });

        star.addEventListener('mouseleave', function() {
            updateStars(ratingInput.value);
        });
    });

    function updateStars(val) {
        stars.forEach(s => {
            if (parseInt(s.getAttribute('data-value')) <= parseInt(val)) {
                s.classList.add('active');
            } else {
                s.classList.remove('active');
            }
        });
    }

    function highlightStars(val) {
        stars.forEach(s => {
            if (parseInt(s.getAttribute('data-value')) <= parseInt(val)) {
                s.style.color = '#fbbf24';
            } else {
                s.style.color = '#dbeafe'; // match new CSS empty color
            }
        });
    }

    document.getElementById('reviewForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const rating = ratingInput.value;
        const comment = document.getElementById('comment').value.trim();
        const msgDiv = document.getElementById('message');
        const submitBtn = document.getElementById('submitBtn');
        const loader = document.getElementById('loader');
        const btnIcon = document.getElementById('btnIcon');
        const btnText = document.getElementById('btnText');

        if (rating === "0") {
            msgDiv.textContent = "Please select a rating.";
            msgDiv.className = "error";
            msgDiv.style.display = "block";
            return;
        }

        if (comment === "") {
            msgDiv.textContent = "Please write a comment.";
            msgDiv.className = "error";
            msgDiv.style.display = "block";
            return;
        }

        // Show loading
        submitBtn.disabled = true;
        loader.style.display = "block";
        btnIcon.style.display = "none";
        btnText.textContent = "Processing...";
        msgDiv.style.display = "none";

        try {
            const formData = new FormData(this);
            const response = await fetch('submit_review.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                msgDiv.textContent = data.message;
                msgDiv.className = "success";
                msgDiv.style.display = "block";
                btnText.textContent = "Success!";
                loader.style.display = "none";
                
                setTimeout(() => {
                    window.location.href = 'product_detail.php?id=<?= $product_id ?>&review=success#tab-reviews';
                }, 1500);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            msgDiv.textContent = error.message || "An error occurred. Please try again.";
            msgDiv.className = "error";
            msgDiv.style.display = "block";
            submitBtn.disabled = false;
            loader.style.display = "none";
            btnIcon.style.display = "block";
            btnText.textContent = "<?= $is_update ? 'Update Review' : 'Submit Review' ?>";
        }
    });

    // Initialize stars on page load (for updates)
    updateStars(ratingInput.value);
</script>

</body>
</html>
