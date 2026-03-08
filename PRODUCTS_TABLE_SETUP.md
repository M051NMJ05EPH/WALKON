# 📦 Products Table Setup Guide

## Quick Setup Instructions

### Method 1: Using phpMyAdmin (Recommended)

1. **Open phpMyAdmin**
   - Navigate to: `http://localhost/phpmyadmin`
   - Login with your credentials (default: username=`root`, password=empty)

2. **Select Database**
   - Click on `walkon_shoes` database in the left sidebar

3. **Import SQL File**
   - Click on the **SQL** tab at the top
   - Copy the contents of `add_products_table.sql`
   - Paste into the SQL query box
   - Click **Go** button

4. **Verify**
   - Click on `walkon_shoes` database
   - You should see the `products` table listed
   - Click on the table to see 5 sample products

### Method 2: Using MySQL Command Line

```bash
# Navigate to MySQL bin directory
cd C:\xampp\mysql\bin

# Login to MySQL
mysql -u root -p

# Run the SQL file
source C:\xampp\htdocs\MINIPROJECT2.0\add_products_table.sql
```

## 📊 Products Table Structure

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT | Primary key, auto-increment |
| `seller_id` | INT | Foreign key to sellers table |
| `product_name` | VARCHAR(255) | Product name |
| `name` | VARCHAR(255) | Alias for product_name |
| `sku` | VARCHAR(100) | Unique product identifier |
| `description` | TEXT | Product description |
| `category` | VARCHAR(100) | Product category |
| `price` | DECIMAL(10,2) | Current selling price |
| `min_price` | DECIMAL(10,2) | Minimum allowed price |
| `max_price` | DECIMAL(10,2) | Maximum allowed price |
| `quantity` | INT | Available stock |
| `stock` | INT | Alias for quantity |
| `channels` | TEXT | Comma-separated sales channels |
| `images` | TEXT | JSON array of image URLs |
| `image_url` | VARCHAR(500) | Primary product image |
| `status` | ENUM | active/inactive/out_of_stock |
| `smart_pricing_status` | BOOLEAN | Smart pricing enabled |
| `views` | INT | Product view count |
| `sales` | INT | Total sales count |
| `created_at` | TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | Last update timestamp |

## 🎯 Sample Products Included

The setup includes 5 sample products:

1. **Nike Air Max 270** - Running Shoes ($129.99)
2. **Adidas Ultraboost 22** - Running Shoes ($180.00)
3. **Puma RS-X Sneakers** - Casual Shoes ($110.00)
4. **New Balance 990v5** - Running Shoes ($175.00)
5. **Converse Chuck Taylor All Star** - Casual Shoes ($55.00)

## 🔍 Verify Your Setup

Run this query in phpMyAdmin SQL tab:

```sql
SELECT 
    id, 
    product_name, 
    sku, 
    price, 
    quantity, 
    status 
FROM products;
```

You should see 5 products listed.

## 🚀 Next Steps

1. **View Products**
   - Navigate to: `http://localhost/MINIPROJECT2.0/products.php`
   - You should see your products displayed

2. **Add More Products**
   - Use the "Add New Listing" feature in the dashboard
   - Or manually insert via phpMyAdmin

3. **Customize Sample Data**
   - Update product names, prices, and images
   - Add your own product categories

## 💡 Tips

- **SKU Format**: Use a consistent format like `BRAND-MODEL-COLOR-SIZE`
- **Images**: Replace placeholder images with real product photos
- **Channels**: Update the channels field based on where you sell
- **Smart Pricing**: Enable for products you want to auto-adjust pricing

## 🐛 Troubleshooting

### Error: "Table already exists"
- The products table is already created
- You can skip this step or drop the table first:
  ```sql
  DROP TABLE IF EXISTS products;
  ```

### Error: "Foreign key constraint fails"
- Make sure the `sellers` table exists first
- Verify you have at least one seller with id=1 and id=2

### Products not showing in products.php
- Check that `db_connect.php` is configured correctly
- Verify the table name matches in your PHP code
- Clear browser cache and refresh

---

**Ready to manage your shoe inventory!** 👟
