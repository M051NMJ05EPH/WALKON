<?php
include '../../config.php';

if (!isset($pdo)) {
    die("Database connection failed. Variable \$pdo is not set in config.php");
}

$pages = [
    [
        'title' => 'Order Tracking & Delivery',
        'slug' => 'tracking-delivery',
        'icon_class' => 'fas fa-shipping-fast',
        'summary' => 'Track your orders in real-time, manage delivery preferences, and get estimated arrival times.',
        'content' => '
        <div class="content-card">
            <h2><i class="fas fa-map-marked-alt"></i> Track Your Order</h2>
            <p>Monitor your WALKON order in real-time with our advanced tracking system. Get instant updates on every step of your delivery journey.</p>
            
            <div class="tracking-tool">
                <h3>Enter Your Tracking Number</h3>
                <div class="tracking-input-group">
                    <input type="text" placeholder="Enter tracking number or order ID...">
                    <button type="button"><i class="fas fa-search"></i> Track</button>
                </div>
                <p style="margin: 0; font-size: 0.95rem; color: rgba(255, 255, 255, 0.8);">
                    <i class="fas fa-info-circle"></i> You can find your tracking number in your order confirmation email
                </p>
            </div>
        </div>

        <div class="content-card">
            <h2><i class="fas fa-clock"></i> Delivery Timelines</h2>
            <p>We strive to get your footwear to you as quickly as possible. Here are our standard delivery timelines:</p>
            <ul>
                <li><strong>Metro Cities:</strong> 2-3 business days</li>
                <li><strong>Tier 2 Cities:</strong> 3-5 business days</li>
                <li><strong>Other Locations:</strong> 5-7 business days</li>
                <li><strong>Express Delivery:</strong> Next-day delivery available in select cities</li>
            </ul>
        </div>
        '
    ],
    [
        'title' => 'Returns & Exchanges',
        'slug' => 'returns-exchanges',
        'icon_class' => 'fas fa-exchange-alt',
        'summary' => 'Our 30-day "Walk Healthy" policy ensures easy returns and exchanges if your footwear doesn\'t fit perfectly.',
        'content' => '
        <div class="content-card">
            <div class="policy-highlight">
                <h3>30-Day Return Policy</h3>
                <p>Not happy with your purchase? Return it within 30 days for a full refund or exchange!</p>
            </div>
        </div>

        <div class="content-card">
            <h2><i class="fas fa-undo"></i> How to Return Your Order</h2>
            <p>Returning your WALKON footwear is simple and straightforward. Follow these easy steps:</p>
            
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h4>Initiate Return</h4>
                    <p>Go to "My Orders" and select "Return Item"</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h4>Select Reason</h4>
                    <p>Choose why you\'re returning the product</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h4>Schedule Pickup</h4>
                    <p>Choose a convenient pickup date and time</p>
                </div>
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h4>Get Refund</h4>
                    <p>Receive refund within 5-7 business days</p>
                </div>
            </div>
        </div>
        '
    ],
    [
        'title' => 'Account Security',
        'slug' => 'account-security',
        'icon_class' => 'fas fa-shield-alt',
        'summary' => 'Manage your password, enable two-factor authentication, and keep your personal data safe and secure.',
        'content' => '
        <div class="content-card">
            <h2><i class="fas fa-lock"></i> Password Management</h2>
            <p>A strong password is your first line of defense. Here\'s how to create and manage a secure password:</p>
            <ul>
                <li>Use at least 12 characters mixing uppercase, lowercase, numbers, and symbols</li>
                <li>Avoid using personal information like birthdays or names</li>
                <li>Never reuse passwords across different platforms</li>
                <li>Change your password every 3-6 months</li>
            </ul>
        </div>

        <div class="content-card">
            <h2><i class="fas fa-mobile-alt"></i> Two-Factor Authentication (2FA)</h2>
            <p>Add an extra layer of security to your WALKON account with two-factor authentication:</p>
            <ul>
                <li>Enable 2FA from Account Settings → Security Settings</li>
                <li>Choose between SMS OTP or Authenticator App</li>
                <li>Verify your identity every time you log in from a new device</li>
            </ul>
        </div>
        '
    ],
    [
        'title' => 'Payment Options',
        'slug' => 'payment-options',
        'icon_class' => 'fas fa-credit-card',
        'summary' => 'Learn about our secure payment methods, installment plans, and wallet integrations for seamless checkout.',
        'content' => '
        <div class="content-card">
            <h2>Accepted Payment Methods</h2>
            <div class="payment-grid">
                <div class="payment-box">
                    <i class="fas fa-credit-card"></i>
                    <h4>Credit/Debit Cards</h4>
                </div>
                <div class="payment-box">
                    <i class="fas fa-wallet"></i>
                    <h4>Digital Wallets</h4>
                </div>
                <div class="payment-box">
                    <i class="fas fa-university"></i>
                    <h4>Net Banking</h4>
                </div>
                <div class="payment-box">
                    <i class="fas fa-qrcode"></i>
                    <h4>UPI</h4>
                </div>
                <div class="payment-box">
                    <i class="fas fa-money-bill-wave"></i>
                    <h4>Cash on Delivery</h4>
                </div>
                <div class="payment-box">
                    <i class="fas fa-calendar-alt"></i>
                    <h4>EMI Options</h4>
                </div>
            </div>
        </div>
        <div class="content-card">
            <h2>Payment Security</h2>
            <p>All transactions are encrypted with industry-standard SSL/TLS protocols. We never store your complete card details. Your payment information is processed through PCI-DSS compliant gateways.</p>
        </div>
        '
    ],
    [
        'title' => 'Size & Fit Guide',
        'slug' => 'size-guide',
        'icon_class' => 'fas fa-ruler',
        'summary' => 'Find your perfect fit with our comprehensive size charts, fitting tips, and brand-specific measurement guides.',
        'content' => '
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
        '
    ],
    [
        'title' => 'Live Customer Support',
        'slug' => 'live-support',
        'icon_class' => 'fas fa-headset',
        'summary' => 'Connect instantly with our footwear experts for personalized assistance, product recommendations, and more.',
        'content' => '
        <div class="content-card">
            <h2>Get in Touch</h2>
            <p>Our team of footwear experts is ready to assist you with any questions or concerns.</p>
            <div class="contact-box">
                <i class="fas fa-phone"></i>
                <h3>Call Us</h3>
                <a href="tel:+911234567890">+91 123 456 7890</a>
                <p style="margin-top: 10px; font-size: 0.95rem;">Available 24/7</p>
            </div>
            <div class="contact-box">
                <i class="fas fa-envelope"></i>
                <h3>Email Support</h3>
                <a href="mailto:support@walkon.com">support@walkon.com</a>
                <p style="margin-top: 10px; font-size: 0.95rem;">Response within 24 hours</p>
            </div>
            <div class="contact-box">
                <i class="fas fa-comments"></i>
                <h3>Live Chat</h3>
                <p>Click the chat icon in the bottom right corner to start a conversation with our team instantly!</p>
            </div>
        </div>
        '
    ]
];

// Clear existing data
$pdo->exec("TRUNCATE TABLE help_pages");

$stmt = $pdo->prepare("INSERT INTO help_pages (title, slug, icon_class, summary, content) VALUES (:title, :slug, :icon_class, :summary, :content)");

foreach ($pages as $page) {
    try {
        $stmt->execute([
            ':title' => $page['title'],
            ':slug' => $page['slug'],
            ':icon_class' => $page['icon_class'],
            ':summary' => $page['summary'],
            ':content' => $page['content']
        ]);
        echo "Inserted page: " . $page['title'] . "\n";
    } catch (PDOException $e) {
        echo "Error inserting page " . $page['title'] . ": " . $e->getMessage() . "\n";
    }
}
?>
