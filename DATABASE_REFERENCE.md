# WalkOn Database - Quick Reference Card

## 🚀 Quick Start Commands

### Start Database
```bash
# Start XAMPP MySQL
# Open XAMPP Control Panel → Start MySQL
```

### Access Database
```bash
# Via phpMyAdmin
http://localhost/phpmyadmin

# Via MySQL Command Line
cd C:\xampp\mysql\bin
mysql -u root -p
USE walkon_shoes;
```

## 📝 Common SQL Queries

### User Management

#### Get all users
```sql
SELECT id, first_name, last_name, email, is_verified, created_at 
FROM users 
ORDER BY created_at DESC;
```

#### Find user by email
```sql
SELECT * FROM users WHERE email = 'user@example.com';
```

#### Count verified users
```sql
SELECT COUNT(*) as verified_users 
FROM users 
WHERE is_verified = 1;
```

### Product Management

#### Get all products
```sql
SELECT p.*, s.business_name 
FROM products p 
JOIN sellers s ON p.seller_id = s.id 
ORDER BY p.created_at DESC;
```

#### Get products by category
```sql
SELECT * FROM products 
WHERE category = 'Sneakers' 
AND status = 'published';
```

#### Get low stock products
```sql
SELECT product_name, sku, quantity 
FROM products 
WHERE quantity < 10 
ORDER BY quantity ASC;
```

#### Search products by name
```sql
SELECT * FROM products 
WHERE product_name LIKE '%Nike%' 
OR description LIKE '%Nike%';
```

### Seller Management

#### Get seller with product count
```sql
SELECT s.*, COUNT(p.id) as product_count 
FROM sellers s 
LEFT JOIN products p ON s.id = p.seller_id 
GROUP BY s.id;
```

#### Get top sellers by product count
```sql
SELECT s.business_name, COUNT(p.id) as total_products 
FROM sellers s 
LEFT JOIN products p ON s.id = p.seller_id 
GROUP BY s.id 
ORDER BY total_products DESC 
LIMIT 10;
```

### Order Management

#### Get recent orders
```sql
SELECT o.*, p.product_name, s.business_name 
FROM orders o 
JOIN products p ON o.product_id = p.id 
JOIN sellers s ON o.seller_id = s.id 
ORDER BY o.order_date DESC 
LIMIT 20;
```

#### Get orders by status
```sql
SELECT * FROM orders 
WHERE order_status = 'pending' 
ORDER BY order_date DESC;
```

#### Calculate total sales
```sql
SELECT SUM(total_amount) as total_sales 
FROM orders 
WHERE order_status = 'completed';
```

#### Sales by channel
```sql
SELECT channel, COUNT(*) as order_count, SUM(total_amount) as revenue 
FROM orders 
GROUP BY channel 
ORDER BY revenue DESC;
```

### Analytics

#### Daily sales report
```sql
SELECT DATE(order_date) as date, 
       COUNT(*) as orders, 
       SUM(total_amount) as revenue 
FROM orders 
WHERE order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
GROUP BY DATE(order_date) 
ORDER BY date DESC;
```

#### Best selling products
```sql
SELECT p.product_name, p.sku, COUNT(o.id) as times_sold, 
       SUM(o.quantity) as units_sold, SUM(o.total_amount) as revenue 
FROM products p 
JOIN orders o ON p.id = o.product_id 
GROUP BY p.id 
ORDER BY revenue DESC 
LIMIT 10;
```

#### Product performance
```sql
SELECT product_name, views, sales, 
       ROUND((sales / views * 100), 2) as conversion_rate 
FROM products 
WHERE views > 0 
ORDER BY conversion_rate DESC;
```

### Sync Logs

#### Recent sync activities
```sql
SELECT * FROM sync_logs 
ORDER BY sync_date DESC 
LIMIT 50;
```

#### Failed syncs
```sql
SELECT * FROM sync_logs 
WHERE status = 'failed' 
ORDER BY sync_date DESC;
```

#### Sync success rate by channel
```sql
SELECT channel, 
       COUNT(*) as total_syncs,
       SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
       ROUND(SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as success_rate
FROM sync_logs 
GROUP BY channel;
```

## 🔧 Maintenance Queries

### Database Cleanup

#### Delete old sync logs (older than 90 days)
```sql
DELETE FROM sync_logs 
WHERE sync_date < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

#### Delete unverified users (older than 30 days)
```sql
DELETE FROM users 
WHERE is_verified = 0 
AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

#### Clear expired reset tokens
```sql
UPDATE users 
SET reset_token = NULL, reset_expires = NULL 
WHERE reset_expires < NOW();
```

