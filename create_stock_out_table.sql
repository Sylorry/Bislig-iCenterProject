-- Create stock_out table to track stock out movements
CREATE TABLE IF NOT EXISTS stock_out (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id VARCHAR(50) NOT NULL,
    quantity INT NOT NULL,
    previous_stock INT NOT NULL,
    new_stock INT NOT NULL,
    created_by VARCHAR(100) DEFAULT 'Admin User',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add some sample data for demonstration (if needed)
-- INSERT INTO stock_out (product_id, quantity, previous_stock, new_stock, created_by, notes, created_at) 
-- SELECT 
--     product_id,
--     -1 as quantity,
--     stock_quantity as previous_stock,
--     GREATEST(stock_quantity - 1, 0) as new_stock,
--     'Admin User' as created_by,
--     'Sample stock out record' as notes,
--     DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY) as created_at
-- FROM products 
-- WHERE stock_quantity > 0 
-- LIMIT 5; 