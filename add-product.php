<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Database connection
include 'config.php';

// Fetch categories for dropdown (optional - adjust table name if needed)
$categories = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku = trim($_POST['sku']);
    $price = floatval($_POST['price']);
    $product_name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $sizes = trim($_POST['sizes']);
    $colors = trim($_POST['colors']);
    $category = $_POST['category'];
    $subcategory = trim($_POST['subcategory']);

    // Handle multiple image URLs (stored as JSON)
    $image_urls = [];
    if (!empty($_POST['image_urls'])) {
        $image_urls = array_filter(explode("\n", trim($_POST['image_urls'])));
    }
    $images_json = json_encode($image_urls);

    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO products (sku, product_name, price, description, sizes, colors, category, subcategory, images, status, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', NOW())");
    $stmt->execute([$sku, $product_name, $price, $description, $sizes, $colors, $category, $subcategory, $images_json]);

    echo '<script>alert("Product added successfully!"); window.location="product.php";</script>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WALKON - Add New Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 1200px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .preview-box {
            background-color: #ff8a65;
            color: white;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #ddd;
        }
        .form-label { font-weight: 600; color: #333; }
        .btn-add-url { background-color: #343a40; }
        .btn-submit { background-color: #4caf50; border: none; font-size: 1.2rem; padding: 12px 40px; }
        .btn-submit:hover { background-color: #388e3c; }
    </style>
</head>
<body>
    <div class="container mt-5 mb-5">
        <h2 class="text-center mb-4 text-success">Add New Shoe Product</h2>

        <form method="POST">
            <div class="row">
                <!-- Left Column: Product Images -->
                <div class="col-lg-6 mb-4">
                    <div class="card p-4">
                        <h4 class="mb-3">Product Images</h4>
                        <p class="text-muted">Upload or paste image URLs for your shoe product. Support for multiple images.</p>

                        <div class="preview-box" id="mainPreview">
                            Product Image Preview
                        </div>

                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="imageUrlInput" placeholder="https://example.com/shoe.jpg">
                            <button class="btn btn-add-url" type="button" onclick="addImage()">Add</button>
                        </div>

                        <button type="button" class="btn btn-secondary w-100" onclick="clearAllImages()">Clear All</button>

                        <div class="image-preview-container mt-3" id="previewContainer"></div>

                        <textarea name="image_urls" id="imageUrlsHidden" style="display:none;"></textarea>
                    </div>
                </div>

                <!-- Right Column: Product Details -->
                <div class="col-lg-6 mb-4">
                    <div class="card p-4">
                        <h4 class="mb-4">Product Details</h4>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label">SKU</label>
                                <input type="text" class="form-control" name="sku" placeholder="e.g. NIKE-001" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">PRICE (₹)</label>
                                <input type="number" step="0.01" class="form-control" name="price" placeholder="0.00" value="0.00" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">PRODUCT NAME</label>
                            <input type="text" class="form-control" name="name" placeholder="Nike Air Max 90" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">DESCRIPTION</label>
                            <textarea class="form-control" name="description" rows="4" placeholder="Describe the shoe, material, comfort level, etc." required></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">SIZES (COMMA SEPARATED)</label>
                                <input type="text" class="form-control" name="sizes" placeholder="6, 7, 8, 9, 10, 11">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">COLORS</label>
                                <input type="text" class="form-control" name="colors" placeholder="Black, White, Red">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">CATEGORY</label>
                                <select class="form-select" name="category">
                                    <option value="">-- Select Category --</option>
                                    <option>Sneakers</option>
                                    <option>Running</option>
                                    <option>Casual</option>
                                    <option>Formal</option>
                                    <option>Sports</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SUBCATEGORY</label>
                                <select class="form-select" name="subcategory">
                                    <option value="">(none)</option>
                                    <option>Men</option>
                                    <option>Women</option>
                                    <option>Kids</option>
                                    <option>Unisex</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-submit">Add Product</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="text-center mt-4">
            <a href="product.php" class="btn btn-outline-secondary">← Back to Products List</a>
        </div>
    </div>

    <script>
        const imageUrls = [];

        function addImage() {
            const input = document.getElementById('imageUrlInput');
            const url = input.value.trim();
            if (url && url.startsWith('http')) {
                imageUrls.push(url);
                updatePreviews();
                input.value = '';
            } else if (url) {
                alert('Please enter a valid image URL starting with http/https');
            }
        }

        function clearAllImages() {
            imageUrls.length = 0;
            updatePreviews();
        }

        function updatePreviews() {
            const container = document.getElementById('previewContainer');
            const mainPreview = document.getElementById('mainPreview');
            const hidden = document.getElementById('imageUrlsHidden');

            container.innerHTML = '';
            hidden.value = imageUrls.join('\n');

            if (imageUrls.length > 0) {
                mainPreview.innerHTML = `<img src="${imageUrls[0]}" class="img-fluid rounded" style="max-height:300px;">`;
                
                imageUrls.forEach(url => {
                    const img = document.createElement('img');
                    img.src = url;
                    img.className = 'image-preview';
                    img.onerror = () => { img.src = 'https://via.placeholder.com/100?text=Invalid'; };
                    container.appendChild(img);
                });
            } else {
                mainPreview.innerHTML = 'Product Image Preview';
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>