<?php
require_once 'db.php';

try {
    $pdo = getConnection();
    
    // Create stock_movements table
    $create_table_sql = "
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
        INDEX idx_product_id (product_id),
        INDEX idx_movement_type (movement_type),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($create_table_sql);
    echo "✓ Stock movements table created successfully!<br>";
    
    // Check if table was created
    $table_check = $pdo->query("SHOW TABLES LIKE 'stock_movements'");
    if ($table_check->rowCount() > 0) {
        echo "✓ Table 'stock_movements' exists in the database.<br>";
        
        // Get table structure
        $structure = $pdo->query("DESCRIBE stock_movements");
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $structure->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "✗ Error: Table was not created successfully.<br>";
    }
    
} catch (PDOException $e) {
    echo "Error creating stock_movements table: " . $e->getMessage();
}
?> 