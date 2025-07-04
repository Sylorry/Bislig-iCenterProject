<?php
// This script checks database connection and table structure
require_once 'db.php';

echo "<h1>Database Connection Test</h1>";

try {
    // Test basic database connection
    $test = $conn->query("SELECT 1")->fetchColumn();
    echo "<p style='color: green; font-weight: bold;'>✓ Database connection successful!</p>";
    
    // Check if products table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'products'");
    $tableExists = $tableCheck->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p style='color: green; font-weight: bold;'>✓ Products table exists</p>";
        
        // Check table structure
        $columns = $conn->query("DESCRIBE products");
        $columnData = $columns->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Products Table Structure:</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        
        foreach ($columnData as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Count products
        $productCount = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
        echo "<p>Total products in database: <strong>$productCount</strong></p>";
        
        if ($productCount > 0) {
            // Show a sample product
            $sampleProduct = $conn->query("SELECT * FROM products LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            
            echo "<h2>Sample Product Record:</h2>";
            echo "<pre>";
            print_r($sampleProduct);
            echo "</pre>";
        }
    } else {
        echo "<p style='color: orange; font-weight: bold;'>⚠ Products table does not exist! It will be created automatically when adding a product.</p>";
        
        // Create the table
        echo "<h2>Creating products table...</h2>";
        
        $conn->exec("CREATE TABLE IF NOT EXISTS products (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            category_id INT(11) NOT NULL,
            product_code VARCHAR(50) NOT NULL UNIQUE,
            brand VARCHAR(100) NOT NULL,
            model VARCHAR(100) NOT NULL,
            storage VARCHAR(100) NOT NULL,
            status VARCHAR(20) NOT NULL,
            stock_quantity INT(11) NOT NULL DEFAULT 0,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
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
        )");
        
        echo "<p style='color: green; font-weight: bold;'>✓ Products table created successfully!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red; font-weight: bold;'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Display more detailed connection information
    echo "<h2>Connection Details:</h2>";
    echo "<pre>";
    echo "PHP Version: " . phpversion() . "\n";
    echo "PDO Drivers: ";
    print_r(PDO::getAvailableDrivers());
    echo "</pre>";
    
    echo "<p>Please check your db.php file to ensure correct database credentials:</p>";
    echo "<ul>";
    echo "<li>Database host</li>";
    echo "<li>Database name</li>";
    echo "<li>Username</li>";
    echo "<li>Password</li>";
    echo "</ul>";
}
?>