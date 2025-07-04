<?php
require_once 'db.php';

echo "<h1>Stocks Table Structure Analysis</h1>";

try {
    $conn = getDBConnection();
    
    // Check if stocks table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'stocks'");
    if ($tableCheck->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Stocks table does not exist!</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Stocks table exists</p>";
    
    // Get table structure
    $stmt = $conn->query("DESCRIBE stocks");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Current Table Structure:</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check products table structure for comparison
    echo "<h2>Products Table Structure (for comparison):</h2>";
    $stmt = $conn->query("DESCRIBE products");
    $productColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($productColumns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for data type mismatch
    $stocksProductIdType = '';
    $productsProductIdType = '';
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'product_id') {
            $stocksProductIdType = $column['Type'];
            break;
        }
    }
    
    foreach ($productColumns as $column) {
        if ($column['Field'] === 'product_id') {
            $productsProductIdType = $column['Type'];
            break;
        }
    }
    
    echo "<h2>Data Type Analysis:</h2>";
    echo "<p><strong>Stocks table product_id type:</strong> " . $stocksProductIdType . "</p>";
    echo "<p><strong>Products table product_id type:</strong> " . $productsProductIdType . "</p>";
    
    if ($stocksProductIdType !== $productsProductIdType) {
        echo "<p style='color: red;'>❌ <strong>DATA TYPE MISMATCH DETECTED!</strong></p>";
        echo "<p>The product_id columns in stocks and products tables have different data types.</p>";
        echo "<p>This is causing the error when trying to insert string product IDs into an integer column.</p>";
    } else {
        echo "<p style='color: green;'>✅ Data types match</p>";
    }
    
    // Check sample data
    echo "<h2>Sample Data Check:</h2>";
    
    // Check products table for sample product_id values
    $stmt = $conn->query("SELECT product_id, product, brand, model FROM products LIMIT 5");
    $sampleProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Sample Products:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Product ID</th><th>Product</th><th>Brand</th><th>Model</th></tr>";
    foreach ($sampleProducts as $product) {
        echo "<tr>";
        echo "<td>" . $product['product_id'] . " (" . gettype($product['product_id']) . ")</td>";
        echo "<td>" . $product['product'] . "</td>";
        echo "<td>" . $product['brand'] . "</td>";
        echo "<td>" . $product['model'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if there are any records in stocks table
    $stmt = $conn->query("SELECT COUNT(*) as count FROM stocks");
    $stocksCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "<p><strong>Records in stocks table:</strong> " . $stocksCount . "</p>";
    
    if ($stocksCount > 0) {
        $stmt = $conn->query("SELECT product_id, purchase_price, date_of_purchase, quantity_sold FROM stocks LIMIT 5");
        $sampleStocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Sample Stocks:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Product ID</th><th>Purchase Price</th><th>Date</th><th>Quantity Sold</th></tr>";
        foreach ($sampleStocks as $stock) {
            echo "<tr>";
            echo "<td>" . $stock['product_id'] . " (" . gettype($stock['product_id']) . ")</td>";
            echo "<td>" . $stock['purchase_price'] . "</td>";
            echo "<td>" . $stock['date_of_purchase'] . "</td>";
            echo "<td>" . $stock['quantity_sold'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 