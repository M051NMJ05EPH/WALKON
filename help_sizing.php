<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Size & Fit Guide - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 50%, #c471f5 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            padding: 40px 20px;
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .container { max-width: 1000px; margin: 0 auto; }
        .back-button {
            display: inline-flex; align-items: center; gap: 10px; color: white;
            text-decoration: none; font-weight: 600; margin-bottom: 30px;
            padding: 12px 24px; background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px); border-radius: 50px;
            border: 2px solid rgba(255, 255, 255, 0.3); transition: all 0.3s ease;
        }
        .back-button:hover { background: rgba(255, 255, 255, 0.3); transform: translateX(-5px); }
        .header { text-align: center; margin-bottom: 60px; }
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
        .header p { font-size: 1.3rem; color: rgba(255, 255, 255, 0.95); }
        .content-card {
            background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(30px);
            border-radius: 30px; padding: 40px; border: 2px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 30px;
        }
        .content-card h2 {
            font-size: 2rem; font-weight: 700; color: white; margin-bottom: 20px;
        }
        .content-card p {
            font-size: 1.1rem; color: rgba(255, 255, 255, 0.95);
            line-height: 1.8; margin-bottom: 15px;
        }
        table {
            width: 100%; border-collapse: collapse; margin-top: 20px;
            background: rgba(255, 255, 255, 0.15); border-radius: 15px; overflow: hidden;
        }
        th, td {
            padding: 15px; text-align: center; color: white; border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        th { background: rgba(255, 255, 255, 0.2); font-weight: 700; }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <div class="header">
            <div class="icon-hero"><i class="fas fa-ruler"></i></div>
            <h1>Size & Fit Guide</h1>
            <p>Find your perfect fit with our comprehensive size charts</p>
        </div>
        <div class="content-card">
            <h2>International Size Chart</h2>
            <table>
                <thead>
                    <tr><th>US</th><th>UK</th><th>EU</th><th>CM</th></tr>
                </thead>
                <tbody>
                    <tr><td>6</td><td>5.5</td><td>39</td><td>24.0</td></tr>
                    <tr><td>7</td><td>6.5</td><td>40</td><td>25.0</td></tr>
                    <tr><td>8</td><td>7.5</td><td>41</td><td>26.0</td></tr>
                    <tr><td>9</td><td>8.5</td><td>42</td><td>27.0</td></tr>
                    <tr><td>10</td><td>9.5</td><td>43</td><td>28.0</td></tr>
                    <tr><td>11</td><td>10.5</td><td>44</td><td>29.0</td></tr>
                </tbody>
            </table>
        </div>
        <div class="content-card">
            <h2>How to Measure Your Foot</h2>
            <p>1. Place a piece of paper on a flat surface against a wall</p>
            <p>2. Stand on the paper with your heel against the wall</p>
            <p>3. Mark the longest part of your foot on the paper</p>
            <p>4. Measure the distance from the wall to the mark in centimeters</p>
            <p>5. Use our size chart above to find your size</p>
        </div>
    </div>
</body>
</html>
