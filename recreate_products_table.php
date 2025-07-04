<?php
require_once 'db.php';

try {
    $pdo = getConnection();
    
    // Drop existing table
    $pdo->exec("DROP TABLE IF EXISTS products");
    
    // Create new table with proper structure
    $sql = "CREATE TABLE products (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        category_id VARCHAR(50) NOT NULL,
        product_id VARCHAR(50) NOT NULL,
        product VARCHAR(100) NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        brand VARCHAR(100) NOT NULL,
        model VARCHAR(100) NOT NULL,
        storage VARCHAR(100) NOT NULL,
        status VARCHAR(20) NOT NULL,
        stock_quantity INT(11) NOT NULL DEFAULT 0,
        purchase_price DECIMAL(10,2) NULL DEFAULT 0,
        selling_price DECIMAL(10,2) NULL DEFAULT 0,
        image1 VARCHAR(255) NULL,
        image2 VARCHAR(255) NULL,
        image3 VARCHAR(255) NULL,
        image4 VARCHAR(255) NULL,
        image5 VARCHAR(255) NULL,
        image6 VARCHAR(255) NULL,
        image7 VARCHAR(255) NULL,
        image8 VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "Successfully recreated products table with proper structure.";
    
} catch (PDOException $e) {
    echo "Error recreating table: " . $e->getMessage();
}
?> 