-- Smart Pricing Database Schema
-- Adds necessary columns to the products table if they don't exist

-- 1. Add min_price column to product_prices
SET @dbname = DATABASE();
SET @tablename = "product_prices";
SET @columnname = "min_price";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE product_prices ADD COLUMN min_price DECIMAL(10,2) DEFAULT NULL"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. Add max_price column to product_prices
SET @columnname = "max_price";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE product_prices ADD COLUMN max_price DECIMAL(10,2) DEFAULT NULL"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 3. Add smart_pricing_status column to product_prices
SET @columnname = "smart_pricing_status";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE product_prices ADD COLUMN smart_pricing_status TINYINT(1) DEFAULT 0"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 4. Add index for performance
-- (Optional cleanup of potential duplicate index first could be added here, but simple ADD INDEX IF NOT EXISTS isn't standard MySQL syntax without procedure)
-- We will skip explicit index creation in this safe script to avoid errors, relying on standard usage.

SELECT 'Smart Pricing columns checked/added successfully.' as Status;
