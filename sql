=== DATABASE: walkon_shoes_v2 ===

Total Tables: 45

--- TABLE: api_credentials ---
CREATE TABLE `api_credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) DEFAULT NULL,
  `channel` varchar(50) DEFAULT NULL,
  `api_key` text DEFAULT NULL,
  `api_secret` text DEFAULT NULL,
  `access_token` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 3
INSERT INTO `api_credentials` (`id`, `seller_id`, `channel`, `api_key`, `api_secret`, `access_token`, `is_active`, `expires_at`) VALUES ('1', '1', 'amazon', NULL, NULL, NULL, '1', NULL);
INSERT INTO `api_credentials` (`id`, `seller_id`, `channel`, `api_key`, `api_secret`, `access_token`, `is_active`, `expires_at`) VALUES ('2', '1', 'shopify', NULL, NULL, NULL, '1', NULL);
INSERT INTO `api_credentials` (`id`, `seller_id`, `channel`, `api_key`, `api_secret`, `access_token`, `is_active`, `expires_at`) VALUES ('3', '1', 'ebay', NULL, NULL, NULL, '1', NULL);

--- TABLE: brand_approvals ---
CREATE TABLE `brand_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `certificate_url` varchar(500) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_feedback` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `seller_id` (`seller_id`),
  CONSTRAINT `brand_approvals_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE,
  CONSTRAINT `brand_approvals_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 0

--- TABLE: brands ---
CREATE TABLE `brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 26
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('1', 'Nike', 'https://upload.wikimedia.org/wikipedia/commons/a/a6/Logo_NIKE.svg', '2026-02-03 23:34:33', '1');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('2', 'Adidas', 'https://upload.wikimedia.org/wikipedia/commons/2/20/Adidas_Logo.svg', '2026-02-03 23:34:33', '1');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('3', 'Puma', 'https://upload.wikimedia.org/wikipedia/commons/8/88/Puma_Logo.svg', '2026-02-03 23:34:33', '1');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('4', 'Jordan', 'https://upload.wikimedia.org/wikipedia/en/3/37/Jumpman_logo.svg', '2026-02-03 23:34:33', '1');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('5', 'Reebok', 'https://upload.wikimedia.org/wikipedia/commons/5/5f/Reebok_Logo.svg', '2026-02-03 23:34:33', '1');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('6', 'New Balance', 'https://upload.wikimedia.org/wikipedia/commons/e/ea/New_Balance_logo.svg', '2026-02-03 23:34:33', '0');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('7', 'Vans', 'https://upload.wikimedia.org/wikipedia/commons/9/91/Vans_logo.svg', '2026-02-03 23:34:33', '0');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('8', 'Bata', 'https://upload.wikimedia.org/wikipedia/commons/c/c6/Bata_logo.svg', '2026-02-03 23:34:33', '0');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('9', 'Converse', 'https://via.placeholder.com/100x50?text=Converse', '2026-02-03 23:58:31', '0');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('10', 'Fila', 'https://via.placeholder.com/100x50?text=Fila', '2026-02-03 23:58:31', '0');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('11', 'Skechers', 'https://via.placeholder.com/100x50?text=Skechers', '2026-02-03 23:58:31', '0');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('12', 'Under Armour', 'https://via.placeholder.com/100x50?text=Under+Armour', '2026-02-03 23:58:31', '0');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('13', 'Crocs', 'https://via.placeholder.com/100x50?text=Crocs', '2026-02-03 23:58:31', '0');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('14', 'Asian', 'https://via.placeholder.com/100x50?text=Asian', '2026-02-03 23:58:31', '0');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('15', 'Campus', 'https://via.placeholder.com/100x50?text=Campus', '2026-02-03 23:58:31', '0');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('16', 'Sparx', 'https://via.placeholder.com/100x50?text=Sparx', '2026-02-03 23:58:31', '1');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('17', 'Asics', NULL, '2026-02-11 09:45:57', '1');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('18', 'Clarks', NULL, '2026-02-17 22:23:01', '1');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('19', 'Dr. Martens', NULL, '2026-02-17 22:23:01', '1');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('20', 'Red Tape', NULL, '2026-02-19 09:06:50', '0');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('35', 'Birkenstock', '', '2026-02-25 09:58:18', '0');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('36', 'Timberland', '', '2026-02-25 09:58:18', '0');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('37', 'Gucci', '', '2026-02-25 09:58:18', '0');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('38', 'Prada', '', '2026-02-25 09:58:18', '0');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('39', 'Balenciaga', '', '2026-02-25 09:58:18', '0');
INSERT INTO `brands` (`id`, `name`, `logo_url`, `created_at`, `is_verified`) VALUES ('40', 'Yeezy', '', '2026-02-25 09:58:18', '0');

--- TABLE: bulk_operations_log ---
CREATE TABLE `bulk_operations_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `affected_count` int(11) DEFAULT 0,
  `action_value` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `seller_id` (`seller_id`),
  CONSTRAINT `bulk_operations_log_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 0

--- TABLE: cart ---
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 8
INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES ('1', '34', '95', '1', '2026-02-19 16:11:27', '2026-02-19 16:12:19');
INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES ('2', '34', '94', '1', '2026-02-19 16:11:32', '2026-02-19 16:12:14');
INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES ('3', '16', '95', '2', '2026-02-20 10:31:13', '2026-02-20 10:31:14');
INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES ('4', '16', '94', '16', '2026-02-20 10:31:17', '2026-02-23 10:27:13');
INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES ('5', '16', '96', '7', '2026-02-23 10:35:42', '2026-02-23 10:42:27');
INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES ('6', '15', '100', '3', '2026-03-05 13:49:40', '2026-03-05 16:48:56');
INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES ('7', '15', '98', '1', '2026-03-06 13:36:45', '2026-03-06 13:36:45');
INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES ('8', '15', '102', '1', '2026-03-08 11:38:16', '2026-03-08 11:38:16');

