-- Create stock_movements table to track all inventory movements
CREATE TABLE IF NOT EXISTS stock_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id VARCHAR(50) NOT NULL,
    movement_type ENUM('IN', 'OUT') NOT NULL,
    quantity INT NOT NULL,
    previous_stock INT NOT NULL,
    new_stock INT NOT NULL,
    created_by VARCHAR(100) DEFAULT 'Admin User',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_movement_type (movement_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add some sample data for demonstration
INSERT INTO stock_movements (product_id, movement_type, quantity, previous_stock, new_stock, created_by, notes, created_at) 
SELECT 
    product_id,
    'IN' as movement_type,
    stock_quantity as quantity,
    0 as previous_stock,
    stock_quantity as new_stock,
    'Admin User' as created_by,
    'Initial stock - Product added to inventory' as notes,
    NOW() - INTERVAL FLOOR(RAND() * 30) DAY as created_at
FROM products 
WHERE (archived IS NULL OR archived = 0)
LIMIT 10; 