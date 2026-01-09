# WalkOn Shoes - Database Setup Guide

## 📋 Overview
This guide will help you set up the MySQL database for the WalkOn Shoes platform.

## 🗄️ Database Information
- **Database Name:** `walkon_shoes`
- **Host:** `localhost`
- **Username:** `root` (default XAMPP)
- **Password:** `` (empty by default)

## 🚀 Quick Setup (Recommended)

### Method 1: Using the PHP Installation Script
This is the easiest method with a visual interface.

1. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL** services

2. **Run the Installation Script**
   - Open your browser
   - Navigate to: `http://localhost/MINIPROJECT2.0/install_database.php`
   - The script will automatically create the database and all tables
   - Follow the on-screen instructions

3. **Done!**
   - Once you see the success message, your database is ready
   - Click "Go to Application" to start using WalkOn

### Method 2: Using phpMyAdmin
If you prefer manual setup:

1. **Start XAMPP**
   - Start Apache and MySQL services

2. **Open phpMyAdmin**
   - Navigate to: `http://localhost/phpmyadmin`

3. **Import SQL File**
   - Click on "Import" tab
   - Click "Choose File"
   - Select `walkon_database.sql` from your project folder
   - Click "Go" at the bottom
   - Wait for the import to complete

4. **Verify**
   - You should see `walkon_shoes` database in the left sidebar
   - Click on it to see all the tables

### Method 3: Using MySQL Command Line

1. **Open Command Prompt**
   - Navigate to XAMPP MySQL bin folder:
   ```bash
   cd C:\xampp\mysql\bin
   ```

2. **Login to MySQL**
   ```bash
   mysql -u root -p
   ```
   (Press Enter when asked for password if you haven't set one)

3. **Run the SQL File**
   ```sql
   source C:\xampp\htdocs\MINIPROJECT2.0\walkon_database.sql
   ```

4. **Verify Database**
   ```sql
   SHOW DATABASES;
   USE walkon_shoes;
   SHOW TABLES;
   ```

## 📊 Database Structure

The database includes the following tables:

### Core Tables
1. **users** - User account information
   - Stores login credentials, verification status
   - Includes password reset functionality

2. **sellers** - Seller/business profiles
   - Business information and contact details
   - Linked to user accounts

3. **products** - Product listings
   - Product details, pricing, inventory
   - Multi-channel sync information
   - Smart pricing settings

### Supporting Tables
4. **orders** - Order management
   - Customer orders across all channels
   - Order status tracking

5. **sync_logs** - Synchronization tracking
   - Logs all channel sync activities
   - Error tracking and debugging

6. **notifications** - User notifications
   - System notifications
   - Activity alerts

7. **pricing_history** - Price tracking
   - Historical price data
   - Analytics support

8. **analytics** - Business metrics
   - Sales data
   - Performance metrics

9. **api_credentials** - Channel integrations
   - API keys for various platforms
   - OAuth tokens

## 🔧 Configuration

After database setup, verify your `config.php` file:

```php
<?php
$host = 'localhost';
$db   = 'walkon_shoes';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

## 📝 Sample Data

The database includes sample data:
- 2 sample users (john.doe@example.com, jane.smith@example.com)
- 2 sample sellers
- 3 sample products
- Default password: `password` (hashed)

**Note:** For security, change or remove sample data in production.

## ✅ Verification Checklist

After setup, verify:
- [ ] Database `walkon_shoes` exists
- [ ] All 9 tables are created
- [ ] You can access the application at `http://localhost/MINIPROJECT2.0/Index.php`
- [ ] You can create a new account
- [ ] You can login successfully

## 🐛 Troubleshooting

### Error: "Connection failed"
- **Solution:** Make sure MySQL is running in XAMPP
- Check if port 3306 is not blocked by firewall

### Error: "Database already exists"
- **Solution:** The database is already set up. You can:
  - Drop the existing database and recreate it
  - Or skip this step and use the existing database

### Error: "Access denied"
- **Solution:** Check your MySQL username and password in `config.php`
- Default XAMPP credentials: username=`root`, password=`` (empty)

### Tables not showing up
- **Solution:** 
  - Refresh phpMyAdmin
  - Check if the SQL script ran completely
  - Look for error messages in the import log

## 🔒 Security Recommendations

1. **Change Default Passwords**
   - Set a password for MySQL root user
   - Update `config.php` accordingly

2. **Remove Installation Files**
   - Delete `install_database.php` after setup
   - Delete `walkon_database.sql` from public directory

3. **Backup Regularly**
   - Export database regularly from phpMyAdmin
   - Store backups in a secure location

## 📞 Support

If you encounter any issues:
1. Check the troubleshooting section above
2. Verify XAMPP services are running
3. Check PHP error logs in `C:\xampp\apache\logs\error.log`
4. Ensure you're using PHP 7.4 or higher

## 🎉 Next Steps

Once your database is set up:
1. Navigate to `http://localhost/MINIPROJECT2.0/Index.php`
2. Create a new account or use sample credentials
3. Start adding products and managing your inventory
4. Explore multi-channel sync features

---

**WalkOn Shoes** - Simplifying Multi-Channel E-commerce