--- TABLE: categories ---
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 7
INSERT INTO `categories` (`id`, `name`, `image_url`, `description`, `created_at`) VALUES ('1', 'Sneakers', 'https://images.unsplash.com/photo-1552346154-21d32810aba3', 'Premium lifestyle sneakers and streetwear.', '2026-02-03 23:34:33');
INSERT INTO `categories` (`id`, `name`, `image_url`, `description`, `created_at`) VALUES ('2', 'Boots', 'assets/boots_category.png', 'Durable and stylish boots for all terrains.', '2026-02-03 23:34:33');
INSERT INTO `categories` (`id`, `name`, `image_url`, `description`, `created_at`) VALUES ('4', 'Running Shoes', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff', 'Lightweight tech for marathon and trail running.', '2026-02-03 23:34:33');
INSERT INTO `categories` (`id`, `name`, `image_url`, `description`, `created_at`) VALUES ('5', 'Formal Shoes', 'assets/formal_category.png', 'Elegance and craftsmanship for every formal occasion.', '2026-02-03 23:47:02');
INSERT INTO `categories` (`id`, `name`, `image_url`, `description`, `created_at`) VALUES ('6', 'Casual Shoes', 'assets/casual_category.png', 'Relaxed style and comfort for your daily journey.', '2026-02-03 23:47:02');
INSERT INTO `categories` (`id`, `name`, `image_url`, `description`, `created_at`) VALUES ('21', 'Footwear', NULL, NULL, '2026-02-17 23:14:24');
INSERT INTO `categories` (`id`, `name`, `image_url`, `description`, `created_at`) VALUES ('22', 'Sports Shoes', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=800&auto=format&fit=crop', NULL, '2026-02-19 09:02:01');

--- TABLE: channel_settings ---
CREATE TABLE `channel_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) NOT NULL,
  `marketplace_id` int(11) NOT NULL,
  `sync_frequency` varchar(20) DEFAULT 'daily',
  `price_margin` decimal(5,2) DEFAULT 0.00,
  `description_override` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_seller_marketplace` (`seller_id`,`marketplace_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 1
INSERT INTO `channel_settings` (`id`, `seller_id`, `marketplace_id`, `sync_frequency`, `price_margin`, `description_override`, `created_at`, `updated_at`) VALUES ('1', '8', '5', 'daily', '500.00', '', '2026-03-06 11:52:21', '2026-03-06 11:52:21');

--- TABLE: daily_sales_analytics ---
CREATE TABLE `daily_sales_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) NOT NULL,
  `recorded_date` date NOT NULL,
  `total_orders` int(11) DEFAULT 0,
  `total_revenue` decimal(10,2) DEFAULT 0.00,
  `units_sold` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_entry` (`seller_id`,`recorded_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 0

--- TABLE: disputes ---
CREATE TABLE `disputes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('open','under_review','resolved','rejected') DEFAULT 'open',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `customer_id` (`customer_id`),
  KEY `seller_id` (`seller_id`),
  CONSTRAINT `disputes_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `disputes_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `disputes_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 0

--- TABLE: help_pages ---
CREATE TABLE `help_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `icon_class` varchar(50) NOT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 6
INSERT INTO `help_pages` (`id`, `title`, `slug`, `icon_class`, `summary`, `content`, `created_at`) VALUES ('1', 'Order Tracking & Delivery', 'tracking-delivery', 'fas fa-shipping-fast', 'Track your orders in real-time, manage delivery preferences, and get estimated arrival times.', '\r\n        <div class=\"content-card\">\r\n            <h2><i class=\"fas fa-map-marked-alt\"></i> Track Your Order</h2>\r\n            <p>Monitor your WALKON order in real-time with our advanced tracking system. Get instant updates on every step of your delivery journey.</p>\r\n            \r\n            <div class=\"tracking-tool\">\r\n                <h3>Enter Your Tracking Number</h3>\r\n                <div class=\"tracking-input-group\">\r\n                    <input type=\"text\" placeholder=\"Enter tracking number or order ID...\">\r\n                    <button type=\"button\"><i class=\"fas fa-search\"></i> Track</button>\r\n                </div>\r\n                <p style=\"margin: 0; font-size: 0.95rem; color: rgba(255, 255, 255, 0.8);\">\r\n                    <i class=\"fas fa-info-circle\"></i> You can find your tracking number in your order confirmation email\r\n                </p>\r\n            </div>\r\n        </div>\r\n\r\n        <div class=\"content-card\">\r\n            <h2><i class=\"fas fa-clock\"></i> Delivery Timelines</h2>\r\n            <p>We strive to get your footwear to you as quickly as possible. Here are our standard delivery timelines:</p>\r\n            <ul>\r\n                <li><strong>Metro Cities:</strong> 2-3 business days</li>\r\n                <li><strong>Tier 2 Cities:</strong> 3-5 business days</li>\r\n                <li><strong>Other Locations:</strong> 5-7 business days</li>\r\n                <li><strong>Express Delivery:</strong> Next-day delivery available in select cities</li>\r\n            </ul>\r\n        </div>\r\n        ', '2026-02-09 10:36:12');
INSERT INTO `help_pages` (`id`, `title`, `slug`, `icon_class`, `summary`, `content`, `created_at`) VALUES ('2', 'Returns & Exchanges', 'returns-exchanges', 'fas fa-exchange-alt', 'Our 30-day \"Walk Healthy\" policy ensures easy returns and exchanges if your footwear doesn\'t fit perfectly.', '\r\n        <div class=\"content-card\">\r\n            <div class=\"policy-highlight\">\r\n                <h3>30-Day Return Policy</h3>\r\n                <p>Not happy with your purchase? Return it within 30 days for a full refund or exchange!</p>\r\n            </div>\r\n        </div>\r\n\r\n        <div class=\"content-card\">\r\n            <h2><i class=\"fas fa-undo\"></i> How to Return Your Order</h2>\r\n            <p>Returning your WALKON footwear is simple and straightforward. Follow these easy steps:</p>\r\n            \r\n            <div class=\"steps-grid\">\r\n                <div class=\"step-card\">\r\n                    <div class=\"step-number\">1</div>\r\n                    <h4>Initiate Return</h4>\r\n                    <p>Go to \"My Orders\" and select \"Return Item\"</p>\r\n                </div>\r\n                <div class=\"step-card\">\r\n                    <div class=\"step-number\">2</div>\r\n                    <h4>Select Reason</h4>\r\n                    <p>Choose why you\'re returning the product</p>\r\n                </div>\r\n                <div class=\"step-card\">\r\n                    <div class=\"step-number\">3</div>\r\n                    <h4>Schedule Pickup</h4>\r\n                    <p>Choose a convenient pickup date and time</p>\r\n                </div>\r\n                <div class=\"step-card\">\r\n                    <div class=\"step-number\">4</div>\r\n                    <h4>Get Refund</h4>\r\n                    <p>Receive refund within 5-7 business days</p>\r\n                </div>\r\n            </div>\r\n        </div>\r\n        ', '2026-02-09 10:36:12');
INSERT INTO `help_pages` (`id`, `title`, `slug`, `icon_class`, `summary`, `content`, `created_at`) VALUES ('3', 'Account Security', 'account-security', 'fas fa-shield-alt', 'Manage your password, enable two-factor authentication, and keep your personal data safe and secure.', '\r\n        <div class=\"content-card\">\r\n            <h2><i class=\"fas fa-lock\"></i> Password Management</h2>\r\n            <p>A strong password is your first line of defense. Here\'s how to create and manage a secure password:</p>\r\n            <ul>\r\n                <li>Use at least 12 characters mixing uppercase, lowercase, numbers, and symbols</li>\r\n                <li>Avoid using personal information like birthdays or names</li>\r\n                <li>Never reuse passwords across different platforms</li>\r\n                <li>Change your password every 3-6 months</li>\r\n            </ul>\r\n        </div>\r\n\r\n        <div class=\"content-card\">\r\n            <h2><i class=\"fas fa-mobile-alt\"></i> Two-Factor Authentication (2FA)</h2>\r\n            <p>Add an extra layer of security to your WALKON account with two-factor authentication:</p>\r\n            <ul>\r\n                <li>Enable 2FA from Account Settings ΓåÆ Security Settings</li>\r\n                <li>Choose between SMS OTP or Authenticator App</li>\r\n                <li>Verify your identity every time you log in from a new device</li>\r\n            </ul>\r\n        </div>\r\n        ', '2026-02-09 10:36:12');
INSERT INTO `help_pages` (`id`, `title`, `slug`, `icon_class`, `summary`, `content`, `created_at`) VALUES ('4', 'Payment Options', 'payment-options', 'fas fa-credit-card', 'Learn about our secure payment methods, installment plans, and wallet integrations for seamless checkout.', '\r\n        <div class=\"content-card\">\r\n            <h2>Accepted Payment Methods</h2>\r\n            <div class=\"payment-grid\">\r\n                <div class=\"payment-box\">\r\n                    <i class=\"fas fa-credit-card\"></i>\r\n                    <h4>Credit/Debit Cards</h4>\r\n                </div>\r\n                <div class=\"payment-box\">\r\n                    <i class=\"fas fa-wallet\"></i>\r\n                    <h4>Digital Wallets</h4>\r\n                </div>\r\n                <div class=\"payment-box\">\r\n                    <i class=\"fas fa-university\"></i>\r\n                    <h4>Net Banking</h4>\r\n                </div>\r\n                <div class=\"payment-box\">\r\n                    <i class=\"fas fa-qrcode\"></i>\r\n                    <h4>UPI</h4>\r\n                </div>\r\n                <div class=\"payment-box\">\r\n                    <i class=\"fas fa-money-bill-wave\"></i>\r\n                    <h4>Cash on Delivery</h4>\r\n                </div>\r\n                <div class=\"payment-box\">\r\n                    <i class=\"fas fa-calendar-alt\"></i>\r\n                    <h4>EMI Options</h4>\r\n                </div>\r\n            </div>\r\n        </div>\r\n        <div class=\"content-card\">\r\n            <h2>Payment Security</h2>\r\n            <p>All transactions are encrypted with industry-standard SSL/TLS protocols. We never store your complete card details. Your payment information is processed through PCI-DSS compliant gateways.</p>\r\n        </div>\r\n        ', '2026-02-09 10:36:12');
INSERT INTO `help_pages` (`id`, `title`, `slug`, `icon_class`, `summary`, `content`, `created_at`) VALUES ('5', 'Size & Fit Guide', 'size-guide', 'fas fa-ruler', 'Find your perfect fit with our comprehensive size charts, fitting tips, and brand-specific measurement guides.', '\r\n        <div class=\"content-card\">\r\n            <h2>International Size Chart</h2>\r\n            <table>\r\n                <thead>\r\n                    <tr><th>US</th><th>UK</th><th>EU</th><th>CM</th></tr>\r\n                </thead>\r\n                <tbody>\r\n                    <tr><td>6</td><td>5.5</td><td>39</td><td>24.0</td></tr>\r\n                    <tr><td>7</td><td>6.5</td><td>40</td><td>25.0</td></tr>\r\n                    <tr><td>8</td><td>7.5</td><td>41</td><td>26.0</td></tr>\r\n                    <tr><td>9</td><td>8.5</td><td>42</td><td>27.0</td></tr>\r\n                    <tr><td>10</td><td>9.5</td><td>43</td><td>28.0</td></tr>\r\n                    <tr><td>11</td><td>10.5</td><td>44</td><td>29.0</td></tr>\r\n                </tbody>\r\n            </table>\r\n        </div>\r\n        <div class=\"content-card\">\r\n            <h2>How to Measure Your Foot</h2>\r\n            <p>1. Place a piece of paper on a flat surface against a wall</p>\r\n            <p>2. Stand on the paper with your heel against the wall</p>\r\n            <p>3. Mark the longest part of your foot on the paper</p>\r\n            <p>4. Measure the distance from the wall to the mark in centimeters</p>\r\n            <p>5. Use our size chart above to find your size</p>\r\n        </div>\r\n        ', '2026-02-09 10:36:12');
INSERT INTO `help_pages` (`id`, `title`, `slug`, `icon_class`, `summary`, `content`, `created_at`) VALUES ('6', 'Live Customer Support', 'live-support', 'fas fa-headset', 'Connect instantly with our footwear experts for personalized assistance, product recommendations, and more.', '\r\n        <div class=\"content-card\">\r\n            <h2>Get in Touch</h2>\r\n            <p>Our team of footwear experts is ready to assist you with any questions or concerns.</p>\r\n            <div class=\"contact-box\">\r\n                <i class=\"fas fa-phone\"></i>\r\n                <h3>Call Us</h3>\r\n                <a href=\"tel:+911234567890\">+91 123 456 7890</a>\r\n                <p style=\"margin-top: 10px; font-size: 0.95rem;\">Available 24/7</p>\r\n            </div>\r\n            <div class=\"contact-box\">\r\n                <i class=\"fas fa-envelope\"></i>\r\n                <h3>Email Support</h3>\r\n                <a href=\"mailto:support@walkon.com\">support@walkon.com</a>\r\n                <p style=\"margin-top: 10px; font-size: 0.95rem;\">Response within 24 hours</p>\r\n            </div>\r\n            <div class=\"contact-box\">\r\n                <i class=\"fas fa-comments\"></i>\r\n                <h3>Live Chat</h3>\r\n                <p>Click the chat icon in the bottom right corner to start a conversation with our team instantly!</p>\r\n            </div>\r\n        </div>\r\n        ', '2026-02-09 10:36:12');

--- TABLE: marketplaces ---
CREATE TABLE `marketplaces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT 'Marketplace',
  `logo_url` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `website_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 6
INSERT INTO `marketplaces` (`id`, `name`, `category`, `logo_url`, `description`, `website_url`, `is_active`, `display_order`, `created_at`) VALUES ('1', 'Amazon', 'E-commerce', 'https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg', 'Global leader in e-commerce.', 'https://amazon.com', '1', '1', '2026-02-03 23:34:33');
INSERT INTO `marketplaces` (`id`, `name`, `category`, `logo_url`, `description`, `website_url`, `is_active`, `display_order`, `created_at`) VALUES ('2', 'Flipkart', 'E-commerce', 'https://upload.wikimedia.org/wikipedia/en/7/7a/Flipkart_logo.svg', 'Indias leading marketplace.', 'https://flipkart.com', '1', '2', '2026-02-03 23:34:33');
INSERT INTO `marketplaces` (`id`, `name`, `category`, `logo_url`, `description`, `website_url`, `is_active`, `display_order`, `created_at`) VALUES ('3', 'eBay', 'E-commerce', 'https://upload.wikimedia.org/wikipedia/commons/1/1b/EBay_logo.svg', 'Global auction and retail site.', 'https://ebay.com', '1', '3', '2026-02-03 23:34:33');
INSERT INTO `marketplaces` (`id`, `name`, `category`, `logo_url`, `description`, `website_url`, `is_active`, `display_order`, `created_at`) VALUES ('4', 'TikTok Shop', 'Social', 'https://upload.wikimedia.org/wikipedia/en/a/a9/TikTok_logo.svg', 'Social commerce platform.', 'https://shop.tiktok.com', '1', '4', '2026-02-03 23:34:33');
INSERT INTO `marketplaces` (`id`, `name`, `category`, `logo_url`, `description`, `website_url`, `is_active`, `display_order`, `created_at`) VALUES ('5', 'Instagram Shop', 'Social', 'https://upload.wikimedia.org/wikipedia/commons/e/e7/Instagram_logo_2016.svg', 'Visual discovery shopping.', 'https://instagram.com', '1', '5', '2026-02-03 23:34:33');
INSERT INTO `marketplaces` (`id`, `name`, `category`, `logo_url`, `description`, `website_url`, `is_active`, `display_order`, `created_at`) VALUES ('6', 'Shopify', 'Direct', 'https://cdn.shopify.com/shopifycloud/brochure/assets/brand-assets/shopify-logo-primary-logo.svg', 'Independent store platform.', 'https://shopify.com', '1', '6', '2026-02-03 23:34:33');

--- TABLE: materials ---
CREATE TABLE `materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 23
INSERT INTO `materials` (`id`, `name`) VALUES ('4', 'Canvas');
INSERT INTO `materials` (`id`, `name`) VALUES ('28', 'Denim');
INSERT INTO `materials` (`id`, `name`) VALUES ('22', 'Flyknit');
INSERT INTO `materials` (`id`, `name`) VALUES ('7', 'Foam');
INSERT INTO `materials` (`id`, `name`) VALUES ('15', 'Full Grain Leather');
INSERT INTO `materials` (`id`, `name`) VALUES ('11', 'Gore-Tex');
INSERT INTO `materials` (`id`, `name`) VALUES ('8', 'Knit');
INSERT INTO `materials` (`id`, `name`) VALUES ('1', 'Leather');
INSERT INTO `materials` (`id`, `name`) VALUES ('3', 'Mesh');
INSERT INTO `materials` (`id`, `name`) VALUES ('16', 'Nappa Leather');
INSERT INTO `materials` (`id`, `name`) VALUES ('25', 'Neoprene');
INSERT INTO `materials` (`id`, `name`) VALUES ('12', 'Nubuck');
INSERT INTO `materials` (`id`, `name`) VALUES ('9', 'Nylon');
INSERT INTO `materials` (`id`, `name`) VALUES ('13', 'Patent Leather');
INSERT INTO `materials` (`id`, `name`) VALUES ('23', 'Primeknit');
INSERT INTO `materials` (`id`, `name`) VALUES ('29', 'Recycled Polyester');
INSERT INTO `materials` (`id`, `name`) VALUES ('6', 'Rubber');
INSERT INTO `materials` (`id`, `name`) VALUES ('27', 'Satin');
INSERT INTO `materials` (`id`, `name`) VALUES ('2', 'Suede');
INSERT INTO `materials` (`id`, `name`) VALUES ('5', 'Synthetic');
INSERT INTO `materials` (`id`, `name`) VALUES ('14', 'Textile');
INSERT INTO `materials` (`id`, `name`) VALUES ('30', 'Vegan Leather');
INSERT INTO `materials` (`id`, `name`) VALUES ('10', 'Velvet');

--- TABLE: order_notes ---
CREATE TABLE `order_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `order_notes_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_notes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 0

--- TABLE: orders ---
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `seller_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `size` varchar(20) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `channel` varchar(50) DEFAULT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_status` varchar(50) DEFAULT 'pending',
  `payment_link` varchar(500) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `courier_name` varchar(100) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `shipped_date` datetime DEFAULT NULL,
  `delivered_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_seller` (`seller_id`),
  KEY `idx_product` (`product_id`),
  KEY `fk_order_user` (`user_id`),
  CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 54
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('1', '1', '1', '1', 'Demo Customer', NULL, NULL, NULL, '1', '9', '22000.00', '22000.00', 'Amazon', 'delivered', 'pending', NULL, NULL, NULL, '2026-01-19 23:34:33', NULL, NULL, '2026-02-03 23:34:33', '2026-02-18 14:57:59');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('2', '1', '1', '2', 'Demo Customer', NULL, NULL, NULL, '1', '9', '11000.00', '11000.00', 'Amazon', 'delivered', 'pending', NULL, NULL, NULL, '2026-01-11 23:34:33', NULL, NULL, '2026-02-03 23:34:33', '2026-02-18 14:57:59');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('3', '1', '1', '3', 'Demo Customer', NULL, NULL, NULL, '1', '9', '17999.00', '17999.00', 'Amazon', 'delivered', 'pending', NULL, NULL, NULL, '2026-01-29 23:34:33', NULL, NULL, '2026-02-03 23:34:33', '2026-02-18 14:57:59');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('4', '1', '1', '4', 'Demo Customer', NULL, NULL, NULL, '1', '9', '22000.00', '22000.00', 'Amazon', 'delivered', 'pending', NULL, NULL, NULL, '2026-02-02 23:34:33', NULL, NULL, '2026-02-03 23:34:33', '2026-02-18 14:57:59');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('5', '1', '1', '5', 'Demo Customer', NULL, NULL, NULL, '1', '9', '19800.00', '19800.00', 'Amazon', 'delivered', 'pending', NULL, NULL, NULL, '2026-01-21 23:34:33', NULL, NULL, '2026-02-03 23:34:33', '2026-02-18 14:57:59');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('6', NULL, '1', '6', 'Demo Customer', NULL, NULL, NULL, '1', '10', '19800.00', '19800.00', 'Amazon', 'delivered', 'pending', NULL, NULL, NULL, '2026-01-24 23:34:33', NULL, NULL, '2026-02-03 23:34:33', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('7', NULL, '1', '7', 'Demo Customer', NULL, NULL, NULL, '1', '9', '17999.00', '17999.00', 'Amazon', 'delivered', 'pending', NULL, NULL, NULL, '2026-02-01 23:34:33', NULL, NULL, '2026-02-03 23:34:33', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('8', NULL, '1', '8', 'Demo Customer', NULL, NULL, NULL, '1', '10', '11000.00', '11000.00', 'Amazon', 'delivered', 'pending', NULL, NULL, NULL, '2026-01-22 23:34:33', NULL, NULL, '2026-02-03 23:34:33', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('9', NULL, '1', '9', 'Demo Customer', NULL, NULL, NULL, '1', '7', '22000.00', '22000.00', 'Amazon', 'delivered', 'pending', NULL, NULL, NULL, '2026-02-02 23:34:33', NULL, NULL, '2026-02-03 23:34:33', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('10', NULL, '1', '10', 'Demo Customer', NULL, NULL, NULL, '1', '7', '14999.00', '14999.00', 'Amazon', 'delivered', 'pending', NULL, NULL, NULL, '2026-01-30 23:34:33', NULL, NULL, '2026-02-03 23:34:33', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('11', NULL, '1', '3', 'Kabir Das', NULL, NULL, NULL, '0', '8', '0.00', '19800.00', 'Myntra', 'pending', 'paid', NULL, NULL, NULL, '2026-01-24 15:56:49', NULL, NULL, '2026-02-04 00:26:49', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('12', NULL, '1', '1', 'Kabir Das', NULL, NULL, NULL, '0', '9', '0.00', '14999.00', 'Ajio', 'processing', 'unpaid', NULL, NULL, NULL, '2026-01-30 14:56:49', NULL, NULL, '2026-02-04 00:26:49', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('13', NULL, '1', '9', 'Vihaan Singh', NULL, NULL, NULL, '0', '7', '0.00', '2495.00', 'Flipkart', 'processing', 'paid', NULL, NULL, NULL, '2026-01-06 20:56:49', NULL, NULL, '2026-02-04 00:26:49', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('14', NULL, '1', '1', 'Aditya Sharma', NULL, NULL, NULL, '0', '7', '0.00', '14999.00', 'Flipkart', 'cancelled', 'refunded', NULL, NULL, NULL, '2026-01-25 15:56:49', NULL, NULL, '2026-02-04 00:26:49', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('15', NULL, '1', '12', 'Aditya Sharma', NULL, NULL, NULL, '0', '7', '0.00', '13999.00', 'Myntra', 'processing', 'unpaid', NULL, NULL, NULL, '2026-01-23 04:56:49', NULL, NULL, '2026-02-04 00:26:49', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('16', NULL, '1', '6', 'Aarav Patel', NULL, NULL, NULL, '0', '8', '0.00', '3499.00', 'Website', 'delivered', 'paid', NULL, NULL, NULL, '2026-01-18 02:56:49', NULL, NULL, '2026-02-04 00:26:49', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('17', NULL, '1', '3', 'Arjun Reddy', NULL, NULL, NULL, '0', '9', '0.00', '19800.00', 'Ajio', 'pending', 'paid', NULL, NULL, NULL, '2026-01-08 03:56:49', NULL, NULL, '2026-02-04 00:26:49', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('18', NULL, '1', '8', 'Vihaan Singh', NULL, NULL, NULL, '0', '8', '0.00', '4999.00', 'Website', 'shipped', 'paid', NULL, NULL, NULL, '2026-01-04 02:56:49', NULL, NULL, '2026-02-04 00:26:49', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('19', NULL, '1', '3', 'Aditya Sharma', NULL, NULL, NULL, '0', '7', '0.00', '19800.00', 'Myntra', 'processing', 'unpaid', NULL, NULL, NULL, '2026-02-01 20:56:49', NULL, NULL, '2026-02-04 00:26:49', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('20', NULL, '1', '1', 'Aarav Patel', NULL, NULL, NULL, '0', '10', '0.00', '14999.00', 'Ajio', 'shipped', 'paid', NULL, NULL, NULL, '2026-01-25 08:56:49', NULL, NULL, '2026-02-04 00:26:49', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('21', NULL, '1', '3', 'Kabir Das', NULL, NULL, NULL, '0', '10', '0.00', '19800.00', 'Myntra', 'processing', 'paid', NULL, NULL, NULL, '2026-01-16 08:56:49', NULL, NULL, '2026-02-04 00:26:49', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('22', NULL, '1', '12', 'Aarav Patel', NULL, NULL, NULL, '0', '10', '0.00', '13999.00', 'Myntra', 'shipped', 'paid', NULL, NULL, NULL, '2026-01-24 23:56:49', NULL, NULL, '2026-02-04 00:26:49', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('23', NULL, '1', '10', 'Aarav Patel', NULL, NULL, NULL, '0', '7', '0.00', '5499.00', 'Ajio', 'processing', 'unpaid', NULL, NULL, NULL, '2026-01-10 12:56:49', NULL, NULL, '2026-02-04 00:26:49', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('24', NULL, '1', '12', 'Sai Iyer', NULL, NULL, NULL, '0', '8', '0.00', '13999.00', 'Website', 'shipped', 'paid', NULL, NULL, NULL, '2026-01-27 19:56:49', NULL, NULL, '2026-02-04 00:26:49', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('25', NULL, '1', '10', 'Vivaan Kumar', NULL, NULL, NULL, '0', '8', '0.00', '5499.00', 'Amazon', 'pending', 'paid', NULL, NULL, NULL, '2026-01-27 01:56:49', NULL, NULL, '2026-02-04 00:26:49', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('26', NULL, '2', '5', 'Rajesh Kumar', 'rajesh.kumar@example.com', NULL, NULL, '2', '9', '11000.00', '22000.00', 'Amazon', 'pending', 'paid', 'https://rzp.io/l/walkon-demo-653', NULL, NULL, '2026-01-31 20:06:45', NULL, NULL, '2026-02-04 00:36:45', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('27', NULL, '2', '3', 'Priya Patel', 'priya.patel@example.com', NULL, NULL, '1', '10', '19800.00', '19800.00', 'Flipkart', 'processing', 'unpaid', 'https://rzp.io/l/walkon-demo-462', NULL, NULL, '2026-01-26 20:06:45', NULL, NULL, '2026-02-04 00:36:45', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('28', NULL, '2', '5', 'Vikram Singh', 'vikram.singh@example.com', NULL, NULL, '2', '7', '11000.00', '22000.00', 'Flipkart', 'delivered', 'paid', 'https://rzp.io/l/walkon-demo-985', NULL, NULL, '2026-01-24 20:06:45', NULL, NULL, '2026-02-04 00:36:45', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('29', NULL, '2', '5', 'Rajesh Kumar', 'rajesh.kumar@example.com', NULL, NULL, '2', '10', '11000.00', '22000.00', 'Flipkart', 'cancelled', 'failed', 'https://rzp.io/l/walkon-demo-578', NULL, NULL, '2026-02-03 20:06:45', NULL, NULL, '2026-02-04 00:36:45', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('30', NULL, '2', '7', 'Sneha Reddy', 'sneha.reddy@example.com', NULL, NULL, '2', '10', '2999.00', '5998.00', 'Instagram', 'shipped', 'unpaid', 'https://rzp.io/l/walkon-demo-235', NULL, NULL, '2026-01-29 20:06:45', NULL, NULL, '2026-02-04 00:36:45', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('31', NULL, '2', '8', 'Amit Sharma', 'amit.sharma@example.com', NULL, NULL, '1', '7', '4999.00', '4999.00', 'Amazon', 'cancelled', 'failed', 'https://rzp.io/l/walkon-demo-486', NULL, NULL, '2026-01-31 20:06:45', NULL, NULL, '2026-02-04 00:36:45', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('32', NULL, '2', '4', 'Rajesh Kumar', 'rajesh.kumar@example.com', NULL, NULL, '1', '8', '17999.00', '17999.00', 'Direct', 'processing', 'unpaid', 'https://rzp.io/l/walkon-demo-961', NULL, NULL, '2026-01-30 20:06:45', NULL, NULL, '2026-02-04 00:36:45', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('33', NULL, '2', '4', 'Priya Patel', 'priya.patel@example.com', NULL, NULL, '2', '7', '17999.00', '35998.00', 'Direct', 'cancelled', 'paid', 'https://rzp.io/l/walkon-demo-143', NULL, NULL, '2026-02-01 20:06:45', NULL, NULL, '2026-02-04 00:36:45', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('34', NULL, '2', '9', 'Ananya Gupta', 'ananya.gupta@example.com', NULL, NULL, '2', '9', '2495.00', '4990.00', 'Amazon', 'cancelled', 'failed', 'https://rzp.io/l/walkon-demo-682', NULL, NULL, '2026-02-03 20:06:45', NULL, NULL, '2026-02-04 00:36:45', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('35', NULL, '2', '8', 'Sneha Reddy', 'sneha.reddy@example.com', NULL, NULL, '1', '8', '4999.00', '4999.00', 'Flipkart', 'cancelled', 'unpaid', 'https://rzp.io/l/walkon-demo-871', NULL, NULL, '2026-01-28 20:06:45', NULL, NULL, '2026-02-04 00:36:45', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('36', NULL, '2', '10', 'Priya Patel', 'priya.patel@example.com', NULL, NULL, '2', '9', '5499.00', '10998.00', 'Flipkart', 'pending', 'failed', 'https://rzp.io/l/walkon-demo-815', NULL, NULL, '2026-01-25 20:06:45', NULL, NULL, '2026-02-04 00:36:45', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('37', NULL, '2', '3', 'Rajesh Kumar', 'rajesh.kumar@example.com', NULL, NULL, '2', '9', '19800.00', '39600.00', 'Direct', 'delivered', 'paid', 'https://rzp.io/l/walkon-demo-353', NULL, NULL, '2026-01-25 20:06:45', NULL, NULL, '2026-02-04 00:36:45', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('38', NULL, '2', '4', 'Priya Patel', 'priya.patel@example.com', NULL, NULL, '1', '7', '17999.00', '17999.00', 'Amazon', 'processing', 'failed', 'https://rzp.io/l/walkon-demo-725', NULL, NULL, '2026-01-27 20:06:45', NULL, NULL, '2026-02-04 00:36:45', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('39', NULL, '2', '2', 'Sneha Reddy', 'sneha.reddy@example.com', NULL, NULL, '1', '9', '22000.00', '22000.00', 'Instagram', 'pending', 'failed', 'https://rzp.io/l/walkon-demo-785', NULL, NULL, '2026-01-29 20:06:45', NULL, NULL, '2026-02-04 00:36:45', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('40', NULL, '2', '10', 'Rajesh Kumar', 'rajesh.kumar@example.com', NULL, NULL, '1', '9', '5499.00', '5499.00', 'Direct', 'cancelled', 'paid', 'https://rzp.io/l/walkon-demo-196', NULL, NULL, '2026-02-01 20:06:45', NULL, NULL, '2026-02-04 00:36:45', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('41', '7', '1', '1', 'Mosin Joseph', 'mosinmjoseph2027@mca.ajce.in', NULL, NULL, '0', '7', '0.00', '2999.00', NULL, 'delivered', 'paid', NULL, NULL, NULL, '2026-02-06 13:42:19', NULL, NULL, '2026-02-06 13:42:19', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('42', '8', '1', '1', 'Arun Antony', 'arunantonyvarhese2028@mca.ajce.in', NULL, NULL, '0', '8', '0.00', '2999.00', NULL, 'delivered', 'paid', NULL, NULL, NULL, '2026-02-06 13:42:19', NULL, NULL, '2026-02-06 13:42:19', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('43', '9', '1', '1', 'Albin Thomas', 'albinthomas 2028@mca.ajce.in', NULL, NULL, '0', '7', '0.00', '2999.00', NULL, 'delivered', 'paid', NULL, NULL, NULL, '2026-02-06 13:42:19', NULL, NULL, '2026-02-06 13:42:19', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('44', NULL, '1', '1', 'MOSIN M JOSEPH INT MCA 2023-2028', NULL, NULL, NULL, '0', '8', '0.00', '3398.00', 'Website', 'processing', 'paid', NULL, NULL, NULL, '2026-02-10 13:55:39', NULL, NULL, '2026-02-10 13:55:39', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('45', NULL, '1', '1', 'josin', NULL, NULL, NULL, '0', '8', '0.00', '3398.00', 'Website', 'processing', 'unpaid', NULL, NULL, NULL, '2026-02-10 13:56:59', NULL, NULL, '2026-02-10 13:56:59', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('46', NULL, '1', '1', 'sobin varghese', NULL, NULL, NULL, '0', '8', '0.00', '3398.00', 'Website', 'processing', 'paid', NULL, NULL, NULL, '2026-02-10 14:11:18', NULL, NULL, '2026-02-10 14:11:18', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('47', NULL, '1', '1', 'MOSIN M JOSEPH INT MCA 2023-2028', NULL, NULL, NULL, '0', '8', '0.00', '3398.00', 'Website', 'processing', 'paid', NULL, NULL, NULL, '2026-02-18 11:53:29', NULL, NULL, '2026-02-18 11:53:29', '2026-02-18 13:41:43');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('48', NULL, '1', '1', 'MOSIN M JOSEPH INT MCA 2023-2028', NULL, NULL, NULL, '0', NULL, '0.00', '3398.00', 'Website', 'processing', 'paid', NULL, NULL, NULL, '2026-02-18 15:19:31', NULL, NULL, '2026-02-18 15:19:31', '2026-02-18 15:19:31');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('49', NULL, '1', '1', 'MOSIN M JOSEPH INT MCA 2023-2028', NULL, NULL, NULL, '0', NULL, '0.00', '3398.00', 'Website', 'delivered', 'paid', NULL, NULL, NULL, '2026-02-18 21:15:44', NULL, NULL, '2026-02-18 21:15:44', '2026-02-19 15:34:22');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('50', '36', '1', '90', 'Jane Doe', 'jane@walkon.com', NULL, NULL, '1', NULL, '3499.00', '3499.00', NULL, 'delivered', 'pending', NULL, NULL, NULL, '2026-02-20 11:31:38', NULL, NULL, '2026-02-20 11:31:38', '2026-02-20 11:31:38');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('51', '36', '1', '91', 'Jane Doe', 'jane@walkon.com', NULL, NULL, '1', NULL, '2999.00', '2999.00', NULL, 'delivered', 'pending', NULL, NULL, NULL, '2026-02-20 11:31:38', NULL, NULL, '2026-02-20 11:31:38', '2026-02-20 11:31:38');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('52', '36', '1', '92', 'Jane Doe', 'jane@walkon.com', NULL, NULL, '1', NULL, '2299.00', '2299.00', NULL, 'delivered', 'pending', NULL, NULL, NULL, '2026-02-20 11:31:38', NULL, NULL, '2026-02-20 11:31:38', '2026-02-20 11:31:38');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('53', NULL, '1', '1', 'MOSIN M JOSEPH INT MCA 2023-2028', NULL, NULL, NULL, '0', NULL, '0.00', '3398.00', 'Website', 'processing', 'paid', NULL, NULL, NULL, '2026-02-23 10:27:37', NULL, NULL, '2026-02-23 10:27:37', '2026-02-23 10:27:37');
INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `product_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `quantity`, `size`, `unit_price`, `total_price`, `channel`, `status`, `payment_status`, `payment_link`, `tracking_number`, `courier_name`, `order_date`, `shipped_date`, `delivered_date`, `created_at`, `updated_at`) VALUES ('54', NULL, '1', '1', 'MOSIN M JOSEPH INT MCA 2023-2028', NULL, NULL, NULL, '0', NULL, '0.00', '3398.00', 'Website', 'processing', 'paid', NULL, NULL, NULL, '2026-02-23 10:28:00', NULL, NULL, '2026-02-23 10:28:00', '2026-02-23 10:28:00');

--- TABLE: payment_methods ---
CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `method_key` varchar(50) NOT NULL,
  `icon_class` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `method_key` (`method_key`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 6
INSERT INTO `payment_methods` (`id`, `method_key`, `icon_class`, `title`, `description`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES ('1', 'card', 'fa-credit-card', 'Credit & Debit Cards', 'Pay seamlessly using your Visa, Mastercard, or RuPay card. Supports saved cards for faster checkout.', '1', '1', '2026-02-10 10:59:20', '2026-02-10 10:59:20');
INSERT INTO `payment_methods` (`id`, `method_key`, `icon_class`, `title`, `description`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES ('2', 'wallet', 'fa-wallet', 'Digital Wallets', 'Instant payments via Paytm, PhonePe, Mobikwik, and more. Enjoy exclusive wallet-specific cashback.', '1', '2', '2026-02-10 10:59:20', '2026-02-10 10:59:20');
INSERT INTO `payment_methods` (`id`, `method_key`, `icon_class`, `title`, `description`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES ('3', 'netbanking', 'fa-university', 'Net Banking', 'Support for all major Indian banks including SBI, HDFC, ICICI, and Axis. Safe and reliable transfers.', '1', '3', '2026-02-10 10:59:20', '2026-02-10 10:59:20');
INSERT INTO `payment_methods` (`id`, `method_key`, `icon_class`, `title`, `description`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES ('4', 'upi', 'fa-qrcode', 'UPI Payments', 'Scan and pay or enter your VPA. Fast, free, and secure via GPay, PhonePe, or BHIM.', '1', '4', '2026-02-10 10:59:20', '2026-02-10 10:59:20');
INSERT INTO `payment_methods` (`id`, `method_key`, `icon_class`, `title`, `description`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES ('5', 'cod', 'fa-money-bill-wave', 'Cash on Delivery', 'Pay when your package arrives. We now also accept scan-on-delivery via UPI for COD orders.', '1', '5', '2026-02-10 10:59:20', '2026-02-10 10:59:20');
INSERT INTO `payment_methods` (`id`, `method_key`, `icon_class`, `title`, `description`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES ('6', 'emi', 'fa-calendar-alt', 'EMI Options', 'Split your payment into easy installments. Zero-interest EMI available on selected bank cards.', '1', '6', '2026-02-10 10:59:20', '2026-02-10 10:59:20');

--- TABLE: platform_features ---
CREATE TABLE `platform_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `icon` varchar(50) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 4
INSERT INTO `platform_features` (`id`, `icon`, `title`, `description`) VALUES ('1', 'fas fa-layer-group', 'Multi-Channel Sync', 'Instant inventory synchronization across 15+ global marketplaces.');
INSERT INTO `platform_features` (`id`, `icon`, `title`, `description`) VALUES ('2', 'fas fa-chart-line', 'Smart Analytics', 'Deep insights into your sales performance with AI-driven forecasting.');
INSERT INTO `platform_features` (`id`, `icon`, `title`, `description`) VALUES ('3', 'fas fa-bolt', 'Auto-Pricing', 'Stay competitive with real-time price matching algorithms.');
INSERT INTO `platform_features` (`id`, `icon`, `title`, `description`) VALUES ('4', 'fas fa-truck-moving', 'Global Logistics', 'Integrated shipping solutions with major worldwide carriers.');

--- TABLE: product_authenticity ---
CREATE TABLE `product_authenticity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `qr_code_url` varchar(500) DEFAULT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_number` (`serial_number`),
  KEY `product_id` (`product_id`),
  KEY `verified_by` (`verified_by`),
  CONSTRAINT `product_authenticity_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_base` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_authenticity_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 0

--- TABLE: product_base ---
CREATE TABLE `product_base` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `sub_category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `status` enum('draft','published','scheduled') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `seller_id` (`seller_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `product_base_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_base_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 139
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('1', '2', '1', NULL, 'Nike Air Max Pulse', 'published', '2026-02-03 23:34:33', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('2', '2', '1', NULL, 'Adidas Yeezy Boost 350', 'published', '2026-02-03 23:34:33', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('3', '2', '2', NULL, 'Timberland Premium 6-Inch', 'published', '2026-02-03 23:34:33', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('4', '2', '1', NULL, 'Nike Air Jordan 1 Retro', 'published', '2026-02-03 23:34:33', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('5', '2', '4', NULL, 'Puma RS-X Reinvent', 'published', '2026-02-03 23:34:33', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('6', '2', '5', NULL, 'Bata Premium Oxford Black', 'published', '2026-02-04 00:06:22', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('7', '2', '5', NULL, 'Classic Leather Derby', 'published', '2026-02-04 00:06:22', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('8', '2', '6', NULL, 'Converse Chuck Taylor All Star', 'published', '2026-02-04 00:06:22', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('9', '2', '6', NULL, 'Crocs Classic Clog Navy', 'published', '2026-02-04 00:06:22', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('10', '2', '6', NULL, 'Skechers Go Walk Evolution', 'published', '2026-02-04 00:06:22', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('11', '2', '1', NULL, 'Fila Disruptor II Premium', 'published', '2026-02-04 00:06:22', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('12', '2', '4', NULL, 'Under Armour Curry Flow 9', 'published', '2026-02-04 00:06:22', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('13', '2', '4', NULL, 'Sparx SM-654 Running', 'published', '2026-02-04 00:06:22', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('14', '2', '6', NULL, 'Asian Tarzan-11', 'published', '2026-02-04 00:06:22', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('15', '3', '4', '9', 'Adidas Ultraboost 22', 'published', '2026-02-04 00:14:01', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('16', '3', '6', '20', 'Sparx Men\'s SM-734 Casual Shoes', 'published', '2026-02-04 00:17:51', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('17', '3', '6', '15', 'Reebok Classic Leather', 'published', '2026-02-04 00:22:25', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('18', '2', '4', '16', 'Gel-Kayano 30', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('19', '2', '4', '13', 'Gel-Nimbus 25', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('20', '2', '1', '2', 'Triple S Sneaker', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('21', '2', '1', '3', 'Speed Trainer', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('22', '2', '5', '18', 'City Formal Derby', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('23', '2', '5', '19', 'Comfort Walk Loafer', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('24', '2', '6', '21', 'Arizona Soft Footbed', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('25', '2', '6', '22', 'Boston Clog', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('26', '2', '4', '13', 'Ghost 15', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('27', '2', '4', '16', 'Adrenaline GTS 23', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('28', '2', '2', '7', 'Desert Boot', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('29', '2', '6', '22', 'Wallabee', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('30', '2', '1', '1', 'Chuck Taylor All Star Hi', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('31', '2', '1', '2', 'Chuck 70 Low', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('32', '2', '6', '21', 'Classic Clog', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('33', '2', '6', '23', 'LiteRide 360', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('34', '2', '2', '6', '1460 8-Eye Boot', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('35', '2', '5', '18', '1461 Smooth Shoe', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('36', '2', '1', '2', 'Disruptor II', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('37', '2', '1', '3', 'Ray Tracer', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('38', '2', '1', '2', 'Ace Sneaker', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('39', '2', '5', '19', 'Horsebit Loafer', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('40', '2', '4', '13', 'Bondi 8', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('41', '2', '4', '14', 'Speedgoat 5', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('42', '2', '1', '1', 'Air Jordan 1 Retro High', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('43', '2', '1', '1', 'Air Jordan 4 OG', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('44', '2', '1', '2', 'Aha Sneakers', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('45', '2', '4', '12', 'Force 10 Sports', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('46', '2', '5', '17', 'Metro Formal Oxford', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('47', '2', '6', '1', 'Casual Loafer XL', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('48', '2', '4', '13', 'Wave Rider 27', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('49', '2', '4', '16', 'Wave Inspire 19', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('50', '2', '4', '13', 'Fresh Foam 1080v12', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('51', '2', '1', '2', '990v6 Core', 'published', '2026-02-04 00:36:27', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('52', '3', '4', '15', 'Puma Velocity Nitro 4', 'published', '2026-02-04 08:03:43', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('53', '3', '5', '16', 'Nike Pegasus 41', 'published', '2026-02-04 08:10:10', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('54', '3', '1', '3', 'ASICS Novablast 5', 'published', '2026-02-04 08:13:50', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('55', '7', '2', '7', 'Fila Men Red Replica', 'published', '2026-02-04 09:04:14', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('57', '7', '4', '11', 'Air Jordan 1 Low', 'published', '2026-02-04 09:17:37', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('58', '4', '4', '14', 'Puma Velocity Nitro 4', 'published', '2026-02-09 10:05:39', '2026-02-17 21:15:26', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('59', '3', '4', '9', 'Nike Air Zoom Pegasus 40', 'published', '2026-02-11 09:49:01', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('60', '3', '4', '11', 'Adidas Solarboost 5', 'published', '2026-02-11 09:49:02', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('61', '3', '4', '15', 'Reebok Nano X3', 'published', '2026-02-11 09:49:02', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('62', '3', NULL, '11', 'Puma Velocity Nitro 2', 'published', '2026-02-11 09:49:02', '2026-02-19 09:02:01', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('63', '3', NULL, '9', 'Puma Deviate Nitro 2', 'published', '2026-02-11 09:49:02', '2026-02-19 09:02:01', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('64', '3', '4', '60', 'Asics Gel-Kayano 30', 'published', '2026-02-11 09:49:02', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('65', '2', '4', NULL, 'Nike Invincible 3', 'published', '2026-02-12 10:00:00', '2026-02-18 11:41:34', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('66', '2', '1', NULL, 'Adidas Forum Low', 'published', '2026-02-13 11:30:00', '2026-02-18 11:41:34', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('67', '2', '1', NULL, 'New Balance 550', 'published', '2026-02-16 14:15:00', '2026-02-18 11:41:34', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('68', '5', '4', '11', 'Adidas Ultraboost 22', 'published', '2026-02-17 21:25:44', '2026-02-17 21:25:44', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('69', '5', '4', '9', 'Red Tape Men Mesh Running Shoes', 'published', '2026-02-17 21:36:26', '2026-02-17 21:36:26', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('70', '2', '1', NULL, 'Nike Air Force 1 07', 'published', '2026-02-17 21:38:11', '2026-02-18 11:41:34', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('71', '2', '1', NULL, 'Puma Suede Classic', 'published', '2026-02-17 21:38:11', '2026-02-18 11:41:34', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('72', '2', '6', NULL, 'Reebok Club C 85', 'published', '2026-02-17 21:38:11', '2026-02-18 11:41:34', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('73', '2', '1', NULL, 'Vans Old Skool', 'published', '2026-02-17 21:38:11', '2026-02-18 11:41:34', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('74', '2', '1', NULL, 'Converse Chuck 70', 'published', '2026-02-17 21:38:11', '2026-02-18 11:41:34', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('75', '2', '4', NULL, 'Skechers GoRun Razor 4', 'published', '2026-02-17 21:38:11', '2026-02-18 11:41:34', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('76', '2', '4', NULL, 'Asics Gel-Nimbus 25', 'published', '2026-02-17 21:38:11', '2026-02-18 11:41:34', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('77', '2', '4', NULL, 'Under Armour Curry 10', 'published', '2026-02-17 21:38:11', '2026-02-18 11:41:34', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('78', '2', '6', NULL, 'Crocs Classic Clog', 'published', '2026-02-17 21:38:11', '2026-02-18 11:41:34', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('79', '2', '1', NULL, 'Jordan Air Jordan 1 Mid', 'published', '2026-02-17 21:38:11', '2026-02-18 11:41:34', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('80', '3', '21', NULL, 'Nike Air Max 270', 'published', '2026-02-17 23:16:09', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('81', '3', '21', NULL, 'Adidas Ultraboost 5.0', 'published', '2026-02-17 23:16:09', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('82', '3', '21', NULL, 'Puma RS-X Bold', 'published', '2026-02-17 23:16:09', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('83', '3', '21', NULL, 'Sparx Men Athletic Shoe', 'published', '2026-02-17 23:16:09', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('84', '3', '21', NULL, 'Asics Gel-Kayano 29', 'published', '2026-02-17 23:16:09', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('85', '3', '21', NULL, 'Clarks Leather Desert Boot', 'published', '2026-02-17 23:16:09', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('86', '3', '21', NULL, 'Dr. Martens 1460 Smooth', 'published', '2026-02-17 23:16:09', '2026-02-18 11:41:34', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('87', '7', '6', '75', 'Sparx Men\'s SM-734 Casual Shoes', 'published', '2026-02-18 10:50:23', '2026-02-18 10:50:23', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('88', '7', '6', '21', 'Nike Air Zoom Pegasus 41', 'published', '2026-02-18 12:30:22', '2026-02-18 12:30:22', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('89', '8', '6', '22', 'Fresh Foam X 880v14 \'Olivine', 'published', '2026-02-18 13:34:04', '2026-02-18 13:34:04', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('90', '1', '5', NULL, 'Red Tape Oxford Formal', 'published', '2026-02-19 09:06:50', '2026-02-19 09:06:50', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('91', '1', '5', NULL, 'Red Tape Derby Brogue', 'published', '2026-02-19 09:06:50', '2026-02-19 09:06:50', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('92', '1', '22', NULL, 'Red Tape Sports Runner', 'published', '2026-02-19 09:06:50', '2026-02-19 09:06:50', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('93', '1', '1', NULL, 'Red Tape Casual Sneaker', 'published', '2026-02-19 09:06:50', '2026-02-19 09:06:50', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('94', '8', '22', '77', 'Red Tape Men\'s Athleisure Shoes', 'published', '2026-02-19 09:11:23', '2026-02-19 09:11:23', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('95', '9', '4', '60', 'shoe', 'published', '2026-02-19 16:10:21', '2026-02-19 16:10:21', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('96', '8', '4', NULL, 'Nike Invincible 3', 'published', '2026-02-22 22:51:11', '2026-02-22 22:51:11', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('97', '8', '1', NULL, 'Converse Chuck 70', 'published', '2026-03-05 12:15:17', '2026-03-05 12:15:17', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('98', '8', '21', NULL, 'Adidas Ultraboost 5.0', 'published', '2026-03-05 12:24:07', '2026-03-05 12:24:07', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('99', '8', NULL, '9', 'Puma Deviate Nitro 2', 'published', '2026-03-05 12:24:22', '2026-03-05 12:24:22', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('100', '8', '6', '21', 'Nike Air Zoom Pegasus 41', 'published', '2026-03-05 13:45:04', '2026-03-05 13:45:04', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('101', '8', '4', NULL, 'Under Armour Curry 10', 'published', '2026-03-05 16:41:10', '2026-03-05 16:41:10', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('102', '8', '1', NULL, 'Nike Air Force 1 07', 'published', '2026-03-05 16:41:15', '2026-03-09 15:15:25', 'approved');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('104', '1', '4', NULL, 'Asian Thunder-01 Sports Shoes', 'published', '2026-03-09 16:20:08', '2026-03-09 16:20:08', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('105', '1', '4', NULL, 'Asics Gel-Kayano 29 Platinum', 'published', '2026-03-09 16:20:08', '2026-03-09 16:20:08', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('106', '1', '5', NULL, 'Bata Premium Oxford Leather', 'published', '2026-03-09 16:20:08', '2026-03-09 16:20:08', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('107', '1', '2', NULL, 'Clarks Suede Desert Boots', 'published', '2026-03-09 16:20:08', '2026-03-09 16:20:08', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('108', '1', '1', NULL, 'Fila Disruptor II Premium', 'published', '2026-03-09 16:20:08', '2026-03-09 16:20:08', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('109', '1', '2', NULL, 'Dr. Martens 1460 Smooth Leather', 'published', '2026-03-09 16:20:08', '2026-03-09 16:20:08', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('110', '1', '5', NULL, 'Red Tape Men\'s Premium Brogues', 'published', '2026-03-09 16:35:29', '2026-03-09 16:35:29', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('111', '1', '1', NULL, 'Red Tape Sleek White Sneakers', 'published', '2026-03-09 16:35:29', '2026-03-09 16:35:29', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('112', '1', '1', NULL, 'Reebok Classic Leather Legacy', 'published', '2026-03-09 16:35:29', '2026-03-09 16:35:29', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('113', '1', '4', NULL, 'Reebok Floatride Nano Training', 'published', '2026-03-09 16:35:29', '2026-03-09 16:35:29', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('114', '1', '4', NULL, 'Skechers GoWalk 6-Iconic', 'published', '2026-03-09 16:35:29', '2026-03-09 16:35:29', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('115', '1', '1', NULL, 'Skechers D\'Lites Lifestyle Sneaker', 'published', '2026-03-09 16:35:29', '2026-03-09 16:35:29', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('116', '1', '4', NULL, 'Sparx Lightweight Mesh Runner', 'published', '2026-03-09 16:35:29', '2026-03-09 16:35:29', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('117', '1', '6', NULL, 'Sparx Sporty Comfort Sandals', 'published', '2026-03-09 16:35:29', '2026-03-09 16:35:29', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('118', '1', '1', NULL, 'Sparx Men\'s Canvas Casual Sneakers', 'published', '2026-03-09 16:40:16', '2026-03-09 16:40:16', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('119', '1', '4', NULL, 'Sparx SM-616 Athletic Running Shoes', 'published', '2026-03-09 16:40:16', '2026-03-09 16:40:16', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('120', '1', '4', NULL, 'Sparx Men\'s Ultra-Light High-Performance Running Shoes', 'published', '2026-03-09 16:40:16', '2026-03-09 20:34:40', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('121', '1', '1', NULL, 'Sparx Mens Slip-on Casual Loafers', 'published', '2026-03-09 16:40:16', '2026-03-09 16:40:16', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('122', '1', '1', NULL, 'Classic Leather', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('123', '1', '22', NULL, 'Nano X3', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('124', '1', '4', NULL, 'Floatride Energy 4', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('125', '1', '1', NULL, 'Club C 85', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('126', '1', '1', NULL, 'Zig Kinetica 3', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('127', '1', '1', NULL, 'Disruptor II Premium', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('128', '1', '1', NULL, 'Ray Tracer Evo', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('129', '1', '22', NULL, 'Grant Hill 2', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('130', '1', '1', NULL, 'Original Fitness', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('131', '1', '1', NULL, 'Mindblower', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('132', '1', '1', NULL, '990v6 Core', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('133', '1', '1', NULL, '574 Classic', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('134', '1', '4', NULL, 'Fresh Foam 1080v12', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('135', '1', '4', NULL, 'FuelCell Rebel v3', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('136', '1', '1', NULL, '2002R Protection Pack', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('137', '1', '1', NULL, 'RS-X Efekt', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('138', '1', '4', NULL, 'Velocity Nitro 2', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('139', '1', '1', NULL, 'Suede Classic XXI', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('140', '1', '1', NULL, 'Cali Dream', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');
INSERT INTO `product_base` (`id`, `seller_id`, `category_id`, `sub_category_id`, `name`, `status`, `created_at`, `updated_at`, `approval_status`) VALUES ('141', '1', '1', NULL, 'Mirage Sport Tech', 'published', '2026-03-09 21:13:38', '2026-03-09 21:13:38', 'pending');

--- TABLE: product_channels ---
CREATE TABLE `product_channels` (
  `product_id` int(11) NOT NULL,
  `channel_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_id`,`channel_name`),
  CONSTRAINT `product_channels_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_base` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 132
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('1', 'Amazon', '2026-02-03 23:34:33');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('1', 'Flipkart', '2026-02-03 23:34:33');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('1', 'Shopify', '2026-02-03 23:34:33');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('2', 'Amazon', '2026-02-03 23:34:33');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('2', 'Flipkart', '2026-02-03 23:34:33');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('2', 'Shopify', '2026-02-03 23:34:33');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('3', 'Amazon', '2026-02-03 23:34:33');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('3', 'Flipkart', '2026-02-03 23:34:33');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('3', 'Shopify', '2026-02-03 23:34:33');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('4', 'Amazon', '2026-02-03 23:34:33');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('4', 'Flipkart', '2026-02-03 23:34:33');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('4', 'Shopify', '2026-02-03 23:34:33');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('5', 'Amazon', '2026-02-03 23:34:33');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('5', 'Flipkart', '2026-02-03 23:34:33');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('5', 'Shopify', '2026-02-03 23:34:33');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('15', 'eBay', '2026-02-04 00:14:01');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('15', 'Instagram Shop', '2026-02-04 00:14:01');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('15', 'TikTok Shop', '2026-02-04 00:14:01');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('16', 'Amazon', '2026-02-04 00:17:51');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('16', 'Flipkart', '2026-02-04 00:17:51');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('16', 'Shopify', '2026-02-04 00:17:51');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('17', 'eBay', '2026-02-04 00:22:25');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('17', 'Flipkart', '2026-02-04 00:22:25');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('17', 'TikTok Shop', '2026-02-04 00:22:25');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('18', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('18', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('19', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('19', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('20', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('20', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('21', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('21', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('22', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('22', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('23', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('23', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('24', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('24', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('25', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('25', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('26', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('26', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('27', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('27', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('28', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('28', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('29', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('29', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('30', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('30', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('31', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('31', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('32', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('32', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('33', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('33', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('34', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('34', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('35', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('35', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('36', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('36', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('37', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('37', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('38', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('38', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('39', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('39', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('40', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('40', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('41', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('41', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('42', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('42', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('43', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('43', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('44', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('44', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('45', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('45', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('46', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('46', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('47', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('47', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('48', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('48', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('49', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('49', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('50', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('50', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('51', 'Direct', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('51', 'WALKON', '2026-02-04 00:36:27');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('52', 'Amazon', '2026-02-04 08:03:43');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('52', 'Flipkart', '2026-02-04 08:03:43');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('52', 'Instagram Shop', '2026-02-04 08:03:43');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('53', 'eBay', '2026-02-04 08:10:10');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('53', 'Shopify', '2026-02-04 08:10:10');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('53', 'TikTok Shop', '2026-02-04 08:10:10');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('54', 'eBay', '2026-02-04 08:13:50');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('54', 'Instagram Shop', '2026-02-04 08:13:50');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('54', 'Shopify', '2026-02-04 08:13:50');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('55', 'eBay', '2026-02-04 09:04:14');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('55', 'Instagram Shop', '2026-02-04 09:04:14');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('55', 'TikTok Shop', '2026-02-04 09:04:14');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('57', 'eBay', '2026-02-04 09:17:37');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('57', 'Flipkart', '2026-02-04 09:17:37');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('57', 'TikTok Shop', '2026-02-04 09:17:37');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('58', 'Amazon', '2026-02-09 10:05:40');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('58', 'Flipkart', '2026-02-09 10:05:40');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('58', 'TikTok Shop', '2026-02-09 10:05:40');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('68', 'Amazon', '2026-02-17 21:25:44');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('68', 'eBay', '2026-02-17 21:25:44');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('68', 'TikTok Shop', '2026-02-17 21:25:44');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('69', 'Amazon', '2026-02-17 21:36:26');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('69', 'eBay', '2026-02-17 21:36:26');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('69', 'Instagram Shop', '2026-02-17 21:36:26');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('69', 'TikTok Shop', '2026-02-17 21:36:26');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('87', 'eBay', '2026-02-18 10:50:23');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('87', 'Flipkart', '2026-02-18 10:50:23');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('87', 'TikTok Shop', '2026-02-18 10:50:23');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('88', 'Amazon', '2026-02-18 12:30:22');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('88', 'Instagram Shop', '2026-02-18 12:30:22');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('88', 'TikTok Shop', '2026-02-18 12:30:22');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('89', 'Amazon', '2026-02-18 13:34:04');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('89', 'eBay', '2026-02-18 13:34:04');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('89', 'Flipkart', '2026-02-18 13:34:04');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('89', 'TikTok Shop', '2026-02-18 13:34:04');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('94', 'Amazon', '2026-02-19 09:11:24');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('94', 'eBay', '2026-02-19 09:11:24');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('94', 'Flipkart', '2026-02-19 09:11:24');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('94', 'Shopify', '2026-02-19 09:11:24');
INSERT INTO `product_channels` (`product_id`, `channel_name`, `created_at`) VALUES ('95', 'Flipkart', '2026-02-19 16:10:21');

--- TABLE: product_colors ---
CREATE TABLE `product_colors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `color_name` varchar(50) NOT NULL,
  `color_hex` varchar(20) DEFAULT '#000000',
  `color_code` varchar(10) DEFAULT '#000000',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_colors_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_base` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1813 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 973
-- (Data too large to dump inline, 973 rows)

--- TABLE: product_descriptions ---
CREATE TABLE `product_descriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_descriptions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_base` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 115
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('1', '1', 'The Air Max Pulse combines street style with rugged performance.', '2026-02-03 23:34:33');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('2', '2', 'The global icon of modern sneaker culture.', '2026-02-03 23:34:33');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('3', '3', 'The original waterproof boot that started it all.', '2026-02-03 23:34:33');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('4', '4', 'The legend that changed basketball and fashion forever.', '2026-02-03 23:34:33');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('5', '5', 'Retrofuturistic design with extreme cushioning.', '2026-02-03 23:34:33');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('6', '15', 'Max-cushioned neutral running shoe with 100% Boost midsole for high energy return (up to ~4% more than previous models). Sock-like Primeknit+ upper (often with recycled materials like Parley Ocean Plastic), Linear Energy Push system for stability, and Continental rubber outsole for grip and durability. Great for easy/recovery runs, long comfortable days, and casual streetwear. Note: Men\'s and women\'s versions have gender-specific lasts (women\'s has more medial support).', '2026-02-04 00:14:01');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('7', '16', 'Stylish and comfortable casual sneakers with synthetic leather upper, lace-up closure, cushioned insole for all-day comfort, lightweight PVC sole for good grip and flexibility. Simple, clean design ΓÇö very popular as everyday white/grey sneakers for college, casual outings, or daily use. Breathable and easy to maintain.', '2026-02-04 00:17:51');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('8', '17', 'Timeless low-top casual sneaker with premium soft leather upper, classic lace-up closure, padded EVA midsole for cushioning, low-profile rubber outsole for durability and grip. Retro 80s-inspired design with perforated toe box for breathability, iconic Reebok window-box logo on side. Extremely versatile ΓÇö perfect for everyday casual wear, college, street style, or light walking. Very comfortable out of the box and ages well.', '2026-02-04 00:22:25');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('9', '18', 'Premium stability running shoes for maximum comfort and support.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('10', '19', 'Highly cushioned road running shoe for plush comfort.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('11', '20', 'Iconic oversized sneaker defineing the dad-shoe trend.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('12', '21', 'Sock-like knit sneaker with a technical sole.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('13', '22', 'Classic leather derby shoes for everyday professional wear.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('14', '23', 'Easy-to-wear slip-on loafers for ultimate comfort.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('15', '24', 'The classic two-strap sandal with legendary cork footbed.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('16', '25', 'Versatile clogs that can be worn for work or leisure.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('17', '26', 'Balanced cushioning and smooth transitions for neutral runners.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('18', '27', 'Stability focused running shoe with GuideRails support system.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('19', '28', 'Original desert boots in premium suede.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('20', '29', 'Classic moccasin construction in durable leather.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('21', '30', 'The timeless high-top canvas sneaker.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('22', '31', 'Premium materials and vintage details for modern comfort.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('23', '32', 'Original foam clog for lightweight comfort and versatility.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('24', '33', 'Enhanced cushioning for active comfort.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('25', '34', 'The original Dr. Martens boot with air-cushioned sole.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('26', '35', 'Classic 3-eye shoe in smooth leather.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('27', '36', 'Bold, chunky sneaker with sawtooth sole.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('28', '37', 'Technical heritage sneaker with mixed materials.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('29', '38', 'Embroidered low-top sneaker in premium leather.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('30', '39', 'Timeless luxury loafers with iconic horsebit detail.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('31', '40', 'Max cushioned road shoe for ultimate plush ride.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('32', '41', 'Top-tier trail running shoe for technical terrain.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('33', '42', 'The sneaker that started it all in its original high-top form.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('34', '43', 'Classic silhouette with flight cushioning and support.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('35', '44', 'Affordable and stylish sneakers for every day.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('36', '45', 'Durable sports shoes for active lifestyle.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('37', '46', 'Sleek leather oxfords for formal occasions.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('38', '47', 'Comfortable loafers for casual weekend outings.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('39', '48', 'Dynamic running shoe with signature Wave Plate technology.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('40', '49', 'Supportive running shoe for overpronation control.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('41', '50', 'Premium cushioning for a smooth and comfortable run.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('42', '51', 'Classic American-made sneaker with unmatched support.', '2026-02-04 00:36:27');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('43', '52', 'Lightweight, versatile daily trainer running shoe with a responsive and energetic ride. Features full-length NITROFOAMΓäó midsole for excellent energy return, cushioning, and bounce. Improved breathable engineered mesh upper for better ventilation and fit. Thick PUMAGRIP rubber outsole delivers reliable traction on wet and dry roads. Ideal for easy daily runs, tempo sessions, longer efforts, and even some racing. Known for being agile, durable, and great value ΓÇö one of the top-rated neutral trainers of 2025ΓÇô2026.', '2026-02-04 08:03:43');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('44', '53', 'Classic, reliable daily trainer for road running. Features a ReactX foam midsole (new for v41, lighter and more responsive than previous React) combined with dual Air Zoom units (forefoot + heel) for energized, springy cushioning and smooth transitions. Breathable engineered mesh upper for better ventilation and reduced weight. Durable waffle-inspired rubber outsole for excellent grip on roads. Versatile for easy runs, tempo sessions, long runs, and beginners to experienced runners. Known for consistent fit, durability, and all-around performance.', '2026-02-04 08:10:10');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('45', '54', 'Highly bouncy, energetic daily trainer with a fun, propulsive ride. Full FF BLASTΓäó MAX midsole foam for soft landings, excellent energy return, and lightweight feel. Engineered jacquard mesh upper for breathability, stretch, and secure fit (with tongue wing construction to reduce movement). Updated outsole geometry for smooth transitions and good traction. Ideal for easy runs, tempo work, long runs, and runners wanting max fun/cushion without feeling heavy. One of the top-rated cushioned neutral shoes in 2025ΓÇô2026 reviews.', '2026-02-04 08:13:50');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('46', '55', 'Predominantly red upper (often with black accents), streamlined silhouette, three-dimensional/enamel-finished synthetic leather or TPU upper for stability, side molding symbolizing F1 aerodynamics, cockpit-style wooden injection structure for instep protection, iconic FILA FLAG logo, bold and sporty streetwear look.', '2026-02-04 09:04:14');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('48', '57', 'Extremely versatile with hundreds of colorways released over the years. Classic and popular options include:\r\nBlack/White (\"Panda\" or neutral tones) ΓÇö clean monochrome look.\r\nBlack/Red/White (\"Bred\" or \"Chicago\" variants) ΓÇö bold, iconic with black upper, red accents, white midsole.\r\nWhite/Black/Red ΓÇö similar to Chicago but often reversed.\r\nOther favorites: Shadow, Obsidian, Neutral Grey, or vibrant collabs/special editions.\r\nFeatures perforated toe box for breathability, padded collar and tongue, leather/synthetic upper (often premium tumbled leather), signature Nike Swoosh and Jumpman logo on tongue/heel, rubber outsole with herringbone traction, encapsulated Air cushioning in the heel for responsive comfort, low-profile silhouette that\'s easy to style with jeans, shorts, or athleisure ΓÇö durable, timeless, and endlessly versatile.', '2026-02-04 09:17:37');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('49', '58', 'Velocity NITROΓäó 4 is a lightweight daily running shoe designed for road performance and everyday training. It features a full-length NITROFOAMΓäó midsole for responsive cushioning and energy return, a breathable engineered mesh upper with PWRTAPE for stability, and a PUMAGRIP outsole for dependable traction on multiple surfaces. Fit is regular with a 10 mm heel-to-toe drop and rounded toe profile.', '2026-02-09 10:05:40');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('50', '65', 'Maximum cushioning to support every mile, the Nike Invincible 3 gives you our highest level of comfort underfoot.', '2026-02-17 21:16:52');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('51', '66', 'The Adidas Forum shoes are back in their original form. A 1980s basketball icon, they make an impact on the streets today.', '2026-02-17 21:16:52');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('52', '67', 'The return of a legend. Originally worn by pros, the new 550 pays tribute to the 1989 original with classic details.', '2026-02-17 21:16:52');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('53', '68', 'The Adidas UltraBoost 22 is a premium performance-running shoe that blends cutting-edge comfort with modern style and sustainability. It features a full-length BOOSTΓäó midsole that delivers high energy return, making every step feel responsive and cushioned ΓÇö ideal for daily runs, recovery sessions, or casual wear.', '2026-02-17 21:25:44');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('54', '69', 'These Red Tape menΓÇÖs mesh running/athleisure shoes are designed for breathable comfort and casual active use. They feature a mesh upper that allows air circulation to keep your feet cool during runs or workouts, plus a cushioned insole for everyday comfort and shock absorption. The lace-up closure provides a secure, adjustable fit, while the EVA/TPU/TPR sole offers flexibility, traction, and lightweight performance ΓÇö suitable for short runs, gym workouts, or casual sporty wear rather than long-distance training.', '2026-02-17 21:36:26');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('55', '70', 'The radiance lives on in the Nike Air Force 1 07, the b-ball OG that puts a fresh spin on what you know best.', '2026-02-17 21:38:11');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('56', '71', 'The Suede is the sneaker that helped build Puma. It remains a classic and is as comfortable as it is stylish.', '2026-02-17 21:38:11');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('57', '72', 'Clean, minimalist design. The Club C 85 is a court classic that delivers effortless style for every day.', '2026-02-17 21:38:11');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('58', '73', 'The Old Skool, the Vans classic skate shoe and first to bare the iconic sidestripe, is a low top lace-up.', '2026-02-17 21:38:11');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('59', '74', 'The Chuck 70 mixes the best details from the 70s-era Chuck with impeccable craftsmanship and premium materials.', '2026-02-17 21:38:11');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('60', '75', 'Race-ready performance. The GoRun Razor 4 features Hyper Burst Pro cushioning for an ultra-responsive ride.', '2026-02-17 21:38:11');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('61', '76', 'Experience the softest landings with the GEL-NIMBUS 25. High-level cushioning for your long distance runs.', '2026-02-17 21:38:11');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('62', '77', 'Elevate your game with the UA Curry 10. UA Flow cushioning is totally rubberless, making it light and ridiculously grippy.', '2026-02-17 21:38:11');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('63', '78', 'Original. Versatile. Comfortable. The legendary Classic Clog that started a comfort revolution around the world.', '2026-02-17 21:38:11');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('64', '79', 'Inspired by the original AJ1, this mid-top edition maintains the iconic look you love with choice colors and crisp leather.', '2026-02-17 21:38:11');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('65', '87', 'Sparx SM-734 is a budget-friendly casual shoe choice with a synthetic upper, lace closure, and basic cushioning ΓÇö great for everyday casual use like college outings, errands, or general walking. The exact SKU model may differ slightly based on colourway and seller listings, but the main model identifier is SM-734.', '2026-02-18 10:50:23');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('66', '88', 'Nike Air Zoom Pegasus 41 is a lightweight daily-training running shoe designed for comfort, stability and long-distance support. It features responsive Zoom Air cushioning and a breathable mesh upper for all-day wear.', '2026-02-18 12:30:22');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('67', '89', 'The New Balance Fresh Foam X 880v14 ΓÇÿOlivineΓÇÖ is a premium neutral running shoe built for everyday training and long-distance comfort. It features a soft and responsive Fresh Foam X midsole that provides smooth cushioning and stable transitions, while the engineered mesh upper delivers excellent breathability and a secure, adaptive fit. The durable rubber outsole offers reliable traction for road running and daily wear. The stylish Olivine colourway makes it suitable for both performance running and casual lifestyle use.', '2026-02-18 13:34:04');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('68', '90', 'Crafted from genuine leather, this sleek Oxford from Red Tape brings understated elegance to your formal wardrobe.', '2026-02-19 09:06:50');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('69', '91', 'Classic brogue detailing meets modern comfort in this Red Tape Derby, perfect for office and evening-out occasions.', '2026-02-19 09:06:50');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('70', '92', 'Lightweight mesh upper with cushioned sole technology ΓÇö ideal for daily runs and gym training sessions.', '2026-02-19 09:06:50');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('71', '93', 'Effortlessly cool casual sneaker in breathable canvas ΓÇö a versatile everyday essential.', '2026-02-19 09:06:50');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('72', '94', 'Designed for active lifestyles and everyday comfort, these athleisure shoes blend performance and casual style.\r\n\r\nConstructed with a breathable mesh/TPU upper that enhances airflow and flexibility while providing support.\r\n\r\nComfortable cushioned insole (often memory-foam or EVA/Classic Comfort EVA) helps absorb impact during walking, training, or daily use.\r\n\r\nDurable EVA + TPU + TPR sole delivers good grip and stability on various surfaces.\r\n\r\nLace-up closure ensures a secure, adjustable fit.\r\n\r\nRound toe and flat heel design gives natural foot movement and balance.\r\n\r\nIdeal for workouts, running around town, gym sessions, or casual daily wear.', '2026-02-19 09:11:23');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('73', '95', 'cvv', '2026-02-19 16:10:21');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('74', '96', 'Maximum cushioning to support every mile, the Nike Invincible 3 gives you our highest level of comfort underfoot.', '2026-02-22 22:51:11');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('75', '97', 'The Chuck 70 mixes the best details from the 70s-era Chuck with impeccable craftsmanship and premium materials.', '2026-03-05 12:15:17');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('76', '100', 'Nike Air Zoom Pegasus 41 is a lightweight daily-training running shoe designed for comfort, stability and long-distance support. It features responsive Zoom Air cushioning and a breathable mesh upper for all-day wear.', '2026-03-05 13:45:04');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('77', '101', 'Elevate your game with the UA Curry 10. UA Flow cushioning is totally rubberless, making it light and ridiculously grippy.', '2026-03-05 16:41:10');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('78', '102', 'The radiance lives on in the Nike Air Force 1 07, the b-ball OG that puts a fresh spin on what you know best.', '2026-03-05 16:41:15');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('79', '104', 'Lightweight and breathable running shoes from Asian. Perfect for daily jogging and gym sessions.', '2026-03-09 16:20:08');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('80', '105', 'High-performance running shoes with advanced GEL technology for maximum cushioning and stability.', '2026-03-09 16:20:08');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('81', '106', 'Classic black leather oxford shoes from Bata. Handcrafted for a sophisticated look at any formal event.', '2026-03-09 16:20:08');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('82', '107', 'The original Clarks Desert Boot. Timeless design in premium tan suede with a signature crepe sole.', '2026-03-09 16:20:08');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('83', '108', 'The iconic chunky sneaker from Fila. Bold design with a sawtooth sole and high-quality leather upper.', '2026-03-09 16:20:08');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('84', '109', 'The classic 8-eye boot. Durably built with smooth leather and the iconic yellow welt stitch.', '2026-03-09 16:20:08');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('85', '110', 'Elegant tan leather brogues by Red Tape. Featuring intricate wingtip detailing and a cushioned footbed for all-day comfort.', '2026-03-09 16:35:29');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('86', '111', 'Modern minimalist sneakers from Red Tape. Perfect for a sharp casual look with jeans or chinos.', '2026-03-09 16:35:29');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('87', '112', 'A heritage-inspired classic. The Reebok Classic Leather brings a timeless silhouette with premium soft leather upper.', '2026-03-09 16:35:29');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('88', '113', 'The ultimate training shoe. Floatride Energy foam provides lightweight, responsive cushioning for high-intensity workouts.', '2026-03-09 16:35:29');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('89', '114', 'Reach the next level of comfort with Skechers GoWalk 6. Features lightweight Ultra Go cushioning and high-rebound Hyper Pillar Technology.', '2026-03-09 16:35:29');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('90', '115', 'Go retro with the Skechers D\'Lites. This chunky lifestyle sneaker combines a sporty look with Memory Foam comfort.', '2026-03-09 16:35:29');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('91', '116', 'Versatile running shoes from Sparx. Durable mesh upper for breathability and a lightweight sole for effortless motion.', '2026-03-09 16:35:29');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('92', '117', 'Durable and stylish sporty sandals from Sparx. Multi-strap design with Velcro closure for a secure and adjustable fit.', '2026-03-09 16:35:29');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('93', '118', 'Classic canvas sneakers from Sparx. Durable vulcanized sole and breathable fabric for everyday comfort.', '2026-03-09 16:40:16');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('94', '119', 'High-performance running shoes with superior grip and shock absorption for your daily track runs.', '2026-03-09 16:40:16');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('95', '120', 'Modern, breathable, and incredibly lightweight. The Sparx Ultra-Light Running Shoes are designed for maximum performance and everyday comfort.', '2026-03-09 16:40:16');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('96', '121', 'Easy-to-wear slip-on loafers by Sparx. Lightweight design with a modern silhouette for office or weekends.', '2026-03-09 16:40:16');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('97', '122', 'Timeless design with premium leather upper.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('98', '123', 'Versatile training shoe for high-intensity workouts.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('99', '124', 'Lightweight and responsive for long-distance runs.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('100', '125', 'Classic tennis-inspired style for everyday wear.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('101', '126', 'Energetic cushioning and bold style.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('102', '127', 'The iconic chunky sneaker that defines bold style.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('103', '128', 'A modern take on heritage running silhouettes.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('104', '129', 'Classic basketball style from a legendary player.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('105', '130', 'Retro court style for a clean, daily look.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('106', '131', '90s-inspired runner with oversized logo detailing.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('107', '132', 'The pinnacle of performance and style, made in the USA.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('108', '133', 'The most New Balance shoe ever, timeless and versatile.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('109', '134', 'Premium cushioning for long-distance comfort.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('110', '135', 'Light and fast for tempo runs and interval training.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('111', '136', 'Deconstructed aesthetic with modern comfort tech.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('112', '137', 'Bold colorways and a bulky silhouette for maximum impact.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('113', '138', 'Responsive NITRO foam for an explosive running experience.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('114', '139', 'The original street style icon since 1968.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('115', '140', 'Laid-back West Coast vibes with a chunky platform.', '2026-03-09 21:13:38');
INSERT INTO `product_descriptions` (`id`, `product_id`, `content`, `created_at`) VALUES ('116', '141', 'Fusion of classic sport and edgy streetwear.', '2026-03-09 21:13:38');

--- TABLE: product_marketplaces ---
CREATE TABLE `product_marketplaces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `marketplace_id` int(11) NOT NULL,
  `product_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `marketplace_id` (`marketplace_id`),
  CONSTRAINT `product_marketplaces_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_base` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_marketplaces_ibfk_2` FOREIGN KEY (`marketplace_id`) REFERENCES `marketplaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 0

--- TABLE: product_media ---
CREATE TABLE `product_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `url` varchar(500) NOT NULL,
  `type` enum('image','video') DEFAULT 'image',
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `color` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_media_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_base` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 141
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('1', '1', 'uploads/products/product_1_698240d0e859c.jpg', 'image', '1', '2026-02-03 23:34:33', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('2', '2', 'uploads/products/product_2_698240d3092ea.jpg', 'image', '1', '2026-02-03 23:34:33', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('3', '3', 'https://images.unsplash.com/photo-1520639889313-72721e0ab9ef', 'image', '1', '2026-02-03 23:34:33', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('4', '4', 'uploads/products/product_4_698240d38d094.jpg', 'image', '1', '2026-02-03 23:34:33', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('5', '5', 'uploads/products/product_5_698240d4aab79.jpg', 'image', '1', '2026-02-03 23:34:33', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('6', '6', 'uploads/products/product_6_698240d535c99.jpg', 'image', '1', '2026-02-04 00:06:22', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('7', '7', 'uploads/products/product_7_698240d59473d.jpg', 'image', '1', '2026-02-04 00:06:22', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('8', '8', 'uploads/products/product_8_698240d5d74de.jpg', 'image', '1', '2026-02-04 00:06:22', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('9', '9', 'uploads/products/product_9_698240d606539.jpg', 'image', '1', '2026-02-04 00:06:22', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('10', '10', 'https://images.unsplash.com/photo-1512374382149-233c48b6303a?w=800', 'image', '1', '2026-02-04 00:06:22', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('12', '12', 'https://images.unsplash.com/photo-1518002171953-a080ee802e12?w=800', 'image', '1', '2026-02-04 00:06:22', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('13', '13', 'uploads/products/product_13_698240d67c6c0.jpg', 'image', '1', '2026-02-04 00:06:22', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('14', '14', 'https://images.unsplash.com/photo-1560769629-975e13f0c470?w=800', 'image', '1', '2026-02-04 00:06:22', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('15', '15', 'uploads/img_698241f10de7f9.87373047.jpg', 'image', '1', '2026-02-04 00:14:01', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('16', '16', 'uploads/img_698242d710ad98.14365740.jpg', 'image', '1', '2026-02-04 00:17:51', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('17', '17', 'uploads/img_698243e9705967.11121783.jpg', 'image', '1', '2026-02-04 00:22:25', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('18', '18', 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('19', '19', 'https://images.unsplash.com/photo-1584735175315-9d5df23860e6?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('20', '20', 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('21', '21', 'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('22', '22', 'https://images.unsplash.com/photo-1533867617858-e7b97e060509?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('23', '23', 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('24', '24', 'https://images.unsplash.com/photo-1603487788427-d31d4024220c?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('25', '25', 'https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('26', '26', 'uploads/adidas_ultraboost.png', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('27', '27', 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('28', '28', 'https://images.unsplash.com/photo-1520639889313-75198e705476?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('29', '29', 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('30', '30', 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('31', '31', 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('32', '32', 'https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('33', '33', 'uploads/adidas_uploaded.png', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('34', '34', 'https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('35', '35', 'https://images.unsplash.com/photo-1512374382149-4332c6c0211d?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('36', '36', 'https://images.unsplash.com/photo-1552346154-21d328109a27?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('37', '37', 'https://images.unsplash.com/photo-1628413993904-94ecb60f1239?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('38', '38', 'https://images.unsplash.com/photo-1562183241-b937e95585b6?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('39', '39', 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('40', '40', 'https://images.unsplash.com/photo-1584735175315-9d5df23860e6?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('41', '41', 'https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('42', '42', 'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('43', '43', 'https://images.stockx.com/images/Air-Jordan-4-Retro-Fire-Red-2020-Product.jpg?fit=fill&bg=FFFFFF&w=700&h=500&fm=webp&auto=compress&q=90&dpr=2', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('44', '44', 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('45', '45', 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('46', '46', 'images/metro_oxford_v2.png', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('47', '47', 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('48', '48', 'https://images.unsplash.com/photo-1584735175315-9d5df23860e6?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('49', '49', 'https://images.unsplash.com/photo-1539185441755-769473a23570?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('50', '50', 'https://images.unsplash.com/photo-1539185441755-769473a23570?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('51', '51', 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?q=80&w=1000&auto=format&fit=crop', 'image', '1', '2026-02-04 00:36:27', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('52', '52', 'uploads/img_6982b007986e28.88611704.jpg', 'image', '1', '2026-02-04 08:03:43', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('53', '53', 'uploads/img_6982b18aa54ec6.64614920.jpg', 'image', '1', '2026-02-04 08:10:10', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('56', '54', 'images/asics_novablast_5.png', 'image', '1', '2026-02-04 08:44:19', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('57', '11', 'https://images.unsplash.com/photo-1512374382149-233c42b6a83b?w=800', 'image', '1', '2026-02-04 08:44:19', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('62', '57', 'uploads/img_6982c1590398e3.62731955.jpg', 'image', '1', '2026-02-04 09:17:37', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('64', '55', 'uploads/air_jordan_back.png', 'image', '1', '2026-02-04 09:19:59', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('65', '58', 'uploads/img_6989641bee0c80.30684905.jpg', 'image', '1', '2026-02-09 10:05:40', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('66', '59', 'uploads/air_jordan_green.png', 'image', '1', '2026-02-11 09:49:02', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('67', '60', 'https://images.unsplash.com/photo-1587563877366-2621ec39bcc1?q=80&w=800&auto=format&fit=crop', 'image', '1', '2026-02-11 09:49:02', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('68', '61', 'https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=800&auto=format&fit=crop', 'image', '1', '2026-02-11 09:49:02', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('69', '62', 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=800&auto=format&fit=crop', 'image', '1', '2026-02-11 09:49:02', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('70', '63', 'https://images.unsplash.com/photo-1552346154-21d32810aba3?q=80&w=800&auto=format&fit=crop', 'image', '1', '2026-02-11 09:49:02', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('71', '64', 'https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=800&auto=format&fit=crop', 'image', '1', '2026-02-11 09:49:02', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('72', '65', 'https://images.stockx.com/360/Nike-ZoomX-Invincible-Run-Flyknit-3-Obsidian-Volt/Images/Nike-ZoomX-Invincible-Run-Flyknit-3-Obsidian-Volt/v2/01.jpg?auto=compress&q=90&dpr=2&updated_at=1682520336&fit=clip&fm=webp&w=894', 'image', '1', '2026-02-17 21:16:52', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('73', '66', 'https://assets.adidas.com/images/h_840,f_auto,q_auto,fl_lossy,c_fill,g_auto/09c29af563104d2a9d2adad100bb6e53_9366/Forum_Low_Shoes_White_FY7756_01_standard.jpg', 'image', '1', '2026-02-17 21:16:52', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('74', '67', 'https://nb.scene7.com/is/image/NB/bb550wt1_nb_02_i?$pdpflexf22x$&wid=440&hei=440', 'image', '1', '2026-02-17 21:16:52', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('75', '68', 'uploads/img_69948f802dd388.15604702.jpg', 'image', '1', '2026-02-17 21:25:44', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('76', '69', 'uploads/img_69949202009f80.01184125.jpg', 'image', '1', '2026-02-17 21:36:26', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('77', '70', 'https://static.nike.com/a/images/t_PDP_1280_v1/f_auto,q_auto:eco/b7d9211c-26e7-431a-ac24-b0540fb3c00f/air-force-1-07-shoes-Wr0Q17.png', 'image', '1', '2026-02-17 21:38:11', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('78', '71', 'https://images.puma.com/image/upload/f_auto,q_auto,b_rgb:fafafa,w_600,h_600/global/352634/03/sv01/fnd/IND/fmt/png/Suede-Classic-XXI-Unisex-Sneakers', 'image', '1', '2026-02-17 21:38:11', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('79', '72', 'https://images.reebok.com/image/upload/f_auto,q_auto,fl_lossy,c_fill,g_auto/01878d21a2a440cab94fad350175b28d_9366/Club_C_85_Shoes_White_AR0456_01_standard.jpg', 'image', '1', '2026-02-17 21:38:11', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('80', '73', 'https://images.vans.com/is/image/Vans/VN000D3HY28-HERO?$583x583$', 'image', '1', '2026-02-17 21:38:11', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('81', '74', 'https://www.converse.in/media/catalog/product/1/6/162050c_a_107x1.jpg', 'image', '1', '2026-02-17 21:38:11', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('82', '75', 'https://www.skechers.in/on/demandware.static/-/Sites-skechersin-Library/default/dw8374826f/images/pdp/246075_BLU_1.jpg', 'image', '1', '2026-02-17 21:38:11', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('83', '76', 'https://images.asics.com/is/image/asics/1011B547_001_SR_RT_GLB?$sf_pdp$', 'image', '1', '2026-02-17 21:38:11', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('84', '77', 'uploads/ua_curry_10.png', 'image', '1', '2026-02-17 21:38:11', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('85', '78', 'https://images.crocs.com/is/image/Crocs/10001_001_ALT100?wid=574&hei=470&fmt=jpeg&qlt=85,1&op_sharpen=0&resMode=sharp2&op_usm=1,1,6,0&iccEmbed=0&printRes=72', 'image', '1', '2026-02-17 21:38:11', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('86', '79', 'https://images.stockx.com/360/Air-Jordan-1-Mid-Dutch-Green-W/Images/Air-Jordan-1-Mid-Dutch-Green-W/v2/01.jpg?auto=compress&q=90&dpr=2&updated_at=1615560936&fit=clip&fm=webp&ixlib=react-9.0.3&w=894', 'image', '1', '2026-02-17 21:38:11', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('87', '80', 'uploads/air_jordan_lifestyle.png', 'image', '1', '2026-02-17 23:16:09', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('88', '81', 'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?auto=format&fit=crop&q=80&w=600', 'image', '1', '2026-02-17 23:16:09', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('89', '82', 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&q=80&w=600', 'image', '1', '2026-02-17 23:16:09', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('90', '83', 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?auto=format&fit=crop&q=80&w=600', 'image', '1', '2026-02-17 23:16:09', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('91', '84', 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&q=80&w=600', 'image', '1', '2026-02-17 23:16:09', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('92', '85', 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&q=80&w=600', 'image', '1', '2026-02-17 23:16:09', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('93', '86', 'https://images.unsplash.com/photo-1560306660-39449238fac5?auto=format&fit=crop&q=80&w=600', 'image', '1', '2026-02-17 23:16:09', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('94', '87', 'uploads/img_69954c17ce6d07.03218183.jpg', 'image', '1', '2026-02-18 10:50:23', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('95', '88', 'uploads/img_69956386469501.75003736.jpg', 'image', '1', '2026-02-18 12:30:22', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('96', '89', 'uploads/img_69957274ad1311.04450490.jpg', 'image', '1', '2026-02-18 13:34:04', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('97', '90', 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-02-19 09:06:50', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('98', '91', 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-02-19 09:06:50', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('99', '92', 'uploads/air_jordan_sole.png', 'image', '1', '2026-02-19 09:06:50', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('100', '93', 'https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-02-19 09:06:50', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('101', '94', 'uploads/img_69968663ec7186.14392056.jpg', 'image', '1', '2026-02-19 09:11:24', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('102', '96', 'https://images.stockx.com/360/Nike-ZoomX-Invincible-Run-Flyknit-3-Obsidian-Volt/Images/Nike-ZoomX-Invincible-Run-Flyknit-3-Obsidian-Volt/v2/01.jpg?auto=compress&q=90&dpr=2&updated_at=1682520336&fit=clip&fm=webp&w=894', 'image', '1', '2026-02-22 22:51:11', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('103', '97', 'https://www.converse.in/media/catalog/product/1/6/162050c_a_107x1.jpg', 'image', '1', '2026-03-05 12:15:17', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('104', '98', 'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?auto=format&fit=crop&q=80&w=600', 'image', '1', '2026-03-05 12:24:07', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('105', '99', 'https://images.unsplash.com/photo-1552346154-21d32810aba3?q=80&w=800&auto=format&fit=crop', 'image', '1', '2026-03-05 12:24:22', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('106', '100', 'uploads/img_69956386469501.75003736.jpg', 'image', '1', '2026-03-05 13:45:04', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('107', '101', 'uploads/ua_curry_10.png', 'image', '1', '2026-03-05 16:41:10', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('108', '102', 'https://static.nike.com/a/images/t_PDP_1280_v1/f_auto,q_auto:eco/b7d9211c-26e7-431a-ac24-b0540fb3c00f/air-force-1-07-shoes-Wr0Q17.png', 'image', '1', '2026-03-05 16:41:15', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('109', '95', 'uploads/air_jordan_top.png', 'image', '1', '2026-03-07 23:04:53', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('110', '1', 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=800&q=80', 'image', '0', '2026-03-09 10:07:57', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('111', '1', 'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=800&q=80', 'image', '0', '2026-03-09 10:07:57', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('112', '104', 'assets/products/asian_sport.png', 'image', '1', '2026-03-09 16:20:08', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('113', '105', 'assets/products/asics_kayano.png', 'image', '1', '2026-03-09 16:20:08', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('114', '106', 'assets/products/bata_premium.png', 'image', '1', '2026-03-09 16:20:08', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('115', '107', 'assets/products/clarks_desert.png', 'image', '1', '2026-03-09 16:20:08', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('116', '108', 'assets/products/fila_disruptor.png', 'image', '1', '2026-03-09 16:20:08', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('117', '109', 'https://images.unsplash.com/photo-1627341577457-41481d6f1ac3?auto=format&fit=crop&w=1000&q=80', 'image', '1', '2026-03-09 16:20:08', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('118', '110', 'assets/products/redtape_oxford.jpg', 'image', '1', '2026-03-09 16:35:29', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('119', '111', 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=1000&q=80', 'image', '1', '2026-03-09 16:35:29', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('120', '112', 'https://images.unsplash.com/photo-1539185441755-769473a23570?auto=format&fit=crop&w=1000&q=80', 'image', '1', '2026-03-09 16:35:29', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('121', '113', 'https://images.unsplash.com/photo-1512374382149-4332c6c0242a?auto=format&fit=crop&w=1000&q=80', 'image', '1', '2026-03-09 16:35:29', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('122', '114', 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?auto=format&fit=crop&w=1000&q=80', 'image', '1', '2026-03-09 16:35:29', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('123', '115', 'https://images.unsplash.com/photo-1562183241-b937e95585b6?auto=format&fit=crop&w=1000&q=80', 'image', '1', '2026-03-09 16:35:29', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('124', '116', 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=1000&q=80', 'image', '1', '2026-03-09 16:35:29', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('125', '117', 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=1000&q=80', 'image', '1', '2026-03-09 16:35:29', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('126', '118', 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=1000&q=80', 'image', '1', '2026-03-09 16:40:16', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('127', '119', 'https://images.unsplash.com/photo-1514989940723-e8e51635b782?auto=format&fit=crop&w=1000&q=80', 'image', '1', '2026-03-09 16:40:16', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('128', '120', 'uploads/sparx_running_shoe.png', 'image', '1', '2026-03-09 16:40:16', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('129', '121', 'https://images.unsplash.com/photo-1531310197839-ccf54634509e?auto=format&fit=crop&w=1000&q=80', 'image', '1', '2026-03-09 16:40:16', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('130', '122', 'https://images.unsplash.com/photo-1544441893-675973e31d85?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('131', '123', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('132', '124', 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('133', '125', 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('134', '126', 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('135', '127', 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('136', '128', 'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('137', '129', 'https://images.unsplash.com/photo-1539185441755-769473a23570?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('138', '130', 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('139', '131', 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('140', '132', 'https://images.unsplash.com/photo-1539185441755-769473a23570?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('141', '133', 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('142', '134', 'https://images.unsplash.com/photo-1584735175315-9d5df23860e6?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('143', '135', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('144', '136', 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('145', '137', 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('146', '138', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('147', '139', 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('148', '140', 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);
INSERT INTO `product_media` (`id`, `product_id`, `url`, `type`, `is_primary`, `created_at`, `color`) VALUES ('149', '141', 'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?auto=format&fit=crop&w=800&q=80', 'image', '1', '2026-03-09 21:13:38', NULL);

--- TABLE: product_prices ---
CREATE TABLE `product_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_price` decimal(10,2) DEFAULT NULL,
  `max_price` decimal(10,2) DEFAULT NULL,
  `smart_pricing_status` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_prices_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_base` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 139
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('1', '1', '14999.00', '12999.00', '17999.00', '1', '2026-02-03 23:34:33');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('2', '2', '22000.00', '20000.00', '25000.00', '1', '2026-02-03 23:34:33');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('3', '3', '19800.00', '17800.00', '22800.00', '1', '2026-02-03 23:34:33');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('4', '4', '17999.00', '15999.00', '20999.00', '1', '2026-02-03 23:34:33');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('5', '5', '11000.00', '9000.00', '14000.00', '1', '2026-02-03 23:34:33');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('6', '6', '3499.00', '3149.10', '3848.90', '0', '2026-02-04 00:06:22');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('7', '7', '2999.00', '2699.10', '3298.90', '0', '2026-02-04 00:06:22');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('8', '8', '4999.00', '4499.10', '5498.90', '0', '2026-02-04 00:06:22');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('9', '9', '2495.00', '2245.50', '2744.50', '0', '2026-02-04 00:06:22');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('10', '10', '5499.00', '4949.10', '6048.90', '0', '2026-02-04 00:06:22');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('11', '11', '6999.00', '6299.10', '7698.90', '0', '2026-02-04 00:06:22');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('12', '12', '13999.00', '12599.10', '15398.90', '0', '2026-02-04 00:06:22');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('13', '13', '1299.00', '1169.10', '1428.90', '0', '2026-02-04 00:06:22');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('14', '14', '899.00', '809.10', '988.90', '0', '2026-02-04 00:06:22');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('15', '15', '3000.00', NULL, NULL, '0', '2026-02-04 00:14:01');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('16', '16', '2000.00', NULL, NULL, '0', '2026-02-04 00:17:51');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('17', '17', '2000.00', NULL, NULL, '0', '2026-02-04 00:22:25');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('18', '18', '160.00', '144.00', '240.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('19', '19', '160.00', '144.00', '240.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('20', '20', '1100.00', '990.00', '1650.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('21', '21', '950.00', '855.00', '1425.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('22', '22', '45.00', '40.50', '67.50', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('23', '23', '35.00', '31.50', '52.50', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('24', '24', '140.00', '126.00', '210.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('25', '25', '155.00', '139.50', '232.50', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('26', '26', '140.00', '126.00', '210.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('27', '27', '140.00', '126.00', '210.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('28', '28', '150.00', '135.00', '225.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('29', '29', '160.00', '144.00', '240.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('30', '30', '65.00', '58.50', '97.50', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('31', '31', '80.00', '72.00', '120.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('32', '32', '50.00', '45.00', '75.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('33', '33', '65.00', '58.50', '97.50', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('34', '34', '170.00', '153.00', '255.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('35', '35', '130.00', '117.00', '195.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('36', '36', '75.00', '67.50', '112.50', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('37', '37', '80.00', '72.00', '120.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('38', '38', '720.00', '648.00', '1080.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('39', '39', '920.00', '828.00', '1380.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('40', '40', '165.00', '148.50', '247.50', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('41', '41', '155.00', '139.50', '232.50', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('42', '42', '180.00', '162.00', '270.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('43', '43', '210.00', '189.00', '315.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('44', '44', '30.00', '27.00', '45.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('45', '45', '40.00', '36.00', '60.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('46', '46', '55.00', '49.50', '82.50', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('47', '47', '45.00', '40.50', '67.50', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('48', '48', '140.00', '126.00', '210.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('49', '49', '140.00', '126.00', '210.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('50', '50', '160.00', '144.00', '240.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('51', '51', '200.00', '180.00', '300.00', '0', '2026-02-04 00:36:27');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('52', '52', '4000.00', NULL, NULL, '0', '2026-02-04 08:03:43');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('53', '53', '5000.00', NULL, NULL, '0', '2026-02-04 08:10:10');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('54', '54', '900.00', NULL, NULL, '0', '2026-02-04 08:13:50');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('55', '55', '800.00', NULL, NULL, '0', '2026-02-04 09:04:14');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('57', '57', '7000.00', NULL, NULL, '0', '2026-02-04 09:17:37');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('58', '58', '3000.00', NULL, NULL, '0', '2026-02-09 10:05:39');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('59', '59', '11995.00', NULL, '14394.00', '0', '2026-02-11 09:49:01');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('60', '60', '14999.00', NULL, '17998.80', '0', '2026-02-11 09:49:02');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('61', '61', '12999.00', NULL, '15598.80', '0', '2026-02-11 09:49:02');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('62', '62', '10999.00', NULL, '13198.80', '0', '2026-02-11 09:49:02');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('63', '63', '15999.00', NULL, '19198.80', '0', '2026-02-11 09:49:02');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('64', '64', '15995.00', NULL, '19194.00', '0', '2026-02-11 09:49:02');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('65', '65', '16995.00', NULL, '18995.00', '0', '2026-02-17 21:16:52');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('66', '66', '9999.00', NULL, '10999.00', '0', '2026-02-17 21:16:52');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('67', '67', '12999.00', NULL, '14999.00', '0', '2026-02-17 21:16:52');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('68', '68', '1000.00', NULL, NULL, '0', '2026-02-17 21:25:44');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('69', '69', '2000.00', NULL, NULL, '0', '2026-02-17 21:36:26');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('70', '70', '8195.00', NULL, '8195.00', '0', '2026-02-17 21:38:11');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('71', '71', '6999.00', NULL, '7999.00', '0', '2026-02-17 21:38:11');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('72', '72', '7599.00', NULL, '7599.00', '0', '2026-02-17 21:38:11');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('73', '73', '4999.00', NULL, '5499.00', '0', '2026-02-17 21:38:11');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('74', '74', '5999.00', NULL, '5999.00', '0', '2026-02-17 21:38:11');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('75', '75', '11999.00', NULL, '13999.00', '0', '2026-02-17 21:38:11');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('76', '76', '15999.00', NULL, '17999.00', '0', '2026-02-17 21:38:11');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('77', '77', '14999.00', NULL, '14999.00', '0', '2026-02-17 21:38:11');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('78', '78', '2995.00', NULL, '3495.00', '0', '2026-02-17 21:38:11');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('79', '79', '11495.00', NULL, '11495.00', '0', '2026-02-17 21:38:11');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('80', '80', '12999.00', NULL, '15999.00', '0', '2026-02-17 23:16:09');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('81', '81', '16999.00', NULL, '18999.00', '0', '2026-02-17 23:16:09');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('82', '82', '8999.00', NULL, '9999.00', '0', '2026-02-17 23:16:09');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('83', '83', '2499.00', NULL, '2999.00', '0', '2026-02-17 23:16:09');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('84', '84', '14999.00', NULL, '16999.00', '0', '2026-02-17 23:16:09');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('85', '85', '7499.00', NULL, '8999.00', '0', '2026-02-17 23:16:09');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('86', '86', '15999.00', NULL, '17999.00', '0', '2026-02-17 23:16:09');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('87', '87', '900.00', NULL, NULL, '0', '2026-02-18 10:50:23');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('88', '88', '2000.00', NULL, NULL, '0', '2026-02-18 12:30:22');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('89', '89', '700.00', NULL, NULL, '0', '2026-02-18 13:34:04');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('90', '90', '3499.00', NULL, NULL, '0', '2026-02-19 09:06:50');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('91', '91', '2999.00', NULL, NULL, '0', '2026-02-19 09:06:50');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('92', '92', '2299.00', NULL, NULL, '0', '2026-02-19 09:06:50');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('93', '93', '1999.00', NULL, NULL, '0', '2026-02-19 09:06:50');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('94', '94', '3000.00', NULL, NULL, '0', '2026-02-19 09:11:23');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('95', '95', '1.00', NULL, NULL, '0', '2026-02-19 16:10:21');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('96', '96', '16995.00', NULL, '18995.00', '0', '2026-02-22 22:51:11');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('97', '97', '5999.00', NULL, '5999.00', '0', '2026-03-05 12:15:17');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('98', '98', '16999.00', NULL, '18999.00', '0', '2026-03-05 12:24:07');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('99', '99', '15999.00', NULL, '19198.80', '0', '2026-03-05 12:24:22');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('100', '100', '2000.00', NULL, NULL, '0', '2026-03-05 13:45:04');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('101', '101', '14999.00', NULL, '14999.00', '0', '2026-03-05 16:41:10');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('102', '102', '8195.00', NULL, '8195.00', '0', '2026-03-05 16:41:15');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('103', '104', '1299.00', NULL, '1999.00', '0', '2026-03-09 16:20:08');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('104', '105', '14999.00', NULL, '16999.00', '0', '2026-03-09 16:20:08');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('105', '106', '3499.00', NULL, '4999.00', '0', '2026-03-09 16:20:08');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('106', '107', '8999.00', NULL, '11999.00', '0', '2026-03-09 16:20:08');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('107', '108', '6499.00', NULL, '7999.00', '0', '2026-03-09 16:20:08');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('108', '109', '15999.00', NULL, '18999.00', '0', '2026-03-09 16:20:08');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('109', '110', '3899.00', NULL, '5499.00', '0', '2026-03-09 16:35:29');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('110', '111', '2499.00', NULL, '3999.00', '0', '2026-03-09 16:35:29');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('111', '112', '5999.00', NULL, '7999.00', '0', '2026-03-09 16:35:29');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('112', '113', '12499.00', NULL, '14999.00', '0', '2026-03-09 16:35:29');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('113', '114', '5499.00', NULL, '6999.00', '0', '2026-03-09 16:35:29');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('114', '115', '6899.00', NULL, '8499.00', '0', '2026-03-09 16:35:29');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('115', '116', '1199.00', NULL, '1899.00', '0', '2026-03-09 16:35:29');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('116', '117', '899.00', NULL, '1299.00', '0', '2026-03-09 16:35:29');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('117', '118', '1499.00', NULL, '2299.00', '0', '2026-03-09 16:40:16');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('118', '119', '1899.00', NULL, '2499.00', '0', '2026-03-09 16:40:16');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('119', '120', '499.00', NULL, '799.00', '0', '2026-03-09 16:40:16');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('120', '121', '1299.00', NULL, '1899.00', '0', '2026-03-09 16:40:16');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('121', '122', '5999.00', '5399.10', '7798.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('122', '123', '9999.00', '8999.10', '12998.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('123', '124', '7999.00', '7199.10', '10398.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('124', '125', '6499.00', '5849.10', '8448.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('125', '126', '8999.00', '8099.10', '11698.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('126', '127', '6999.00', '6299.10', '9098.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('127', '128', '5499.00', '4949.10', '7148.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('128', '129', '8499.00', '7649.10', '11048.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('129', '130', '4999.00', '4499.10', '6498.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('130', '131', '5999.00', '5399.10', '7798.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('131', '132', '18999.00', '17099.10', '24698.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('132', '133', '7499.00', '6749.10', '9748.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('133', '134', '12999.00', '11699.10', '16898.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('134', '135', '10999.00', '9899.10', '14298.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('135', '136', '13999.00', '12599.10', '18198.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('136', '137', '8999.00', '8099.10', '11698.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('137', '138', '10999.00', '9899.10', '14298.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('138', '139', '6499.00', '5849.10', '8448.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('139', '140', '7499.00', '6749.10', '9748.70', '0', '2026-03-09 21:13:38');
INSERT INTO `product_prices` (`id`, `product_id`, `price`, `min_price`, `max_price`, `smart_pricing_status`, `created_at`) VALUES ('140', '141', '7999.00', '7199.10', '10398.70', '0', '2026-03-09 21:13:38');

--- TABLE: product_reviews ---
CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_review` (`product_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_base` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 0

--- TABLE: product_sizes ---
CREATE TABLE `product_sizes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `size_value` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_sizes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_base` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=595 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 583
-- (Data too large to dump inline, 583 rows)

--- TABLE: product_skus ---
CREATE TABLE `product_skus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_skus_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_base` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 106
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('1', '1', 'NK-AMP-001', '2026-02-03 23:34:33');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('2', '2', 'AD-YZY-350', '2026-02-03 23:34:33');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('3', '3', 'TM-P6I-001', '2026-02-03 23:34:33');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('4', '4', 'JD-AJ1-001', '2026-02-03 23:34:33');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('5', '5', 'PM-RSX-001', '2026-02-03 23:34:33');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('6', '15', 'ADIDAS-UB22-WHT-9', '2026-02-04 00:14:01');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('7', '16', 'SM-734', '2026-02-04 00:17:51');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('8', '17', '100074', '2026-02-04 00:22:25');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('9', '18', 'AS-GEL-2404', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('10', '19', 'AS-GEL-1825', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('11', '20', 'BA-TRI-5159', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('12', '21', 'BA-SPE-2720', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('13', '22', 'BA-CIT-4415', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('14', '23', 'BA-COM-2904', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('15', '24', 'BI-ARI-2351', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('16', '25', 'BI-BOS-8215', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('17', '26', 'BR-GHO-1513', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('18', '27', 'BR-ADR-2991', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('19', '28', 'CL-DES-6546', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('20', '29', 'CL-WAL-1773', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('21', '30', 'CO-CHU-9103', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('22', '31', 'CO-CHU-5719', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('23', '32', 'CR-CLA-3040', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('24', '33', 'CR-LIT-8555', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('25', '34', 'DR-146-3313', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('26', '35', 'DR-146-5982', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('27', '36', 'FI-DIS-8457', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('28', '37', 'FI-RAY-5968', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('29', '38', 'GU-ACE-7124', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('30', '39', 'GU-HOR-6027', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('31', '40', 'HO-BON-1833', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('32', '41', 'HO-SPE-6402', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('33', '42', 'JO-AIR-3139', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('34', '43', 'JO-AIR-5923', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('35', '44', 'LI-AHA-9960', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('36', '45', 'LI-FOR-5635', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('37', '46', 'ME-MET-9240', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('38', '47', 'ME-CAS-3606', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('39', '48', 'MI-WAV-4223', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('40', '49', 'MI-WAV-3618', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('41', '50', 'NE-FRE-1537', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('42', '51', 'NE-990-3722', '2026-02-04 00:36:27');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('43', '52', '311140', '2026-02-04 08:03:43');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('44', '53', 'FD2722', '2026-02-04 08:10:10');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('45', '54', '1011B974', '2026-02-04 08:13:50');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('46', '55', '1RM02752G-023', '2026-02-04 09:04:14');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('48', '57', '553558-XXX', '2026-02-04 09:17:37');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('49', '58', '311140_04', '2026-02-09 10:05:39');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('50', '65', 'NIKE-INV-3-WHT', '2026-02-17 21:16:52');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('51', '66', 'ADI-FORUM-LO-BLU', '2026-02-17 21:16:52');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('52', '67', 'NB-550-GRY', '2026-02-17 21:16:52');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('53', '68', 'GX3061', '2026-02-17 21:25:44');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('54', '69', 'RSO3782', '2026-02-17 21:36:26');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('55', '70', 'NIKE-AF1-WHT', '2026-02-17 21:38:11');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('56', '71', 'PUMA-SUE-BLK', '2026-02-17 21:38:11');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('57', '72', 'RBK-CLBC-85', '2026-02-17 21:38:11');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('58', '73', 'VANS-OS-BLK', '2026-02-17 21:38:11');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('59', '74', 'CNV-CH70-PAR', '2026-02-17 21:38:11');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('60', '75', 'SKCH-RAZ-4', '2026-02-17 21:38:11');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('61', '76', 'ASICS-NIMB-25', '2026-02-17 21:38:11');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('62', '77', 'UA-CUR-10', '2026-02-17 21:38:11');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('63', '78', 'CROCS-CL-BLK', '2026-02-17 21:38:11');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('64', '79', 'JOR-AJ1-MID-RED', '2026-02-17 21:38:11');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('65', '80', 'NK-AM270-BLK', '2026-02-17 23:16:09');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('66', '81', 'AD-UB5-WHT', '2026-02-17 23:16:09');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('67', '82', 'PM-RSX-RED', '2026-02-17 23:16:09');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('68', '83', 'SX-ATH-NVY', '2026-02-17 23:16:09');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('69', '84', 'AS-GK29-BLU', '2026-02-17 23:16:09');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('70', '85', 'CL-DB-TAN', '2026-02-17 23:16:09');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('71', '86', 'DM-1460-BLK', '2026-02-17 23:16:09');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('72', '87', 'SD0734GWHGY', '2026-02-18 10:50:23');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('73', '88', 'NK-PG41-M-BLK', '2026-02-18 12:30:22');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('74', '89', 'NB-FFX-880V14-OLV-M', '2026-02-18 13:34:04');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('75', '90', 'RT-OXF-FRM-001', '2026-02-19 09:06:50');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('76', '91', 'RT-DRB-BRG-001', '2026-02-19 09:06:50');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('77', '92', 'RT-SPT-RN-001', '2026-02-19 09:06:50');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('78', '93', 'RT-CAS-SNK-001', '2026-02-19 09:06:50');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('79', '94', 'RT_SH_RSO4464', '2026-02-19 09:11:23');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('80', '95', '1khal', '2026-02-19 16:10:21');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('81', '96', 'NIKE-INV-3-WHT-S8', '2026-02-22 22:51:11');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('82', '97', 'CNV-CH70-PAR-S8', '2026-03-05 12:15:17');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('83', '98', 'AD-UB5-WHT-S8', '2026-03-05 12:24:07');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('84', '99', '-S8', '2026-03-05 12:24:22');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('85', '100', 'NK-PG41-M-BLK-S8', '2026-03-05 13:45:04');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('86', '101', 'UA-CUR-10-S8', '2026-03-05 16:41:10');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('87', '102', 'NIKE-AF1-WHT-S8', '2026-03-05 16:41:15');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('88', '122', 'REE-CLA-8057', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('89', '123', 'REE-NAN-7133', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('90', '124', 'REE-FLO-2562', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('91', '125', 'REE-CLU-5941', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('92', '126', 'REE-ZIG-4273', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('93', '127', 'FIL-DIS-4171', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('94', '128', 'FIL-RAY-5953', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('95', '129', 'FIL-GRA-7830', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('96', '130', 'FIL-ORI-1948', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('97', '131', 'FIL-MIN-5946', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('98', '132', 'NEW-990-9730', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('99', '133', 'NEW-574-2326', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('100', '134', 'NEW-FRE-9071', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('101', '135', 'NEW-FUE-7798', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('102', '136', 'NEW-200-2874', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('103', '137', 'PUM-RS--9460', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('104', '138', 'PUM-VEL-2877', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('105', '139', 'PUM-SUE-8846', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('106', '140', 'PUM-CAL-4955', '2026-03-09 21:13:38');
INSERT INTO `product_skus` (`id`, `product_id`, `sku`, `created_at`) VALUES ('107', '141', 'PUM-MIR-2018', '2026-03-09 21:13:38');

--- TABLE: product_specs ---
CREATE TABLE `product_specs` (
  `product_id` int(11) NOT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `heel_height` varchar(50) DEFAULT NULL,
  `outer_material` varchar(100) DEFAULT NULL,
  `season` varchar(100) DEFAULT NULL,
  `shoe_type` varchar(100) DEFAULT NULL,
  `occasion` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  KEY `brand_id` (`brand_id`),
  CONSTRAINT `product_specs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_base` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_specs_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 139
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('1', '1', 'Unisex', NULL, 'Leather', NULL, NULL, 'Lifestyle');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('2', '2', 'Unisex', NULL, 'Primeknit', NULL, NULL, 'Lifestyle');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('3', NULL, 'Unisex', NULL, 'Nubuck', NULL, NULL, 'Lifestyle');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('4', '4', 'Unisex', NULL, 'Full-Grain Leather', NULL, NULL, 'Lifestyle');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('5', '3', 'Unisex', NULL, 'Flyknit', NULL, NULL, 'Lifestyle');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('6', '8', 'Men', NULL, 'Patent Leather', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('7', '8', 'Men', NULL, 'Full-Grain Leather', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('8', '9', 'Unisex', NULL, 'Canvas', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('9', '13', 'Unisex', NULL, 'Croslite', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('10', '11', 'Women', NULL, 'Mesh', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('11', '10', 'Women', NULL, 'Synthethic Leather', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('12', '12', 'Men', NULL, 'Warp Knit', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('13', '16', 'Men', NULL, 'Mesh', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('14', '14', 'Men', NULL, 'Canvas', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('15', '2', 'Boys', '3MM', 'Textile', 'Summer', 'Boots', '');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('16', '16', 'Boys', '2mm', 'Patent Leather', 'Winter', 'Loafers', '');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('17', '5', 'Boys', '3MM', 'Synthetic', 'Winter', 'Heels', '');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('18', '14', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('19', '14', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('20', '36', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('21', '36', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('22', '8', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('23', '8', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('24', '32', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('25', '32', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('26', '20', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('27', '20', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('28', '31', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('29', '31', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('30', '17', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('31', '17', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('32', '33', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('33', '33', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('34', '29', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('35', '29', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('36', '19', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('37', '19', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('38', '34', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('39', '34', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('40', '22', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('41', '22', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('42', '4', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('43', '4', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('44', '27', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('45', '27', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('46', '28', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('47', '28', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('48', '23', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('49', '23', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('50', '6', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('51', '6', 'Unisex', NULL, NULL, NULL, 'Footwear', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('52', '3', 'Boys', '3MM', 'Rubber', 'Summer', 'Loafers', '');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('53', '1', 'Boys', '3MM', 'Synthetic', 'Summer', 'Heels', '');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('54', '14', 'Boys', '3MM', 'Nylon', 'Summer', 'Sandals', '');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('55', '10', 'Boys', '-3MM', 'Velvet', 'Summer', 'Sneakers', '');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('57', '4', 'Boys', '-3MM', 'Textile', 'Summer', 'Sandals', '');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('58', '3', 'Men', '5 inch', 'Foam', 'Summer', NULL, '');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('59', '1', 'Men', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('60', '2', 'Men', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('61', '5', 'Men', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('62', '3', 'Men', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('63', '3', 'Men', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('64', '17', 'Men', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('65', '1', 'Men', NULL, 'Flyknit', NULL, 'Running', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('66', '2', 'Unisex', NULL, 'Leather', NULL, 'Sneaker', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('67', '6', 'Men', NULL, 'Suede/Leather', NULL, 'Casual/Sneaker', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('68', NULL, 'Men', '3MM', 'Synthetic', 'Summer', NULL, '');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('69', NULL, 'Men', '3MM', 'Nylon', 'Summer', NULL, '');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('70', '1', 'Unisex', NULL, 'Leather', NULL, 'Sneaker', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('71', '3', 'Men', NULL, 'Suede', NULL, 'Sneaker', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('72', '5', 'Men', NULL, 'Leather', NULL, 'Casual', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('73', '7', 'Unisex', NULL, 'Canvas', NULL, 'Skate', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('74', '9', 'Unisex', NULL, 'Canvas', NULL, 'Sneaker', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('75', '11', 'Men', NULL, 'Mesh', NULL, 'Running', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('76', '17', 'Women', NULL, 'Engineered Mesh', NULL, 'Running', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('77', '12', 'Men', NULL, 'UA Warp', NULL, 'Basketball', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('78', '13', 'Unisex', NULL, 'Croslite', NULL, 'Clog', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('79', '4', 'Unisex', NULL, 'Leather', NULL, 'Sneaker', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('80', '1', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('81', '2', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('82', '3', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('83', '16', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('84', '17', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('85', '18', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('86', '19', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('87', NULL, 'Men', '2mm', 'Rubber', 'Summer', NULL, '');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('88', '1', 'Men', '3MM', 'Rubber', 'Summer', NULL, '');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('89', '6', 'Men', '3MM', 'Mesh', 'Summer', NULL, '');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('90', '20', NULL, NULL, 'Genuine Leather', NULL, 'Oxford', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('91', '20', NULL, NULL, 'Full Grain Leather', NULL, 'Derby', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('92', '20', NULL, NULL, 'Mesh & Synthetic', NULL, 'Running', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('93', '20', NULL, NULL, 'Canvas', NULL, 'Lifestyle', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('94', '20', 'Men', '3MM', 'Rubber', 'Winter', NULL, 'Sports');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('95', '12', 'Men', '3MM', 'Velvet', 'Autumn', NULL, 'Outdoor');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('96', '1', 'Men', NULL, 'Flyknit', NULL, 'Running', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('97', '9', 'Unisex', NULL, 'Canvas', NULL, 'Sneaker', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('98', '2', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('99', '3', 'Men', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('100', '1', 'Men', '3MM', 'Rubber', 'Summer', NULL, '');
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('101', '12', 'Men', NULL, 'UA Warp', NULL, 'Basketball', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('102', '1', 'Unisex', NULL, 'Leather', NULL, 'Sneaker', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('104', '14', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('105', '17', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('106', '8', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('107', '18', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('108', '10', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('109', '19', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('110', '20', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('111', '20', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('112', '5', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('113', '5', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('114', '11', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('115', '11', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('116', '16', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('117', '16', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('118', '16', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('119', '16', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('120', '16', NULL, NULL, NULL, NULL, 'Running', NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('121', '16', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('122', '5', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('123', '5', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('124', '5', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('125', '5', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('126', '5', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('127', '10', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('128', '10', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('129', '10', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('130', '10', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('131', '10', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('132', '6', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('133', '6', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('134', '6', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('135', '6', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('136', '6', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('137', '3', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('138', '3', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('139', '3', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('140', '3', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);
INSERT INTO `product_specs` (`product_id`, `brand_id`, `gender`, `heel_height`, `outer_material`, `season`, `shoe_type`, `occasion`) VALUES ('141', '3', 'Unisex', NULL, 'Premium Materials', NULL, NULL, NULL);

--- TABLE: product_stock ---
CREATE TABLE `product_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `temp_hold` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_stock_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_base` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 115
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('1', '1', '100', '0', '2026-02-03 23:34:33');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('2', '2', '100', '0', '2026-02-03 23:34:33');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('3', '3', '100', '0', '2026-02-03 23:34:33');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('4', '4', '100', '0', '2026-02-03 23:34:33');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('5', '5', '100', '0', '2026-02-03 23:34:33');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('6', '6', '50', '0', '2026-02-04 00:06:22');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('7', '7', '50', '0', '2026-02-04 00:06:22');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('8', '8', '50', '0', '2026-02-04 00:06:22');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('9', '9', '50', '0', '2026-02-04 00:06:22');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('10', '10', '50', '0', '2026-02-04 00:06:22');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('11', '11', '50', '0', '2026-02-04 00:06:22');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('12', '12', '50', '0', '2026-02-04 00:06:22');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('13', '13', '50', '0', '2026-02-04 00:06:22');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('14', '14', '50', '0', '2026-02-04 00:06:22');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('15', '15', '20', '0', '2026-02-04 00:14:01');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('16', '16', '20', '0', '2026-02-04 00:17:51');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('17', '17', '15', '0', '2026-02-04 00:22:25');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('18', '18', '125', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('19', '19', '167', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('20', '20', '150', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('21', '21', '81', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('22', '22', '82', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('23', '23', '67', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('24', '24', '195', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('25', '25', '73', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('26', '26', '65', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('27', '27', '104', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('28', '28', '60', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('29', '29', '124', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('30', '30', '178', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('31', '31', '125', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('32', '32', '91', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('33', '33', '80', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('34', '34', '73', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('35', '35', '151', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('36', '36', '60', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('37', '37', '86', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('38', '38', '94', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('39', '39', '80', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('40', '40', '57', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('41', '41', '116', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('42', '42', '175', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('43', '43', '125', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('44', '44', '89', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('45', '45', '135', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('46', '46', '67', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('47', '47', '187', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('48', '48', '72', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('49', '49', '82', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('50', '50', '75', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('51', '51', '144', '0', '2026-02-04 00:36:27');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('52', '52', '15', '0', '2026-02-04 08:03:43');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('53', '53', '15', '0', '2026-02-04 08:10:10');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('54', '54', '20', '0', '2026-02-04 08:13:50');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('55', '55', '-5', '0', '2026-02-04 09:04:14');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('57', '57', '15', '0', '2026-02-04 09:17:37');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('58', '58', '15', '0', '2026-02-09 10:05:39');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('59', '65', '25', '0', '2026-02-17 21:16:52');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('60', '66', '15', '0', '2026-02-17 21:16:52');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('61', '67', '10', '0', '2026-02-17 21:16:52');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('62', '68', '20', '0', '2026-02-17 21:25:44');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('63', '69', '20', '0', '2026-02-17 21:36:26');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('64', '70', '50', '0', '2026-02-17 21:38:11');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('65', '71', '30', '0', '2026-02-17 21:38:11');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('66', '72', '20', '0', '2026-02-17 21:38:11');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('67', '73', '100', '0', '2026-02-17 21:38:11');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('68', '74', '40', '0', '2026-02-17 21:38:11');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('69', '75', '15', '0', '2026-02-17 21:38:11');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('70', '76', '12', '0', '2026-02-17 21:38:11');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('71', '77', '8', '0', '2026-02-17 21:38:11');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('72', '78', '200', '0', '2026-02-17 21:38:11');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('73', '79', '25', '0', '2026-02-17 21:38:11');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('74', '80', '50', '0', '2026-02-17 23:16:09');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('75', '81', '50', '0', '2026-02-17 23:16:09');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('76', '82', '50', '0', '2026-02-17 23:16:09');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('77', '83', '50', '0', '2026-02-17 23:16:09');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('78', '84', '50', '0', '2026-02-17 23:16:09');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('79', '85', '50', '0', '2026-02-17 23:16:09');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('80', '86', '50', '0', '2026-02-17 23:16:09');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('81', '87', '20', '0', '2026-02-18 10:50:23');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('82', '88', '30', '0', '2026-02-18 12:30:22');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('83', '89', '20', '0', '2026-02-18 13:34:04');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('84', '90', '60', '0', '2026-02-19 09:06:50');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('85', '91', '60', '0', '2026-02-19 09:06:50');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('86', '92', '60', '0', '2026-02-19 09:06:50');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('87', '93', '60', '0', '2026-02-19 09:06:50');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('88', '94', '30', '0', '2026-02-19 09:11:23');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('89', '95', '1', '0', '2026-02-19 16:10:21');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('90', '96', '10', '0', '2026-02-22 22:51:11');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('91', '97', '10', '0', '2026-03-05 12:15:17');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('92', '98', '10', '0', '2026-03-05 12:24:08');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('93', '99', '10', '0', '2026-03-05 12:24:22');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('94', '100', '10', '0', '2026-03-05 13:45:04');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('95', '101', '10', '0', '2026-03-05 16:41:10');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('96', '102', '10', '0', '2026-03-05 16:41:15');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('97', '122', '82', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('98', '123', '39', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('99', '124', '65', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('100', '125', '95', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('101', '126', '42', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('102', '127', '57', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('103', '128', '88', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('104', '129', '45', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('105', '130', '86', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('106', '131', '44', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('107', '132', '44', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('108', '133', '45', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('109', '134', '67', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('110', '135', '50', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('111', '136', '38', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('112', '137', '32', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('113', '138', '68', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('114', '139', '65', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('115', '140', '80', '0', '2026-03-09 21:13:38');
INSERT INTO `product_stock` (`id`, `product_id`, `quantity`, `temp_hold`, `created_at`) VALUES ('116', '141', '59', '0', '2026-03-09 21:13:38');

--- TABLE: product_sync_logs ---
CREATE TABLE `product_sync_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `channel` varchar(50) NOT NULL,
  `status` enum('pending','success','error','failed') DEFAULT 'pending',
  `message` text DEFAULT NULL,
  `sync_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `idx_seller_product` (`seller_id`,`product_id`),
  KEY `idx_channel_status` (`channel`,`status`),
  CONSTRAINT `product_sync_logs_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_sync_logs_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product_base` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 0

--- TABLE: seller_marketplaces ---
CREATE TABLE `seller_marketplaces` (
  `seller_id` int(11) NOT NULL,
  `marketplace_id` int(11) NOT NULL,
  `status` enum('connected','disconnected') DEFAULT 'disconnected',
  `last_sync` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`seller_id`,`marketplace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 20
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('1', '1', 'connected', NULL, '2026-02-06 15:29:39');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('1', '2', 'connected', NULL, '2026-02-06 15:29:39');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('1', '6', 'connected', NULL, '2026-02-06 15:29:39');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('2', '1', 'connected', NULL, '2026-02-06 15:29:39');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('2', '2', 'connected', NULL, '2026-02-06 15:29:39');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('2', '3', 'connected', NULL, '2026-02-06 15:29:39');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('2', '4', 'connected', NULL, '2026-02-06 15:29:39');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('2', '5', 'connected', NULL, '2026-02-06 15:29:39');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('2', '6', 'connected', NULL, '2026-02-06 15:29:39');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('3', '2', 'connected', NULL, '2026-02-06 15:29:39');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('3', '3', 'connected', NULL, '2026-02-06 15:29:39');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('3', '4', 'connected', NULL, '2026-02-06 15:29:39');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('3', '5', 'connected', NULL, '2026-02-06 15:29:39');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('4', '1', 'disconnected', NULL, '2026-02-10 08:57:40');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('4', '2', 'connected', '2026-03-06 22:24:13', '2026-03-06 22:24:13');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('4', '4', 'connected', '2026-03-06 22:24:13', '2026-03-06 22:24:13');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('8', '5', 'disconnected', '2026-03-06 11:52:26', '2026-03-06 11:51:33');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('9', '2', 'connected', '2026-03-06 22:24:13', '2026-03-06 22:24:13');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('9', '3', 'connected', '2026-03-06 22:24:13', '2026-03-06 22:24:13');
INSERT INTO `seller_marketplaces` (`seller_id`, `marketplace_id`, `status`, `last_sync`, `created_at`) VALUES ('9', '4', 'connected', '2026-03-06 22:24:13', '2026-03-06 22:24:13');

--- TABLE: sellers ---
CREATE TABLE `sellers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `business_name` varchar(150) DEFAULT NULL,
  `website_url` varchar(500) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 8
INSERT INTO `sellers` (`id`, `name`, `email`, `password`, `business_name`, `website_url`, `city`, `country`, `is_active`, `created_at`, `is_verified`) VALUES ('1', 'Demo Seller', 'seller@walkon.com', '$2y$10$6Rui3PUAgQxOBR033PqW/O01QyLL872EGW36yNZZQojSCuFDd71U.', 'WalkOn Official Store', NULL, NULL, NULL, '1', '2026-02-03 23:34:33', '1');
INSERT INTO `sellers` (`id`, `name`, `email`, `password`, `business_name`, `website_url`, `city`, `country`, `is_active`, `created_at`, `is_verified`) VALUES ('2', 'Admin WalkOn', 'admin@walkon.com', '$2y$10$dIwxjmd8v.B8CUsR0shQG.S41dtZtYF1pz.ISFw2Mm/4cKlAdlwmS', 'WALKON Official', NULL, NULL, NULL, '1', '2026-02-04 00:14:01', '1');
INSERT INTO `sellers` (`id`, `name`, `email`, `password`, `business_name`, `website_url`, `city`, `country`, `is_active`, `created_at`, `is_verified`) VALUES ('3', 'MOSIN  M JOSEPH INT MCA 2023-2028', 'mosinmjoseph2028@mca.ajce.in', 'social_login_or_legacy', 'MOSIN  M JOSEPH INT MCA 2023-2028 Store', NULL, NULL, NULL, '1', '2026-02-04 00:32:12', '0');
INSERT INTO `sellers` (`id`, `name`, `email`, `password`, `business_name`, `website_url`, `city`, `country`, `is_active`, `created_at`, `is_verified`) VALUES ('4', 'alen sony', 'alensony@gmail.com', '$2y$10$VHlgy.elVYceEeldzgbUD.9QTzGSDcQsRotQcAUfvLPm8hkVoQdEK', 'My Store', NULL, NULL, NULL, '1', '2026-02-09 10:05:39', '0');
INSERT INTO `sellers` (`id`, `name`, `email`, `password`, `business_name`, `website_url`, `city`, `country`, `is_active`, `created_at`, `is_verified`) VALUES ('5', 'josses thomas', 'jossesthomas@gmail.com', '$2y$10$60JPv3Xm952PvBjTsTTk8.Ycq2Z4JEEZVjwTHHV7HjkHjKgclcld6', 'My Store', NULL, NULL, NULL, '1', '2026-02-17 21:25:44', '0');
INSERT INTO `sellers` (`id`, `name`, `email`, `password`, `business_name`, `website_url`, `city`, `country`, `is_active`, `created_at`, `is_verified`) VALUES ('7', 'Store Owner', 'owner@walkon.com', '$2y$10$abcdefghijklmnopqrstuv', 'My Store', NULL, NULL, NULL, '1', '2026-02-18 10:26:05', '0');
INSERT INTO `sellers` (`id`, `name`, `email`, `password`, `business_name`, `website_url`, `city`, `country`, `is_active`, `created_at`, `is_verified`) VALUES ('8', 'John Entrepreneur', 'entrepreneur@walkon.com', '$2y$10$vtg.Cp9hoghuk6K.8N6IGuG9wtzeivUfDedXLuSa2kjLmBjSbMAT6', 'My Store', NULL, NULL, NULL, '1', '2026-02-18 13:34:04', '0');
INSERT INTO `sellers` (`id`, `name`, `email`, `password`, `business_name`, `website_url`, `city`, `country`, `is_active`, `created_at`, `is_verified`) VALUES ('9', 'ashin t', 'a1@gmail.com', '$2y$10$5sohM7ICKxj6OdH2gOfe0.3/erOQv1WU4rmoy5Cj1El9O58tPWrcu', 'My Store', NULL, NULL, NULL, '1', '2026-02-19 16:10:21', '0');

--- TABLE: site_settings ---
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 0

--- TABLE: size_guides ---
CREATE TABLE `size_guides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `uk_size` varchar(10) NOT NULL,
  `us_size` varchar(10) NOT NULL,
  `eu_size` varchar(10) NOT NULL,
  `cm_length` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_brand` (`brand_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 7
INSERT INTO `size_guides` (`id`, `category`, `brand_id`, `uk_size`, `us_size`, `eu_size`, `cm_length`, `created_at`) VALUES ('1', 'Men', NULL, '6', '7', '40', '25.00', '2026-02-18 13:41:43');
INSERT INTO `size_guides` (`id`, `category`, `brand_id`, `uk_size`, `us_size`, `eu_size`, `cm_length`, `created_at`) VALUES ('2', 'Men', NULL, '7', '8', '41', '25.40', '2026-02-18 13:41:43');
INSERT INTO `size_guides` (`id`, `category`, `brand_id`, `uk_size`, `us_size`, `eu_size`, `cm_length`, `created_at`) VALUES ('3', 'Men', NULL, '8', '9', '42.5', '26.20', '2026-02-18 13:41:43');
INSERT INTO `size_guides` (`id`, `category`, `brand_id`, `uk_size`, `us_size`, `eu_size`, `cm_length`, `created_at`) VALUES ('4', 'Men', NULL, '9', '10', '44', '27.10', '2026-02-18 13:41:43');
INSERT INTO `size_guides` (`id`, `category`, `brand_id`, `uk_size`, `us_size`, `eu_size`, `cm_length`, `created_at`) VALUES ('5', 'Men', NULL, '10', '11', '45', '27.90', '2026-02-18 13:41:43');
INSERT INTO `size_guides` (`id`, `category`, `brand_id`, `uk_size`, `us_size`, `eu_size`, `cm_length`, `created_at`) VALUES ('6', 'Men', NULL, '11', '12', '46', '28.80', '2026-02-18 13:41:43');
INSERT INTO `size_guides` (`id`, `category`, `brand_id`, `uk_size`, `us_size`, `eu_size`, `cm_length`, `created_at`) VALUES ('7', 'Men', NULL, '12', '13', '47', '29.60', '2026-02-18 13:41:43');

--- TABLE: sizes_ref ---
CREATE TABLE `sizes_ref` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `size_value` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `size_value` (`size_value`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 20
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('14', 'EU 39');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('15', 'EU 40');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('16', 'EU 41');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('17', 'EU 42');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('18', 'EU 43');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('19', 'EU 44');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('20', 'EU 45');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('5', 'UK 10');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('6', 'UK 11');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('7', 'UK 12');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('1', 'UK 6');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('2', 'UK 7');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('3', 'UK 8');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('4', 'UK 9');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('11', 'US 10');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('12', 'US 11');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('13', 'US 12');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('8', 'US 7');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('9', 'US 8');
INSERT INTO `sizes_ref` (`id`, `size_value`) VALUES ('10', 'US 9');

--- TABLE: smart_pricing_log ---
CREATE TABLE `smart_pricing_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `new_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `idx_seller_date` (`seller_id`,`created_at`),
  CONSTRAINT `smart_pricing_log_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `smart_pricing_log_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product_base` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 0

--- TABLE: staff_permissions ---
CREATE TABLE `staff_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `is_granted` tinyint(1) DEFAULT 1,
  `granted_by` int(11) DEFAULT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_permission` (`user_id`,`permission_key`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_permission_key` (`permission_key`),
  KEY `granted_by` (`granted_by`),
  CONSTRAINT `staff_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_permissions_ibfk_2` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 0

--- TABLE: store_settings ---
CREATE TABLE `store_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) DEFAULT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_category` (`category`),
  KEY `idx_setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  KEY `seller_id` (`seller_id`),
  CONSTRAINT `store_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 9
INSERT INTO `store_settings` (`id`, `seller_id`, `setting_key`, `setting_value`, `category`, `updated_by`, `updated_at`) VALUES ('1', NULL, 'store_name', 'WALKON Footwear Store', 'business', NULL, '2026-02-05 21:28:34');
INSERT INTO `store_settings` (`id`, `seller_id`, `setting_key`, `setting_value`, `category`, `updated_by`, `updated_at`) VALUES ('2', NULL, 'store_email', 'contact@walkon.com', 'business', NULL, '2026-02-05 21:28:34');
INSERT INTO `store_settings` (`id`, `seller_id`, `setting_key`, `setting_value`, `category`, `updated_by`, `updated_at`) VALUES ('3', NULL, 'store_phone', '+91 1234567890', 'business', NULL, '2026-02-05 21:28:34');
INSERT INTO `store_settings` (`id`, `seller_id`, `setting_key`, `setting_value`, `category`, `updated_by`, `updated_at`) VALUES ('4', NULL, 'tax_rate', '18', 'financial', NULL, '2026-02-05 21:28:34');
INSERT INTO `store_settings` (`id`, `seller_id`, `setting_key`, `setting_value`, `category`, `updated_by`, `updated_at`) VALUES ('5', NULL, 'currency', 'INR', 'financial', NULL, '2026-02-05 21:28:34');
INSERT INTO `store_settings` (`id`, `seller_id`, `setting_key`, `setting_value`, `category`, `updated_by`, `updated_at`) VALUES ('6', NULL, 'currency_symbol', 'Γé╣', 'financial', NULL, '2026-02-05 21:28:34');
INSERT INTO `store_settings` (`id`, `seller_id`, `setting_key`, `setting_value`, `category`, `updated_by`, `updated_at`) VALUES ('7', NULL, 'return_window_days', '30', 'policy', NULL, '2026-02-05 21:28:34');
INSERT INTO `store_settings` (`id`, `seller_id`, `setting_key`, `setting_value`, `category`, `updated_by`, `updated_at`) VALUES ('8', NULL, 'brand_color_primary', '#10b981', 'branding', NULL, '2026-02-05 21:28:34');
INSERT INTO `store_settings` (`id`, `seller_id`, `setting_key`, `setting_value`, `category`, `updated_by`, `updated_at`) VALUES ('9', NULL, 'brand_color_secondary', '#059669', 'branding', NULL, '2026-02-05 21:28:34');

--- TABLE: sub_categories ---
CREATE TABLE `sub_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cat_sub` (`category_id`,`name`),
  CONSTRAINT `sub_categories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 39
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('1', '1', 'High-Top', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('2', '1', 'Low-Top', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('3', '1', 'Slip-On', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('4', '1', 'Luxury', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('5', '2', 'Chelsea', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('6', '2', 'Combat', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('7', '2', 'Hiking', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('8', '2', 'Chukkas', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('9', '4', 'Road Running', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('10', '4', 'Trail Running', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('11', '4', 'Performance', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('16', '5', 'Oxfords', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('17', '5', 'Derbys', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('18', '5', 'Loafers', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('19', '5', 'Brogues', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('20', '6', 'Espadrilles', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('21', '6', 'Boat Shoes', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('22', '6', 'Mules', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('23', '6', 'Sandals', '2026-02-03 23:47:02');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('51', '1', 'Canvas', '2026-02-03 23:58:31');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('56', '2', 'Ankle Boots', '2026-02-03 23:58:31');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('60', '4', 'Cushioned', '2026-02-03 23:58:31');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('70', '5', 'Monk Straps', '2026-02-03 23:58:31');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('75', '6', 'Slides', '2026-02-03 23:58:31');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('76', '22', 'Running', '2026-02-19 09:02:01');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('77', '22', 'Training', '2026-02-19 09:02:01');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('78', '22', 'Football', '2026-02-19 09:02:01');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('79', '22', 'Basketball', '2026-02-19 09:02:01');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('80', '22', 'Tennis', '2026-02-19 09:02:01');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('81', '22', 'Cricket', '2026-02-19 09:02:01');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('82', '21', 'Sneakers', '2026-02-25 09:55:26');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('83', '21', 'Formal Shoes', '2026-02-25 09:55:26');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('84', '21', 'Casual Shoes', '2026-02-25 09:55:26');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('85', '21', 'Sports Shoes', '2026-02-25 09:55:26');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('86', '21', 'Sandals & Floaters', '2026-02-25 09:55:26');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('87', '21', 'Boots', '2026-02-25 09:55:26');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('88', '21', 'Loafers', '2026-02-25 09:55:26');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('89', '21', 'Slides & Flip Flops', '2026-02-25 09:55:26');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`) VALUES ('90', '21', 'Ethnic Shoes', '2026-02-25 09:55:26');

--- TABLE: user_activity_logs ---
CREATE TABLE `user_activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `user_activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=300 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 299
-- (Data too large to dump inline, 299 rows)

--- TABLE: user_addresses ---
CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `address_type` enum('shipping','billing') DEFAULT 'shipping',
  `street_address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 1
INSERT INTO `user_addresses` (`id`, `user_id`, `address_type`, `street_address`, `city`, `state`, `zip_code`, `country`, `is_default`, `created_at`) VALUES ('1', '15', 'shipping', 'MEPPURATH(H)', 'KOTTAYAM', 'Kerala', '686541', 'India', '1', '2026-02-19 09:56:52');

--- TABLE: users ---
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `verification_token` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role` enum('admin','store_owner','entrepreneur','customer') NOT NULL DEFAULT 'customer',
  `seller_id` int(11) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `fk_user_seller` (`seller_id`),
  CONSTRAINT `fk_user_seller` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 37
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('1', 'Super', 'Admin', 'admin@walkon.com', '$2y$10$/2dkDLMjT21hFu7Cvy9oL.yhVgn8TUJNeuTfq46Ees91wrjyXGeLu', NULL, '1', '1', '2026-03-09 15:45:26', NULL, NULL, '2026-02-03 23:34:33', '2026-03-09 15:45:26', 'admin', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('2', 'MOSIN  M JOSEPH', 'INT MCA 2023-2028', 'mosinmjoseph2028@mca.ajce.in', '$2y$10$4ouwJCmFv6TBTPLXSVhmVusPxHWsSbXssp4v/F8G6bIX20JKl.7EC', NULL, '0', '1', '2026-02-17 22:23:34', NULL, NULL, '2026-02-03 23:43:19', '2026-02-17 22:23:34', 'entrepreneur', '3', NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('3', 'Store', 'Owner', 'owner@walkon.com', '$2y$10$B44J7y2zHE8QbCy/COCWROK7S0WWi3.GDWMb/PIu6uMm3YEWxfY6y', NULL, '1', '1', '2026-03-09 15:51:44', NULL, NULL, '2026-02-04 10:10:51', '2026-03-09 15:51:44', 'store_owner', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('4', 'Inventory', 'Manager', 'inventory@walkon.com', '$2y$10$4dWfLLcw5Uhm4VjMbE4Xf.j47/DvnnLcbYNFCBTFou4N30QAugiVC', NULL, '1', '1', NULL, NULL, NULL, '2026-02-04 10:10:51', '2026-02-11 13:57:29', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('5', 'Support', 'Staff', 'staff@walkon.com', '$2y$10$DDA3iWKCqleXyDVXLKfTJumt2Wp0zGj9VQAtESW6UALut16EPhb7a', NULL, '1', '1', '2026-02-05 21:47:11', NULL, NULL, '2026-02-04 10:10:51', '2026-02-11 13:57:29', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('6', 'Arun', 'Antony', 'arun@gmail.com', '$2y$10$2viqKbIl4.VjpUeS0JJKIuFrOVEBsNA2BY551nLXZPpMlg1EuQPky', NULL, '0', '1', '2026-02-06 15:53:51', NULL, NULL, '2026-02-06 12:38:06', '2026-02-06 15:53:51', 'entrepreneur', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('7', 'Mosin', 'Joseph', 'mosinmjoseph2027@mca.ajce.in', '$2y$10$7kGaN9oa9Lb36S/CVzemCuiE3VGrCAOtBiinVgby0.gh1TCYKwS86', NULL, '1', '1', '2026-02-18 15:18:12', NULL, NULL, '2026-02-06 13:24:17', '2026-02-18 15:18:12', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('8', 'Arun', 'Antony', 'arunantonyvarhese2028@mca.ajce.in', '$2y$10$5q6lwBiQCfYklai.V92Vmu7uByIo6h.3Q1OjZFU2.g8Bpzvi8FdeO', NULL, '1', '1', NULL, NULL, NULL, '2026-02-06 13:42:19', '2026-02-06 13:42:19', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('9', 'Albin', 'Thomas', 'albinthomas 2028@mca.ajce.in', '$2y$10$5q6lwBiQCfYklai.V92Vmu7uByIo6h.3Q1OjZFU2.g8Bpzvi8FdeO', NULL, '1', '1', NULL, NULL, NULL, '2026-02-06 13:42:19', '2026-02-06 13:42:19', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('10', 'NNN', 'VVV', 'kghgdtry@gmail.com', '$2y$10$PHZnhW/pGeXBkyPsWD/cHO/jFMirxh8X4YPmTYsjEBQ/0k9mi7obS', NULL, '0', '1', '2026-02-06 13:51:40', NULL, NULL, '2026-02-06 13:51:21', '2026-02-06 13:51:40', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('11', 'sobin', 'varghese', 'sobinvarghese@gmail.com', '$2y$10$L7HOtmtBoI8LmLn6N5AkYeb.ZCqubo.NQKbpdgnWqDXSO/RK0cV6.', NULL, '0', '1', '2026-02-10 14:09:38', NULL, NULL, '2026-02-06 14:09:34', '2026-02-18 09:07:15', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('12', 'jithin', 'manoj', 'jithinmanoj@gmail.com', '$2y$10$.GkU0Z47Vm95lICg6ICcWOQgB9QBG3vV2SaBIL6Ml8OEpaHLmt7qq', NULL, '0', '1', '2026-02-18 09:56:20', NULL, NULL, '2026-02-06 14:12:10', '2026-02-18 09:56:20', 'customer', NULL, '8590225629', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('13', 'alen', 'sony', 'alensony@gmail.com', '$2y$10$VHlgy.elVYceEeldzgbUD.9QTzGSDcQsRotQcAUfvLPm8hkVoQdEK', NULL, '0', '1', '2026-02-17 21:40:14', NULL, NULL, '2026-02-06 15:01:50', '2026-02-17 21:40:14', 'entrepreneur', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('14', 'yyy', 'ii', 'arunantonyvarghese2028@mca.ajce.in', '$2y$10$QzJFnx2nlocyVePpEI9GJuAZeg5JDeKq7g8KCzndlfSOv0/I50onq', NULL, '0', '1', '2026-02-06 16:22:12', NULL, NULL, '2026-02-06 15:55:00', '2026-02-06 16:22:12', 'customer', NULL, '9876547890', NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('15', 'John', 'Entrepreneur', 'entrepreneur@walkon.com', '$2y$10$vtg.Cp9hoghuk6K.8N6IGuG9wtzeivUfDedXLuSa2kjLmBjSbMAT6', NULL, '1', '1', '2026-03-10 11:42:29', NULL, NULL, '2026-02-08 19:41:12', '2026-03-10 11:42:29', 'entrepreneur', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('16', 'Jane', 'Customer', 'customer@walkon.com', '$2y$10$B0/wzBtxh0uUPv.RazlG6upLfDZueVotUE0Qf3F/nvGrEyloW76Xu', NULL, '1', '1', '2026-03-10 11:54:21', NULL, NULL, '2026-02-08 19:41:12', '2026-03-10 11:54:21', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('17', 'Michael', 'Smith', 'michael.smith@example.com', '$2y$10$Bj5MQOXhKnjvxWOxvRSRke9HN6kzafuRwpsRMUjX5VFq2YBP.MeTW', NULL, '1', '1', NULL, NULL, NULL, '2026-02-08 19:41:12', '2026-02-08 19:41:12', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('18', 'Sarah', 'Johnson', 'sarah.johnson@example.com', '$2y$10$9T6I/k1Q4b8OzAMz6FdE3.vReUu4ui7mbf4T/COZAAUAGMrkrTIhm', NULL, '1', '1', NULL, NULL, NULL, '2026-02-08 19:41:12', '2026-02-08 19:41:12', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('19', 'MOSIN', '2023-2028', 'mmm@gmail.com', '$2y$10$yIcJzhRp1u1Ftpfmp3F.HeYLP.DKzJHi6xjyvNktqlgFmdYWIwkvi', NULL, '0', '1', '2026-02-09 15:17:59', NULL, NULL, '2026-02-09 15:17:44', '2026-02-18 09:07:15', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('20', 'john', 'kuttan', 'johnkuttan@gmail.com', '$2y$10$NifNgYEsO5VwnmWoHxTRkearQC3l/siwDqD6Io976EQIPihoW7oz6', NULL, '0', '1', '2026-02-10 14:58:30', NULL, NULL, '2026-02-10 14:58:14', '2026-02-18 09:07:15', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('21', 'appu', 'kuttan', 'appukuttan@gmail.com', '$2y$10$eRZmP2Ldczpl2p3GChqxKu0h7KoZiSefc9CAX/6qGDbOGLTqfDcYO', NULL, '0', '1', '2026-02-10 15:35:50', NULL, NULL, '2026-02-10 15:35:20', '2026-02-18 09:07:15', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('22', 'm', 'q', 'n@c.in', '$2y$10$Bem4IQY/bY0ZDyTX9IoSe.uaGWQTN/pVAmtaXIj1mai9C0nQV073m', NULL, '1', '1', NULL, NULL, NULL, '2026-02-10 15:54:32', '2026-02-11 13:57:29', 'customer', '3', NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('23', 'josin', 'joseph', 'josinjoseph@gmail.com', '$2y$10$foO/PljDddrbNOmT9R63SOkn6RuVmpyBk2NJhgEtAVLo.1.uPeQQi', NULL, '0', '1', '2026-02-10 16:05:14', NULL, NULL, '2026-02-10 16:04:56', '2026-02-18 09:07:15', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('24', 'josin', 'm joseph', 'josinmjoseph@gmail.com', '$2y$10$UfX3oYXJkWo1p08KPkEGU.irwPcSyHEC0lQUsR4/I1K9Xyl8UdbNy', NULL, '1', '1', NULL, NULL, NULL, '2026-02-15 21:52:02', '2026-02-18 09:07:15', 'customer', '2', NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('25', 'joel', 'thomas', 'joelthomas@gmail.com', '$2y$10$RVLSba2H.dDyMTTZbuece.Gnc/37mu.we39tMB/jq650GzQRwPFcq', NULL, '0', '1', '2026-02-17 22:02:43', NULL, NULL, '2026-02-17 20:21:35', '2026-02-17 22:02:43', 'entrepreneur', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('26', 'josses', 'thomas', 'jossesthomas@gmail.com', '$2y$10$60JPv3Xm952PvBjTsTTk8.Ycq2Z4JEEZVjwTHHV7HjkHjKgclcld6', NULL, '0', '1', '2026-02-18 08:57:15', NULL, NULL, '2026-02-17 21:21:24', '2026-02-18 09:07:15', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('27', 'sujo', 'thomas', 'sujothomas@gmail.com', '$2y$10$iFYvdmNJa6T4.R6OyHTUu.0rvaxQpWdWD3pY3fVdbnGHhns0j0uDS', NULL, '0', '1', '2026-02-17 21:45:38', NULL, NULL, '2026-02-17 21:45:18', '2026-02-17 21:45:38', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('28', 'MOSIN', 'joseph', 'mosinmjoseph@gmail.com', '$2y$10$eOhNu13LmT/bSQ0K9AzhWeAZS4ZBE7E8Q4dlqPlsxa5k6fjE0KA3O', NULL, '0', '1', '2026-02-18 09:41:35', NULL, NULL, '2026-02-17 22:21:13', '2026-02-18 09:41:35', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('29', 'joseph', 'joseph', 'josephjoseph@gmail.com', '$2y$10$IBDfAofVXRqLYgh5Sfjnc.gUhguOJPalysVBcrwlSX5nSInDg/bMa', NULL, '0', '1', '2026-02-17 22:25:06', NULL, NULL, '2026-02-17 22:24:55', '2026-02-17 22:25:06', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('30', 'christy', 'sojan', 'christysojan@gmail.com', '$2y$10$qr4ebcx1oB8iz5MiQoYQfuh5O3PlO/aYy9qB/.rN4UORChZgSSsv6', NULL, '0', '1', '2026-02-17 23:12:11', NULL, NULL, '2026-02-17 23:11:58', '2026-02-18 09:07:15', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('31', 'Arun', 'Antony', 'arunantony@gmail.com', '$2y$10$.ZGAmaGX005HxK8Qj5eHEuHj9.l70uaIJ.HwcOD9rXs0ve1mMPXMC', NULL, '0', '1', NULL, NULL, NULL, '2026-02-18 08:58:21', '2026-02-18 08:58:21', 'entrepreneur', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('32', 'MOSIN', 'mjoseph', 'mosinmjoseph2028@gmail.com', '$2y$10$GfdVOkLZwJof5qvvN4mKPexdSViplpW/7yta3NmleAAPu6i2KhbA6', NULL, '0', '1', NULL, NULL, NULL, '2026-02-18 09:31:14', '2026-02-18 09:31:14', 'entrepreneur', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('33', 'albin', 'thomas', 'albinthomas@gmail.com', '$2y$10$8AsIQMS3exxJrIRUzZ2kOOB7ATBBEl2Iugu6Nt3H0.IDu1GQ0bOmG', NULL, '0', '1', '2026-02-18 09:35:21', NULL, NULL, '2026-02-18 09:35:03', '2026-02-18 09:35:21', '', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('34', 'ashin', 'tom', 'a@gmail.com', '$2y$10$f9RRrmiDglK/4jpn4z0r8ecsYO7yCN0.Ti8eqxHlUexUrxRDzKaUC', NULL, '0', '1', '2026-02-19 16:11:18', NULL, NULL, '2026-02-19 16:04:49', '2026-02-19 16:11:18', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('35', 'ashin', 't', 'a1@gmail.com', '$2y$10$5sohM7ICKxj6OdH2gOfe0.3/erOQv1WU4rmoy5Cj1El9O58tPWrcu', NULL, '0', '1', '2026-02-19 16:09:11', NULL, NULL, '2026-02-19 16:08:57', '2026-02-19 16:09:11', 'entrepreneur', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('36', 'Jane', 'Doe', 'jane@walkon.com', 'jane123', NULL, '0', '1', NULL, NULL, NULL, '2026-02-20 11:31:38', '2026-02-20 11:31:38', 'customer', NULL, NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `verification_token`, `is_verified`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `role`, `seller_id`, `phone`, `profile_photo`) VALUES ('37', 'Mosin', 'S', 'mosin@gmail.com', '$2y$10$4LrR1eyE9HYN8DT7jOYKteM8WlbDxtw8M6STUyB7x9cDOwYGWRHQm', NULL, '0', '1', '2026-03-05 11:53:12', NULL, NULL, '2026-03-05 11:51:59', '2026-03-05 11:53:12', 'entrepreneur', NULL, NULL, NULL);

--- TABLE: wallet_transactions ---
CREATE TABLE `wallet_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wallet_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `type` enum('credit','debit','payout') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `commission_deducted` decimal(10,2) DEFAULT 0.00,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `wallet_id` (`wallet_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `wallet_transactions_ibfk_1` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wallet_transactions_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 5
INSERT INTO `wallet_transactions` (`id`, `wallet_id`, `order_id`, `type`, `amount`, `commission_deducted`, `description`, `created_at`) VALUES ('1', '4', '47', 'credit', '3058.20', '339.80', 'Payment for Order #47 (10% Comm. Deducted)', '2026-02-18 11:53:29');
INSERT INTO `wallet_transactions` (`id`, `wallet_id`, `order_id`, `type`, `amount`, `commission_deducted`, `description`, `created_at`) VALUES ('2', '4', '48', 'credit', '3058.20', '339.80', 'Payment for Order #48 (10% Comm. Deducted)', '2026-02-18 15:19:31');
INSERT INTO `wallet_transactions` (`id`, `wallet_id`, `order_id`, `type`, `amount`, `commission_deducted`, `description`, `created_at`) VALUES ('3', '4', '49', 'credit', '3058.20', '339.80', 'Payment for Order #49 (10% Comm. Deducted)', '2026-02-18 21:15:44');
INSERT INTO `wallet_transactions` (`id`, `wallet_id`, `order_id`, `type`, `amount`, `commission_deducted`, `description`, `created_at`) VALUES ('4', '4', '53', 'credit', '3058.20', '339.80', 'Payment for Order #53 (10% Comm. Deducted)', '2026-02-23 10:27:38');
INSERT INTO `wallet_transactions` (`id`, `wallet_id`, `order_id`, `type`, `amount`, `commission_deducted`, `description`, `created_at`) VALUES ('5', '4', '54', 'credit', '3058.20', '339.80', 'Payment for Order #54 (10% Comm. Deducted)', '2026-02-23 10:28:00');

--- TABLE: wallets ---
CREATE TABLE `wallets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) NOT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_id` (`seller_id`),
  CONSTRAINT `wallets_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Row count: 6
INSERT INTO `wallets` (`id`, `seller_id`, `balance`, `last_updated`) VALUES ('1', '2', '0.00', '2026-02-17 20:25:56');
INSERT INTO `wallets` (`id`, `seller_id`, `balance`, `last_updated`) VALUES ('2', '4', '0.00', '2026-02-17 20:25:56');
INSERT INTO `wallets` (`id`, `seller_id`, `balance`, `last_updated`) VALUES ('3', '3', '0.00', '2026-02-17 20:25:56');
INSERT INTO `wallets` (`id`, `seller_id`, `balance`, `last_updated`) VALUES ('4', '1', '15291.00', '2026-02-23 10:28:00');
INSERT INTO `wallets` (`id`, `seller_id`, `balance`, `last_updated`) VALUES ('10', '7', '0.00', '2026-02-18 12:25:17');
INSERT INTO `wallets` (`id`, `seller_id`, `balance`, `last_updated`) VALUES ('11', '8', '0.00', '2026-02-18 21:40:59');

--- TABLE: wishlist ---
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_base` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Row count: 10
INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES ('1', '2', '52', '2026-02-03 22:05:35');
INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES ('2', '1', '54', '2026-02-04 08:49:01');
INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES ('3', '1', '31', '2026-02-04 08:49:04');
INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES ('4', '1', '51', '2026-02-04 08:49:07');
INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES ('7', '34', '95', '2026-02-19 16:11:42');
INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES ('8', '36', '93', '2026-02-20 11:31:38');
INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES ('9', '36', '1', '2026-02-20 11:31:38');
INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES ('10', '16', '136', '2026-03-10 11:54:37');
INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES ('11', '16', '129', '2026-03-10 11:54:54');
INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES ('12', '16', '140', '2026-03-10 11:55:02');

