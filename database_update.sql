-- Database Update for Reservations Table
-- This SQL will modify the reservations table to support up to 5 products per reservation

-- First, let's check if the table exists and create it if it doesn't
CREATE TABLE IF NOT EXISTS reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    email VARCHAR(255) NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    archived TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Product fields for up to 5 products
    product_id_1 VARCHAR(100) NULL,
    product_id_2 VARCHAR(100) NULL,
    product_id_3 VARCHAR(100) NULL,
    product_id_4 VARCHAR(100) NULL,
    product_id_5 VARCHAR(100) NULL,
    
    -- Product details for each product (optional, can be retrieved from products table)
    product_name_1 VARCHAR(255) NULL,
    product_name_2 VARCHAR(255) NULL,
    product_name_3 VARCHAR(255) NULL,
    product_name_4 VARCHAR(255) NULL,
    product_name_5 VARCHAR(255) NULL,
    
    product_brand_1 VARCHAR(100) NULL,
    product_brand_2 VARCHAR(100) NULL,
    product_brand_3 VARCHAR(100) NULL,
    product_brand_4 VARCHAR(100) NULL,
    product_brand_5 VARCHAR(100) NULL,
    
    product_model_1 VARCHAR(100) NULL,
    product_model_2 VARCHAR(100) NULL,
    product_model_3 VARCHAR(100) NULL,
    product_model_4 VARCHAR(100) NULL,
    product_model_5 VARCHAR(100) NULL,
    
    product_price_1 DECIMAL(10,2) NULL,
    product_price_2 DECIMAL(10,2) NULL,
    product_price_3 DECIMAL(10,2) NULL,
    product_price_4 DECIMAL(10,2) NULL,
    product_price_5 DECIMAL(10,2) NULL,
    
    -- Payment fields
    reservation_fee DECIMAL(10,2) DEFAULT 0.00,
    remaining_reservation_fee DECIMAL(10,2) DEFAULT 0.00,
    proof_of_payment VARCHAR(255) NULL,
    
    -- Product count
    product_count INT DEFAULT 1,
    
    -- Indexes for better performance
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_date (reservation_date),
    INDEX idx_archived (archived)
);

-- If the table already exists, add the new columns
ALTER TABLE reservations 
ADD COLUMN IF NOT EXISTS product_id_1 VARCHAR(100) NULL AFTER created_at,
ADD COLUMN IF NOT EXISTS product_id_2 VARCHAR(100) NULL AFTER product_id_1,
ADD COLUMN IF NOT EXISTS product_id_3 VARCHAR(100) NULL AFTER product_id_2,
ADD COLUMN IF NOT EXISTS product_id_4 VARCHAR(100) NULL AFTER product_id_3,
ADD COLUMN IF NOT EXISTS product_id_5 VARCHAR(100) NULL AFTER product_id_4,
ADD COLUMN IF NOT EXISTS product_name_1 VARCHAR(255) NULL AFTER product_id_5,
ADD COLUMN IF NOT EXISTS product_name_2 VARCHAR(255) NULL AFTER product_name_1,
ADD COLUMN IF NOT EXISTS product_name_3 VARCHAR(255) NULL AFTER product_name_2,
ADD COLUMN IF NOT EXISTS product_name_4 VARCHAR(255) NULL AFTER product_name_3,
ADD COLUMN IF NOT EXISTS product_name_5 VARCHAR(255) NULL AFTER product_name_4,
ADD COLUMN IF NOT EXISTS product_brand_1 VARCHAR(100) NULL AFTER product_name_5,
ADD COLUMN IF NOT EXISTS product_brand_2 VARCHAR(100) NULL AFTER product_brand_1,
ADD COLUMN IF NOT EXISTS product_brand_3 VARCHAR(100) NULL AFTER product_brand_2,
ADD COLUMN IF NOT EXISTS product_brand_4 VARCHAR(100) NULL AFTER product_brand_3,
ADD COLUMN IF NOT EXISTS product_brand_5 VARCHAR(100) NULL AFTER product_brand_4,
ADD COLUMN IF NOT EXISTS product_model_1 VARCHAR(100) NULL AFTER product_brand_5,
ADD COLUMN IF NOT EXISTS product_model_2 VARCHAR(100) NULL AFTER product_model_1,
ADD COLUMN IF NOT EXISTS product_model_3 VARCHAR(100) NULL AFTER product_model_2,
ADD COLUMN IF NOT EXISTS product_model_4 VARCHAR(100) NULL AFTER product_model_3,
ADD COLUMN IF NOT EXISTS product_model_5 VARCHAR(100) NULL AFTER product_model_4,
ADD COLUMN IF NOT EXISTS product_price_1 DECIMAL(10,2) NULL AFTER product_model_5,
ADD COLUMN IF NOT EXISTS product_price_2 DECIMAL(10,2) NULL AFTER product_price_1,
ADD COLUMN IF NOT EXISTS product_price_3 DECIMAL(10,2) NULL AFTER product_price_2,
ADD COLUMN IF NOT EXISTS product_price_4 DECIMAL(10,2) NULL AFTER product_price_3,
ADD COLUMN IF NOT EXISTS product_price_5 DECIMAL(10,2) NULL AFTER product_price_4,
ADD COLUMN IF NOT EXISTS reservation_fee DECIMAL(10,2) DEFAULT 0.00 AFTER product_price_5,
ADD COLUMN IF NOT EXISTS remaining_reservation_fee DECIMAL(10,2) DEFAULT 0.00 AFTER reservation_fee,
ADD COLUMN IF NOT EXISTS proof_of_payment VARCHAR(255) NULL AFTER remaining_reservation_fee,
ADD COLUMN IF NOT EXISTS product_count INT DEFAULT 1 AFTER proof_of_payment;

