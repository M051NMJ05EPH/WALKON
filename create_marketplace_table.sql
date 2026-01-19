-- Create marketplace table
CREATE TABLE IF NOT EXISTS marketplaces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    logo_url VARCHAR(255),
    description TEXT,
    website_url VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert popular marketplaces
INSERT INTO marketplaces (name, logo_url, description, website_url, display_order) VALUES
('Amazon', 'https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg', 'World\'s largest online marketplace', 'https://www.amazon.in', 1),
('Flipkart', 'https://upload.wikimedia.org/wikipedia/en/7/7a/Flipkart_logo.svg', 'India\'s leading e-commerce platform', 'https://www.flipkart.com', 2),
('Myntra', 'https://constant.myntassets.com/web/assets/img/myntra_logo.png', 'Fashion and lifestyle marketplace', 'https://www.myntra.com', 3),
('Shopify', 'https://cdn.shopify.com/shopifycloud/brochure/assets/brand-assets/shopify-logo-primary-logo-456baa801ee66a0a435671082365958316831c9960c480451dd0330bcdae304f.svg', 'Build your own online store', 'https://www.shopify.com', 4),
('Meesho', 'https://www.meesho.com/assets/svnz/meeshoLogo.svg', 'Social commerce platform', 'https://www.meesho.com', 5),
('TikTok Shop', 'https://sf16-website-login.neutral.ttwstatic.com/obj/tiktok_web_login_static/tiktok/webapp/main/webapp-desktop/8152caf0c8e8bc67ae0d.png', 'Live shopping experience', 'https://shop.tiktok.com', 6),
('Ajio', 'https://assets.ajio.com/static/img/Ajio-Logo.svg', 'Fashion and lifestyle brand', 'https://www.ajio.com', 7),
('Nykaa', 'https://adn-static1.nykaa.com/media/wysiwyg/2019/lgo.svg', 'Beauty and wellness marketplace', 'https://www.nykaa.com', 8);