### Database Optimization

#### Optimize all tables
```sql
OPTIMIZE TABLE users, sellers, products, orders, sync_logs, notifications;
```

#### Check table sizes
```sql
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'walkon_shoes'
ORDER BY (data_length + index_length) DESC;
```

#### Analyze tables
```sql
ANALYZE TABLE users, sellers, products, orders;
```

## 💾 Backup & Restore

### Backup Database
```bash
# Via Command Line
cd C:\xampp\mysql\bin
mysqldump -u root -p walkon_shoes > walkon_backup.sql

# Via phpMyAdmin
# 1. Select 'walkon_shoes' database
# 2. Click 'Export' tab
# 3. Click 'Go'
```

### Restore Database
```bash
# Via Command Line
cd C:\xampp\mysql\bin
mysql -u root -p walkon_shoes < walkon_backup.sql

# Via phpMyAdmin
# 1. Select 'walkon_shoes' database
# 2. Click 'Import' tab
# 3. Choose file and click 'Go'
```

## 🔍 Debugging Queries

### Check foreign key constraints
```sql
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'walkon_shoes'
AND REFERENCED_TABLE_NAME IS NOT NULL;
```

### Find duplicate SKUs
```sql
SELECT sku, COUNT(*) as count 
FROM products 
GROUP BY sku 
HAVING count > 1;
```

### Find orphaned products (no seller)
```sql
SELECT p.* 
FROM products p 
LEFT JOIN sellers s ON p.seller_id = s.id 
WHERE s.id IS NULL;
```

### Check database encoding
```sql
SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME 
FROM INFORMATION_SCHEMA.SCHEMATA 
WHERE SCHEMA_NAME = 'walkon_shoes';
```

## 📊 Reporting Queries

### Monthly revenue report
```sql
SELECT 
    DATE_FORMAT(order_date, '%Y-%m') as month,
    COUNT(*) as total_orders,
    SUM(total_amount) as revenue
FROM orders
WHERE order_status = 'completed'
GROUP BY DATE_FORMAT(order_date, '%Y-%m')
ORDER BY month DESC;
```

### Seller performance
```sql
SELECT 
    s.business_name,
    COUNT(DISTINCT p.id) as products,
    COUNT(o.id) as orders,
    COALESCE(SUM(o.total_amount), 0) as revenue
FROM sellers s
LEFT JOIN products p ON s.id = p.seller_id
LEFT JOIN orders o ON s.id = o.seller_id
GROUP BY s.id
ORDER BY revenue DESC;
```

### Category distribution
```sql
SELECT 
    category,
    COUNT(*) as product_count,
    AVG(price) as avg_price,
    MIN(price) as min_price,
    MAX(price) as max_price
FROM products
GROUP BY category
ORDER BY product_count DESC;
```

## 🛠️ Useful PHP Queries

### Get user by ID (PHP PDO)
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
```

### Insert new product (PHP PDO)
```php
$sql = "INSERT INTO products (seller_id, product_name, sku, price, quantity) 
        VALUES (?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$seller_id, $name, $sku, $price, $qty]);
$product_id = $pdo->lastInsertId();
```

### Update product price (PHP PDO)
```php
$sql = "UPDATE products SET price = ? WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$new_price, $product_id]);
```

### Delete product (PHP PDO)
```php
$sql = "DELETE FROM products WHERE id = ? AND seller_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$product_id, $seller_id]);
```

## 🔐 Security Best Practices

### Always use prepared statements
```php
// ✅ GOOD - Prevents SQL Injection
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// ❌ BAD - Vulnerable to SQL Injection
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $pdo->query($sql);
```

### Hash passwords
```php
// Creating user
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Verifying password
if (password_verify($input_password, $stored_hash)) {
    // Password correct
}
```

## 📞 Quick Troubleshooting

| Issue | Solution |
|-------|----------|
| Can't connect to database | Check if MySQL is running in XAMPP |
| Access denied | Verify username/password in config.php |
| Table doesn't exist | Run install_database.php |
| Duplicate entry error | Check for unique constraints (email, SKU) |
| Foreign key constraint fails | Ensure referenced record exists |
| Slow queries | Add indexes, optimize queries |

## 🎯 Performance Tips

1. **Use LIMIT** for large datasets
2. **Index frequently searched columns**
3. **Use JOIN instead of multiple queries**
4. **Avoid SELECT * when possible**
5. **Use prepared statements** (cached execution plans)
6. **Regular OPTIMIZE TABLE** maintenance

---

**Quick Reference Version:** 1.0  
**Last Updated:** 2026-01-08