-- Add indexes if they don't exist
CREATE INDEX IF NOT EXISTS idx_email ON reservations(email);
CREATE INDEX IF NOT EXISTS idx_status ON reservations(status);
CREATE INDEX IF NOT EXISTS idx_date ON reservations(reservation_date);
CREATE INDEX IF NOT EXISTS idx_archived ON reservations(archived);

-- Migration script to move existing single-product reservations to new structure
-- This will help if you have existing data

-- First, backup existing data (optional but recommended)
-- CREATE TABLE reservations_backup AS SELECT * FROM reservations;

-- Update existing records to use the new structure
-- This assumes existing records have a single product_id column
-- UPDATE reservations 
-- SET product_id_1 = product_id,
--     product_count = 1
-- WHERE product_id_1 IS NULL AND product_id IS NOT NULL;

-- Note: After running this migration, you may want to drop the old product_id column
-- ALTER TABLE reservations DROP COLUMN IF EXISTS product_id;

-- Sample queries for the new structure:

-- 1. Insert a reservation with multiple products
/*
INSERT INTO reservations (
    name, contact_number, address, email, reservation_date, reservation_time,
    product_id_1, product_id_2, product_id_3,
    product_name_1, product_name_2, product_name_3,
    product_brand_1, product_brand_2, product_brand_3,
    product_model_1, product_model_2, product_model_3,
    product_price_1, product_price_2, product_price_3,
    reservation_fee, remaining_reservation_fee, product_count
) VALUES (
    'John Doe', '09123456789', '123 Main St', 'john@example.com', CURDATE(), CURTIME(),
    'IPHONE_XR_128GB', 'IPAD_AIR_M2', 'MACBOOK_AIR_M2',
    'iPhone XR', 'iPad Air M2', 'MacBook Air M2',
    'Apple', 'Apple', 'Apple',
    'XR', 'Air M2', 'Air M2',
    25000.00, 35000.00, 45000.00,
    1500.00, 105000.00, 3
);
*/

-- 2. Query to get all products for a reservation
/*
SELECT 
    reservation_id,
    name,
    product_id_1, product_name_1, product_brand_1, product_model_1, product_price_1,
    product_id_2, product_name_2, product_brand_2, product_model_2, product_price_2,
    product_id_3, product_name_3, product_brand_3, product_model_3, product_price_3,
    product_id_4, product_name_4, product_brand_4, product_model_4, product_price_4,
    product_id_5, product_name_5, product_brand_5, product_model_5, product_price_5,
    product_count,
    reservation_fee,
    remaining_reservation_fee
FROM reservations 
WHERE reservation_id = ?;
*/

-- 3. Query to get total value of all products in a reservation
/*
SELECT 
    reservation_id,
    name,
    (COALESCE(product_price_1, 0) + 
     COALESCE(product_price_2, 0) + 
     COALESCE(product_price_3, 0) + 
     COALESCE(product_price_4, 0) + 
     COALESCE(product_price_5, 0)) as total_value,
    reservation_fee,
    remaining_reservation_fee
FROM reservations;
*/ 