<?php
require_once 'db.php';

try {
    $pdo = getConnection();
    
    // Alter table to make purchase_price and selling_price optional with default values
    $sql = "ALTER TABLE products 
            MODIFY COLUMN purchase_price DECIMAL(10,2) DEFAULT 0,
            MODIFY COLUMN selling_price DECIMAL(10,2) DEFAULT 0";
    
    $pdo->exec($sql);
    echo "Successfully modified price fields to have default values.";
    
} catch (PDOException $e) {
    echo "Error modifying table structure: " . $e->getMessage();
}
?> 